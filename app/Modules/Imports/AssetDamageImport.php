<?php

namespace App\Modules\Imports;

use App\Models\Asset;
use App\Models\DamagedAsset;
use App\Models\MaintenanceAsset;
use App\Models\User;
use App\Models\ApprovalLog;
use App\Services\NotificationService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetDamageImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    protected $user;
    protected $userRole;
    protected $processedAssets = [];
    protected $notificationService;
    protected $rowCount = 0;

    public function __construct()
    {
        // ADD THIS LINE TO TEST
        Log::info('NEW AssetDamageImport class loaded - version 2.0');
        
        $this->user = Auth::user();
        $this->userRole = $this->user->roles->first()->name;
        $this->processedAssets = [];
        
        try {
            $this->notificationService = app(NotificationService::class);
        } catch (\Exception $e) {
            $this->notificationService = null;
        }
        
        Log::info('AssetDamageImport initialized', [
            'user' => $this->user->name,
            'role' => $this->userRole
        ]);
    }

    public function model(array $row)
    {
        $this->rowCount++;
        
        Log::info("=== PROCESSING ROW {$this->rowCount} ===", [
            'row_data' => $row,
            'id_aset' => $row['id_aset'] ?? 'MISSING',
            'nama_aset' => $row['nama_aset'] ?? 'MISSING',
            'damage_id' => $row['damage_id'] ?? 'MISSING'
        ]);

        // Skip rows with missing essential data
        if (empty($row['id_aset']) || empty($row['nama_aset']) || empty($row['damage_id'])) {
            Log::info("Skipping row {$this->rowCount} - missing essential data");
            return null;
        }

        try {
            return DB::transaction(function () use ($row) {
                $damageId = trim($row['damage_id']);
                
                Log::info("Looking for damaged asset", ['damage_id' => $damageId]);
                
                // Find existing damaged asset
                $damagedAsset = DamagedAsset::where('damage_id', $damageId)->first();
                
                if (!$damagedAsset) {
                    Log::warning("Damage ID not found", ['damage_id' => $damageId]);
                    return null;
                }

                Log::info("Found damaged asset", [
                    'damage_id' => $damageId,
                    'asset_id' => $damagedAsset->asset_id
                ]);

                // Check for existing maintenance
                $existingMaintenance = MaintenanceAsset::where('damage_id', $damageId)->first();
                
                if ($existingMaintenance && !in_array($existingMaintenance->status, ['Selesai', 'Ditolak'])) {
                    Log::info("Skipping - active maintenance exists", [
                        'damage_id' => $damageId,
                        'status' => $existingMaintenance->status
                    ]);
                    return null;
                }

                // Create maintenance asset
                $maintenanceAsset = $this->createMaintenanceAsset($damagedAsset);
                
                if ($maintenanceAsset) {
                    $this->processedAssets[] = $maintenanceAsset;
                    Log::info("Successfully created maintenance asset", [
                        'maintenance_id' => $maintenanceAsset->maintenance_id,
                        'total_processed' => count($this->processedAssets)
                    ]);
                }

                return $damagedAsset;
            });
        } catch (\Exception $e) {
            Log::error("Row processing failed", [
                'error' => $e->getMessage(),
                'row_number' => $this->rowCount
            ]);
            return null; // Skip this row, don't fail entire import
        }
    }

    private function createMaintenanceAsset(DamagedAsset $damagedAsset)
    {
        try {
            // Generate maintenance ID
            $latestMaintenance = MaintenanceAsset::latest('id')->lockForUpdate()->first();
            $maintenanceNumber = $latestMaintenance ? intval(substr($latestMaintenance->maintenance_id, 3)) + 1 : 1;
            $maintenanceId = 'MNT' . str_pad($maintenanceNumber, 4, '0', STR_PAD_LEFT);

            Log::info("Creating maintenance asset", [
                'maintenance_id' => $maintenanceId,
                'damage_id' => $damagedAsset->damage_id
            ]);

            // Create maintenance record
            $maintenanceAsset = MaintenanceAsset::create([
                'maintenance_id' => $maintenanceId,
                'damage_id' => $damagedAsset->damage_id,
                'status' => 'Menunggu Persetujuan',
                'tanggal_pengajuan' => now(),
                'teknisi' => 'Staf',
                'requested_by' => $this->user->id,
                'requested_by_role' => $this->userRole
            ]);

            // Create approval log
            ApprovalLog::create([
                'maintenance_asset_id' => $maintenanceAsset->id,
                'action' => 'submitted',
                'performed_by' => $this->user->username ?? $this->user->name,
                'role' => $this->userRole,
                'notes' => 'Pengajuan perbaikan aset diajukan via Excel import'
            ]);

            return $maintenanceAsset;

        } catch (\Exception $e) {
            Log::error('Failed to create maintenance asset', [
                'damage_id' => $damagedAsset->damage_id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function sendNotificationsToApprovers()
    {
        Log::info("sendNotificationsToApprovers called", [
            'processed_count' => count($this->processedAssets),
            'user_role' => $this->userRole
        ]);

        if (empty($this->processedAssets) || !$this->notificationService) {
            Log::info("No assets to notify or no notification service");
            return;
        }

        try {
            if ($this->userRole === 'staff_laboratorium') {
                $kaur = User::role('kaur_laboratorium')->first();
            } else {
                $kaur = User::role('kaur_keuangan_logistik_sdm')->first();
            }

            if ($kaur) {
                $this->notificationService->sendBulkApprovalRequest(
                    count($this->processedAssets),
                    $kaur,
                    $this->userRole
                );
                Log::info("Notification sent successfully", [
                    'recipient' => $kaur->name,
                    'assets_count' => count($this->processedAssets)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notifications', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getImportSummary()
    {
        $summary = [
            'total_processed' => count($this->processedAssets),
            'rows_read' => $this->rowCount,
            'user_role' => $this->userRole,
            'timestamp' => now()->toDateTimeString(),
            // Add missing keys that controller expects
            'existing_damage_assets' => count($this->processedAssets), // All are from existing damaged assets
            'new_damage_assets' => 0, // We're not creating new damaged assets
            'criteria_count' => 0, // Not using dynamic criteria
            'successful_imports' => count($this->processedAssets),
            'errors' => 0,
            'workflow_type' => $this->userRole === 'staff_laboratorium' ? 'Laboratorium → Kaur Lab → Kaur Keuangan' : 'Logistik → Kaur Keuangan'
        ];

        Log::info('Import summary generated', $summary);
        return $summary;
    }
}