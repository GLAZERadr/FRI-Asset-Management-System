<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'division',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get all notifications for the user
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Get unread notifications
     */
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * Check if user can approve maintenance requests
     */
    public function canApprove()
    {
        return $this->hasAnyRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']);
    }

    /**
     * Check if user needs kaur lab approval
     */
    public function needsKaurLabApproval()
    {
        return $this->hasRole('staff_laboratorium');
    }

    /**
     * Get the appropriate approver for this user
     */
    public function getApprover()
    {
        if ($this->hasRole('staff_laboratorium')) {
            return User::role('kaur_laboratorium')->first();
        } elseif ($this->hasRole('staff_logistik')) {
            return User::role('kaur_keuangan_logistik_sdm')->first();
        } elseif ($this->hasRole('kaur_laboratorium')) {
            return User::role('kaur_keuangan_logistik_sdm')->first();
        }
        
        return null;
    }
}