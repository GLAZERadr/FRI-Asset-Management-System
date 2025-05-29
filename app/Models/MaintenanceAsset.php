<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_id',
        'damage_id',
        'asset_id',
        'status',
        'tanggal_pengajuan',
        'tanggal_perbaikan',
        'tanggal_selesai',
        'teknisi',
        'estimasi_waktu_perbaikan',
        'requested_by',
        'requested_by_role',
        'kaur_lab_approved_at',
        'kaur_lab_approved_by',
        'kaur_keuangan_approved_at',
        'kaur_keuangan_approved_by',
        'priority_score',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_perbaikan' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'kaur_lab_approved_at' => 'datetime',
        'kaur_keuangan_approved_at' => 'datetime',
        'priority_score' => 'float'
    ];

    /**
     * Get the asset for this maintenance
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'asset_id');
    }

    /**
     * Get the damaged asset record
     */
    public function damagedAsset()
    {
        return $this->belongsTo(DamagedAsset::class, 'damage_id', 'damage_id');
    }

    /**
     * Get the user who requested the maintenance
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get approval logs for this maintenance
     */
    public function approvalLogs()
    {
        return $this->hasMany(ApprovalLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Check if maintenance needs approval from a specific role
     */
    public function needsApprovalFrom($role)
    {
        if ($this->status !== 'Menunggu Persetujuan') {
            return false;
        }

        switch ($role) {
            case 'kaur_laboratorium':
                return $this->requested_by_role === 'staff_laboratorium' && 
                       is_null($this->kaur_lab_approved_at);
                
            case 'kaur_keuangan_logistik_sdm':
                // Direct from staff logistik OR approved by kaur lab
                if ($this->requested_by_role === 'staff_logistik' && is_null($this->kaur_keuangan_approved_at)) {
                    return true;
                }
                if ($this->requested_by_role === 'staff_laboratorium' && 
                    !is_null($this->kaur_lab_approved_at) && 
                    is_null($this->kaur_keuangan_approved_at)) {
                    return true;
                }
                return false;
                
            default:
                return false;
        }
    }

    /**
     * Get the next approver for this maintenance
     */
    public function getNextApprover()
    {
        if ($this->status !== 'Menunggu Persetujuan') {
            return null;
        }

        if ($this->requested_by_role === 'staff_laboratorium' && is_null($this->kaur_lab_approved_at)) {
            return User::role('kaur_laboratorium')->first();
        }

        if (is_null($this->kaur_keuangan_approved_at)) {
            return User::role('kaur_keuangan_logistik_sdm')->first();
        }

        return null;
    }

    /**
     * Check if maintenance has been fully approved
     */
    public function isFullyApproved()
    {
        if ($this->requested_by_role === 'staff_logistik') {
            return !is_null($this->kaur_keuangan_approved_at);
        }

        if ($this->requested_by_role === 'staff_laboratorium') {
            return !is_null($this->kaur_lab_approved_at) && !is_null($this->kaur_keuangan_approved_at);
        }

        return false;
    }

    /**
     * Scope for pending approvals
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'Menunggu Persetujuan');
    }

    /**
     * Scope for approved items
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'Diterima');
    }
}