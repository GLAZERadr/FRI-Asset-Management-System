<?php
namespace App\Http\Controllers;

use App\Models\Criteria;
use App\Models\CriteriaComparison;
use App\Models\AhpWeight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CriteriaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $department = AhpWeight::getUserDepartment($user);
        
        // CHANGE: Get only criteria for current department
        $criteria = Criteria::forDepartment($department)->get();
        $calculationHistory = AhpWeight::getCalculationHistory($department);
        $activeWeights = AhpWeight::getActiveWeights($department);
        
        Log::info('Criteria index accessed', [
            'user' => $user->name,
            'role' => $user->roles->first()->name,
            'department' => $department,
            'criteria_count' => $criteria->count()
        ]);
        
        return view('kriteria.index', compact('criteria', 'calculationHistory', 'activeWeights', 'department'));
    }

    public function create()
    {
        $user = Auth::user();
        $department = AhpWeight::getUserDepartment($user);
        
        // CHANGE: Get only criteria for current department
        $criteria = Criteria::forDepartment($department)->get();
        $activeWeights = AhpWeight::getActiveWeights($department);
        $isConsistent = AhpWeight::areCurrentWeightsConsistent($department);
        
        // Get active configuration for form population
        $activeConfiguration = AhpWeight::getActiveConfiguration($department);
        
        Log::info('Criteria create page accessed', [
            'user' => $user->name,
            'department' => $department,
            'existing_criteria_count' => $criteria->count()
        ]);
        
        return view('kriteria.create', compact('criteria', 'activeWeights', 'isConsistent', 'department', 'activeConfiguration'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kriteria' => 'required|string|max:255',
            'tipe_kriteria' => 'required|in:benefit,cost'
        ]);

        $user = Auth::user();
        $department = AhpWeight::getUserDepartment($user);

        // CHANGE: Generate unique kriteria_id for this department only
        $kriteriaId = $this->generateUniqueKriteriaId($department);

        // CHANGE: Create criteria with department and created_by
        $criteria = Criteria::create([
            'kriteria_id' => $kriteriaId,
            'nama_kriteria' => $request->nama_kriteria,
            'tipe_kriteria' => $request->tipe_kriteria,
            'department' => $department,
            'created_by' => $user->id
        ]);

        Log::info('Criteria created', [
            'user' => $user->name,
            'department' => $department,
            'criteria_id' => $kriteriaId,
            'criteria_name' => $request->nama_kriteria
        ]);

        return response()->json([
            'success' => true,
            'message' => "Kriteria berhasil ditambahkan untuk departemen {$department}",
            'criteria' => $criteria
        ]);
    }

    /**
     * Generate unique kriteria_id by finding next available number FOR SPECIFIC DEPARTMENT
     */
    private function generateUniqueKriteriaId($department)
    {
        // CHANGE: Only check existing IDs within the same department
        $existingIds = Criteria::where('department', $department)->pluck('kriteria_id')->toArray();
        
        // Extract numbers from existing IDs
        $existingNumbers = array_map(function($id) {
            return intval(substr($id, 1));
        }, $existingIds);
        
        // Find the first available number starting from 1
        $nextNumber = 1;
        while (in_array($nextNumber, $existingNumbers)) {
            $nextNumber++;
        }
        
        return 'C' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function calculate(Request $request)
    {
        try {
            $user = Auth::user();
            $department = AhpWeight::getUserDepartment($user);
            
            Log::info('AHP Calculate request received', [
                'user' => $user->name,
                'department' => $department,
                'request' => $request->all()
            ]);
            
            // CHANGE: Get only criteria for current department
            $criteria = Criteria::forDepartment($department)->get();
            
            if ($criteria->count() < 2) {
                return response()->json([
                    'error' => "Minimal 2 kriteria diperlukan untuk perhitungan di departemen {$department}"
                ]);
            }

            // Save pairwise comparisons (department-specific)
            if ($request->has('comparisons')) {
                Log::info('Saving comparisons for department', [
                    'department' => $department,
                    'comparisons' => $request->comparisons
                ]);
                $this->saveComparisons($request->comparisons, $department);
            }

            // Calculate AHP
            $result = $this->calculateAHP($criteria, $department);
            
            Log::info('AHP calculation successful', [
                'department' => $department,
                'result' => $result
            ]);
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('AHP calculation error', [
                'department' => $department ?? 'unknown',
                'user' => $user->name ?? 'unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error calculating AHP: ' . $e->getMessage()
            ], 500);
        }
    }

    // CHANGE: Add department parameter to saveComparisons
    private function saveComparisons($comparisons, $department)
    {
        try {
            // Clear existing comparisons for this department
            CriteriaComparison::where('department', $department)->delete();
            
            foreach ($comparisons as $comparison) {
                Log::info('Saving comparison for department', array_merge($comparison, ['department' => $department]));
                
                CriteriaComparison::create([
                    'criteria_1' => $comparison['criteria_1'],
                    'criteria_2' => $comparison['criteria_2'],
                    'comparison_value' => $comparison['value'],
                    'department' => $department // Add department to comparisons
                ]);
                
                // Save inverse comparison only if it's not a self-comparison
                if ($comparison['criteria_1'] !== $comparison['criteria_2']) {
                    CriteriaComparison::create([
                        'criteria_1' => $comparison['criteria_2'],
                        'criteria_2' => $comparison['criteria_1'],
                        'comparison_value' => 1 / $comparison['value'],
                        'department' => $department // Add department to comparisons
                    ]);
                }
            }
            
            Log::info('All comparisons saved successfully for department', ['department' => $department]);
        } catch (\Exception $e) {
            Log::error('Error saving comparisons', [
                'department' => $department,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // CHANGE: Add department parameter to calculateAHP
    private function calculateAHP($criteria, $department)
    {
        try {
            $n = $criteria->count();
            $criteriaIds = $criteria->pluck('kriteria_id')->toArray();
            
            Log::info('Starting AHP calculation', [
                'department' => $department,
                'criteria_count' => $n,
                'criteria_ids' => $criteriaIds
            ]);
            
            // Build comparison matrix using department-specific comparisons
            $matrix = [];
            foreach ($criteriaIds as $i => $criteria1) {
                foreach ($criteriaIds as $j => $criteria2) {
                    if ($i === $j) {
                        $matrix[$i][$j] = 1;
                    } else {
                        // CHANGE: Filter comparisons by department
                        $comparison = CriteriaComparison::where('criteria_1', $criteria1)
                            ->where('criteria_2', $criteria2)
                            ->where('department', $department)
                            ->first();
                        $matrix[$i][$j] = $comparison ? $comparison->comparison_value : 1;
                    }
                }
            }
            
            Log::info('Comparison matrix built for department', [
                'department' => $department,
                'matrix' => $matrix
            ]);
            
            // Rest of your existing AHP calculation logic remains the same...
            // Calculate column sums
            $columnSums = [];
            for ($j = 0; $j < $n; $j++) {
                $sum = 0;
                for ($i = 0; $i < $n; $i++) {
                    $sum += $matrix[$i][$j];
                }
                $columnSums[$j] = $sum;
            }
            
            Log::info('Column sums calculated', ['column_sums' => $columnSums]);
            
            // Normalize matrix
            $normalizedMatrix = [];
            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $normalizedMatrix[$i][$j] = $matrix[$i][$j] / $columnSums[$j];
                }
            }
            
            Log::info('Normalized matrix calculated for department', ['department' => $department]);
            
            // Calculate priority weights (row averages)
            $weights = [];
            for ($i = 0; $i < $n; $i++) {
                $sum = 0;
                for ($j = 0; $j < $n; $j++) {
                    $sum += $normalizedMatrix[$i][$j];
                }
                $weights[$i] = $sum / $n;
            }
            
            Log::info('Weights calculated for department', [
                'department' => $department,
                'weights' => $weights
            ]);
            
            // Calculate consistency
            $consistency = $this->calculateConsistency($matrix, $weights, $n);
            Log::info('Consistency calculated for department', [
                'department' => $department,
                'consistency' => $consistency
            ]);
            
            return [
                'matrix' => $matrix,
                'normalized_matrix' => $normalizedMatrix,
                'weights' => $weights,
                'random_index' => $consistency['ri'],
                'consistency_index' => $consistency['ci'],
                'consistency_ratio' => $consistency['cr'],
                'lambda_max' => $consistency['lambda_max'],
                'is_consistent' => $consistency['cr'] <= 0.1,
                'department' => $department, // Add department to result
                'criteria' => $criteria->map(function($item, $index) use ($weights) {
                    return [
                        'kriteria_id' => $item->kriteria_id,
                        'nama_kriteria' => $item->nama_kriteria,
                        'tipe_kriteria' => $item->tipe_kriteria,
                        'weight' => $weights[$index] ?? 0
                    ];
                })
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in calculateAHP for department', [
                'department' => $department,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // Your existing calculateConsistency method remains the same
    private function calculateConsistency($matrix, $weights, $n)
    {
        try {
            // Random Index values for different matrix sizes
            $randomIndex = [
                1 => 0.00,
                2 => 0.00, 
                3 => 0.58,
                4 => 0.90,
                5 => 1.12,
                6 => 1.24,
                7 => 1.32,
                8 => 1.41,
                9 => 1.45,
                10 => 1.49
            ];
            
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
            
            // Get Random Index
            $ri = $randomIndex[$n] ?? 1.49;
            
            // Calculate Consistency Ratio
            $cr = $ri > 0 ? $ci / $ri : 0;
            
            return [
                'lambda_max' => $lambdaMax,
                'ci' => $ci,
                'ri' => $ri,
                'cr' => $cr
            ];
            
        } catch (\Exception $e) {
            Log::error('Error in calculateConsistency', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // Your existing storeWeights method with department logging
    public function storeWeights(Request $request)
    {
        try {
            $user = Auth::user();
            $department = AhpWeight::getUserDepartment($user);
            
            $validated = $request->validate([
                'criteria' => 'required|array',
                'weights' => 'required|array',
                'consistency_ratio' => 'required|numeric',
                'consistency_index' => 'numeric',
                'lambda_max' => 'numeric',
                'random_index' => 'numeric',
                'matrix' => 'array',
                'normalized_matrix' => 'array',
                'pairwise_comparisons' => 'array'
            ]);
            
            DB::beginTransaction();
            
            // CHANGE: Get current criteria information from department-specific query
            $criteriaIds = array_column($validated['criteria'], 'kriteria_id');
            $currentCriteria = Criteria::whereIn('kriteria_id', $criteriaIds)
                                      ->where('department', $department)
                                      ->get()
                                      ->keyBy('kriteria_id');
            
            // Prepare criteria data with complete information (including names and types)
            $enrichedCriteria = [];
            foreach ($validated['criteria'] as $index => $criterion) {
                $criteriaId = $criterion['kriteria_id'];
                $dbCriterion = $currentCriteria->get($criteriaId);
                
                if ($dbCriterion) {
                    $enrichedCriteria[] = [
                        'kriteria_id' => $criteriaId,
                        'nama_kriteria' => $dbCriterion->nama_kriteria,
                        'tipe_kriteria' => $dbCriterion->tipe_kriteria,
                        'weight' => $validated['weights'][$index] ?? 0
                    ];
                } else {
                    Log::warning('Criteria not found in department', [
                        'criteria_id' => $criteriaId,
                        'department' => $department
                    ]);
                    // Fallback to data from request if somehow not found in database
                    $enrichedCriteria[] = [
                        'kriteria_id' => $criteriaId,
                        'nama_kriteria' => $criterion['nama_kriteria'] ?? 'Unknown Criteria',
                        'tipe_kriteria' => $criterion['tipe_kriteria'] ?? 'benefit',
                        'weight' => $validated['weights'][$index] ?? 0
                    ];
                }
            }
            
            // Prepare consistency data
            $consistencyData = [
                'cr' => $validated['consistency_ratio'],
                'ci' => $validated['consistency_index'] ?? 0,
                'lambda_max' => $validated['lambda_max'] ?? 0,
                'ri' => $validated['random_index'] ?? 0
            ];
            
            // Prepare matrix data for storage
            $matrixData = [
                'comparison_matrix' => $validated['matrix'] ?? null,
                'normalized_matrix' => $validated['normalized_matrix'] ?? null
            ];
            
            // Get pairwise comparisons from request
            $pairwiseComparisons = $validated['pairwise_comparisons'] ?? [];
            
            // Store in database with enriched criteria data
            $calculationId = AhpWeight::storeCalculation(
                $enrichedCriteria,
                $validated['weights'],
                $consistencyData,
                $user->username ?? 'System',
                $department,
                $matrixData,
                $pairwiseComparisons
            );
            
            DB::commit();
            
            Log::info('AHP weights stored successfully', [
                'calculation_id' => $calculationId,
                'department' => $department,
                'user' => $user->name,
                'criteria_count' => count($enrichedCriteria),
                'consistency_ratio' => $validated['consistency_ratio'],
                'criteria_details' => $enrichedCriteria,
                'pairwise_comparisons_count' => count($pairwiseComparisons)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "AHP weights stored successfully for {$department} department",
                'calculation_id' => $calculationId,
                'department' => $department,
                'debug_info' => [
                    'criteria_count' => count($enrichedCriteria),
                    'criteria_stored' => $enrichedCriteria,
                    'consistency_ratio' => $validated['consistency_ratio'],
                    'pairwise_comparisons_saved' => count($pairwiseComparisons)
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing AHP weights: ' . $e->getMessage(), [
                'department' => $department ?? 'unknown',
                'user' => $user->name ?? 'unknown',
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error storing weights: ' . $e->getMessage()
            ], 500);
        }
    }

    // Rest of your existing methods with department awareness...
    public function getActiveWeights()
    {
        try {
            $user = Auth::user();
            $department = AhpWeight::getUserDepartment($user);
            
            $activeWeights = AhpWeight::getActiveWeightsForTopsis($department);
            
            if (!$activeWeights) {
                return response()->json([
                    'success' => false,
                    'message' => "No active AHP weights found for {$department} department",
                    'data' => null,
                    'department' => $department
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => $activeWeights,
                'department' => $department,
                'criteria_count' => count($activeWeights),
                'is_consistent' => AhpWeight::areCurrentWeightsConsistent($department)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting active weights: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving weights: ' . $e->getMessage()
            ], 500);
        }
    }

    public function setActiveWeights(Request $request, $calculationId)
    {
        try {
            $user = Auth::user();
            $department = AhpWeight::getUserDepartment($user);
            
            DB::beginTransaction();
            
            // Deactivate all current weights for this department
            AhpWeight::where('is_active', true)
                     ->where('department', $department)
                     ->update(['is_active' => false]);
            
            // Activate specified calculation
            $updated = AhpWeight::where('calculation_id', $calculationId)
                               ->where('department', $department)
                               ->update(['is_active' => true]);
            
            if ($updated === 0) {
                throw new \Exception('Calculation ID not found for your department');
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "AHP weights activated successfully for {$department} department"
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting active weights: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error activating weights: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $department = AhpWeight::getUserDepartment($user);
        
        // CHANGE: Only allow deletion of criteria from user's department
        $criteria = Criteria::where('kriteria_id', $id)
                           ->where('department', $department)
                           ->firstOrFail();
        
        // Check if user can manage this criteria
        if (!$criteria->canManage($user)) {
            abort(403, 'You can only delete criteria from your own department.');
        }
        
        // Delete related comparisons for this department
        CriteriaComparison::where('department', $department)
            ->where(function($query) use ($id) {
                $query->where('criteria_1', $id)
                      ->orWhere('criteria_2', $id);
            })
            ->delete();
            
        // Delete related AHP weights for this department
        AhpWeight::where('criteria_id', $id)
                 ->where('department', $department)
                 ->delete();
            
        $criteria->delete();
        
        Log::info('Criteria deleted', [
            'user' => $user->name,
            'department' => $department,
            'criteria_id' => $id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Kriteria berhasil dihapus dari departemen {$department}"
        ]);
    }

    public function getCalculationConfiguration($calculationId)
    {
        try {
            $user = Auth::user();
            $department = AhpWeight::getUserDepartment($user);
            
            $weights = AhpWeight::where('calculation_id', $calculationId)
                              ->where('department', $department)
                              ->get();
            
            if ($weights->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Calculation not found for your department'
                ], 404);
            }
            
            $firstWeight = $weights->first();
            $matrixData = $firstWeight->matrix_data;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'calculation_id' => $calculationId,
                    'criteria' => $matrixData['criteria_used'] ?? [],
                    'pairwise_comparisons' => $matrixData['pairwise_comparisons'] ?? [],
                    'weights' => $weights->pluck('weight', 'criteria_id')->toArray(),
                    'consistency_ratio' => $firstWeight->consistency_ratio,
                    'consistency_index' => $firstWeight->consistency_index,
                    'lambda_max' => $firstWeight->lambda_max,
                    'random_index' => $firstWeight->random_index,
                    'calculated_by' => $firstWeight->calculated_by,
                    'calculated_at' => $firstWeight->created_at,
                    'department' => $firstWeight->department,
                    'comparison_matrix' => $matrixData['comparison_matrix'] ?? null,
                    'normalized_matrix' => $matrixData['normalized_matrix'] ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting calculation configuration: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving calculation: ' . $e->getMessage()
            ], 500);
        }
    }
}