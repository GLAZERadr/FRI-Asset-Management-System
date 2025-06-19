<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'nama_asset',
        'lokasi',
        'tingkat_kepentingan_asset',
        'kategori',
        'spesifikasi',
        'kode_ruangan',
        'tgl_perolehan',
        'masa_pakai_maksimum',
        'masa_pakai_duration',
        'masa_pakai_unit',
        'nilai_perolehan',
        'sumber_perolehan',
        'status_kelayakan',
        'vendor',
        'foto_asset'
    ];

    protected $casts = [
        'tgl_perolehan' => 'datetime',
        'masa_pakai_maksimum' => 'datetime',
        'nilai_perolehan' => 'decimal:2',
    ];

    public function damagedAssets()
    {
        return $this->hasMany(DamagedAsset::class, 'asset_id', 'asset_id');
    }

    public function maintenanceAssets()
    {
        return $this->hasMany(MaintenanceAsset::class, 'asset_id', 'asset_id');
    }

    public function requestedAssets()
    {
        return $this->hasMany(RequestedAsset::class, 'asset_id', 'asset_id');
    }

    public function monitoringReports()
    {
        return AssetMonitoring::whereJsonContains('monitoring_data', function ($query) {
            $query->where('asset_id', $this->asset_id);
        })->get();
    }
    
    /**
     * Get formatted masa pakai duration with unit
     */
    public function getFormattedMasaPakaiAttribute()
    {
        if (!$this->masa_pakai_duration || !$this->masa_pakai_unit) {
            return null;
        }
        
        return $this->masa_pakai_duration . ' ' . $this->masa_pakai_unit;
    }
    
    /**
     * Check if asset is still within useful life
     */
    public function getIsActiveAttribute()
    {
        if (!$this->masa_pakai_maksimum) {
            return true;
        }
        
        return now()->lte($this->masa_pakai_maksimum);
    }
}