<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamagedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'damage_id',
        'asset_id',
        'tingkat_kerusakan',
        'estimasi_biaya',
        'deskripsi_kerusakan',
        'tanggal_pelaporan',
        'pelapor',
        'vendor',
    ];

    protected $casts = [
        'tanggal_pelaporan' => 'datetime',
        'estimasi_biaya' => 'decimal:2'
    ];

    /**
     * Get the asset associated with this damage
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'asset_id');
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
}