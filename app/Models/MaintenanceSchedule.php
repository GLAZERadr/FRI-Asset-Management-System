<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'asset_id',
        'tanggal_pemeliharaan',
        'jenis_pemeliharaan',
        'alasan_penjadwalan',
        'status',
        'penanggung_jawab',
        'catatan_tambahan',
        'auto_generated',
        'deskripsi_pemeliharaan',
        'catatan_tindak_lanjut',
        'created_by'
    ];

    protected $casts = [
        'tanggal_pemeliharaan' => 'date',
        'auto_generated' => 'boolean'
    ];

    /**
     * Get the asset associated with this maintenance schedule
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'asset_id');
    }

    /**
     * Get previous maintenance notes for this asset
     */
    public function getPreviousMaintenanceNotes()
    {
        return self::where('asset_id', $this->asset_id)
                   ->where('id', '!=', $this->id)
                   ->whereNotNull('catatan_tambahan')
                   ->orderBy('tanggal_pemeliharaan', 'desc')
                   ->take(3)
                   ->pluck('catatan_tambahan');
    }

    /**
     * Generate unique schedule ID
     */
    public static function generateId()
    {
        // Get current date
        $date = date('Ymd'); // 20250317 for March 17, 2025
        
        // Get the latest sequence number for this date
        $basePattern = "LP-{$date}-";
        $latestReport = self::where('schedule_id', 'LIKE', $basePattern . '%')
                           ->orderBy('schedule_id', 'desc')
                           ->first();
        
        if ($latestReport) {
            // Extract the sequence number from the last report ID
            $lastSequence = intval(substr($latestReport->schedule_id, strlen($basePattern)));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        // Format sequence with leading zeros (2 digits)
        $sequenceFormatted = str_pad($newSequence, 2, '0', STR_PAD_LEFT);
        
        return $basePattern . $sequenceFormatted;
    }

    /**
     * Auto-generate maintenance schedules for frequently damaged assets
     * Rule: If asset_id appears more than 3 times in last 2 months in damaged_assets
     */
    public static function autoGenerateFromDamageReports()
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        $generatedCount = 0;

        // Get assets that have been damaged more than 3 times in last 2 months
        $frequentlyDamagedAssets = DamagedAsset::where('tanggal_pelaporan', '>=', $twoMonthsAgo)
            ->groupBy('asset_id')
            ->havingRaw('COUNT(*) > 3')
            ->pluck('asset_id');

        foreach ($frequentlyDamagedAssets as $assetId) {
            // Check if there's already a scheduled/auto-generated maintenance for this asset
            $existingSchedule = self::where('asset_id', $assetId)
                ->where('status', 'Dijadwalkan')
                ->where('tanggal_pemeliharaan', '>=', Carbon::now())
                ->exists();

            if (!$existingSchedule) {
                // Get the asset details
                $asset = Asset::where('asset_id', $assetId)->first();
                
                if ($asset) {
                    // Get damage count for reasoning
                    $damageCount = DamagedAsset::where('asset_id', $assetId)
                        ->where('tanggal_pelaporan', '>=', $twoMonthsAgo)
                        ->count();

                    // Get latest damage types for additional context
                    $latestDamages = DamagedAsset::where('asset_id', $assetId)
                        ->where('tanggal_pelaporan', '>=', $twoMonthsAgo)
                        ->orderBy('tanggal_pelaporan', 'desc')
                        ->take(3)
                        ->pluck('tingkat_kerusakan')
                        ->unique()
                        ->implode(', ');

                    // Schedule maintenance for next week
                    $maintenanceDate = Carbon::now()->addWeek()->startOfWeek()->addDays(1); // Next Monday

                    $scheduleId = self::generateId();

                    self::create([
                        'schedule_id' => $scheduleId,
                        'asset_id' => $assetId,
                        'tanggal_pemeliharaan' => $maintenanceDate,
                        'jenis_pemeliharaan' => 'Khusus',
                        'alasan_penjadwalan' => "Sistem otomatis: Aset mengalami kerusakan {$damageCount} kali dalam 2 bulan terakhir. Tingkat kerusakan: {$latestDamages}",
                        'status' => 'Dijadwalkan',
                        'penanggung_jawab' => 'Staff Logistik',
                        'catatan_tambahan' => "Pemeliharaan preventif diperlukan karena tingginya frekuensi kerusakan. Harap periksa kondisi menyeluruh dan identifikasi penyebab utama kerusakan berulang.",
                        'auto_generated' => true,
                        'created_by' => 'System Auto-Generator'
                    ]);

                    $generatedCount++;
                }
            }
        }

        return $generatedCount;
    }

    /**
     * Get maintenance history for an asset
     */
    public static function getAssetMaintenanceHistory($assetId, $limit = 5)
    {
        return self::where('asset_id', $assetId)
                   ->with('asset')
                   ->orderBy('tanggal_pemeliharaan', 'desc')
                   ->take($limit)
                   ->get();
    }

    /**
     * Check if asset needs maintenance based on damage frequency
     */
    public static function checkAssetNeedsMaintenance($assetId)
    {
        $twoMonthsAgo = Carbon::now()->subMonths(2);
        
        $damageCount = DamagedAsset::where('asset_id', $assetId)
            ->where('tanggal_pelaporan', '>=', $twoMonthsAgo)
            ->count();

        return $damageCount > 3;
    }

    /**
     * Scope for scheduled maintenance
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'Dijadwalkan');
    }

    /**
     * Scope for completed maintenance
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'Selesai');
    }

    /**
     * Scope for auto-generated schedules
     */
    public function scopeAutoGenerated($query)
    {
        return $query->where('auto_generated', true);
    }

    /**
     * Scope for manual schedules
     */
    public function scopeManual($query)
    {
        return $query->where('auto_generated', false);
    }
}