<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_id',
        'asset_id',
        'nilai_prioritas',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id', 'asset_id');
    }
}