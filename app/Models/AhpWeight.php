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
        'department', // New field for department separation
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
     * Get the currently active AHP weights for a specific department
     */
    public static function getActiveWeights($department = null)
    {
        $query = self::where('is_active', true)
                     ->orderBy('created_at', 'desc');
        
        if ($department) {
            $query->where('department', $department);
        }
        
        return $query->get()
                    ->groupBy('calculation_id')
                    ->first(); // Get the most recent active calculation
    }

    /**
     * Get active weights formatted for TOPSIS calculation for specific department
     */
    public static function getActiveWeightsForTopsis($department = null)
    {
        $activeWeights = self::getActiveWeights($department);
        
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
     * Store new AHP calculation results with department
     */
    public static function storeCalculation($criteria, $weights, $consistencyData, $calculatedBy, $department, $matrixData = null, $pairwiseComparisons = null)
    {
        $calculationId = 'AHP_' . strtoupper($department) . '_' . date('Ymd_His') . '_' . Str::random(6);
        
        // Deactivate all previous weights for this department
        self::where('is_active', true)
            ->where('department', $department)
            ->update(['is_active' => false]);
        
        // Prepare comprehensive matrix data
        $completeMatrixData = [
            'comparison_matrix' => $matrixData['comparison_matrix'] ?? null,
            'normalized_matrix' => $matrixData['normalized_matrix'] ?? null,
            'pairwise_comparisons' => $pairwiseComparisons, // Store original pairwise comparisons
            'criteria_used' => $criteria, // Store criteria configuration
            'calculation_date' => now()->toDateTimeString(),
            'calculation_method' => 'AHP',
            'department' => $department
        ];
        
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
                'department' => $department,
                'is_active' => true,
                'matrix_data' => $completeMatrixData
            ]);
        }
        
        return $calculationId;
    }

    /**
     * Get calculation history for specific department
     */
    public static function getCalculationHistory($department = null, $limit = 10)
    {
        $query = self::select('calculation_id', 'consistency_ratio', 'calculated_by', 'created_at', 'criteria_count', 'department')
                     ->distinct()
                     ->orderBy('created_at', 'desc')
                     ->limit($limit);
        
        if ($department) {
            $query->where('department', $department);
        }
        
        return $query->get()
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
     * Check if current weights are consistent for specific department
     */
    public static function areCurrentWeightsConsistent($department = null)
    {
        $activeWeights = self::getActiveWeights($department);
        
        if (!$activeWeights || $activeWeights->isEmpty()) {
            return false;
        }

        $firstWeight = $activeWeights->first();
        return $firstWeight->consistency_ratio <= 0.1;
    }

    /**
     * Get user's department based on role
     */
    public static function getUserDepartment($user)
    {
        if ($user->hasRole(['kaur_laboratorium', 'staff_laboratorium'])) {
            return 'laboratorium';
        } elseif ($user->hasRole(['kaur_keuangan_logistik_sdm', 'staff_logistik'])) {
            return 'keuangan_logistik';
        }
        
        return 'general'; // fallback
    }

    /**
     * Get appropriate AHP weights based on asset location
     */
    public static function getWeightsForAsset($asset)
    {
        // Determine department based on asset location
        $isLabAsset = str_contains($asset->lokasi, 'Laboratorium');
        $department = $isLabAsset ? 'laboratorium' : 'keuangan_logistik';
        
        return self::getActiveWeightsForTopsis($department);
    }

    /**
     * Get active configuration including pairwise comparisons for form population
     */
    public static function getActiveConfiguration($department = null)
    {
        $activeWeights = self::getActiveWeights($department);
        
        if (!$activeWeights || $activeWeights->isEmpty()) {
            return null;
        }

        $firstWeight = $activeWeights->first();
        $matrixData = $firstWeight->matrix_data;
        
        if (!$matrixData || !is_array($matrixData)) {
            return null;
        }

        return [
            'calculation_id' => $firstWeight->calculation_id,
            'criteria' => $matrixData['criteria_used'] ?? [],
            'pairwise_comparisons' => $matrixData['pairwise_comparisons'] ?? [],
            'weights' => $activeWeights->pluck('weight', 'criteria_id')->toArray(),
            'consistency_ratio' => $firstWeight->consistency_ratio,
            'consistency_index' => $firstWeight->consistency_index,
            'lambda_max' => $firstWeight->lambda_max,
            'random_index' => $firstWeight->random_index,
            'calculated_by' => $firstWeight->calculated_by,
            'calculated_at' => $firstWeight->created_at,
            'department' => $firstWeight->department,
            'comparison_matrix' => $matrixData['comparison_matrix'] ?? null,
            'normalized_matrix' => $matrixData['normalized_matrix'] ?? null
        ];
    }
}