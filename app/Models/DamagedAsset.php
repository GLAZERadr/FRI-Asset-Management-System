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
        'vendor',
        'damaged_image',
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
        $monthYear = date('mY'); // 032025 for March 2025
        
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
        
        // Get the latest sequence number for this month and role
        $basePattern = "DMG-{$monthYear}-{$roleCode}-";
        $latestReport = self::where('id_laporan', 'LIKE', $basePattern . '%')
                           ->orderBy('id_laporan', 'desc')
                           ->first();
        
        if ($latestReport) {
            // Extract the sequence number from the last report ID
            $lastSequence = intval(substr($latestReport->id_laporan, strlen($basePattern)));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        // Format sequence with leading zeros (3 digits)
        $sequenceFormatted = str_pad($newSequence, 2, '0', STR_PAD_LEFT);
        
        return $basePattern . $sequenceFormatted;
    }

    public static function generateVerId()
    {
        // Get current month and year
        $Year = date('Y'); // 032025 for March 2025
        
        // Get the latest sequence number for this month and role
        $basePattern = "VER-{$Year}-";
        $latestReport = self::where('id_laporan', 'LIKE', $basePattern . '%')
                           ->orderBy('id_laporan', 'desc')
                           ->first();
        
        if ($latestReport) {
            // Extract the sequence number from the last report ID
            $lastSequence = intval(substr($latestReport->id_laporan, strlen($basePattern)));
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
        $latestReport = self::where('id_laporan', 'LIKE', $basePattern . '%')
                           ->orderBy('id_laporan', 'desc')
                           ->first();
        
        if ($latestReport) {
            // Extract the sequence number from the last report ID
            $lastSequence = intval(substr($latestReport->id_laporan, strlen($basePattern)));
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }
        
        // Format sequence with leading zeros (3 digits)
        $sequenceFormatted = str_pad($newSequence, 2, '0', STR_PAD_LEFT);
        
        return $basePattern . $sequenceFormatted;
    }
}