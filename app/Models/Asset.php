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
}