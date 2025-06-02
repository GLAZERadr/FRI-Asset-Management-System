<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_ruangan',
        'nama_pelapor',
        'tanggal_laporan',
        'monitoring_data', // JSON field to store all asset statuses
        'user_id'
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
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
}