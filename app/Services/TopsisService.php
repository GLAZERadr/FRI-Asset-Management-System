<?php

namespace App\Services;

use App\Models\MaintenanceAsset;
use App\Models\Criteria;
use App\Models\CriteriaComparison;
use Illuminate\Support\Collection;

class TopsisService
{
    /**
     * Calculate TOPSIS for maintenance assets using AHP weights
     */
    public function calculatePriorityWithWeights(Collection $damagedAssets, array $ahpWeights)
    {
        if ($damagedAssets->isEmpty()) {
            return [];
        }

        // Build decision matrix
        $decisionMatrix = $this->buildDecisionMatrixFromDamaged($damagedAssets);
        
        // Normalize the matrix
        $normalizedMatrix = $this->normalizeMatrix($decisionMatrix);
        
        // Map AHP weights to criteria (assume mapping based on criteria order)
        $criteriaWeights = $this->mapAhpWeightsToCriteria($ahpWeights);
        
        // Apply weights
        $weightedMatrix = $this->applyWeights($normalizedMatrix, $criteriaWeights);
        
        // Determine ideal solutions
        $idealPositive = $this->getIdealPositive($weightedMatrix);
        $idealNegative = $this->getIdealNegative($weightedMatrix);
        
        // Calculate distances
        $distances = $this->calculateDistances($weightedMatrix, $idealPositive, $idealNegative);
        
        // Calculate relative closeness
        $scores = $this->calculateRelativeCloseness($distances);
        
        return $this->formatScoresWithRanks($scores);
    }

    /**
     * Calculate TOPSIS for maintenance assets with default weights
     */
    public function calculatePriority(Collection $maintenanceAssets)
    {
        if ($maintenanceAssets->isEmpty()) {
            return [];
        }

        // Get criteria and their weights from AHP
        $criteriaWeights = $this->getCriteriaWeights();
        
        // Build decision matrix
        $decisionMatrix = $this->buildDecisionMatrix($maintenanceAssets);
        
        // Normalize the matrix
        $normalizedMatrix = $this->normalizeMatrix($decisionMatrix);
        
        // Apply weights
        $weightedMatrix = $this->applyWeights($normalizedMatrix, $criteriaWeights);
        
        // Determine ideal solutions
        $idealPositive = $this->getIdealPositive($weightedMatrix);
        $idealNegative = $this->getIdealNegative($weightedMatrix);
        
        // Calculate distances
        $distances = $this->calculateDistances($weightedMatrix, $idealPositive, $idealNegative);
        
        // Calculate relative closeness
        $scores = $this->calculateRelativeCloseness($distances);
        
        // Update assets with scores and rankings
        $this->updateAssetsWithScores($maintenanceAssets, $scores);
        
        return $scores;
    }

    /**
     * Map AHP weights to TOPSIS criteria
     */
    private function mapAhpWeightsToCriteria($ahpWeights)
    {
        // Default mapping - adjust based on your criteria structure
        return [
            'tingkat_kerusakan' => $ahpWeights[0] ?? 0.5,
            'kepentingan_asset' => $ahpWeights[1] ?? 0.3,
            'estimasi_biaya' => $ahpWeights[2] ?? 0.2
        ];
    }

    /**
     * Build decision matrix from damaged assets
     */
    private function buildDecisionMatrixFromDamaged(Collection $damagedAssets)
    {
        $matrix = [];
        
        foreach ($damagedAssets as $index => $asset) {
            $matrix[$index] = [
                'id' => $asset->id,
                'tingkat_kerusakan' => $this->getTingkatKerusakanScore($asset->tingkat_kerusakan),
                'kepentingan_asset' => $asset->asset->tingkat_kepentingan_asset,
                'estimasi_biaya' => $asset->estimasi_biaya
            ];
        }
        
        return $matrix;
    }

    /**
     * Get criteria weights from AHP calculation
     */
    private function getCriteriaWeights()
    {
        // Get weights from the latest AHP calculation or use defaults
        return [
            'tingkat_kerusakan' => 0.5,
            'kepentingan_asset' => 0.3,
            'estimasi_biaya' => 0.2
        ];
    }

    /**
     * Build decision matrix from maintenance assets
     */
    private function buildDecisionMatrix(Collection $maintenanceAssets)
    {
        $matrix = [];
        
        foreach ($maintenanceAssets as $index => $asset) {
            $matrix[$index] = [
                'id' => $asset->id,
                'tingkat_kerusakan' => $this->getTingkatKerusakanScore($asset->damagedAsset->tingkat_kerusakan),
                'kepentingan_asset' => $asset->asset->tingkat_kepentingan_asset,
                'estimasi_biaya' => $asset->damagedAsset->estimasi_biaya
            ];
        }
        
        return $matrix;
    }

    /**
     * Convert tingkat kerusakan to numeric score
     */
    private function getTingkatKerusakanScore($tingkat)
    {
        $scores = [
            'Ringan' => 3,
            'Sedang' => 6,
            'Berat' => 9
        ];
        
        return $scores[$tingkat] ?? 1;
    }

