<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_asset_id',
        'action',
        'performed_by',
        'role',
        'notes'
    ];

    public function maintenanceAsset()
    {
        return $this->belongsTo(MaintenanceAsset::class);
    }
}