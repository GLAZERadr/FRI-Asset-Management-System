<?php
namespace App\Services;

use App\Models\MaintenanceAsset;
use App\Models\Criteria;
use App\Models\CriteriaComparison;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopsisService
{
    /**
     * Enhanced calculatePriorityWithWeights with better error handling and logging
     */
    public function calculatePriorityWithWeights(Collection $maintenanceAssets, array $ahpCriteriaWeights)
    {
        if ($maintenanceAssets->isEmpty()) {
            Log::warning('TOPSIS: Empty maintenance assets collection provided');
            return [];
        }

        if (empty($ahpCriteriaWeights)) {
            Log::warning('TOPSIS: Empty AHP criteria weights provided');
            return [];
        }

        Log::info('Starting TOPSIS calculation with improved error handling', [
            'assets_count' => $maintenanceAssets->count(),
            'criteria_weights_count' => count($ahpCriteriaWeights),
            'criteria_keys' => array_keys($ahpCriteriaWeights)
        ]);

        try {
            // ✅ HANDLE SINGLE ASSET SCENARIO
            if ($maintenanceAssets->count() === 1) {
                Log::info('Single asset detected - using simplified scoring method');
                return $this->calculateSingleAssetScore($maintenanceAssets->first(), $ahpCriteriaWeights);
            }

            // Validate all assets have required relationships
            $validAssets = $maintenanceAssets->filter(function($asset) {
                $isValid = $asset instanceof \App\Models\MaintenanceAsset && 
                        $asset->damagedAsset && 
                        $asset->asset;
                
                if (!$isValid) {
                    Log::warning('Invalid asset filtered out', [
                        'asset_id' => $asset->id ?? 'unknown',
                        'has_damaged_asset' => isset($asset->damagedAsset),
                        'has_asset' => isset($asset->asset)
                    ]);
                }
                
                return $isValid;
            });

            if ($validAssets->isEmpty()) {
                Log::error('No valid assets found for TOPSIS calculation');
                return [];
            }

            if ($validAssets->count() < $maintenanceAssets->count()) {
                Log::warning('Some assets were filtered out due to missing relationships', [
                    'original_count' => $maintenanceAssets->count(),
                    'valid_count' => $validAssets->count()
                ]);
            }

            // Build decision matrix
            $decisionMatrix = $this->buildDynamicDecisionMatrix($validAssets);
            
            if (empty($decisionMatrix)) {
                Log::error('Empty decision matrix generated');
                return [];
            }

            // Validate matrix has valid data
            if (!$this->validateDecisionMatrix($decisionMatrix)) {
                Log::error('Decision matrix validation failed');
                return [];
            }
            
            // Normalize the matrix
            $normalizedMatrix = $this->normalizeMatrix($decisionMatrix);
            
            if (empty($normalizedMatrix)) {
                Log::error('Matrix normalization failed');
                return [];
            }
            
            // Apply AHP weights dynamically
            $weightedMatrix = $this->applyDynamicWeights($normalizedMatrix, $ahpCriteriaWeights);
            
            // Determine ideal solutions based on criteria types
            $idealPositive = $this->getDynamicIdealPositive($weightedMatrix, $ahpCriteriaWeights);
            $idealNegative = $this->getDynamicIdealNegative($weightedMatrix, $ahpCriteriaWeights);
            
            // Validate ideal solutions are different
            if ($this->areIdealSolutionsIdentical($idealPositive, $idealNegative)) {
                Log::warning('Ideal positive and negative solutions are identical - using alternative scoring');
                return $this->calculateAlternativeScoring($validAssets, $ahpCriteriaWeights);
            }
            
            // Calculate distances
            $distances = $this->calculateDistances($weightedMatrix, $idealPositive, $idealNegative);
            
            // Calculate relative closeness
            $scores = $this->calculateRelativeCloseness($distances);
            
            // Format scores with ranks
            $rankedScores = $this->formatScoresWithRanks($scores);
            
            // Update maintenance assets with scores
            $updatedCount = 0;
            foreach ($validAssets as $asset) {
                if (isset($rankedScores[$asset->id])) {
                    try {
                        $asset->update([
                            'priority_score' => $rankedScores[$asset->id]['score'],
                            'priority_calculated_at' => now(),
                            'priority_method' => 'TOPSIS_AHP_Enhanced'
                        ]);
                        $updatedCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to update asset priority score', [
                            'asset_id' => $asset->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            Log::info('TOPSIS calculation completed successfully', [
                'scores_calculated' => count($rankedScores),
                'assets_updated' => $updatedCount
            ]);
            
            return $rankedScores;

        } catch (\Exception $e) {
            Log::error('TOPSIS calculation failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'assets_count' => $maintenanceAssets->count()
            ]);
            
            // Try fallback calculation for individual assets
            return $this->fallbackCalculation($maintenanceAssets, $ahpCriteriaWeights);
        }
    }

    /**
     * Fallback calculation when main TOPSIS fails
     */
    private function fallbackCalculation($maintenanceAssets, $ahpCriteriaWeights)
    {
        Log::info('Starting fallback calculation for TOPSIS');
        
        $scores = [];
        
        foreach ($maintenanceAssets as $asset) {
            try {
                $singleResult = $this->calculateSingleAssetScore($asset, $ahpCriteriaWeights);
                if (!empty($singleResult)) {
                    $scores = array_merge($scores, $singleResult);
                }
            } catch (\Exception $e) {
                Log::error('Fallback calculation failed for asset', [
                    'asset_id' => $asset->id ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Re-rank scores
        if (!empty($scores)) {
            $sortedScores = $scores;
            uasort($sortedScores, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            $rank = 1;
            foreach ($sortedScores as $id => $data) {
                $scores[$id]['rank'] = $rank++;
            }
            
            Log::info('Fallback calculation completed', [
                'scores_calculated' => count($scores)
            ]);
        }
        
        return $scores;
    }

    /**
     * Check if ideal solutions are identical (causing calculation issues)
     */
    private function areIdealSolutionsIdentical($idealPositive, $idealNegative)
    {
        if (count($idealPositive) !== count($idealNegative)) {
            return false;
        }
        
        foreach ($idealPositive as $key => $value) {
            if (!isset($idealNegative[$key]) || abs($value - $idealNegative[$key]) > 0.0001) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Calculate score for single asset using simplified method
     */
    private function calculateSingleAssetScore($maintenanceAsset, $ahpCriteriaWeights)
    {
        $damagedAsset = $maintenanceAsset->damagedAsset;
        $relatedAsset = $maintenanceAsset->asset;
        
        if (!$damagedAsset || !$relatedAsset) {
            Log::error('Single asset missing required relationships');
            return [];
        }
        
        $criteria = Criteria::all();
        $weightedScore = 0;
        $totalWeight = 0;
        
        foreach ($criteria as $criterion) {
            $criteriaId = $criterion->kriteria_id;
            
            if (!isset($ahpCriteriaWeights[$criteriaId])) {
                continue;
            }
            
            $weight = $ahpCriteriaWeights[$criteriaId]['weight'];
            $criteriaType = $ahpCriteriaWeights[$criteriaId]['tipe_kriteria'] ?? $criterion->tipe_kriteria;
            
            // Extract criteria value
            $rawValue = $this->extractCriteriaValue($damagedAsset, $criterion);
            
            // Normalize to 0-1 scale based on criteria type
            $normalizedValue = $this->normalizeSingleValue($rawValue, $criterion, $criteriaType);
            
            $weightedScore += $normalizedValue * $weight;
            $totalWeight += $weight;
            
            Log::debug('Single asset criteria calculation:', [
                'criteria' => $criterion->nama_kriteria,
                'raw_value' => $rawValue,
                'normalized_value' => $normalizedValue,
                'weight' => $weight,
                'weighted_contribution' => $normalizedValue * $weight
            ]);
        }
        
        // Final score (ensure it's between 0 and 1)
        $finalScore = $totalWeight > 0 ? min($weightedScore / $totalWeight, 1.0) : 0.5;
        
        // Update the asset
        $maintenanceAsset->update([
            'priority_score' => $finalScore,
            'priority_calculated_at' => now(),
            'priority_method' => 'Single_Asset_Weighted'
        ]);
        
        Log::info('Single asset score calculated:', [
            'asset_id' => $maintenanceAsset->id,
            'final_score' => $finalScore,
            'total_weight' => $totalWeight
        ]);
        
        return [
            $maintenanceAsset->id => [
                'score' => $finalScore,
                'rank' => 1
            ]
        ];
    }

    /**
     * Normalize single value for single asset calculation
     */
    private function normalizeSingleValue($value, $criterion, $criteriaType)
    {
        $criteriaNameLower = strtolower($criterion->nama_kriteria);
        
        if (str_contains($criteriaNameLower, 'kerusakan')) {
            // Scale 3,6,9 to 0.33,0.66,1.0
            return min($value / 9, 1.0);
        }
        
        if (str_contains($criteriaNameLower, 'kepentingan')) {
            // Scale 3,6,9 to 0.33,0.66,1.0
            return min($value / 9, 1.0);
        }
        
        if (str_contains($criteriaNameLower, 'biaya')) {
            // For cost: lower is better, so invert and scale
            if ($value <= 0) return 1.0;
            // Scale based on reasonable cost range (100K to 10M)
            $maxCost = 10000000; // 10 million
            $minCost = 100000;   // 100 thousand
            $normalizedCost = max(0, min(1, ($maxCost - $value) / ($maxCost - $minCost)));
            return $normalizedCost;
        }
        
        // Default: scale to 0-1 range
        return min($value / 9, 1.0);
    }

    /**
     * Validate decision matrix has valid data
     */
    private function validateDecisionMatrix($matrix)
    {
        if (empty($matrix)) {
            return false;
        }
        
        foreach ($matrix as $row) {
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                
                if (!is_numeric($value) || is_nan($value) || is_infinite($value)) {
                    Log::warning('Invalid value in decision matrix:', [
                        'key' => $key,
                        'value' => $value
                    ]);
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Alternative scoring when TOPSIS fails
     */
    private function calculateAlternativeScoring($maintenanceAssets, $ahpCriteriaWeights)
    {
        Log::info('Using alternative scoring method');
        
        $scores = [];
        
        foreach ($maintenanceAssets as $asset) {
            $singleAssetResult = $this->calculateSingleAssetScore($asset, $ahpCriteriaWeights);
            if (!empty($singleAssetResult)) {
                $scores = array_merge($scores, $singleAssetResult);
            }
        }
        
        // Re-rank based on scores
        $sortedScores = $scores;
        uasort($sortedScores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        $rank = 1;
        foreach ($sortedScores as $id => $data) {
            $scores[$id]['rank'] = $rank++;
        }
        
        return $scores;
    }

    /**
     * Build decision matrix dynamically based on available criteria
     */
    private function buildDynamicDecisionMatrix(Collection $assets)
    {
        $criteria = Criteria::all();
        $matrix = [];
        
        Log::info('Building dynamic decision matrix', [
            'criteria_count' => $criteria->count(),
            'assets_count' => $assets->count()
        ]);
        
        foreach ($assets as $index => $item) {
            // Handle both MaintenanceAsset and DamagedAsset
            if ($item instanceof \App\Models\MaintenanceAsset) {
                $asset = $item->damagedAsset;
                $rowId = $item->id; // Use maintenance asset ID
            } else {
                $asset = $item;
                $rowId = $item->id; // Use damaged asset ID
            }
            
            $row = ['id' => $rowId];
            
            foreach ($criteria as $criterion) {
                $value = $this->extractCriteriaValue($asset, $criterion);
                $row[$criterion->kriteria_id] = $value;
                
                Log::debug('Extracted criteria value', [
                    'asset_id' => $rowId,
                    'criteria_id' => $criterion->kriteria_id,
                    'criteria_name' => $criterion->nama_kriteria,
                    'value' => $value
                ]);
            }
            
            $matrix[$index] = $row;
        }
        
        Log::info('Dynamic decision matrix built', [
            'matrix_sample' => array_slice($matrix, 0, 2),
            'criteria_count' => $criteria->count()
        ]);
        
        return $matrix;
    }

    /**
     * Extract criteria value from asset based on criteria name and type
     */
    private function extractCriteriaValue($asset, $criterion)
    {
        $criteriaNameLower = strtolower($criterion->nama_kriteria);
        
        // Handle specific criteria based on name patterns
        if (str_contains($criteriaNameLower, 'kerusakan')) {
            return $this->getTingkatKerusakanScore($asset->tingkat_kerusakan);
        }
        
        if (str_contains($criteriaNameLower, 'biaya')) {
            return floatval($asset->estimasi_biaya);
        }
        
        if (str_contains($criteriaNameLower, 'kepentingan')) {
            $kepentingan = $asset->asset->tingkat_kepentingan_asset ?? 'Sedang';
            return $this->getKepentinganScore($kepentingan);
        }
        
        if (str_contains($criteriaNameLower, 'waktu')) {
            // Try to get from maintenance asset if available
            $waktu = '1 hari'; // default
            if (isset($asset->estimasi_waktu_perbaikan)) {
                $waktu = $asset->estimasi_waktu_perbaikan;
            }
            return $this->getWaktuScore($waktu);
        }
        
        if (str_contains($criteriaNameLower, 'kompleksitas')) {
            $kompleksitas = $asset->kompleksitas ?? 'Sedang';
            return $this->getKompleksitasScore($kompleksitas);
        }
        
        if (str_contains($criteriaNameLower, 'urgensi')) {
            $urgensi = $asset->tingkat_urgensi ?? 'Sedang';
            return $this->getUrgensiScore($urgensi);
        }
        
        if (str_contains($criteriaNameLower, 'dampak')) {
            $dampak = $asset->dampak_operasional ?? 'Sedang';
            return $this->getDampakScore($dampak);
        }
        
        // Check if there's additional criteria data stored as JSON
        if (isset($asset->additional_criteria)) {
            $additionalData = json_decode($asset->additional_criteria, true);
            if (isset($additionalData[$criterion->kriteria_id])) {
                $value = $additionalData[$criterion->kriteria_id]['value'];
                return $this->normalizeCustomValue($value, $criterion);
            }
        }
        
        // Try to extract from asset relationship if available
        if ($asset->asset && property_exists($asset->asset, $criteriaNameLower)) {
            $value = $asset->asset->{$criteriaNameLower};
            return $this->normalizeCustomValue($value, $criterion);
        }
        
        // Default scoring based on criteria type
        if ($criterion->tipe_kriteria === 'cost') {
            return 100000; // Default cost value
        } else {
            return 5; // Default benefit score (1-9 scale)
        }
    }

    /**
     * Convert tingkat kerusakan to numeric score (Higher is worse, so higher score for benefit type)
     */
    private function getTingkatKerusakanScore($tingkat)
    {
        $scores = [
            'Ringan' => 3,
            'Sedang' => 6,
            'Berat' => 9
        ];
        
        return $scores[$tingkat] ?? 5;
    }

    /**
     * Convert kepentingan to numeric score (Higher is more important)
     */
    private function getKepentinganScore($kepentingan)
    {
        $scores = [
            'Rendah' => 3,
            'Sedang' => 6,
            'Tinggi' => 9
        ];
        
        return $scores[$kepentingan] ?? 5;
    }

    /**
     * Convert waktu estimation to numeric score (For cost criteria - lower time is better)
     */
    private function getWaktuScore($waktu)
    {
        // Extract number from time string
        preg_match('/(\d+)/', $waktu, $matches);
        $days = isset($matches[1]) ? intval($matches[1]) : 1;
        
        // Return actual days for cost criteria (lower is better)
        return $days;
    }

    /**
     * Convert kompleksitas to numeric score
     */
    private function getKompleksitasScore($kompleksitas)
    {
        $scores = [
            'Rendah' => 3,
            'Sedang' => 6,
            'Tinggi' => 9
        ];
        
        return $scores[$kompleksitas] ?? 5;
    }

    /**
     * Convert urgensi to numeric score
     */
    private function getUrgensiScore($urgensi)
    {
        $scores = [
            'Rendah' => 3,
            'Sedang' => 6,
            'Tinggi' => 9
        ];
        
        return $scores[$urgensi] ?? 5;
    }

    /**
     * Convert dampak to numeric score
     */
    private function getDampakScore($dampak)
    {
        $scores = [
            'Rendah' => 3,
            'Sedang' => 6,
            'Tinggi' => 9
        ];
        
        return $scores[$dampak] ?? 5;
    }

    /**
     * Normalize custom criteria values
     */
    private function normalizeCustomValue($value, $criterion)
    {
        if (is_numeric($value)) {
            return floatval($value);
        }
        
        if (is_null($value) || $value === '') {
            return $criterion->tipe_kriteria === 'cost' ? 0 : 1;
        }
        
        // For text values, try to map to numeric scale
        $valueLower = strtolower(trim($value));
        
        // Common low values
        if (in_array($valueLower, ['rendah', 'low', 'ringan', 'mudah', 'cepat', 'murah'])) {
            return 3;
        } 
        // Common medium values
        elseif (in_array($valueLower, ['sedang', 'medium', 'tengah', 'normal', 'standar'])) {
            return 6;
        } 
        // Common high values
        elseif (in_array($valueLower, ['tinggi', 'high', 'berat', 'sulit', 'lama', 'mahal'])) {
            return 9;
        }
        
        // Try to extract numbers from mixed text
        preg_match('/(\d+)/', $value, $matches);
        if (isset($matches[1])) {
            return floatval($matches[1]);
        }
        
        // Default value based on criteria type
        return $criterion->tipe_kriteria === 'cost' ? 1000 : 5;
    }

    /**
     * Normalize the decision matrix using Euclidean normalization
     */
    private function normalizeMatrix($matrix)
    {
        if (empty($matrix)) {
            return [];
        }
        
        $normalized = [];
        $columnSums = [];
        
        // Get all criteria keys (excluding 'id') - Fix for undefined array key 0
        $criteriaKeys = [];
        $firstRow = reset($matrix); // Get first element safely
        if ($firstRow && is_array($firstRow)) {
            $criteriaKeys = array_filter(array_keys($firstRow), function($key) {
                return $key !== 'id';
            });
        }
        
        if (empty($criteriaKeys)) {
            Log::error('No criteria keys found in matrix', [
                'matrix_structure' => array_keys($matrix),
                'first_row' => $firstRow
            ]);
            return [];
        }
        
        Log::info('Matrix normalization', [
            'criteria_keys' => $criteriaKeys,
            'matrix_count' => count($matrix)
        ]);
        
        // Calculate column sums for normalization (sum of squares)
        foreach ($matrix as $row) {
            if (!is_array($row)) {
                Log::warning('Invalid row in matrix', ['row' => $row]);
                continue;
            }
            
            foreach ($criteriaKeys as $key) {
                if (!isset($columnSums[$key])) {
                    $columnSums[$key] = 0;
                }
                $value = $row[$key] ?? 0;
                $columnSums[$key] += pow($value, 2);
            }
        }
        
        // Calculate square roots
        foreach ($columnSums as $key => $sum) {
            $columnSums[$key] = sqrt($sum);
        }
        
        Log::info('Matrix normalization column sums', [
            'column_sums' => $columnSums
        ]);
        
        // Normalize values
        foreach ($matrix as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            
            $normalized[$index] = ['id' => $row['id'] ?? $index];
            
            foreach ($criteriaKeys as $key) {
                $value = $row[$key] ?? 0;
                $normalized[$index][$key] = $columnSums[$key] > 0 ? $value / $columnSums[$key] : 0;
            }
        }
        
        Log::info('Matrix normalization completed', [
            'normalized_count' => count($normalized)
        ]);
        
        return $normalized;
    }

    /**
     * Apply AHP weights dynamically to normalized matrix
     */
    private function applyDynamicWeights($matrix, $ahpCriteriaWeights)
    {
        $weighted = [];
        
        Log::info('Applying dynamic weights', [
            'weights_available' => array_keys($ahpCriteriaWeights)
        ]);
        
        foreach ($matrix as $index => $row) {
            $weighted[$index] = ['id' => $row['id']];
            
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                
                $weight = $ahpCriteriaWeights[$key]['weight'] ?? 0;
                $weighted[$index][$key] = $value * $weight;
                
                Log::debug('Applied weight', [
                    'criteria' => $key,
                    'original_value' => $value,
                    'weight' => $weight,
                    'weighted_value' => $weighted[$index][$key]
                ]);
            }
        }
        
        return $weighted;
    }

    /**
     * Get ideal positive solution dynamically based on criteria types
     */
    private function getDynamicIdealPositive($matrix, $ahpCriteriaWeights)
    {
        if (empty($matrix)) {
            return [];
        }
        
        $ideal = [];
        
        // Fix: Use reset() instead of $matrix[0]
        $firstRow = reset($matrix);
        if (!$firstRow || !is_array($firstRow)) {
            Log::error('Invalid matrix structure for ideal positive calculation', [
                'matrix_structure' => array_keys($matrix),
                'first_row' => $firstRow
            ]);
            return [];
        }
        
        $criteriaKeys = array_filter(array_keys($firstRow), function($key) {
            return $key !== 'id';
        });
        
        foreach ($criteriaKeys as $criteriaId) {
            $values = array_column($matrix, $criteriaId);
            $criteriaInfo = $ahpCriteriaWeights[$criteriaId] ?? ['tipe_kriteria' => 'benefit'];
            
            if ($criteriaInfo['tipe_kriteria'] === 'cost') {
                // Cost criteria - lower is better (minimum is ideal)
                $ideal[$criteriaId] = min($values);
            } else {
                // Benefit criteria - higher is better (maximum is ideal)
                $ideal[$criteriaId] = max($values);
            }
        }
        
        Log::info('Ideal positive solution calculated', ['ideal_positive' => $ideal]);
        
        return $ideal;
    }

    /**
     * Get ideal negative solution dynamically based on criteria types
     */
    private function getDynamicIdealNegative($matrix, $ahpCriteriaWeights)
    {
        if (empty($matrix)) {
            return [];
        }
        
        $ideal = [];
        
        // Fix: Use reset() instead of $matrix[0]
        $firstRow = reset($matrix);
        if (!$firstRow || !is_array($firstRow)) {
            Log::error('Invalid matrix structure for ideal negative calculation', [
                'matrix_structure' => array_keys($matrix),
                'first_row' => $firstRow
            ]);
            return [];
        }
        
        $criteriaKeys = array_filter(array_keys($firstRow), function($key) {
            return $key !== 'id';
        });
        
        foreach ($criteriaKeys as $criteriaId) {
            $values = array_column($matrix, $criteriaId);
            $criteriaInfo = $ahpCriteriaWeights[$criteriaId] ?? ['tipe_kriteria' => 'benefit'];
            
            if ($criteriaInfo['tipe_kriteria'] === 'cost') {
                // Cost criteria - lower is better, so max is worst (negative ideal)
                $ideal[$criteriaId] = max($values);
            } else {
                // Benefit criteria - higher is better, so min is worst (negative ideal)
                $ideal[$criteriaId] = min($values);
            }
        }
        
        Log::info('Ideal negative solution calculated', ['ideal_negative' => $ideal]);
        
        return $ideal;
    }

    /**
     * Calculate Euclidean distances to ideal solutions
     */
    private function calculateDistances($matrix, $idealPositive, $idealNegative)
    {
        $distances = [];
        
        foreach ($matrix as $row) {
            $distancePositive = 0;
            $distanceNegative = 0;
            
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                
                $distancePositive += pow($value - ($idealPositive[$key] ?? 0), 2);
                $distanceNegative += pow($value - ($idealNegative[$key] ?? 0), 2);
            }
            
            $distances[$row['id']] = [
                'positive' => sqrt($distancePositive),
                'negative' => sqrt($distanceNegative)
            ];
        }
        
        Log::info('Distances calculated for ' . count($distances) . ' alternatives');
        
        return $distances;
    }

    /**
     * Calculate relative closeness (similarity to ideal solution)
     */
    private function calculateRelativeCloseness($distances)
    {
        $scores = [];
        
        foreach ($distances as $id => $distance) {
            $totalDistance = $distance['positive'] + $distance['negative'];
            
            // Relative closeness coefficient
            $scores[$id] = $totalDistance > 0 ? $distance['negative'] / $totalDistance : 0;
        }
        
        // Sort by score descending (higher score = higher priority)
        arsort($scores);
        
        Log::info('Relative closeness calculated', [
            'scores_sample' => array_slice($scores, 0, 5, true)
        ]);
        
        return $scores;
    }

    /**
     * Format scores with ranks
     */
    private function formatScoresWithRanks($scores)
    {
        $rank = 1;
        $rankedScores = [];
        
        foreach ($scores as $id => $score) {
            $rankedScores[$id] = [
                'score' => round($score, 6),
                'rank' => $rank++
            ];
        }
        
        return $rankedScores;
    }

    /**
     * Update maintenance assets with scores and rankings
     */
    private function updateAssetsWithScores($maintenanceAssets, $scores)
    {
        $updatedCount = 0;
        
        foreach ($scores as $damagedAssetId => $scoreData) {
            // Find maintenance asset by damaged asset ID
            $maintenanceAsset = $maintenanceAssets->firstWhere('damage_id', function($value) use ($damagedAssetId) {
                // Find by damaged asset relationship
                return $value === $damagedAssetId;
            });
            
            if (!$maintenanceAsset) {
                // Try alternative approach - find by damaged asset ID directly
                $maintenanceAsset = $maintenanceAssets->filter(function($asset) use ($damagedAssetId) {
                    return $asset->damagedAsset && $asset->damagedAsset->id === $damagedAssetId;
                })->first();
            }
            
            if ($maintenanceAsset) {
                $maintenanceAsset->update([
                    'priority_score' => $scoreData['score'],
                    'priority_calculated_at' => now(),
                    'priority_method' => 'TOPSIS_AHP_Dynamic'
                ]);
                $updatedCount++;
                
                Log::info('Updated maintenance asset with TOPSIS score', [
                    'maintenance_id' => $maintenanceAsset->maintenance_id,
                    'damaged_asset_id' => $damagedAssetId,
                    'score' => $scoreData['score'],
                    'rank' => $scoreData['rank']
                ]);
            } else {
                Log::warning('Could not find maintenance asset for damaged asset', [
                    'damaged_asset_id' => $damagedAssetId
                ]);
            }
        }
        
        return $updatedCount;
    }

    /**
     * Legacy method for backward compatibility
     */
    public function calculatePriority(Collection $maintenanceAssets)
    {
        if ($maintenanceAssets->isEmpty()) {
            return [];
        }

        // Try to get AHP weights from session
        $ahpCriteriaWeights = session('ahp_criteria_weights');
        
        if ($ahpCriteriaWeights) {
            // Use dynamic calculation with AHP weights
            $damagedAssets = $maintenanceAssets->map(function($asset) {
                return $asset->damagedAsset;
            })->filter();
            
            $scores = $this->calculatePriorityWithWeights($damagedAssets, $ahpCriteriaWeights);
            
            // Update the maintenance assets
            $this->updateAssetsWithScores($maintenanceAssets, $scores);
            
            return $scores;
        }
        
        // Fallback to default weights
        $defaultWeights = $this->getDefaultCriteriaWeights();
        $damagedAssets = $maintenanceAssets->map(function($asset) {
            return $asset->damagedAsset;
        })->filter();
        
        $scores = $this->calculatePriorityWithWeights($damagedAssets, $defaultWeights);
        $this->updateAssetsWithScores($maintenanceAssets, $scores);
        
        return $scores;
    }

    /**
     * Get default criteria weights when AHP is not available
     */
    private function getDefaultCriteriaWeights()
    {
        $criteria = Criteria::all();
        $defaultWeights = [];
        
        $weightPerCriteria = 1.0 / max(1, $criteria->count());
        
        foreach ($criteria as $criterion) {
            $defaultWeights[$criterion->kriteria_id] = [
                'weight' => $weightPerCriteria,
                'nama_kriteria' => $criterion->nama_kriteria,
                'tipe_kriteria' => $criterion->tipe_kriteria
            ];
        }
        
        Log::info('Using default criteria weights', [
            'criteria_count' => $criteria->count(),
            'weight_per_criteria' => $weightPerCriteria
        ]);
        
        return $defaultWeights;
    }

    /**
     * Trigger TOPSIS calculation (can be called from controller)
     */
    public function triggerTopsisCalculation(Request $request)
    {
        $user = Auth::user();
        
        // Only allow kaur roles to trigger TOPSIS calculation
        if (!$user->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm'])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk melakukan kalkulasi prioritas.'
            ], 403);
        }
        
        try {
            $currentUserDepartment = AhpWeight::getUserDepartment($user);
            
            // Get all pending maintenance assets with their relationships
            $pendingAssets = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Menunggu Persetujuan')
                ->get();
            
            // if ($pendingAssets->count() === 0) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Tidak ada pengajuan yang perlu dikalkulasi prioritasnya.'
            //     ]);
            // }
            
            $updatedCount = 0;
            $totalAssets = $pendingAssets->count();
            $departmentInfo = [];
            
            if ($user->hasRole('kaur_laboratorium')) {
                // Use laboratorium department AHP weights for all assets under kaur_laboratorium
                $ahpCriteriaWeights = AhpWeight::getActiveWeightsForTopsis('laboratorium');
                
                if (!$ahpCriteriaWeights) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bobot kriteria AHP untuk departemen laboratorium belum tersedia. Silakan lakukan kalkulasi AHP terlebih dahulu.',
                        'redirect_url' => route('kriteria.create')
                    ]);
                }
                
                // Validate that weights have actual values
                $hasValidWeights = false;
                foreach ($ahpCriteriaWeights as $criteriaId => $data) {
                    if (isset($data['weight']) && $data['weight'] > 0) {
                        $hasValidWeights = true;
                        break;
                    }
                }
                
                if (!$hasValidWeights) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bobot kriteria AHP laboratorium tidak valid (semua bobot bernilai 0). Silakan lakukan kalkulasi AHP ulang.',
                        'redirect_url' => route('kriteria.create')
                    ]);
                }
                
                // Check if weights are consistent
                if (!AhpWeight::areCurrentWeightsConsistent('laboratorium')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bobot kriteria AHP laboratorium tidak konsisten (CR > 0.1). Silakan lakukan kalkulasi AHP ulang.',
                        'redirect_url' => route('kriteria.create')
                    ]);
                }
                
                Log::info('Starting TOPSIS calculation for kaur_laboratorium', [
                    'pending_assets_count' => $pendingAssets->count(),
                    'criteria_weights' => $ahpCriteriaWeights,
                    'user' => $user->name
                ]);
                
                // Calculate TOPSIS scores with laboratorium AHP weights
                $priorityScores = $this->topsisService->calculatePriorityWithWeights(
                    $pendingAssets, 
                    $ahpCriteriaWeights
                );
                
                $updatedCount = count($priorityScores);
                $departmentInfo = [
                    'laboratorium' => [
                        'assets_count' => $pendingAssets->count(),
                        'updated_count' => $updatedCount,
                        'criteria_count' => count($ahpCriteriaWeights)
                    ]
                ];
                
            } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
                // Separate assets by location/department and use appropriate weights
                $labAssets = $pendingAssets->filter(function($asset) {
                    return str_contains($asset->asset->lokasi, 'Laboratorium');
                });
                
                $logisticAssets = $pendingAssets->filter(function($asset) {
                    return !str_contains($asset->asset->lokasi, 'Laboratorium');
                });
                
                $departmentInfo = [
                    'laboratorium' => ['assets_count' => $labAssets->count(), 'updated_count' => 0],
                    'keuangan_logistik' => ['assets_count' => $logisticAssets->count(), 'updated_count' => 0]
                ];
                
                // Calculate for lab assets using laboratorium weights
                if ($labAssets->count() > 0) {
                    $labAhpWeights = AhpWeight::getActiveWeightsForTopsis('laboratorium');
                    if ($labAhpWeights && AhpWeight::areCurrentWeightsConsistent('laboratorium')) {
                        Log::info('Calculating TOPSIS for lab assets by kaur_keuangan', [
                            'assets_count' => $labAssets->count(),
                            'department' => 'laboratorium'
                        ]);
                        
                        $labPriorityScores = $this->topsisService->calculatePriorityWithWeights($labAssets, $labAhpWeights);
                        $updatedCount += count($labPriorityScores);
                        $departmentInfo['laboratorium']['updated_count'] = count($labPriorityScores);
                        $departmentInfo['laboratorium']['criteria_count'] = count($labAhpWeights);
                    } else {
                        Log::warning('Lab AHP weights not available or inconsistent for kaur_keuangan calculation');
                    }
                }
                
                // Calculate for logistic assets using keuangan_logistik weights
                if ($logisticAssets->count() > 0) {
                    $logisticAhpWeights = AhpWeight::getActiveWeightsForTopsis('keuangan_logistik');
                    if ($logisticAhpWeights && AhpWeight::areCurrentWeightsConsistent('keuangan_logistik')) {
                        Log::info('Calculating TOPSIS for logistic assets by kaur_keuangan', [
                            'assets_count' => $logisticAssets->count(),
                            'department' => 'keuangan_logistik'
                        ]);
                        
                        $logisticPriorityScores = $this->topsisService->calculatePriorityWithWeights($logisticAssets, $logisticAhpWeights);
                        $updatedCount += count($logisticPriorityScores);
                        $departmentInfo['keuangan_logistik']['updated_count'] = count($logisticPriorityScores);
                        $departmentInfo['keuangan_logistik']['criteria_count'] = count($logisticAhpWeights);
                    } else {
                        Log::warning('Logistic AHP weights not available or inconsistent for kaur_keuangan calculation');
                    }
                }
                
                if ($updatedCount === 0) {
                    $missingDepartments = [];
                    if ($labAssets->count() > 0 && $departmentInfo['laboratorium']['updated_count'] === 0) {
                        $missingDepartments[] = 'laboratorium';
                    }
                    if ($logisticAssets->count() > 0 && $departmentInfo['keuangan_logistik']['updated_count'] === 0) {
                        $missingDepartments[] = 'keuangan_logistik';
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Bobot kriteria AHP untuk departemen ' . implode(' dan ', $missingDepartments) . ' belum tersedia atau tidak konsisten. Silakan pastikan kalkulasi AHP sudah dilakukan untuk semua departemen.',
                        'missing_departments' => $missingDepartments,
                        'redirect_url' => route('kriteria.create')
                    ]);
                }
            }
            
            if ($updatedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghitung prioritas. Tidak ada data yang valid untuk dikalkulasi.'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghitung prioritas untuk {$updatedCount} dari {$totalAssets} pengajuan menggunakan metode TOPSIS dengan bobot AHP departemen yang sesuai.",
                'data' => [
                    'updated_count' => $updatedCount,
                    'total_assets' => $totalAssets,
                    'current_user_department' => $currentUserDepartment,
                    'department_breakdown' => $departmentInfo,
                    'method' => 'TOPSIS_AHP_Department_Specific',
                    'calculation_time' => now()->toDateTimeString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('TOPSIS calculation error: ' . $e->getMessage(), [
                'user' => $user->name,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung prioritas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed calculation report for debugging/analysis
     */
    public function getCalculationReport(Collection $damagedAssets, array $ahpCriteriaWeights)
    {
        $decisionMatrix = $this->buildDynamicDecisionMatrix($damagedAssets);
        $normalizedMatrix = $this->normalizeMatrix($decisionMatrix);
        $weightedMatrix = $this->applyDynamicWeights($normalizedMatrix, $ahpCriteriaWeights);
        $idealPositive = $this->getDynamicIdealPositive($weightedMatrix, $ahpCriteriaWeights);
        $idealNegative = $this->getDynamicIdealNegative($weightedMatrix, $ahpCriteriaWeights);
        $distances = $this->calculateDistances($weightedMatrix, $idealPositive, $idealNegative);
        $scores = $this->calculateRelativeCloseness($distances);
        
        return [
            'decision_matrix' => $decisionMatrix,
            'normalized_matrix' => $normalizedMatrix,
            'weighted_matrix' => $weightedMatrix,
            'ideal_positive' => $idealPositive,
            'ideal_negative' => $idealNegative,
            'distances' => $distances,
            'final_scores' => $this->formatScoresWithRanks($scores),
            'criteria_weights' => $ahpCriteriaWeights
        ];
    }

    /**
     * Debug TOPSIS calculation with detailed logging
     */
    public function debugCalculatePriorityWithWeights(Collection $maintenanceAssets, array $ahpCriteriaWeights)
    {
        Log::info('=== STARTING TOPSIS DEBUG CALCULATION ===', [
            'assets_count' => $maintenanceAssets->count(),
            'criteria_weights_count' => count($ahpCriteriaWeights)
        ]);

        if ($maintenanceAssets->isEmpty()) {
            Log::warning('Empty maintenance assets collection');
            return [];
        }

        // Debug 1: Check AHP weights structure
        Log::info('AHP Criteria Weights Structure:', [
            'weights' => $ahpCriteriaWeights,
            'criteria_ids' => array_keys($ahpCriteriaWeights)
        ]);

        // Debug 2: Check criteria in database
        $criteria = Criteria::all();
        Log::info('Database Criteria:', [
            'criteria_count' => $criteria->count(),
            'criteria_details' => $criteria->map(function($c) {
                return [
                    'id' => $c->kriteria_id,
                    'name' => $c->nama_kriteria,
                    'type' => $c->tipe_kriteria
                ];
            })->toArray()
        ]);

        // Debug 3: Check asset relationships and data
        foreach ($maintenanceAssets as $index => $item) {
            Log::info("=== ASSET DEBUG {$index} ===");
            
            if ($item instanceof \App\Models\MaintenanceAsset) {
                Log::info('MaintenanceAsset Details:', [
                    'maintenance_id' => $item->maintenance_id,
                    'id' => $item->id,
                    'asset_id' => $item->asset_id,
                    'damage_id' => $item->damage_id
                ]);

                $damagedAsset = $item->damagedAsset;
                $relatedAsset = $item->asset;

                Log::info('DamagedAsset Check:', [
                    'exists' => !is_null($damagedAsset),
                    'id' => $damagedAsset ? $damagedAsset->id : null,
                    'tingkat_kerusakan' => $damagedAsset ? $damagedAsset->tingkat_kerusakan : null,
                    'estimasi_biaya' => $damagedAsset ? $damagedAsset->estimasi_biaya : null,
                    'asset_relationship' => $damagedAsset && $damagedAsset->asset ? 'exists' : 'missing'
                ]);

                Log::info('RelatedAsset Check:', [
                    'exists' => !is_null($relatedAsset),
                    'id' => $relatedAsset ? $relatedAsset->asset_id : null,
                    'nama_asset' => $relatedAsset ? $relatedAsset->nama_asset : null,
                    'lokasi' => $relatedAsset ? $relatedAsset->lokasi : null,
                    'tingkat_kepentingan_asset' => $relatedAsset ? $relatedAsset->tingkat_kepentingan_asset : null
                ]);

                // Test criteria extraction for this asset
                if ($damagedAsset) {
                    foreach ($criteria as $criterion) {
                        $extractedValue = $this->debugExtractCriteriaValue($damagedAsset, $criterion);
                        Log::info("Criteria Extraction Test:", [
                            'criteria_id' => $criterion->kriteria_id,
                            'criteria_name' => $criterion->nama_kriteria,
                            'extracted_value' => $extractedValue
                        ]);
                    }
                }
            }
        }

        // Debug 4: Build decision matrix with detailed logging
        try {
            Log::info('=== BUILDING DECISION MATRIX ===');
            $decisionMatrix = $this->buildDynamicDecisionMatrixDebug($maintenanceAssets);
            
            if (empty($decisionMatrix)) {
                Log::error('Decision matrix is empty - calculation cannot proceed');
                return [];
            }

            Log::info('Decision Matrix Built:', [
                'matrix_count' => count($decisionMatrix),
                'sample_row' => $decisionMatrix[0] ?? null,
                'all_rows' => $decisionMatrix
            ]);

            // Debug 5: Test normalization
            Log::info('=== TESTING NORMALIZATION ===');
            $normalizedMatrix = $this->normalizeMatrixDebug($decisionMatrix);
            
            Log::info('Normalized Matrix:', [
                'matrix_count' => count($normalizedMatrix),
                'sample_row' => $normalizedMatrix[0] ?? null
            ]);

            // Debug 6: Test weight application
            Log::info('=== TESTING WEIGHT APPLICATION ===');
            $weightedMatrix = $this->applyDynamicWeightsDebug($normalizedMatrix, $ahpCriteriaWeights);
            
            Log::info('Weighted Matrix:', [
                'matrix_count' => count($weightedMatrix),
                'sample_row' => $weightedMatrix[0] ?? null
            ]);

            // Debug 7: Test ideal solutions
            Log::info('=== TESTING IDEAL SOLUTIONS ===');
            $idealPositive = $this->getDynamicIdealPositive($weightedMatrix, $ahpCriteriaWeights);
            $idealNegative = $this->getDynamicIdealNegative($weightedMatrix, $ahpCriteriaWeights);
            
            Log::info('Ideal Solutions:', [
                'ideal_positive' => $idealPositive,
                'ideal_negative' => $idealNegative
            ]);

            // Debug 8: Test distance calculation
            Log::info('=== TESTING DISTANCE CALCULATION ===');
            $distances = $this->calculateDistances($weightedMatrix, $idealPositive, $idealNegative);
            
            Log::info('Distances:', [
                'distances_count' => count($distances),
                'distances' => $distances
            ]);

            // Debug 9: Test final scoring
            Log::info('=== TESTING FINAL SCORING ===');
            $scores = $this->calculateRelativeCloseness($distances);
            $rankedScores = $this->formatScoresWithRanks($scores);
            
            Log::info('Final Scores:', [
                'scores' => $rankedScores
            ]);

            Log::info('=== TOPSIS DEBUG CALCULATION COMPLETED SUCCESSFULLY ===');
            return $rankedScores;

        } catch (\Exception $e) {
            Log::error('TOPSIS Debug Calculation Failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Debug version of criteria value extraction
     */
    private function debugExtractCriteriaValue($asset, $criterion)
    {
        $criteriaNameLower = strtolower($criterion->nama_kriteria);
        
        Log::debug("Extracting criteria value:", [
            'criteria_name' => $criterion->nama_kriteria,
            'criteria_name_lower' => $criteriaNameLower,
            'criteria_type' => $criterion->tipe_kriteria
        ]);

        // Handle specific criteria based on name patterns
        if (str_contains($criteriaNameLower, 'kerusakan')) {
            $value = $this->getTingkatKerusakanScore($asset->tingkat_kerusakan);
            Log::debug("Kerusakan extraction:", [
                'raw_value' => $asset->tingkat_kerusakan,
                'converted_value' => $value
            ]);
            return $value;
        }
        
        if (str_contains($criteriaNameLower, 'biaya')) {
            $value = floatval($asset->estimasi_biaya);
            Log::debug("Biaya extraction:", [
                'raw_value' => $asset->estimasi_biaya,
                'converted_value' => $value
            ]);
            return $value;
        }
        
        if (str_contains($criteriaNameLower, 'kepentingan')) {
            // Check if asset relationship exists
            if (!$asset->asset) {
                Log::warning("Asset relationship missing for kepentingan extraction");
                $value = $this->getKepentinganScore('Sedang');
            } else {
                $kepentingan = $asset->asset->tingkat_kepentingan_asset ?? 'Sedang';
                $value = $this->getKepentinganScore($kepentingan);
            }
            
            Log::debug("Kepentingan extraction:", [
                'asset_exists' => !is_null($asset->asset),
                'raw_value' => $asset->asset->tingkat_kepentingan_asset ?? 'default',
                'converted_value' => $value
            ]);
            return $value;
        }
        
        // Default handling
        $defaultValue = $criterion->tipe_kriteria === 'cost' ? 100000 : 5;
        Log::debug("Default value used:", [
            'criteria_type' => $criterion->tipe_kriteria,
            'default_value' => $defaultValue
        ]);
        
        return $defaultValue;
    }

    /**
     * Debug version of decision matrix building
     */
    private function buildDynamicDecisionMatrixDebug(Collection $assets)
    {
        $criteria = Criteria::all();
        $matrix = [];
        
        Log::info('Building decision matrix with debug:', [
            'criteria_count' => $criteria->count(),
            'assets_count' => $assets->count()
        ]);
        
        foreach ($assets as $index => $item) {
            Log::info("Processing asset {$index}");
            
            // Handle both MaintenanceAsset and DamagedAsset
            if ($item instanceof \App\Models\MaintenanceAsset) {
                $asset = $item->damagedAsset;
                $rowId = $item->id;
                
                if (!$asset) {
                    Log::error("MaintenanceAsset {$item->id} has no damagedAsset relationship");
                    continue;
                }
            } else {
                $asset = $item;
                $rowId = $item->id;
            }
            
            $row = ['id' => $rowId];
            
            foreach ($criteria as $criterion) {
                $value = $this->debugExtractCriteriaValue($asset, $criterion);
                $row[$criterion->kriteria_id] = $value;
                
                Log::debug('Matrix value set:', [
                    'asset_id' => $rowId,
                    'criteria_id' => $criterion->kriteria_id,
                    'value' => $value
                ]);
            }
            
            $matrix[$index] = $row;
            Log::info("Asset {$index} row completed:", ['row' => $row]);
        }
        
        Log::info('Decision matrix completed:', [
            'matrix_rows' => count($matrix),
            'matrix' => $matrix
        ]);
        
        return $matrix;
    }

    /**
     * Debug version of matrix normalization
     */
    private function normalizeMatrixDebug($matrix)
    {
        if (empty($matrix)) {
            Log::error('Cannot normalize empty matrix');
            return [];
        }

        $normalized = [];
        $columnSums = [];
        
        // Get all criteria keys (excluding 'id')
        $criteriaKeys = array_filter(array_keys($matrix[0]), function($key) {
            return $key !== 'id';
        });
        
        Log::info('Normalization criteria keys:', ['keys' => $criteriaKeys]);
        
        // Calculate column sums for normalization (sum of squares)
        foreach ($matrix as $row) {
            foreach ($criteriaKeys as $key) {
                if (!isset($columnSums[$key])) {
                    $columnSums[$key] = 0;
                }
                $columnSums[$key] += pow($row[$key], 2);
            }
        }
        
        // Calculate square roots
        foreach ($columnSums as $key => $sum) {
            $columnSums[$key] = sqrt($sum);
            
            // Check for zero division
            if ($columnSums[$key] == 0) {
                Log::warning("Zero column sum for criteria {$key} - this will cause division by zero");
            }
        }
        
        Log::info('Normalization column sums:', [
            'column_sums' => $columnSums
        ]);
        
        // Normalize values
        foreach ($matrix as $index => $row) {
            $normalized[$index] = ['id' => $row['id']];
            
            foreach ($criteriaKeys as $key) {
                if ($columnSums[$key] > 0) {
                    $normalized[$index][$key] = $row[$key] / $columnSums[$key];
                } else {
                    $normalized[$index][$key] = 0;
                    Log::warning("Zero division avoided for criteria {$key}");
                }
            }
        }
        
        Log::info('Matrix normalization completed:', [
            'normalized_count' => count($normalized)
        ]);
        
        return $normalized;
    }

    /**
     * Debug version of weight application
     */
    private function applyDynamicWeightsDebug($matrix, $ahpCriteriaWeights)
    {
        $weighted = [];
        
        Log::info('Applying weights with debug:', [
            'matrix_count' => count($matrix),
            'weights_available' => array_keys($ahpCriteriaWeights)
        ]);
        
        foreach ($matrix as $index => $row) {
            $weighted[$index] = ['id' => $row['id']];
            
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                
                $weight = $ahpCriteriaWeights[$key]['weight'] ?? 0;
                $weighted[$index][$key] = $value * $weight;
                
                Log::debug('Weight application:', [
                    'criteria' => $key,
                    'original_value' => $value,
                    'weight' => $weight,
                    'weighted_value' => $weighted[$index][$key]
                ]);
            }
        }
        
        Log::info('Weight application completed');
        return $weighted;
    }
}