<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AhpWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculation_id',
        'criteria_id',
        'nama_kriteria',
        'tipe_kriteria',
        'weight',
        'consistency_ratio',
        'consistency_index',
        'lambda_max',
        'random_index',
        'criteria_count',
        'calculated_by',
        'is_active',
        'matrix_data'
    ];

    protected $casts = [
        'weight' => 'decimal:6',
        'consistency_ratio' => 'decimal:4',
        'consistency_index' => 'decimal:6',
        'lambda_max' => 'decimal:6',
        'random_index' => 'decimal:4',
        'is_active' => 'boolean',
        'matrix_data' => 'array'
    ];

    /**
     * Relationship with Criteria
     */
    public function criteria()
    {
        return $this->belongsTo(Criteria::class, 'criteria_id', 'kriteria_id');
    }

    /**
     * Get the currently active AHP weights
     */
    public static function getActiveWeights()
    {
        return self::where('is_active', true)
                   ->orderBy('created_at', 'desc')
                   ->get()
                   ->groupBy('calculation_id')
                   ->first(); // Get the most recent active calculation
    }

    /**
     * Get active weights formatted for TOPSIS calculation
     */
    public static function getActiveWeightsForTopsis()
    {
        $activeWeights = self::getActiveWeights();
        
        if (!$activeWeights) {
            return null;
        }

        $formattedWeights = [];
        foreach ($activeWeights as $weight) {
            $formattedWeights[$weight->criteria_id] = [
                'weight' => (float) $weight->weight,
                'nama_kriteria' => $weight->nama_kriteria,
                'tipe_kriteria' => $weight->tipe_kriteria
            ];
        }

        return $formattedWeights;
    }

    /**
     * Store new AHP calculation results
     */
    public static function storeCalculation($criteria, $weights, $consistencyData, $calculatedBy, $matrixData = null)
    {
        $calculationId = 'AHP_' . date('Ymd_His') . '_' . Str::random(6);
        
        // Deactivate all previous weights
        self::where('is_active', true)->update(['is_active' => false]);
        
        // Store new weights
        foreach ($criteria as $index => $criterion) {
            self::create([
                'calculation_id' => $calculationId,
                'criteria_id' => $criterion['kriteria_id'],
                'nama_kriteria' => $criterion['nama_kriteria'],
                'tipe_kriteria' => $criterion['tipe_kriteria'],
                'weight' => $weights[$index],
                'consistency_ratio' => $consistencyData['cr'],
                'consistency_index' => $consistencyData['ci'],
                'lambda_max' => $consistencyData['lambda_max'],
                'random_index' => $consistencyData['ri'],
                'criteria_count' => count($criteria),
                'calculated_by' => $calculatedBy,
                'is_active' => true,
                'matrix_data' => $matrixData
            ]);
        }
        
        return $calculationId;
    }

    /**
     * Get calculation history
     */
    public static function getCalculationHistory($limit = 10)
    {
        return self::select('calculation_id', 'consistency_ratio', 'calculated_by', 'created_at', 'criteria_count')
                   ->distinct()
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get()
                   ->groupBy('calculation_id')
                   ->map(function ($group) {
                       return $group->first();
                   })
                   ->values();
    }

    /**
     * Get weights by calculation ID
     */
    public static function getWeightsByCalculationId($calculationId)
    {
        return self::where('calculation_id', $calculationId)
                   ->orderBy('criteria_id')
                   ->get();
    }

    /**
     * Check if current weights are consistent
     */
    public static function areCurrentWeightsConsistent()
    {
        $activeWeights = self::getActiveWeights();
        
        if (!$activeWeights || $activeWeights->isEmpty()) {
            return false;
        }

        $firstWeight = $activeWeights->first();
        return $firstWeight->consistency_ratio <= 0.1;
    }
}