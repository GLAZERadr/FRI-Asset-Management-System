<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'notifiable_type',
        'notifiable_id',
        'related_model',
        'related_id',
        'action_url',
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function relatedModel()
    {
        if ($this->related_model && $this->related_id) {
            return app($this->related_model)::find($this->related_id);
        }
        return null;
    }
}