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
        'status',
        'tanggal_pengajuan',
        'tanggal_perbaikan',
        'tanggal_selesai',
        'teknisi',
        'requested_by',
        'requested_by_role',
        'kaur_lab_approved_at',
        'kaur_lab_approved_by',
        'kaur_keuangan_approved_at',
        'kaur_keuangan_approved_by',
        'priority_score',
        'priority_calculated_at',
        'priority_method',
        'penyebab_kerusakan',
        'deskripsi_perbaikan',
        'hasil_perbaikan',
        'rekomendasi',
        'catatan',
        'photos'
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_perbaikan' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'kaur_lab_approved_at' => 'datetime',
        'kaur_keuangan_approved_at' => 'datetime',
        'priority_calculated_at' => 'datetime',
        'priority_score' => 'float',
        'photos' => 'array' // Cast photos as array for JSON storage
    ];

    /**
     * Get the asset for this maintenance
     * FIXED: Get asset through damaged asset relationship
     */
    public function asset()
    {
        return $this->hasOneThrough(
            Asset::class,
            DamagedAsset::class,
            'damage_id', // Foreign key on damaged_assets table
            'asset_id',  // Foreign key on assets table
            'damage_id', // Local key on maintenance_assets table
            'asset_id'   // Local key on damaged_assets table
        );
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
     * Check if priority score needs recalculation
     */
    public function needsPriorityRecalculation($lastAhpCalculation = null)
    {
        if (!$this->priority_calculated_at) {
            return true;
        }
        
        if ($lastAhpCalculation && $this->priority_calculated_at < $lastAhpCalculation) {
            return true;
        }
        
        return false;
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

    /**
     * Scope for items with priority scores
     */
    public function scopeWithPriorityScore($query)
    {
        return $query->whereNotNull('priority_score');
    }

    /**
     * Scope for items that need priority recalculation
     */
    public function scopeNeedsPriorityRecalculation($query, $lastAhpCalculation = null)
    {
        $query->where(function($q) use ($lastAhpCalculation) {
            $q->whereNull('priority_calculated_at');
            
            if ($lastAhpCalculation) {
                $q->orWhere('priority_calculated_at', '<', $lastAhpCalculation);
            }
        });
        
        return $query;
    }

    /**
     * Get formatted photos array
     */
    public function getPhotosAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }
        
        return $value ?: [];
    }

    /**
     * Set photos attribute
     */
    public function setPhotosAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['photos'] = json_encode($value);
        } else {
            $this->attributes['photos'] = $value;
        }
    }
}