    /**
     * Normalize the decision matrix
     */
    private function normalizeMatrix($matrix)
    {
        $normalized = [];
        $columnSums = [];
        
        // Calculate column sums for normalization
        foreach ($matrix as $row) {
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                
                if (!isset($columnSums[$key])) {
                    $columnSums[$key] = 0;
                }
                $columnSums[$key] += pow($value, 2);
            }
        }
        
        // Calculate square roots
        foreach ($columnSums as $key => $sum) {
            $columnSums[$key] = sqrt($sum);
        }
        
        // Normalize values
        foreach ($matrix as $index => $row) {
            $normalized[$index] = ['id' => $row['id']];
            
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                
                $normalized[$index][$key] = $columnSums[$key] > 0 ? $value / $columnSums[$key] : 0;
            }
        }
        
        return $normalized;
    }

    /**
     * Apply weights to normalized matrix
     */
    private function applyWeights($matrix, $weights)
    {
        $weighted = [];
        
        foreach ($matrix as $index => $row) {
            $weighted[$index] = ['id' => $row['id']];
            
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                
                $weighted[$index][$key] = $value * ($weights[$key] ?? 1);
            }
        }
        
        return $weighted;
    }

    /**
     * Get ideal positive solution
     */
    private function getIdealPositive($matrix)
    {
        $ideal = [];
        $criteria = ['tingkat_kerusakan', 'kepentingan_asset', 'estimasi_biaya'];
        
        foreach ($criteria as $criterion) {
            $values = array_column($matrix, $criterion);
            
            if ($criterion === 'estimasi_biaya') {
                // Cost criteria - lower is better
                $ideal[$criterion] = min($values);
            } else {
                // Benefit criteria - higher is better
                $ideal[$criterion] = max($values);
            }
        }
        
        return $ideal;
    }

    /**
     * Get ideal negative solution
     */
    private function getIdealNegative($matrix)
    {
        $ideal = [];
        $criteria = ['tingkat_kerusakan', 'kepentingan_asset', 'estimasi_biaya'];
        
        foreach ($criteria as $criterion) {
            $values = array_column($matrix, $criterion);
            
            if ($criterion === 'estimasi_biaya') {
                // Cost criteria - lower is better
                $ideal[$criterion] = max($values);
            } else {
                // Benefit criteria - higher is better
                $ideal[$criterion] = min($values);
            }
        }
        
        return $ideal;
    }

    /**
     * Calculate distances to ideal solutions
     */
    private function calculateDistances($matrix, $idealPositive, $idealNegative)
    {
        $distances = [];
        
        foreach ($matrix as $index => $row) {
            $distancePositive = 0;
            $distanceNegative = 0;
            
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                
                $distancePositive += pow($value - $idealPositive[$key], 2);
                $distanceNegative += pow($value - $idealNegative[$key], 2);
            }
            
            $distances[$row['id']] = [
                'positive' => sqrt($distancePositive),
                'negative' => sqrt($distanceNegative)
            ];
        }
        
        return $distances;
    }

    /**
     * Calculate relative closeness
     */
    private function calculateRelativeCloseness($distances)
    {
        $scores = [];
        
        foreach ($distances as $id => $distance) {
            $totalDistance = $distance['positive'] + $distance['negative'];
            $scores[$id] = $totalDistance > 0 ? $distance['negative'] / $totalDistance : 0;
        }
        
        // Sort by score descending
        arsort($scores);
        
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
                'score' => $score,
                'rank' => $rank++
            ];
        }
        
        return $rankedScores;
    }

    /**
     * Update assets with scores and rankings
     */
    private function updateAssetsWithScores($maintenanceAssets, $scores)
    {
        $rank = 1;
        
        foreach ($scores as $id => $score) {
            $asset = $maintenanceAssets->firstWhere('id', $id);
            if ($asset) {
                $asset->update([
                    'priority_score' => $score,
                    'priority_rank' => $rank
                ]);
                $rank++;
            }
        }
    }

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
            // Get AHP weights from session
            $ahpWeights = session('ahp_weights');
            
            if (!$ahpWeights) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bobot kriteria AHP belum tersedia. Silakan lakukan kalkulasi AHP terlebih dahulu.'
                ]);
            }
            
            // Get all pending maintenance assets
            $pendingAssets = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Menunggu Persetujuan')
                ->get();
            
            if ($pendingAssets->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada pengajuan yang perlu dikalkulasi prioritasnya.'
                ]);
            }
            
            // Calculate TOPSIS scores with AHP weights
            $priorityScores = $this->topsisService->calculatePriorityWithWeights($pendingAssets, $ahpWeights);
            
            // Update all maintenance assets with new scores
            $updatedCount = 0;
            foreach ($pendingAssets as $asset) {
                if (isset($priorityScores[$asset->id])) {
                    $asset->update([
                        'priority_score' => $priorityScores[$asset->id]['score'],
                        'priority_calculated_at' => now(),
                        'priority_method' => 'TOPSIS_AHP'
                    ]);
                    $updatedCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghitung prioritas untuk {$updatedCount} pengajuan menggunakan metode TOPSIS dengan bobot AHP.",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('TOPSIS calculation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghitung prioritas: ' . $e->getMessage()
            ], 500);
        }
    }
}