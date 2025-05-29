<?php

namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\CriteriaComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CriteriaController extends Controller
{
    public function index()
    {
        $criteria = Criteria::all();
        return view('kriteria.index', compact('criteria'));
    }

    public function create()
    {
        $criteria = Criteria::all();
        return view('kriteria.create', compact('criteria'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kriteria' => 'required|string|max:255',
            'tipe_kriteria' => 'required|in:benefit,cost'
        ]);

        // Generate kriteria_id
        $lastCriteria = Criteria::latest()->first();
        $number = $lastCriteria ? (intval(substr($lastCriteria->kriteria_id, 1)) + 1) : 1;
        $kriteriaId = 'C' . str_pad($number, 3, '0', STR_PAD_LEFT);

        Criteria::create([
            'kriteria_id' => $kriteriaId,
            'nama_kriteria' => $request->nama_kriteria,
            'tipe_kriteria' => $request->tipe_kriteria
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kriteria berhasil ditambahkan'
        ]);
    }

    public function calculate(Request $request)
    {
        try {
            \Log::info('AHP Calculate request received', ['request' => $request->all()]);
            
            $criteria = Criteria::all();
            
            if ($criteria->count() < 2) {
                return response()->json([
                    'error' => 'Minimal 2 kriteria diperlukan untuk perhitungan'
                ]);
            }

            // Save pairwise comparisons
            if ($request->has('comparisons')) {
                \Log::info('Saving comparisons', ['comparisons' => $request->comparisons]);
                $this->saveComparisons($request->comparisons);
            }

            // Calculate AHP
            $result = $this->calculateAHP($criteria);
            
            \Log::info('AHP calculation successful', ['result' => $result]);
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error('AHP calculation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error calculating AHP: ' . $e->getMessage()
            ], 500);
        }
    }

    private function saveComparisons($comparisons)
    {
        try {
            // Clear existing comparisons
            CriteriaComparison::truncate();

            foreach ($comparisons as $comparison) {
                \Log::info('Saving comparison', $comparison);
                
                CriteriaComparison::create([
                    'criteria_1' => $comparison['criteria_1'],
                    'criteria_2' => $comparison['criteria_2'],
                    'comparison_value' => $comparison['value']
                ]);

                // Save inverse comparison only if it's not a self-comparison
                if ($comparison['criteria_1'] !== $comparison['criteria_2']) {
                    CriteriaComparison::create([
                        'criteria_1' => $comparison['criteria_2'],
                        'criteria_2' => $comparison['criteria_1'],
                        'comparison_value' => 1 / $comparison['value']
                    ]);
                }
            }
            
            \Log::info('All comparisons saved successfully');
        } catch (\Exception $e) {
            \Log::error('Error saving comparisons', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function calculateAHP($criteria)
    {
        try {
            $n = $criteria->count();
            $criteriaIds = $criteria->pluck('kriteria_id')->toArray();
            
            \Log::info('Starting AHP calculation', [
                'criteria_count' => $n,
                'criteria_ids' => $criteriaIds
            ]);
            
            // Build comparison matrix
            $matrix = [];
            foreach ($criteriaIds as $i => $criteria1) {
                foreach ($criteriaIds as $j => $criteria2) {
                    if ($i === $j) {
                        $matrix[$i][$j] = 1;
                    } else {
                        $comparison = CriteriaComparison::where('criteria_1', $criteria1)
                            ->where('criteria_2', $criteria2)
                            ->first();
                        $matrix[$i][$j] = $comparison ? $comparison->comparison_value : 1;
                    }
                }
            }

            \Log::info('Comparison matrix built', ['matrix' => $matrix]);

            // Calculate column sums
            $columnSums = [];
            for ($j = 0; $j < $n; $j++) {
                $sum = 0;
                for ($i = 0; $i < $n; $i++) {
                    $sum += $matrix[$i][$j];
                }
                $columnSums[$j] = $sum;
            }

            \Log::info('Column sums calculated', ['column_sums' => $columnSums]);

            // Normalize matrix
            $normalizedMatrix = [];
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $normalizedMatrix[$i][$j] = $matrix[$i][$j] / $columnSums[$j];
                }
            }

            \Log::info('Normalized matrix calculated');

            // Calculate priority weights (row averages)
            $weights = [];
            for ($i = 0; $i < $n; $i++) {
                $sum = 0;
                for ($j = 0; $j < $n; $j++) {
                    $sum += $normalizedMatrix[$i][$j];
                }
                $weights[$i] = $sum / $n;
            }

            \Log::info('Weights calculated', ['weights' => $weights]);

            // Calculate consistency
            $consistency = $this->calculateConsistency($matrix, $weights, $n);

            \Log::info('Consistency calculated', ['consistency' => $consistency]);

            return [
                'matrix' => $matrix,
                'normalized_matrix' => $normalizedMatrix,
                'weights' => $weights,
                'consistency_index' => $consistency['ci'],
                'consistency_ratio' => $consistency['cr'],
                'lambda_max' => $consistency['lambda_max'],
                'is_consistent' => $consistency['cr'] <= 0.1,
                'criteria' => $criteria->map(function($item, $index) use ($weights) {
                    return [
                        'kriteria_id' => $item->kriteria_id,
                        'nama_kriteria' => $item->nama_kriteria,
                        'weight' => $weights[$index] ?? 0
                    ];
                })
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in calculateAHP', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    private function buildComparisonMatrix($comparisons, $criteria)
    {
        $n = $criteria->count();
        $matrix = array_fill(0, $n, array_fill(0, $n, 1));
        
        // Create mapping of criteria ID to index
        $criteriaMap = [];
        foreach ($criteria as $index => $criterion) {
            $criteriaMap[$criterion->kriteria_id] = $index;
        }
        
        // Fill matrix based on comparisons
        foreach ($comparisons as $comparison) {
            $i = $criteriaMap[$comparison['criteria_1']] ?? null;
            $j = $criteriaMap[$comparison['criteria_2']] ?? null;
            
            if ($i !== null && $j !== null) {
                if ($i == $j) {
                    $matrix[$i][$j] = 1; // Diagonal
                } else {
                    $matrix[$i][$j] = $comparison['value'];
                    $matrix[$j][$i] = 1 / $comparison['value']; // Reciprocal
                }
            }
        }
        
        return $matrix;
    }

    private function calculateConsistency($matrix, $weights, $n)
    {
        try {
            // Random Index values for different matrix sizes
            $randomIndex = [0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41, 1.45, 1.49];
            
            // Calculate lambda max
            $lambdaMax = 0;
            for ($i = 0; $i < $n; $i++) {
                $sum = 0;
                for ($j = 0; $j < $n; $j++) {
                    $sum += $matrix[$i][$j] * $weights[$j];
                }
                if ($weights[$i] != 0) {
                    $lambdaMax += $sum / $weights[$i];
                }
            }
            $lambdaMax = $lambdaMax / $n;

            // Calculate Consistency Index
            $ci = ($lambdaMax - $n) / ($n - 1);

            // Calculate Consistency Ratio
            $ri = $randomIndex[$n] ?? 1.49;
            $cr = $ri > 0 ? $ci / $ri : 0;

            return [
                'lambda_max' => $lambdaMax,
                'ci' => $ci,
                'cr' => $cr
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error in calculateConsistency', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function storeWeights(Request $request)
    {
        try {
            $validated = $request->validate([
                'criteria' => 'required|array',
                'weights' => 'required|array',
                'consistency_ratio' => 'required|numeric'
            ]);
            
            // Store weights in session for use in TOPSIS
            session([
                'ahp_weights' => $validated['weights'],
                'ahp_criteria' => $validated['criteria'],
                'ahp_consistency_ratio' => $validated['consistency_ratio'],
                'ahp_calculation_time' => now()->toDateTimeString()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'AHP weights stored successfully'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error storing AHP weights: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error storing weights: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $criteria = Criteria::where('kriteria_id', $id)->firstOrFail();
        
        // Delete related comparisons
        CriteriaComparison::where('criteria_1', $id)
            ->orWhere('criteria_2', $id)
            ->delete();
            
        $criteria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kriteria berhasil dihapus'
        ]);
    }
}