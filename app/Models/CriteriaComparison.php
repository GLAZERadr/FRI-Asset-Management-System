<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaComparison extends Model
{
    use HasFactory;

    // Laravel will automatically use 'criteria_comparisons' table name
    // But you can specify it explicitly if needed:
    // protected $table = 'criteria_comparison';

    protected $fillable = [
        'criteria_1',
        'criteria_2', 
        'comparison_value'
    ];

    public function firstCriteria()
    {
        return $this->belongsTo(Criteria::class, 'criteria_1', 'kriteria_id');
    }

    public function secondCriteria()
    {
        return $this->belongsTo(Criteria::class, 'criteria_2', 'kriteria_id');
    }
}