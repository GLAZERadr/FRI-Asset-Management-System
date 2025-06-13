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
        'verified',
        'verified_at'
    ];

    protected $casts = [
        'tanggal_pelaporan' => 'datetime',
        'estimasi_waktu_perbaikan' => 'datetime',
        'verified_at' => 'datetime',
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
}