<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DamagedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'damage_id',
        'asset_id',
        'tingkat_kerusakan',
        'estimasi_biaya',
        'estimasi_waktu_perbaikan',
        'deskripsi_kerusakan',
        'tanggal_pelaporan',
        'pelapor',
        'reporter_name',
        'reporter_role',
        'damaged_image',
        'petugas',
        'status',
        'id_laporan',
        'alasan_penolakan',
        'reviewer',
        'verification_id',
        'verified',
        'verified_at',
        'validated',
        'validated_at',
        'validation_id'
    ];

    protected $casts = [
        'tanggal_pelaporan' => 'datetime',
        'estimasi_waktu_perbaikan' => 'datetime',
        'verified_at' => 'datetime',
        'validated_at' => 'datetime',
        'estimasi_biaya' => 'decimal:2',
        'additional_criteria' => 'array'
    ];

    /**
     * Get the asset associated with this damage
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'asset_id');
    }

    public function assetMonitoring()
    {
        return $this->belongsTo(AssetMonitoring::class, 'id_laporan', 'id_laporan');
    }

    /**
     * Get the maintenance asset associated with this damaged asset
     * FIXED: Use damage_id as the foreign key
     */
    public function maintenanceAsset()
    {
        return $this->hasOne(MaintenanceAsset::class, 'damage_id', 'damage_id');
    }

    /**
     * Check if this damaged asset has a maintenance request
     */
    public function hasMaintenanceRequest()
    {
        return $this->maintenanceAsset()->exists();
    }

    /**
     * Scope for damaged assets without maintenance requests
     */
    public function scopeWithoutMaintenance($query)
    {
        return $query->whereDoesntHave('maintenanceAsset');
    }

    /**
     * Scope for damaged assets with maintenance requests
     */
    public function scopeWithMaintenance($query)
    {
        return $query->whereHas('maintenanceAsset');
    }

    public static function generateIdLaporan($user = null)
    {
        // Get current month and year
        $monthYear = date('Ym'); // 202506 for June 2025
        
        // Determine role code based on user's role
        $roleCode = 'GEN'; // Default
        
        if ($user) {
            $userRoles = $user->getRoleNames(); // Get all role names
            $primaryRole = $userRoles->first(); // Get the first (primary) role
            
            $roleCode = match($primaryRole) {
                'staff_laboratorium' => 'LAB',
                'staff_logistik' => 'LOG',
                'kaur_laboratorium' => 'KLAB',
                'kaur_keuangan_logistik_sdm' => 'KKEU',
                'wakil_dekan_2' => 'WD2',
                'staff_keuangan' => 'SKEU',
                default => 'GEN'
            };
        }
        
        // Use a do-while loop to ensure uniqueness
        do {
            // Get the latest sequence number for this month
            $basePattern = "LR-{$monthYear}-";
            $latestReport = self::where('damage_id', 'LIKE', $basePattern . '%')
                               ->orderBy('damage_id', 'desc')
                               ->first();
            
            if ($latestReport) {
                // Extract the sequence number from the damage_id (not id_laporan)
                $lastSequence = intval(substr($latestReport->damage_id, strlen($basePattern)));
                $newSequence = $lastSequence + 1;
            } else {
                $newSequence = 1;
            }
            
            // Format sequence with leading zeros (3 digits)
            $sequenceFormatted = str_pad($newSequence, 3, '0', STR_PAD_LEFT);
            
            $newDamageId = $basePattern . $sequenceFormatted;
            
            // Check if this ID already exists
            $exists = self::where('damage_id', $newDamageId)->exists();
            
        } while ($exists);
        
        return $newDamageId;
    }

    public static function generateVerId()
    {
        // Get current month and year
        $Year = date('Y'); // 032025 for March 2025
        
        // Get the latest sequence number for this month and role
        $basePattern = "VER-{$Year}-";
        $latestReport = self::where('verification_id', 'LIKE', $basePattern . '%')
                           ->orderBy('verification_id', 'desc')
                           ->first();
        
        if ($latestReport) {
            // Extract the sequence number from the last report ID
            $lastSequence = intval(substr($latestReport->verification_id, strlen($basePattern)));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        // Format sequence with leading zeros (3 digits)
        $sequenceFormatted = str_pad($newSequence, 2, '0', STR_PAD_LEFT);
        
        return $basePattern . $sequenceFormatted;
    }


    public static function generateValId()
    {
        // Get current month and year
        $Year = date('Y'); // 032025 for March 2025
        
        // Get the latest sequence number for this month and role
        $basePattern = "VAL-{$Year}-";
        $latestReport = self::where('validation_id', 'LIKE', $basePattern . '%')
                           ->orderBy('validation_id', 'desc')
                           ->first();
        
        if ($latestReport) {
            // Extract the sequence number from the last report ID
            $lastSequence = intval(substr($latestReport->validation_id, strlen($basePattern)));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        // Format sequence with leading zeros (3 digits)
        $sequenceFormatted = str_pad($newSequence, 2, '0', STR_PAD_LEFT);
        
        return $basePattern . $sequenceFormatted;
    }
}