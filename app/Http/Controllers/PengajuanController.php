<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceAsset;
use App\Models\DamagedAsset;
use App\Models\Asset;
use App\Models\Payment;
use App\Models\User;
use App\Models\ApprovalLog;
use App\Models\AhpWeight;
use App\Services\NotificationService;
use App\Services\TopsisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Modules\Imports\AssetDamageImport;
use App\Modules\Exports\AssetTemplateExport;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class PengajuanController extends Controller
{
    protected $notificationService;
    protected $topsisService;
    protected $processedAssets = [];
    protected $userRole;

    public function __construct(NotificationService $notificationService, TopsisService $topsisService)
    {
        $this->notificationService = $notificationService;
        $this->topsisService = $topsisService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MaintenanceAsset::with(['asset', 'damagedAsset']);
        
        // Filter based on user role according to the new rules
        if ($user->hasRole(['staff_laboratorium', 'kaur_laboratorium'])) {
            // Show only lab assets (approved or not approved by kaur keuangan)
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'LIKE', '%Laboratorium%');
            });
        } elseif ($user->hasRole(['staff_logistik'])) {
            // Show only logistic assets (approved or not approved by kaur keuangan)
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
            });
        } elseif ($user->hasRole(['kaur_keuangan_logistik_sdm'])) {
            $query->whereHas('asset');
        }

        // Apply filters
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('tanggal_pengajuan', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('tanggal_pengajuan', '<=', $request->end_date);
        }
        
        if ($request->filled('lokasi')) {
            $lokasi = $request->lokasi;
            $query->whereHas('asset', function($q) use ($lokasi) {
                $q->where('lokasi', 'LIKE', '%' . $lokasi . '%');
            });
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Get the maintenance requests - ordered by submission date
        $maintenanceRequests = $query->orderBy('tanggal_pengajuan', 'desc')->paginate(10);
        
        // Calculate stats
        $stats = $this->calculateStats($user);
        
        $locations = Asset::distinct()->pluck('lokasi')->filter();
        
        return view('pengajuan.index', compact('maintenanceRequests', 'locations', 'stats'));
    }
    
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Log the start of the method
        Log::info('PengajuanController::create called', [
            'user_role' => $user->roles->first()->name,
            'request_params' => $request->all()
        ]);
        
        if ($user->hasRole(['staff_laboratorium', 'staff_logistik'])) {
            // Staff roles logic
            $query = DamagedAsset::with(['asset', 'maintenanceAsset'])
                ->where('validated', 'Yes');
            
            if ($user->hasRole('staff_laboratorium')) {
                $query->whereHas('asset', function($q) {
                    $q->where('lokasi', 'LIKE', '%Laboratorium%');
                })
                ->where(function($q) {
                    $q->whereDoesntHave('maintenanceAsset')
                    ->orWhereHas('maintenanceAsset', function($subQ) {
                        $subQ->whereIn('status', ['Ditolak']);
                    });
                });
            } else {
                $query->whereHas('asset', function($q) {
                    $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
                })
                ->where(function($q) {
                    $q->whereDoesntHave('maintenanceAsset')
                    ->orWhereHas('maintenanceAsset', function($subQ) {
                        $subQ->whereIn('status', ['Ditolak']);
                    });
                });
            }
            
            $damagedAssets = $query->orderBy('tanggal_pelaporan', 'desc')
                ->get()
                ->groupBy('asset_id')
                ->map(function($group) {
                    return $group->first();
                })
                ->values();
                
        } elseif ($user->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2'])) {
            // Get MaintenanceAssets for kaur roles
            $query = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Menunggu Persetujuan');
                
            // Apply role-specific filters
            if ($user->hasRole('kaur_laboratorium')) {
                $query->where('requested_by_role', 'staff_laboratorium')
                    ->whereNull('kaur_lab_approved_at');
            } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
                $query->where(function($q) {
                    $q->where('requested_by_role', 'staff_logistik')
                    ->whereNull('kaur_keuangan_approved_at');
                })->orWhere(function($q) {
                    $q->where('requested_by_role', 'staff_laboratorium')
                    ->whereNotNull('kaur_lab_approved_at')
                    ->whereNull('kaur_keuangan_approved_at');
                });
            }
            
            // Apply user filters
            if ($request->has('lokasi') && $request->lokasi) {
                $query->whereHas('asset', function($q) use ($request) {
                    $q->where('lokasi', $request->lokasi);
                });
            }
            
            if ($request->has('tingkat_kerusakan') && $request->tingkat_kerusakan) {
                $query->whereHas('damagedAsset', function($q) use ($request) {
                    $q->where('tingkat_kerusakan', $request->tingkat_kerusakan);
                });
            }
            
            $damagedAssets = $query->get();
            
            Log::info('Maintenance assets retrieved for kaur role', [
                'total_assets' => $damagedAssets->count(),
                'user_role' => $user->roles->first()->name
            ]);
        } else {
            $damagedAssets = collect();
        }
        
        // ===== SIMPLIFIED AUTOMATIC TOPSIS CALCULATION =====
        $priorityScores = [];
        if ($user->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']) && 
            $damagedAssets->count() > 0) {
            
            Log::info('Starting priority score calculation/retrieval', [
                'total_assets' => $damagedAssets->count(),
                'user_role' => $user->roles->first()->name
            ]);
            
            // First, try to get existing priority scores
            $priorityScores = $this->getStoredPriorityScores($damagedAssets);
            
            // Check if we need to calculate new scores
            $assetsWithoutScores = $damagedAssets->filter(function($asset) {
                return $asset instanceof \App\Models\MaintenanceAsset && 
                       is_null($asset->priority_score);
            });
            
            Log::info('Priority score status check', [
                'total_assets' => $damagedAssets->count(),
                'assets_with_scores' => count($priorityScores),
                'assets_without_scores' => $assetsWithoutScores->count()
            ]);
            
            // If there are assets without scores, calculate them
            if ($assetsWithoutScores->count() > 0) {
                Log::info('Assets without scores detected, attempting calculation');
                
                // Determine user department for AHP weights
                $department = null;
                if ($user->hasRole('kaur_laboratorium')) {
                    $department = 'laboratorium';
                } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
                    // For mixed department handling, we'll process separately
                    $this->calculateMixedDepartmentScores($damagedAssets);
                    // Refresh priority scores after calculation
                    $priorityScores = $this->getStoredPriorityScores($damagedAssets);
                }
                
                // For single department (kaur_laboratorium)
                if ($department === 'laboratorium') {
                    $ahpWeights = AhpWeight::getActiveWeightsForTopsis($department);
                    if ($ahpWeights && !empty($ahpWeights)) {
                        Log::info('Calculating TOPSIS for single department', [
                            'department' => $department,
                            'assets_count' => $damagedAssets->count()
                        ]);
                        
                        try {
                            $calculatedScores = $this->topsisService->calculatePriorityWithWeights(
                                $damagedAssets, 
                                $ahpWeights
                            );
                            
                            if (!empty($calculatedScores)) {
                                $priorityScores = $calculatedScores;
                                Log::info('TOPSIS calculation successful', [
                                    'scores_calculated' => count($calculatedScores)
                                ]);
                            } else {
                                Log::warning('TOPSIS calculation returned empty results');
                            }
                        } catch (\Exception $e) {
                            Log::error('TOPSIS calculation failed', [
                                'error' => $e->getMessage(),
                                'department' => $department
                            ]);
                        }
                    } else {
                        Log::warning('No AHP weights available for department: ' . $department);
                    }
                }
            }
            
            Log::info('Priority score calculation completed', [
                'final_scores_count' => count($priorityScores)
            ]);
        }
        
        // Apply sorting and pagination
        if ($user->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2'])) {
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            if ($sortField === 'priority' && !empty($priorityScores)) {
                $damagedAssets = $damagedAssets->sortByDesc(function($item) use ($priorityScores) {
                    return $priorityScores[$item->id]['score'] ?? 0;
                })->values();
            } elseif ($sortField === 'estimasi_biaya') {
                $damagedAssets = $damagedAssets->sortBy(function($item) use ($sortDirection) {
                    $biaya = $item instanceof \App\Models\MaintenanceAsset ? 
                        ($item->damagedAsset->estimasi_biaya ?? 0) : 
                        ($item->estimasi_biaya ?? 0);
                    return $sortDirection === 'asc' ? $biaya : -$biaya;
                })->values();
            }
        }
        
        // Convert to paginated result
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginatedItems = $damagedAssets->slice(($page - 1) * $perPage, $perPage);
        $maintenanceAssets = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $damagedAssets->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $locations = Asset::distinct()->pluck('lokasi');
        $tingkatKerusakanOptions = ['Ringan', 'Sedang', 'Berat'];
        
        Log::info('PengajuanController::create completed', [
            'final_priority_scores_count' => count($priorityScores),
            'maintenance_assets_count' => $maintenanceAssets->count()
        ]);
        
        return view('pengajuan.create', compact('maintenanceAssets', 'locations', 'tingkatKerusakanOptions', 'priorityScores'));
    }

    /**
     * Force calculation of priority scores for assets without scores
     */
    public function ensurePriorityScores()
    {
        $user = Auth::user();
        
        if (!$user->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            // Get all maintenance assets without priority scores
            $assetsWithoutScores = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Menunggu Persetujuan')
                ->whereNull('priority_score')
                ->get();
            
            if ($assetsWithoutScores->count() === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'All assets already have priority scores',
                    'calculated' => 0
                ]);
            }
            
            Log::info('Auto-calculating missing priority scores', [
                'assets_without_scores' => $assetsWithoutScores->count(),
                'user' => $user->name
            ]);
            
            $totalCalculated = 0;
            
            // Separate by department
            $labAssets = $assetsWithoutScores->filter(function($asset) {
                return str_contains($asset->asset->lokasi, 'Laboratorium');
            });
            
            $logisticAssets = $assetsWithoutScores->filter(function($asset) {
                return !str_contains($asset->asset->lokasi, 'Laboratorium');
            });
            
            // Calculate for lab assets
            if ($labAssets->count() > 0) {
                $labWeights = AhpWeight::getActiveWeightsForTopsis('laboratorium');
                if ($labWeights && !empty($labWeights)) {
                    try {
                        $labScores = $this->topsisService->calculatePriorityWithWeights($labAssets, $labWeights);
                        $totalCalculated += count($labScores);
                        Log::info('Lab assets priority scores calculated', ['count' => count($labScores)]);
                    } catch (\Exception $e) {
                        Log::error('Lab assets calculation failed', ['error' => $e->getMessage()]);
                    }
                }
            }
            
            // Calculate for logistic assets
            if ($logisticAssets->count() > 0) {
                $logisticWeights = AhpWeight::getActiveWeightsForTopsis('keuangan_logistik');
                if ($logisticWeights && !empty($logisticWeights)) {
                    try {
                        $logisticScores = $this->topsisService->calculatePriorityWithWeights($logisticAssets, $logisticWeights);
                        $totalCalculated += count($logisticScores);
                        Log::info('Logistic assets priority scores calculated', ['count' => count($logisticScores)]);
                    } catch (\Exception $e) {
                        Log::error('Logistic assets calculation failed', ['error' => $e->getMessage()]);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Auto-calculated priority scores for {$totalCalculated} assets",
                'calculated' => $totalCalculated,
                'total_without_scores' => $assetsWithoutScores->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Auto priority calculation failed', [
                'error' => $e->getMessage(),
                'user' => $user->name
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate priority scores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate scores for mixed departments (for kaur_keuangan_logistik_sdm)
     */
    private function calculateMixedDepartmentScores($damagedAssets)
    {
        // Separate by department
        $labAssets = $damagedAssets->filter(function($asset) {
            return str_contains($asset->asset->lokasi, 'Laboratorium');
        });
        
        $logisticAssets = $damagedAssets->filter(function($asset) {
            return !str_contains($asset->asset->lokasi, 'Laboratorium');
        });
        
        Log::info('Mixed department calculation initiated', [
            'lab_assets' => $labAssets->count(),
            'logistic_assets' => $logisticAssets->count()
        ]);
        
        // Calculate Lab assets if any
        if ($labAssets->count() > 0) {
            $labWeights = AhpWeight::getActiveWeightsForTopsis('laboratorium');
            if ($labWeights && !empty($labWeights)) {
                try {
                    $this->topsisService->calculatePriorityWithWeights($labAssets, $labWeights);
                    Log::info('Lab assets calculation completed');
                } catch (\Exception $e) {
                    Log::error('Lab assets calculation failed', ['error' => $e->getMessage()]);
                }
            }
        }
        
        // Calculate Logistic assets if any
        if ($logisticAssets->count() > 0) {
            $logisticWeights = AhpWeight::getActiveWeightsForTopsis('keuangan_logistik');
            if ($logisticWeights && !empty($logisticWeights)) {
                try {
                    $this->topsisService->calculatePriorityWithWeights($logisticAssets, $logisticWeights);
                    Log::info('Logistic assets calculation completed');
                } catch (\Exception $e) {
                    Log::error('Logistic assets calculation failed', ['error' => $e->getMessage()]);
                }
            }
        }
    }

    /**
     * Get stored priority scores from database - IMPROVED VERSION
     */
    private function getStoredPriorityScores($damagedAssets)
    {
        $priorityScores = [];
        
        foreach ($damagedAssets as $asset) {
            if ($asset instanceof MaintenanceAsset && $asset->priority_score !== null) {
                $priorityScores[$asset->id] = [
                    'score' => $asset->priority_score,
                    'rank' => 1 // Will be recalculated based on current dataset
                ];
                
                Log::debug('Retrieved existing priority score', [
                    'asset_id' => $asset->id,
                    'score' => $asset->priority_score
                ]);
            }
        }
        
        // Recalculate ranks based on current scores
        if (!empty($priorityScores)) {
            $sortedScores = $priorityScores;
            uasort($sortedScores, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            $rank = 1;
            foreach ($sortedScores as $id => $data) {
                $priorityScores[$id]['rank'] = $rank++;
            }
            
            Log::info('Retrieved and ranked existing priority scores', [
                'scores_count' => count($priorityScores)
            ]);
        }
        
        return $priorityScores;
    }

    /**
     * Enhanced TOPSIS calculation with retry logic and better error handling
     */
    private function calculateTopsisWithRetry($assets, $ahpWeights, $department, $maxRetries = 3)
    {
        $retryCount = 0;
        $calculatedScores = [];
        
        // Debug the calculation setup
        $this->debugTopsisCalculation($assets, $department);
        
        while ($retryCount < $maxRetries && empty($calculatedScores)) {
            try {
                Log::info('Attempting TOPSIS calculation', [
                    'department' => $department,
                    'assets_count' => $assets->count(),
                    'retry_count' => $retryCount,
                    'ahp_weights_count' => count($ahpWeights)
                ]);
                
                // Validate inputs before calculation
                if ($assets->count() === 0) {
                    Log::warning('No assets provided for TOPSIS calculation', [
                        'department' => $department
                    ]);
                    break;
                }
                
                if (empty($ahpWeights)) {
                    Log::warning('No AHP weights provided for TOPSIS calculation', [
                        'department' => $department
                    ]);
                    break;
                }
                
                // Validate that all assets have required data
                $validAssets = $assets->filter(function($asset) {
                    if (!($asset instanceof \App\Models\MaintenanceAsset)) {
                        return false;
                    }
                    
                    $damagedAsset = $asset->damagedAsset;
                    $relatedAsset = $asset->asset;
                    
                    return $damagedAsset && 
                        $relatedAsset && 
                        !is_null($damagedAsset->tingkat_kerusakan) &&
                        !is_null($damagedAsset->estimasi_biaya) &&
                        !is_null($relatedAsset->tingkat_kepentingan_asset);
                });
                
                if ($validAssets->count() === 0) {
                    Log::warning('No valid assets for TOPSIS calculation', [
                        'department' => $department,
                        'total_assets' => $assets->count(),
                        'valid_assets' => $validAssets->count()
                    ]);
                    break;
                }
                
                if ($validAssets->count() < $assets->count()) {
                    Log::warning('Some assets filtered out due to missing data', [
                        'department' => $department,
                        'total_assets' => $assets->count(),
                        'valid_assets' => $validAssets->count()
                    ]);
                }
                
                // Attempt calculation with valid assets
                $calculatedScores = $this->topsisService->calculatePriorityWithWeights($validAssets, $ahpWeights);
                
                if (!empty($calculatedScores)) {
                    Log::info('TOPSIS calculation successful', [
                        'department' => $department,
                        'scores_calculated' => count($calculatedScores),
                        'retry_count' => $retryCount
                    ]);
                    break;
                } else {
                    Log::warning('TOPSIS calculation returned empty results', [
                        'department' => $department,
                        'retry_count' => $retryCount
                    ]);
                }
                
            } catch (\Exception $e) {
                Log::error('TOPSIS calculation attempt failed', [
                    'department' => $department,
                    'retry_count' => $retryCount,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // If it's a specific error we can't retry, break early
                if (str_contains($e->getMessage(), 'weights') || 
                    str_contains($e->getMessage(), 'criteria') ||
                    str_contains($e->getMessage(), 'division by zero')) {
                    Log::error('Non-retryable error encountered', [
                        'department' => $department,
                        'error' => $e->getMessage()
                    ]);
                    break;
                }
            }
            
            $retryCount++;
            
            if ($retryCount < $maxRetries && empty($calculatedScores)) {
                // Wait a bit before retrying
                sleep(1);
                Log::info('Retrying TOPSIS calculation', [
                    'department' => $department,
                    'retry_count' => $retryCount,
                    'max_retries' => $maxRetries
                ]);
            }
        }
        
        if (empty($calculatedScores) && $retryCount >= $maxRetries) {
            Log::error('TOPSIS calculation failed after all retries', [
                'department' => $department,
                'max_retries' => $maxRetries,
                'final_assets_count' => $assets->count(),
                'ahp_weights_available' => !empty($ahpWeights)
            ]);
        }
        
        return $calculatedScores;
    }

    public function forceCalculateAllPriorities()
    {
        $user = Auth::user();
        
        if (!$user->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            Log::info('Force calculating all priority scores', [
                'triggered_by' => $user->name
            ]);
            
            // Get all maintenance assets without priority scores
            $allAssetsWithoutScores = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Menunggu Persetujuan')
                ->whereNull('priority_score')
                ->get();
            
            if ($allAssetsWithoutScores->count() === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'All assets already have priority scores'
                ]);
            }
            
            $totalCalculated = 0;
            
            // Separate by department and calculate
            $labAssets = $allAssetsWithoutScores->filter(function($asset) {
                return str_contains($asset->asset->lokasi, 'Laboratorium');
            });
            
            $logisticAssets = $allAssetsWithoutScores->filter(function($asset) {
                return !str_contains($asset->asset->lokasi, 'Laboratorium');
            });
            
            // Calculate for lab assets
            if ($labAssets->count() > 0) {
                $labWeights = AhpWeight::getActiveWeightsForTopsis('laboratorium');
                if ($labWeights) {
                    $labScores = $this->calculateTopsisWithRetry($labAssets, $labWeights, 'laboratorium');
                    $totalCalculated += count($labScores);
                }
            }
            
            // Calculate for logistic assets
            if ($logisticAssets->count() > 0) {
                $logisticWeights = AhpWeight::getActiveWeightsForTopsis('keuangan_logistik');
                if ($logisticWeights) {
                    $logisticScores = $this->calculateTopsisWithRetry($logisticAssets, $logisticWeights, 'keuangan_logistik');
                    $totalCalculated += count($logisticScores);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Priority scores calculated for {$totalCalculated} out of {$allAssetsWithoutScores->count()} assets",
                'calculated' => $totalCalculated,
                'total' => $allAssetsWithoutScores->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Force calculate all priorities failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate priorities: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTopsisStatus()
    {
        // Get last calculation from database instead of session
        $activeWeights = AhpWeight::getActiveWeights();
        $ahpWeights = AhpWeight::getActiveWeightsForTopsis();
        
        $lastCalculation = null;
        if ($activeWeights && $activeWeights->isNotEmpty()) {
            $firstWeight = $activeWeights->first();
            $lastCalculation = [
                'timestamp' => $firstWeight->created_at->toDateTimeString(),
                'calculated_by' => $firstWeight->calculated_by,
                'consistency_ratio' => $firstWeight->consistency_ratio,
                'criteria_count' => $firstWeight->criteria_count
            ];
        }
        
        return response()->json([
            'ahp_available' => !empty($ahpWeights),
            'last_calculation' => $lastCalculation,
            'criteria_count' => $ahpWeights ? count($ahpWeights) : 0,
            'pending_assets_count' => MaintenanceAsset::where('status', 'Menunggu Persetujuan')->count(),
            'is_consistent' => AhpWeight::areCurrentWeightsConsistent(),
            'ahp_weights_sample' => $ahpWeights ? array_slice($ahpWeights, 0, 2, true) : null // For debugging
        ]);
    }

    public function getTopsisResults()
    {
        $results = MaintenanceAsset::with(['asset', 'damagedAsset'])
            ->where('status', 'Menunggu Persetujuan')
            ->whereNotNull('priority_score')
            ->orderBy('priority_score', 'desc')
            ->get()
            ->map(function($asset, $index) {
                return [
                    'rank' => $index + 1,
                    'maintenance_id' => $asset->maintenance_id,
                    'asset_name' => $asset->asset->nama_asset,
                    'asset_location' => $asset->asset->lokasi,
                    'damage_level' => $asset->damagedAsset->tingkat_kerusakan,
                    'estimated_cost' => $asset->damagedAsset->estimasi_biaya,
                    'priority_score' => round($asset->priority_score, 4),
                    'calculated_at' => $asset->priority_calculated_at,
                    'method' => $asset->priority_method
                ];
            });

        return response()->json([
            'success' => true,
            'results' => $results,
            'total_count' => $results->count()
        ]);
    }
    
    /**
     * Calculate priority for damaged assets that don't have maintenance requests yet
     */
    private function calculateDamagedAssetPriority($damagedAssets)
    {
        $priorityData = [];
        
        // Build decision matrix from damaged assets
        foreach ($damagedAssets as $asset) {
            // Handle both DamagedAsset and MaintenanceAsset objects
            if ($asset instanceof MaintenanceAsset) {
                $damagedAsset = $asset->damagedAsset;
                $relatedAsset = $asset->asset;
            } else {
                $damagedAsset = $asset;
                $relatedAsset = $asset->asset;
            }
            
            $priorityData[] = [
                'id' => $asset->id,
                'tingkat_kerusakan' => $this->getTingkatKerusakanScore($damagedAsset->tingkat_kerusakan),
                'kepentingan_asset' => $relatedAsset->tingkat_kepentingan_asset,
                'estimasi_biaya' => $damagedAsset->estimasi_biaya
            ];
        }
        
        // If no data, return empty array
        if (empty($priorityData)) {
            return [];
        }
        
        // Get criteria weights (default values)
        $weights = [
            'tingkat_kerusakan' => 0.5,
            'kepentingan_asset' => 0.3,
            'estimasi_biaya' => 0.2
        ];
        
        // Normalize the matrix
        $normalized = $this->normalizeMatrix($priorityData);
        
        // Apply weights
        $weighted = $this->applyWeights($normalized, $weights);
        
        // Calculate ideal solutions
        $idealPositive = $this->getIdealSolution($weighted, 'positive');
        $idealNegative = $this->getIdealSolution($weighted, 'negative');
        
        // Calculate distances and scores
        $scores = [];
        foreach ($weighted as $row) {
            $distancePositive = $this->calculateDistance($row, $idealPositive);
            $distanceNegative = $this->calculateDistance($row, $idealNegative);
            
            $score = ($distancePositive + $distanceNegative) > 0 
                ? $distanceNegative / ($distancePositive + $distanceNegative) 
                : 0;
                
            $scores[$row['id']] = $score;
        }
        
        // Sort by score descending
        arsort($scores);
        
        // Assign ranks
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
    
    private function getTingkatKerusakanScore($tingkat)
    {
        $scores = [
            'Ringan' => 3,
            'Sedang' => 6,
            'Berat' => 9
        ];
        
        return $scores[$tingkat] ?? 1;
    }
    
    private function normalizeMatrix($matrix)
    {
        $normalized = [];
        $columnSums = [];
        
        // Calculate column sums
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
        foreach ($matrix as $row) {
            $normalizedRow = ['id' => $row['id']];
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                $normalizedRow[$key] = $columnSums[$key] > 0 ? $value / $columnSums[$key] : 0;
            }
            $normalized[] = $normalizedRow;
        }
        
        return $normalized;
    }
    
    private function applyWeights($matrix, $weights)
    {
        $weighted = [];
        
        foreach ($matrix as $row) {
            $weightedRow = ['id' => $row['id']];
            foreach ($row as $key => $value) {
                if ($key === 'id') continue;
                $weightedRow[$key] = $value * ($weights[$key] ?? 1);
            }
            $weighted[] = $weightedRow;
        }
        
        return $weighted;
    }
    
    private function getIdealSolution($matrix, $type)
    {
        $ideal = [];
        $criteria = ['tingkat_kerusakan', 'kepentingan_asset', 'estimasi_biaya'];
        
        foreach ($criteria as $criterion) {
            $values = array_column($matrix, $criterion);
            
            if ($criterion === 'estimasi_biaya') {
                // Cost criteria - lower is better
                $ideal[$criterion] = $type === 'positive' ? min($values) : max($values);
            } else {
                // Benefit criteria - higher is better
                $ideal[$criterion] = $type === 'positive' ? max($values) : min($values);
            }
        }
        
        return $ideal;
    }
    
    private function calculateDistance($row, $ideal)
    {
        $distance = 0;
        
        foreach ($ideal as $key => $value) {
            if (isset($row[$key])) {
                $distance += pow($row[$key] - $value, 2);
            }
        }
        
        return sqrt($distance);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'damaged_asset_ids' => 'required|array|min:1',
            'damaged_asset_ids.*' => 'exists:damaged_assets,id'
        ]);
        
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $createdAssets = [];
            
            // Get all damaged assets that should be available to this user
            $availableDamagedAssets = collect();
            
            if ($user->hasRole('staff_laboratorium')) {
                $availableDamagedAssets = DamagedAsset::with(['asset', 'maintenanceAsset'])
                    ->whereDoesntHave('maintenanceAsset')
                    ->orWhereHas('maintenanceAsset', function($q) {
                        $q->whereIn('status', ['Ditolak']);
                    })
                    ->whereHas('asset', function($q) {
                        $q->where('lokasi', 'LIKE', '%Laboratorium%');
                    })
                    ->get();
            } elseif ($user->hasRole('staff_logistik')) {
                $availableDamagedAssets = DamagedAsset::with(['asset', 'maintenanceAsset'])
                    ->whereDoesntHave('maintenanceAsset')
                    ->orWhereHas('maintenanceAsset', function($q) {
                        $q->whereIn('status', ['Ditolak']);
                    })
                    ->whereHas('asset', function($q) {
                        $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
                    })
                    ->get();
            }
            
            // Process checked damaged assets
            foreach ($validated['damaged_asset_ids'] as $damagedAssetId) {
                $damagedAsset = DamagedAsset::findOrFail($damagedAssetId);
                
                // Check if already has active maintenance request
                if ($damagedAsset->maintenanceAsset && !in_array($damagedAsset->maintenanceAsset->status, ['Ditolak'])) {
                    continue;
                }
                
                // Delete existing rejected maintenance request if exists
                if ($damagedAsset->maintenanceAsset && $damagedAsset->maintenanceAsset->status === 'Ditolak') {
                    $damagedAsset->maintenanceAsset->delete();
                }
                
                // Generate maintenance ID
                $latestMaintenance = MaintenanceAsset::latest('id')->lockForUpdate()->first();
                $maintenanceNumber = $latestMaintenance ? intval(substr($latestMaintenance->maintenance_id, 3)) + 1 : 1;
                $maintenanceId = 'MNT' . str_pad($maintenanceNumber, 4, '0', STR_PAD_LEFT);
                
                // Create maintenance record
                $maintenanceAsset = MaintenanceAsset::create([
                    'maintenance_id' => $maintenanceId,
                    'damage_id' => $damagedAsset->damage_id,
                    'asset_id' => $damagedAsset->asset_id,
                    'status' => 'Menunggu Persetujuan',
                    'tanggal_pengajuan' => now(),
                    'teknisi' => $damagedAsset->vendor ? 'Vendor' : 'Staf',
                    'requested_by' => $user->id,
                    'requested_by_role' => $user->roles->first()->name
                ]);
                
                // Log the submission
                ApprovalLog::create([
                    'maintenance_asset_id' => $maintenanceAsset->id,
                    'action' => 'submitted',
                    'performed_by' => $user->username,
                    'role' => $user->roles->first()->name,
                    'notes' => 'Pengajuan perbaikan aset diajukan'
                ]);
                
                $createdAssets[] = $maintenanceAsset;
            }
            
            // Send notification to approver
            $approver = $user->getApprover();
            if ($approver && !empty($createdAssets)) {
                foreach ($createdAssets as $asset) {
                    $this->notificationService->sendApprovalRequest(
                        $asset,
                        $approver,
                        $user->roles->first()->name
                    );
                }
            }
            
            DB::commit();
            
            return redirect()->route('pengajuan.create')
                ->with('success', 'Pengajuan perbaikan berhasil diajukan dan menunggu persetujuan.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function approve(Request $request, $id)
    {
        $maintenanceAsset = MaintenanceAsset::findOrFail($id);
        $user = Auth::user();
        
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500'
        ]);
        
        DB::beginTransaction();
        
        try {
            $action = $validated['action'];
            $status = $action === 'approve' ? 'Diterima' : 'Ditolak';
            
            // Update based on user role
            if ($user->hasRole('kaur_laboratorium')) {
                // Kaur lab approving staff lab submission
                if ($action === 'approve') {
                    $maintenanceAsset->update([
                        'kaur_lab_approved_at' => now(),
                        'kaur_lab_approved_by' => $user->username,
                        'status' => 'Menunggu Persetujuan' // Keep status as waiting for next approval
                    ]);
                    
                    // Send notification to kaur keuangan for next approval
                    $kaurKeuangan = User::role('kaur_keuangan_logistik_sdm')->first();
                    if ($kaurKeuangan) {
                        $this->notificationService->sendApprovalRequest(
                            $maintenanceAsset,
                            $kaurKeuangan,
                            'Kaur Laboratorium'
                        );
                    }
                } else {
                    // Rejected by kaur lab - delete maintenance request and damaged asset
                    $damagedAsset = $maintenanceAsset->damagedAsset;
                    $maintenanceAsset->delete();
                    if ($damagedAsset) {
                        $damagedAsset->delete();
                    }
                }
                
            } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
                // Kaur keuangan final approval
                if ($action === 'approve') {
                    $maintenanceAsset->update([
                        'kaur_keuangan_approved_at' => now(),
                        'kaur_keuangan_approved_by' => $user->username,
                        'status' => $status
                    ]);
                } else {
                    // Rejected by kaur keuangan - delete maintenance request and damaged asset
                    $damagedAsset = $maintenanceAsset->damagedAsset;
                    $maintenanceAsset->delete();
                    if ($damagedAsset) {
                        $damagedAsset->delete();
                    }
                }
            }
            
            // Log the approval action (only if not deleted)
            if ($action === 'approve' || MaintenanceAsset::find($id)) {
                ApprovalLog::create([
                    'maintenance_asset_id' => $maintenanceAsset->id,
                    'action' => $action === 'approve' ? 'approved' : 'rejected',
                    'performed_by' => $user->username,
                    'role' => $user->roles->first()->name,
                    'notes' => $validated['notes'] ?? null
                ]);
            }
            
            // Send notification to original requester (only if maintenance asset still exists)
            if ($action === 'approve') {
                $requester = User::find($maintenanceAsset->requested_by);
                if ($requester) {
                    $this->notificationService->sendApprovalResult(
                        $maintenanceAsset,
                        $requester,
                        $status,
                        $user->roles->first()->name
                    );
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $action === 'approve' ? 'Pengajuan berhasil disetujui' : 'Pengajuan ditolak dan data terkait dihapus'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'maintenance_ids' => 'required|array|min:1',
            'maintenance_ids.*' => 'exists:maintenance_assets,id',
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500'
        ]);
        
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $action = $validated['action'];
            $processedCount = 0;
            $rejectedCount = 0;
            
            // Get all maintenance assets that should be available to this user for approval
            $availableMaintenanceAssets = collect();
            
            if ($user->hasRole('kaur_laboratorium')) {
                $availableMaintenanceAssets = MaintenanceAsset::where('requested_by_role', 'staff_laboratorium')
                    ->whereNull('kaur_lab_approved_at')
                    ->where('status', 'Menunggu Persetujuan')
                    ->get();
            } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
                $availableMaintenanceAssets = MaintenanceAsset::where('status', 'Menunggu Persetujuan')
                    ->where(function($q) {
                        $q->where('requested_by_role', 'staff_logistik')
                        ->whereNull('kaur_keuangan_approved_at');
                    })->orWhere(function($q) {
                        $q->where('requested_by_role', 'staff_laboratorium')
                        ->whereNotNull('kaur_lab_approved_at')
                        ->whereNull('kaur_keuangan_approved_at');
                    })->get();
            }
            
            // Get IDs of unchecked maintenance assets
            $checkedMaintenanceIds = $validated['maintenance_ids'];
            $allAvailableIds = $availableMaintenanceAssets->pluck('id')->toArray();
            $uncheckedMaintenanceIds = array_diff($allAvailableIds, $checkedMaintenanceIds);
            
            // Handle unchecked maintenance assets - both roles now reject instead of delete
            if (!empty($uncheckedMaintenanceIds)) {
                $uncheckedMaintenanceAssets = MaintenanceAsset::whereIn('id', $uncheckedMaintenanceIds)->get();
                
                foreach ($uncheckedMaintenanceAssets as $maintenanceAsset) {
                    if ($user->hasRole('kaur_laboratorium')) {
                        $maintenanceAsset->update([
                            'status' => 'Ditolak',
                            'kaur_lab_approved_at' => now(),
                            'kaur_lab_approved_by' => $user->username,
                        ]);
                        
                        ApprovalLog::create([
                            'maintenance_asset_id' => $maintenanceAsset->id,
                            'action' => 'rejected',
                            'performed_by' => $user->username,
                            'role' => $user->roles->first()->name,
                            'notes' => 'Ditolak karena tidak dipilih dalam bulk approval'
                        ]);
                    } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
                        $maintenanceAsset->update([
                            'status' => 'Ditolak',
                            'kaur_keuangan_approved_at' => now(),
                            'kaur_keuangan_approved_by' => $user->username,
                        ]);
                        
                        // Log the rejection
                        ApprovalLog::create([
                            'maintenance_asset_id' => $maintenanceAsset->id,
                            'action' => 'rejected',
                            'performed_by' => $user->username,
                            'role' => $user->roles->first()->name,
                            'notes' => 'Ditolak karena tidak dipilih dalam bulk approval'
                        ]);
                    }
                    
                    // Send notification to original requester
                    $requester = User::find($maintenanceAsset->requested_by);
                    if ($requester) {
                        $this->notificationService->sendApprovalResult(
                            $maintenanceAsset,
                            $requester,
                            'Ditolak',
                            $user->roles->first()->name
                        );
                    }
                    
                    $rejectedCount++;
                }
            }
            
            // Process checked maintenance assets
            foreach ($validated['maintenance_ids'] as $maintenanceId) {
                $maintenanceAsset = MaintenanceAsset::find($maintenanceId);
                if (!$maintenanceAsset) continue;
                
                if ($user->hasRole('kaur_laboratorium')) {
                    if ($action === 'approve') {
                        $maintenanceAsset->update([
                            'kaur_lab_approved_at' => now(),
                            'kaur_lab_approved_by' => $user->username,
                            'status' => 'Menunggu Persetujuan'
                        ]);
                    } else {
                        // Explicitly rejected - change status to 'Ditolak'
                        $maintenanceAsset->update([
                            'status' => 'Ditolak',
                            'kaur_lab_approved_at' => now(),
                            'kaur_lab_approved_by' => $user->username,
                            'rejection_reason' => $validated['notes'] ?? 'Ditolak'
                        ]);
                    }
                } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
                    if ($action === 'approve') {
                        $maintenanceAsset->update([
                            'kaur_keuangan_approved_at' => now(),
                            'kaur_keuangan_approved_by' => $user->username,
                            'status' => 'Diterima'
                        ]);
                    } else {
                        // Explicitly rejected
                        $maintenanceAsset->update([
                            'status' => 'Ditolak',
                            'kaur_keuangan_approved_at' => now(),
                            'kaur_keuangan_approved_by' => $user->username,
                            'rejection_reason' => $validated['notes'] ?? 'Ditolak'
                        ]);
                    }
                }
                
                // Log the action
                ApprovalLog::create([
                    'maintenance_asset_id' => $maintenanceAsset->id,
                    'action' => $action === 'approve' ? 'approved' : 'rejected',
                    'performed_by' => $user->username,
                    'role' => $user->roles->first()->name,
                    'notes' => $validated['notes'] ?? 'Bulk action'
                ]);
                
                // Send notification to original requester
                $requester = User::find($maintenanceAsset->requested_by);
                if ($requester) {
                    $this->notificationService->sendApprovalResult(
                        $maintenanceAsset,
                        $requester,
                        $action === 'approve' ? 'Diterima' : 'Ditolak',
                        $user->roles->first()->name
                    );
                }
                
                $processedCount++;
            }
            
            DB::commit();
            
            $message = $action === 'approve' 
                ? "Berhasil menyetujui {$processedCount} pengajuan"
                : "Berhasil menolak {$processedCount} pengajuan";
                
            if ($rejectedCount > 0) {
                $message .= " dan menolak {$rejectedCount} pengajuan yang tidak dipilih";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Add method to store AHP weights in session
    public function storeWeights(Request $request)
    {
        try {
            $validated = $request->validate([
                'criteria' => 'required|array',
                'weights' => 'required|array',
                'consistency_ratio' => 'required|numeric'
            ]);
            
            Log::info('Storing AHP weights', [
                'criteria_received' => $validated['criteria'],
                'weights_received' => $validated['weights'],
                'consistency_ratio' => $validated['consistency_ratio']
            ]);
            
            // Store weights in session for use in TOPSIS with criteria mapping
            $criteriaWeightMapping = [];
            foreach ($validated['criteria'] as $index => $criterion) {
                $criteriaWeightMapping[$criterion['kriteria_id']] = [
                    'weight' => $validated['weights'][$index],
                    'nama_kriteria' => $criterion['nama_kriteria'],
                    'tipe_kriteria' => $criterion['tipe_kriteria']
                ];
            }
            
            Log::info('Created criteria weight mapping', [
                'mapping' => $criteriaWeightMapping
            ]);
            
            session([
                'ahp_weights' => $validated['weights'],
                'ahp_criteria' => $validated['criteria'],
                'ahp_criteria_weights' => $criteriaWeightMapping,
                'ahp_consistency_ratio' => $validated['consistency_ratio'],
                'ahp_calculation_time' => now()->toDateTimeString()
            ]);
            
            // Verify what was stored
            $storedWeights = session('ahp_criteria_weights');
            Log::info('Verified stored weights', [
                'stored_weights' => $storedWeights
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'AHP weights stored successfully',
                'debug_info' => [
                    'criteria_count' => count($criteriaWeightMapping),
                    'sample_mapping' => array_slice($criteriaWeightMapping, 0, 2, true)
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error storing weights', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error storing AHP weights: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error storing weights: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getNotifications()
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getUnreadNotifications($user);
        $allNotifications = $this->notificationService->getAllNotifications($user, 5);
        
        return response()->json([
            'unread_count' => $notifications->count(),
            'notifications' => $allNotifications
        ]);
    }
    
    public function markNotificationAsRead($id)
    {
        $this->notificationService->markAsRead($id);
        return response()->json(['success' => true]);
    }
    
    public function markAllNotificationsAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::user());
        return response()->json(['success' => true]);
    }
    
    private function calculateStats($user)
    {
        $stats = [];
        
        if ($user->hasRole('wakil_dekan_2')) {
            // Wakil Dekan 2 has access to all data across departments
            $allMaintenanceRequests = MaintenanceAsset::with(['asset', 'damagedAsset']);
            
            // Basic status counts
            $stats['completed'] = (clone $allMaintenanceRequests)->where('status', 'Selesai')->count();
            $stats['in_progress'] = (clone $allMaintenanceRequests)->where('status', 'Dikerjakan')->count();
            $stats['received'] = (clone $allMaintenanceRequests)->where('status', 'Diterima')->count();
            $stats['rejected'] = (clone $allMaintenanceRequests)->where('status', 'Ditolak')->count();
            
            // Total expenditure from completed repairs
            $stats['total_expenditure'] = Payment::where('status', 'sudah_dibayar')
                ->sum('total_tagihan');
            
            // Highest repair cost and asset
            $highestCostMaintenance = (clone $allMaintenanceRequests)
                ->whereHas('damagedAsset')
                ->with(['damagedAsset', 'asset'])
                ->get()
                ->sortByDesc(function($maintenance) {
                    return $maintenance->damagedAsset->estimasi_biaya ?? 0;
                })
                ->first();
                
            $stats['highest_repair_cost'] = $highestCostMaintenance ? 
                ($highestCostMaintenance->damagedAsset->estimasi_biaya ?? 0) : 0;
            $stats['highest_cost_asset'] = $highestCostMaintenance ? 
                $highestCostMaintenance->asset->nama_asset : '-';
            
            // Department-wise repair requests
            $stats['lab_requests'] = (clone $allMaintenanceRequests)
                ->whereHas('asset', function($q) {
                    $q->where('lokasi', 'LIKE', '%Laboratorium%');
                })->count();
                
            $stats['logistic_requests'] = (clone $allMaintenanceRequests)
                ->whereHas('asset', function($q) {
                    $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
                })->count();
                
        } else {
            // Original stats calculation for other roles
            $query = MaintenanceAsset::with(['asset', 'damagedAsset']);
            
            // Apply role-based filtering
            if ($user->hasRole(['staff_laboratorium', 'kaur_laboratorium'])) {
                $query->whereHas('asset', function($q) {
                    $q->where('lokasi', 'LIKE', '%Laboratorium%');
                });
            } elseif ($user->hasRole(['staff_logistik', 'kaur_keuangan_logistik_sdm'])) {
                $query->whereHas('asset', function($q) {
                    $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
                });
            }
            
            $stats['completed'] = (clone $query)->where('status', 'Selesai')->count();
            $stats['in_progress'] = (clone $query)->where('status', 'Dikerjakan')->count();
            $stats['received'] = (clone $query)->where('status', 'Diterima')->count();
            
            // Calculate total expenditure from completed repairs
            $completedRepairs = (clone $query)
                ->where('status', 'Selesai')
                ->whereHas('damagedAsset')
                ->with('damagedAsset')
                ->get();
                
            $stats['total_expenditure'] = Payment::where('status', 'sudah_dibayar')
                ->sum('total_tagihan');
        }
        
        return $stats;
    }
    
    public function show($id)
    {
        $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset', 'approvalLogs'])->findOrFail($id);
        
        // Check permissions
        $user = Auth::user();
        if (!$this->canViewMaintenanceAsset($user, $maintenanceAsset)) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('pengajuan.show', compact('maintenanceAsset'));
    }
    
    private function canViewMaintenanceAsset($user, $maintenanceAsset)
    {
        // Users can always view their own requests
        if ($maintenanceAsset->requested_by == $user->id) {
            return true;
        }
        
        // Get asset location to determine department
        $isLaboratoryAsset = str_contains($maintenanceAsset->asset->lokasi, 'Laboratorium');
        
        // Staff Laboratorium permissions - can view all laboratory department requests
        if ($user->hasRole('staff_laboratorium')) {
            return $isLaboratoryAsset;
        }
        
        // Staff Logistik permissions - can view all logistic department requests
        if ($user->hasRole('staff_logistik')) {
            return !$isLaboratoryAsset;
        }
        
        // Kaur Laboratorium permissions - can view all laboratory-related requests
        if ($user->hasRole('kaur_laboratorium')) {
            return $isLaboratoryAsset;
        }
        
        // Kaur Keuangan Logistik SDM permissions - can view all requests
        if ($user->hasRole('kaur_keuangan_logistik_sdm')) {
            return true;
        }
        
        // Wakil Dekan 2 permissions - can view all requests
        if ($user->hasRole('wakil_dekan_2')) {
            return true;
        }
        
        // Default: deny access
        return false;
    }
    
    // Update the store method to handle bulk selection
    public function storeSelected(Request $request)
    {
        $validated = $request->validate([
            'selected_assets' => 'required|array|min:1',
            'selected_assets.*' => 'exists:damaged_assets,id'
        ]);
        
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $createdCount = 0;
            
            foreach ($validated['selected_assets'] as $damagedAssetId) {
                $damagedAsset = DamagedAsset::findOrFail($damagedAssetId);
                
                // Check if maintenance request already exists
                if ($damagedAsset->maintenanceAsset) {
                    continue;
                }
                
                // Generate maintenance ID
                $latestMaintenance = MaintenanceAsset::latest('id')->lockForUpdate()->first();
                $maintenanceNumber = $latestMaintenance ? intval(substr($latestMaintenance->maintenance_id, 3)) + 1 : 1;
                $maintenanceId = 'MNT' . str_pad($maintenanceNumber, 4, '0', STR_PAD_LEFT);
                
                // Create maintenance record
                $maintenanceAsset = MaintenanceAsset::create([
                    'maintenance_id' => $maintenanceId,
                    'damage_id' => $damagedAsset->damage_id,
                    'asset_id' => $damagedAsset->asset_id,
                    'status' => 'Menunggu Persetujuan',
                    'tanggal_pengajuan' => now(),
                    'teknisi' => $damagedAsset->vendor ? 'Vendor' : 'Staf',
                    'requested_by' => $user->id,
                    'requested_by_role' => $user->roles->first()->name
                ]);
                
                // Log the submission
                ApprovalLog::create([
                    'maintenance_asset_id' => $maintenanceAsset->id,
                    'action' => 'submitted',
                    'performed_by' => $user->username,
                    'role' => $user->roles->first()->name,
                    'notes' => 'Pengajuan perbaikan aset diajukan'
                ]);
                
                $createdCount++;
            }
            
            // Send notification to approver if any assets were created
            if ($createdCount > 0) {
                $approver = $user->getApprover();
                if ($approver) {
                    // Send one notification for bulk submission
                    $this->notificationService->sendBulkApprovalRequest(
                        $createdCount,
                        $approver,
                        $user->roles->first()->name
                    );
                }
            }
            
            DB::commit();
            
            return redirect()->route('pengajuan.index')
                ->with('success', "Berhasil mengajukan {$createdCount} pengajuan perbaikan.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function detailed(Request $request)
    {
        $user = Auth::user();
        $query = MaintenanceAsset::with(['asset', 'damagedAsset']);
        
        // Filter based on user role according to the new rules
        if ($user->hasRole(['staff_laboratorium', 'kaur_laboratorium'])) {
            // Show only lab assets (approved or not approved by kaur keuangan)
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'LIKE', '%Laboratorium%');
            });
        } elseif ($user->hasRole(['staff_logistik'])) {
            // Show only logistic assets (approved or not approved by kaur keuangan)
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
            });
        } elseif ($user->hasRole(['kaur_keuangan_logistik_sdm'])) {
            $query->whereHas('asset');
        }

        // Apply filters
        if ($request->filled('lokasi')) {
            $lokasi = $request->lokasi;
            $query->whereHas('asset', function($q) use ($lokasi) {
                $q->where('lokasi', 'LIKE', '%' . $lokasi . '%');
            });
        }
        
        if ($request->filled('petugas')) {
            $petugas = $request->petugas;
            $query->whereHas('damagedAsset', function($q) use ($petugas) {
                $q->where('petugas', $petugas);
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Handle sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        // Apply sorting based on field
        switch ($sortField) {
            case 'estimasi_biaya':
                $query->leftJoin('damaged_assets', 'maintenance_assets.damage_id', '=', 'damaged_assets.damage_id')
                    ->orderBy('damaged_assets.estimasi_biaya', $sortDirection)
                    ->select('maintenance_assets.*');
                break;
                
            case 'estimasi_waktu':
                $query->orderByRaw("
                    CASE 
                        WHEN estimasi_waktu_perbaikan LIKE '%jam%' THEN CAST(REGEXP_REPLACE(estimasi_waktu_perbaikan, '[^0-9]', '') AS UNSIGNED) / 24
                        WHEN estimasi_waktu_perbaikan LIKE '%hari%' THEN CAST(REGEXP_REPLACE(estimasi_waktu_perbaikan, '[^0-9]', '') AS UNSIGNED)
                        WHEN estimasi_waktu_perbaikan LIKE '%minggu%' THEN CAST(REGEXP_REPLACE(estimasi_waktu_perbaikan, '[^0-9]', '') AS UNSIGNED) * 7
                        WHEN estimasi_waktu_perbaikan LIKE '%bulan%' THEN CAST(REGEXP_REPLACE(estimasi_waktu_perbaikan, '[^0-9]', '') AS UNSIGNED) * 30
                        ELSE 999999
                    END {$sortDirection}
                ");
                break;
                
            case 'tanggal_pengajuan':
                $query->orderBy('tanggal_pengajuan', $sortDirection);
                break;
                
            case 'status':
                $query->orderBy('status', $sortDirection);
                break;
                
            default:
                $query->orderBy('created_at', $sortDirection);
                break;
        }
        
        // Get paginated results
        $maintenanceRequests = $query->paginate(10)->appends($request->query());
        
        // Get filter options
        $locations = Asset::distinct()->pluck('lokasi')->filter();
        $petugasList = ['Vendor', 'Staf'];
        $statusList = MaintenanceAsset::distinct()->pluck('status')->filter();
        
        return view('pengajuan.detailed', compact('maintenanceRequests', 'locations', 'petugasList', 'statusList'));
    }

    public function updatePhotos(Request $request, $id)
    {
        $validated = $request->validate([
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max per image
            'status' => 'required|in:Selesai'
        ]);
    
        DB::beginTransaction();
        
        try {
            $maintenanceAsset = MaintenanceAsset::findOrFail($id);
            
            // Only allow updating photos when changing to "Selesai"
            if ($request->status !== 'Selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Photos can only be uploaded when status is changed to Selesai.'
                ], 400);
            }
    
            $uploadedPhotos = [];
            
            // Handle multiple photo uploads to Cloudinary
            if ($request->hasFile('photos')) {
                Configuration::instance([
                    'cloud' => [
                        'cloud_name' => config('cloudinary.cloud_name'),
                        'api_key' => config('cloudinary.api_key'),
                        'api_secret' => config('cloudinary.api_secret'),
                    ],
                    'url' => ['secure' => true]
                ]);
    
                $upload = new UploadApi();
                
                foreach ($request->file('photos') as $index => $photo) {
                    try {
                        $filename = time() . '_' . uniqid();
                        
                        $result = $upload->upload($photo->getRealPath(), [
                            'folder' => 'maintenance-photos',
                            'public_id' => $filename,
                            'quality' => 'auto',
                            'fetch_format' => 'auto'
                        ]);
                        
                        $uploadedPhotos[] = [
                            'filename' => $filename,
                            'path' => $result['secure_url'],
                            'original_name' => $photo->getClientOriginalName(),
                            'uploaded_at' => now()->toDateTimeString()
                        ];
                    } catch (\Exception $e) {
                        Log::error('Photo upload failed: ' . $e->getMessage());
                        continue;
                    }
                }
            }
    
            // Merge with existing photos if any
            $existingPhotos = $maintenanceAsset->photos ?? [];
            $allPhotos = array_merge($existingPhotos, $uploadedPhotos);
    
            // Update maintenance asset with photos and status
            $maintenanceAsset->update([
                'photos' => $allPhotos,
                'status' => 'Selesai',
                'tanggal_selesai' => now()
            ]);
    
            // Log the status change
            ApprovalLog::create([
                'maintenance_asset_id' => $maintenanceAsset->id,
                'action' => 'status_updated',
                'performed_by' => Auth::user()->username,
                'role' => Auth::user()->roles->first()->name,
                'notes' => 'Status changed to Selesai with ' . count($uploadedPhotos) . ' photos uploaded'
            ]);
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Status updated to Selesai and photos uploaded successfully.',
                'photos_count' => count($uploadedPhotos),
                'total_photos' => count($allPhotos)
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error uploading photos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Diterima,Dikerjakan,Selesai,Ditolak'
        ]);
    
        $maintenanceAsset = MaintenanceAsset::findOrFail($id);
        
        // Only update if current status allows it
        if (!in_array($maintenanceAsset->status, ['Selesai', 'Ditolak'])) {
            
            // If changing to "Selesai", we'll handle this via the photo upload modal
            if ($validated['status'] === 'Selesai') {
                // For AJAX requests, return JSON
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'show_photo_modal' => true,
                        'maintenance_id' => $id,
                        'message' => 'Please upload completion photos.'
                    ]);
                }
                // For regular form submissions, redirect back with a flag
                return back()->with('show_photo_modal', $id);
            }
            
            // For other status changes, update normally
            $maintenanceAsset->update([
                'status' => $validated['status'],
                'tanggal_perbaikan' => $validated['status'] === 'Dikerjakan' ? now() : $maintenanceAsset->tanggal_perbaikan
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
            }
            
            return back()->with('success', 'Status berhasil diperbarui.');
        }
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Status tidak dapat diubah.']);
        }
        
        return back()->with('error', 'Status tidak dapat diubah.');
    }

    public function downloadTemplate()
    {
        try {
            // For GET requests (backward compatibility) - empty template
            return Excel::download(new AssetTemplateExport([], 'damaged_assets'), 'template_data_kerusakan_aset.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }

    public function downloadSelectedTemplate(Request $request)
    {
        try {
            $validated = $request->validate([
                'selected_assets' => 'array',
                'selected_assets.*' => 'integer',
                'asset_type' => 'string|in:damaged_assets,maintenance_assets'
            ]);

            $selectedAssets = $validated['selected_assets'] ?? [];
            $assetType = $validated['asset_type'] ?? 'damaged_assets';
            
            // Determine filename based on selection
            $filename = empty($selectedAssets) 
                ? 'template_data_kerusakan_aset.xlsx'
                : 'data_aset_terpilih_' . date('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(
                new AssetTemplateExport($selectedAssets, $assetType), 
                $filename
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh template: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadTemplate(Request $request)
    {
        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ], [
            'excel_file.required' => 'File Excel wajib diunggah.',
            'excel_file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
            'excel_file.max' => 'Ukuran file maksimal 10MB.',
        ]);

        DB::beginTransaction();
        
        try {
            $file = $request->file('excel_file');
            $user = Auth::user();
            
            Log::info('Starting Excel import', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'user' => $user->name,
                'role' => $user->roles->first()->name
            ]);
            
            // Initialize the import class
            $import = new AssetDamageImport();
            
            // Perform the Excel import with error catching
            try {
                Excel::import($import, $file);
                Log::info('Excel import completed successfully');
            } catch (\Exception $importError) {
                Log::error('Excel import process failed', [
                    'error' => $importError->getMessage(),
                    'trace' => $importError->getTraceAsString()
                ]);
                throw $importError;
            }
            
            // In your controller, replace the problematic section with:
            try {
                if (method_exists($import, 'sendNotificationsToApprovers')) {
                    $import->sendNotificationsToApprovers();
                    Log::info('Notifications sent successfully');
                } else {
                    Log::warning('sendNotificationsToApprovers method does not exist');
                }
            } catch (\Exception $notificationError) {
                Log::warning('Failed to send notifications', [
                    'error' => $notificationError->getMessage()
                ]);
            }

            // Get import summary
            try {
                if (method_exists($import, 'getImportSummary')) {
                    $summary = $import->getImportSummary();
                } else {
                    Log::warning('getImportSummary method does not exist');
                    $summary = [
                        'total_processed' => 0,
                        'user_role' => Auth::user()->roles->first()->name,
                        'timestamp' => now()->toDateTimeString()
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to get import summary', ['error' => $e->getMessage()]);
                $summary = ['total_processed' => 0];
            }
            
            Log::info('Import summary retrieved', $summary);
            
            DB::commit();
            
            Log::info('Excel import transaction committed successfully', $summary);
            
            // Create detailed success message based on what was processed
            $message = "File Excel berhasil diproses! ";
            
            if ($summary['total_processed'] > 0) {
                $message .= "{$summary['total_processed']} pengajuan perbaikan telah dibuat dan dikirim untuk persetujuan.";
                
                // Add breakdown if there were both existing and new assets
                if ($summary['existing_damage_assets'] > 0 && $summary['new_damage_assets'] > 0) {
                    $message .= " ({$summary['existing_damage_assets']} dari aset rusak yang sudah ada, {$summary['new_damage_assets']} aset rusak baru)";
                } elseif ($summary['existing_damage_assets'] > 0) {
                    $message .= " (Semua dari aset rusak yang sudah ada)";
                } elseif ($summary['new_damage_assets'] > 0) {
                    $message .= " (Semua aset rusak baru)";
                }
                
                // Add role-specific workflow information
                if ($user->hasRole('staff_laboratorium')) {
                    $message .= " Pengajuan telah dikirim ke Kaur Laboratorium untuk persetujuan.";
                } elseif ($user->hasRole('staff_logistik')) {
                    $message .= " Pengajuan telah dikirim ke Kaur Keuangan Logistik SDM untuk persetujuan.";
                }
                
                // Add criteria information
                if ($summary['criteria_count'] > 0) {
                    $message .= " Data kriteria dinamis ({$summary['criteria_count']} kriteria) telah diproses untuk kalkulasi TOPSIS.";
                }
            } else {
                $message = "File Excel berhasil diproses, tetapi tidak ada pengajuan baru yang dibuat. Pastikan data dalam file sudah benar dan belum memiliki pengajuan aktif.";
            }
            
            return redirect()->route('pengajuan.create')
                ->with('success', $message)
                ->with('import_summary', $summary);
                
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                
                // Log each validation failure for debugging
                Log::error('Excel validation failure', [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values()
                ]);
            }
            
            Log::error('Excel import validation failed', [
                'total_failures' => count($failures),
                'user' => $user->name
            ]);
            
            return back()->with('error', 'Validasi gagal: ' . implode('<br>', $errorMessages));
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Excel import error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => $user->name,
                'file' => $file ? $file->getClientOriginalName() : 'unknown'
            ]);
            
            // Provide more specific error messages
            $errorMessage = 'Terjadi kesalahan saat memproses file: ';
            
            if (str_contains($e->getMessage(), 'Class') && str_contains($e->getMessage(), 'not found')) {
                $errorMessage .= 'Import class tidak ditemukan. Pastikan AssetDamageImport sudah ada.';
            } elseif (str_contains($e->getMessage(), 'Connection') || str_contains($e->getMessage(), 'database')) {
                $errorMessage .= 'Kesalahan koneksi database. Silakan coba lagi.';
            } elseif (str_contains($e->getMessage(), 'Memory') || str_contains($e->getMessage(), 'memory')) {
                $errorMessage .= 'File terlalu besar untuk diproses. Coba dengan file yang lebih kecil.';
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            return back()->with('error', $errorMessage);
        }
    }

    private function recalculateAllPriorityScores($ahpWeights)
    {
        // Get all pending maintenance assets
        $allPendingAssets = MaintenanceAsset::with(['asset', 'damagedAsset'])
            ->where('status', 'Menunggu Persetujuan')
            ->get();
        
        if ($allPendingAssets->count() > 0) {
            // Calculate TOPSIS scores with AHP weights
            $allPriorityScores = $this->topsisService->calculatePriorityWithWeights($allPendingAssets, $ahpWeights);
            
            // Update all maintenance assets with new scores
            foreach ($allPendingAssets as $asset) {
                if (isset($allPriorityScores[$asset->id])) {
                    $asset->update([
                        'priority_score' => $allPriorityScores[$asset->id]['score'],
                        'priority_calculated_at' => now(),
                        'priority_method' => 'TOPSIS_AHP'
                    ]);
                }
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
            // Get AHP criteria weights from database instead of session
            $ahpCriteriaWeights = AhpWeight::getActiveWeightsForTopsis();
            
            if (!$ahpCriteriaWeights) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bobot kriteria AHP belum tersedia. Silakan lakukan kalkulasi AHP terlebih dahulu.',
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
                    'message' => 'Bobot kriteria AHP tidak valid (semua bobot bernilai 0). Silakan lakukan kalkulasi AHP ulang.',
                    'redirect_url' => route('kriteria.create')
                ]);
            }
            
            // Check if weights are consistent
            if (!AhpWeight::areCurrentWeightsConsistent()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bobot kriteria AHP tidak konsisten (CR > 0.1). Silakan lakukan kalkulasi AHP ulang.',
                    'redirect_url' => route('kriteria.create')
                ]);
            }
            
            // Get all pending maintenance assets with their relationships
            $pendingAssets = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Menunggu Persetujuan')
                ->get();
            
            Log::info('Starting TOPSIS calculation', [
                'pending_assets_count' => $pendingAssets->count(),
                'criteria_weights' => $ahpCriteriaWeights,
                'user' => $user->name
            ]);
            
            // Calculate TOPSIS scores with dynamic AHP weights
            $priorityScores = $this->topsisService->calculatePriorityWithWeights(
                $pendingAssets, 
                $ahpCriteriaWeights
            );
            
            // Count updated assets
            $updatedCount = count($priorityScores);
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghitung prioritas untuk {$updatedCount} pengajuan menggunakan metode TOPSIS dengan bobot AHP dari database.",
                'data' => [
                    'updated_count' => $updatedCount,
                    'total_criteria' => count($ahpCriteriaWeights),
                    'method' => 'TOPSIS_AHP_Database',
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

    public function downloadPhoto(Request $request, $id, $photoIndex)
    {
        try {
            $maintenanceAsset = MaintenanceAsset::findOrFail($id);
            
            // Check if user has permission to view this maintenance asset
            $user = Auth::user();
            if (!$this->canViewMaintenanceAsset($user, $maintenanceAsset)) {
                abort(403, 'Unauthorized action.');
            }
            
            // Check if photos exist
            if (!$maintenanceAsset->photos || !is_array($maintenanceAsset->photos)) {
                abort(404, 'No photos found.');
            }
            
            // Check if photo index is valid
            if (!isset($maintenanceAsset->photos[$photoIndex])) {
                abort(404, 'Photo not found.');
            }
            
            $photo = $maintenanceAsset->photos[$photoIndex];
            $photoUrl = $photo['path']; // Cloudinary URL
            
            // Check if it's a valid Cloudinary URL
            if (!str_contains($photoUrl, 'cloudinary.com')) {
                abort(404, 'Invalid photo URL.');
            }
            
            // Redirect to Cloudinary URL for download
            return redirect($photoUrl);
            
        } catch (\Exception $e) {
            abort(404, 'Photo not found.');
        }
    }
    
    public function downloadAllPhotos($id)
    {
        try {
            $maintenanceAsset = MaintenanceAsset::findOrFail($id);
            
            // Check if user has permission to view this maintenance asset
            $user = Auth::user();
            if (!$this->canViewMaintenanceAsset($user, $maintenanceAsset)) {
                abort(403, 'Unauthorized action.');
            }
            
            // Check if photos exist
            if (!$maintenanceAsset->photos || !is_array($maintenanceAsset->photos) || count($maintenanceAsset->photos) === 0) {
                abort(404, 'No photos found.');
            }
            
            // Create a ZIP file
            $zip = new \ZipArchive();
            $zipFileName = 'foto-perbaikan-' . $maintenanceAsset->maintenance_id . '.zip';
            $zipFilePath = storage_path('app/temp/' . $zipFileName);
            
            // Create temp directory if it doesn't exist
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
                abort(500, 'Could not create ZIP file.');
            }
            
            // Download each photo from Cloudinary and add to ZIP
            foreach ($maintenanceAsset->photos as $index => $photo) {
                $photoUrl = $photo['path']; // Cloudinary URL
                
                if (str_contains($photoUrl, 'cloudinary.com')) {
                    try {
                        $imageData = file_get_contents($photoUrl);
                        if ($imageData !== false) {
                            $filename = $photo['original_name'] ?? 'foto-perbaikan-' . ($index + 1) . '.jpg';
                            $zip->addFromString($filename, $imageData);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to download photo for ZIP: ' . $e->getMessage());
                        continue;
                    }
                }
            }
            
            $zip->close();
            
            // Return ZIP file download and then delete it
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            abort(500, 'Could not create photo archive.');
        }
    }

    public function sendNotificationsToApprovers()
    {
        if (empty($this->processedAssets)) {
            return;
        }

        try {
            $user = Auth::user();
            
            // Group assets by workflow (lab vs logistic)
            $labAssets = [];
            $logisticAssets = [];

            foreach ($this->processedAssets as $maintenanceAsset) {
                if (str_contains($maintenanceAsset->asset->lokasi, 'Laboratorium')) {
                    $labAssets[] = $maintenanceAsset;
                } else {
                    $logisticAssets[] = $maintenanceAsset;
                }
            }

            // Send notifications based on workflow
            if ($user->hasRole('staff_laboratorium') && !empty($labAssets)) {
                $kaur = User::role('kaur_laboratorium')->first();
                if ($kaur && $this->notificationService) {
                    $this->notificationService->sendBulkApprovalRequest(
                        count($labAssets),
                        $kaur,
                        'staff_laboratorium'
                    );
                }
            } elseif ($user->hasRole('staff_logistik') && !empty($logisticAssets)) {
                $kaur = User::role('kaur_keuangan_logistik_sdm')->first();
                if ($kaur && $this->notificationService) {
                    $this->notificationService->sendBulkApprovalRequest(
                        count($logisticAssets),
                        $kaur,
                        'staff_logistik'
                    );
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send import notifications', [
                'error' => $e->getMessage()
            ]);
        }
    }

    // Add this method too
    public function getImportSummary()
    {
        return [
            'total_processed' => count($this->processedAssets),
            'existing_damage_assets' => 0,
            'new_damage_assets' => count($this->processedAssets),
            'user_role' => $this->userRole ?? Auth::user()->roles->first()->name,
            'criteria_count' => Criteria::count(),
            'timestamp' => now()->toDateTimeString()
        ];
    }
    
    public function destroy($id)
    {
        $maintenanceAsset = MaintenanceAsset::findOrFail($id);
                    
        $maintenanceAsset->delete();

        return redirect()->route('pengajuan.index')
            ->with('success', 'Pengajuan berhasil dihapus.');        
    }
}