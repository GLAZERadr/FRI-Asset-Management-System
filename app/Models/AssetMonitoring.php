<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AssetMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_laporan',
        'kode_ruangan',
        'nama_pelapor',
        'reviewer',
        'tanggal_laporan',
        'validated',
        'validated_at',
        'catatan',
        'monitoring_data',
        'user_id'
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'validated_at' => 'datetime',
        'monitoring_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Get all assets that were monitored in this report
    public function getMonitoredAssets()
    {
        if (!$this->monitoring_data) {
            return collect();
        }

        $assetIds = collect($this->monitoring_data)->pluck('asset_id');
        return Asset::whereIn('asset_id', $assetIds)->get();
    }

    // Get assets in the same room (kode_ruangan)
    public function roomAssets()
    {
        return Asset::where('kode_ruangan', $this->kode_ruangan)->get();
    }

    /**
     * Generate unique report ID based on user division
     * Format: LAP-MMYYYY-DIV-XXX
     * Example: LAP-032025-LAB-001, LAP-032025-LOG-001
     */
    public static function generateIdLaporan($user = null)
    {
        // Get current month and year
        $monthYear = date('mY'); // 032025 for March 2025
        
        // Determine role code based on user's role
        $roleCode = 'LOG'; // Default
        
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
        $basePattern = "LAP-{$monthYear}-{$roleCode}-";
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
        $sequenceFormatted = str_pad($newSequence, 3, '0', STR_PAD_LEFT);
        
        return $basePattern . $sequenceFormatted;
    }

    public static function generateIdLaporanCS()
    {
        // Get current month and year
        $monthYear = date('mY'); // 032025 for March 2025
        
        // Get the latest sequence number for this month and role
        $basePattern = "LAP-{$monthYear}-LOG-";
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
        $sequenceFormatted = str_pad($newSequence, 3, '0', STR_PAD_LEFT);
        
        return $basePattern . $sequenceFormatted;
    }

    public function getRoleDisplayName()
    {
        if (!$this->id_laporan) {
            return 'Unknown';
        }
        
        // Extract role code from id_laporan (LAP-032025-LAB-001 -> LAB)
        $parts = explode('-', $this->id_laporan);
        if (count($parts) >= 3) {
            $roleCode = $parts[2];
            
            return match($roleCode) {
                'LAB' => 'Staff Laboratorium',
                'LOG' => 'Staff Logistik',
                'KLAB' => 'Kaur Laboratorium',
                'KKEU' => 'Kaur Keuangan',
                'WD2' => 'Wakil Dekan 2',
                'SKEU' => 'Staff Keuangan',
                'GEN' => 'General',
                default => 'Unknown'
            };
        }
        
        return 'Unknown';
    }
}