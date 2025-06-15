<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'lokasi',
        'tanggal_pemeliharaan',
        'deskripsi_pemeliharaan',
        'catatan_tindak_lanjut',
        'photos',
        'status',
        'created_by'
    ];

    protected $casts = [
        'tanggal_pemeliharaan' => 'date',
        'photos' => 'array'
    ];

    public static function generateId()
    {
        // Get current month and year
        $Year = date('Ymd'); // 20250317 for March 2025
        
        // Get the latest sequence number for this month and role
        $basePattern = "LP-{$Year}-";
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
        
        // Format sequence with leading zeros (3 digits)
        $sequenceFormatted = str_pad($newSequence, 2, '0', STR_PAD_LEFT);
        
        return $basePattern . $sequenceFormatted;
    }
}