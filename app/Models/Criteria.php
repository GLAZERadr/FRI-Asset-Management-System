<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    use HasFactory;

    // Specify the table name explicitly
    protected $table = 'criteria';

    protected $fillable = [
        'kriteria_id',
        'nama_kriteria',
        'tipe_kriteria',
    ];

    // Relationship with comparisons
    public function comparisons()
    {
        return $this->hasMany(CriteriaComparison::class, 'criteria_1', 'kriteria_id');
    }

    public function inverseComparisons()
    {
        return $this->hasMany(CriteriaComparison::class, 'criteria_2', 'kriteria_id');
    }
}