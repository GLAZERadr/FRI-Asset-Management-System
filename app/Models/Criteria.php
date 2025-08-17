<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Criteria extends Model
{
    use HasFactory;

    protected $fillable = [
        'kriteria_id',
        'nama_kriteria',
        'tipe_kriteria',
        'department',
        'deskripsi_kriteria',
        'created_by'
    ];

    /**
     * Scope criteria by user's department
     */
    public function scopeForCurrentUser($query)
    {
        $user = Auth::user();
        $department = $this->getUserDepartment($user);
        
        return $query->where('department', $department);
    }

    /**
     * Scope criteria by specific department
     */
    public function scopeForDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get user's department based on role
     */
    public static function getUserDepartment($user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user) {
            return 'laboratorium'; // Default
        }

        if ($user->hasRole(['staff_laboratorium', 'kaur_laboratorium'])) {
            return 'laboratorium';
        } elseif ($user->hasRole(['staff_logistik', 'kaur_keuangan_logistik_sdm'])) {
            return 'keuangan_logistik';
        }

        return 'laboratorium'; // Default fallback
    }

    /**
     * Boot method to automatically set department and created_by
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($criteria) {
            if (Auth::check()) {
                $criteria->department = self::getUserDepartment();
                $criteria->created_by = Auth::id();
            }
        });
    }

    /**
     * Get criteria for TOPSIS calculation by department
     */
    public static function getForTopsis($department = null)
    {
        if (!$department) {
            $department = self::getUserDepartment();
        }

        return self::where('department', $department)->get();
    }

    /**
     * Check if user can manage this criteria
     */
    public function canManage($user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }

        $userDepartment = self::getUserDepartment($user);
        return $this->department === $userDepartment;
    }

    /**
     * Get the user who created this criteria
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}