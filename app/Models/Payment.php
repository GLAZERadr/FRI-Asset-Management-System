<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'no_invoice',
        'jatuh_tempo',
        'vendor',
        'total_tagihan',
        'file_invoice',
        'tipe_pembayaran',
        'status',
        'tanggal_pembayaran',
        'updated_by',
    ];

    protected $casts = [
        'jatuh_tempo' => 'date',
        'tanggal_pembayaran' => 'datetime',
        'total_tagihan' => 'decimal:2',
    ];

    protected $dates = [
        'jatuh_tempo',
        'tanggal_pembayaran',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Constants for payment types
    const TIPE_PEMBAYARAN = [
        'setelah_perbaikan' => 'Setelah Perbaikan',
        'sebelum_perbaikan' => 'Sebelum Perbaikan',
        'transfer' => 'Transfer Bank',
        'cash' => 'Cash',
        'check' => 'Cek',
    ];

    // Constants for payment status
    const STATUS = [
        'belum_dibayar' => 'Belum dibayar',
        'sudah_dibayar' => 'Sudah dibayar',
        'terlambat' => 'Terlambat',
        'dibatalkan' => 'Dibatalkan',
    ];

    /**
     * Relationship with User who last updated the payment
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Accessor for formatted total tagihan
     */
    public function getFormattedTotalTagihanAttribute()
    {
        return 'Rp ' . number_format($this->total_tagihan, 0, ',', '.');
    }

    /**
     * Accessor for payment type label
     */
    public function getTipeLabel()
    {
        return self::TIPE_PEMBAYARAN[$this->tipe_pembayaran] ?? $this->tipe_pembayaran;
    }

    /**
     * Accessor for status label
     */
    public function getStatusLabel()
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue()
    {
        return $this->status === 'belum_dibayar' && $this->jatuh_tempo < now();
    }

    /**
     * Check if payment is paid
     */
    public function isPaid()
    {
        return $this->status === 'sudah_dibayar';
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'belum_dibayar');
    }

    /**
     * Scope for paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'sudah_dibayar');
    }

    /**
     * Scope for overdue payments
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'belum_dibayar')
                    ->where('jatuh_tempo', '<', now());
    }

    /**
     * Scope for payments by vendor
     */
    public function scopeByVendor($query, $vendor)
    {
        return $query->where('vendor', 'like', "%{$vendor}%");
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set updated_by when updating
        static::updating(function ($payment) {
            if (auth()->check()) {
                $payment->updated_by = auth()->id();
            }
            
            // Set tanggal_pembayaran when status changes to paid
            if ($payment->isDirty('status') && $payment->status === 'sudah_dibayar' && !$payment->tanggal_pembayaran) {
                $payment->tanggal_pembayaran = now();
            }
        });
    }
}