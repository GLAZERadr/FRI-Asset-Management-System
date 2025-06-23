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

class PengajuanController extends Controller
{
    protected $notificationService;
    protected $topsisService;

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
        
        if ($user->hasRole(['staff_laboratorium', 'staff_logistik'])) {
            // Start with base query
            $query = DamagedAsset::with(['asset', 'maintenanceAsset'])
                ->where('validated', 'Yes');
            
            // Apply division filter first, then maintenance conditions within that scope
            if ($user->hasRole('staff_laboratorium')) {
                // Laboratory staff - only see laboratory assets
                $query->whereHas('asset', function($q) {
                    $q->where('lokasi', 'LIKE', '%Laboratorium%');
                })
                ->where(function($q) {
                    // Within laboratory assets, show those without maintenance OR rejected
                    $q->whereDoesntHave('maintenanceAsset') // No maintenance request at all
                    ->orWhereHas('maintenanceAsset', function($subQ) {
                        $subQ->whereIn('status', ['Ditolak']); // Only show rejected ones
                    });
                });
            } else {
                // Logistics staff - only see non-laboratory (logistics) assets  
                $query->whereHas('asset', function($q) {
                    $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
                })
                ->where(function($q) {
                    // Within logistics assets, show those without maintenance OR rejected
                    $q->whereDoesntHave('maintenanceAsset') // No maintenance request at all
                    ->orWhereHas('maintenanceAsset', function($subQ) {
                        $subQ->whereIn('status', ['Ditolak']); // Only show rejected ones
                    });
                });
            }
            
            // Group by asset_id and get the latest damage report for each asset
            $damagedAssets = $query->orderBy('tanggal_pelaporan', 'desc')
                ->get()
                ->groupBy('asset_id')
                ->map(function($group) {
                    return $group->first();
                })
                ->values();
        }elseif ($user->hasRole('kaur_laboratorium')) {
            // Show maintenance requests submitted by staff lab
            $query = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('requested_by_role', 'staff_laboratorium')
                ->whereNull('kaur_lab_approved_at')
                ->where('maintenance_assets.status', 'Menunggu Persetujuan');  // Specify table name
                
            // Apply filters BEFORE getting the data
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
            
            // Apply sorting BEFORE getting the data
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }
            
            switch ($sortField) {
                case 'estimasi_biaya':
                    $query->join('damaged_assets', 'maintenance_assets.damage_id', '=', 'damaged_assets.damage_id')
                        ->orderBy('damaged_assets.estimasi_biaya', $sortDirection)
                        ->select('maintenance_assets.*');
                    break;
                case 'priority':
                    $query->orderByRaw("COALESCE(priority_score, 0) {$sortDirection}");
                    break;
                default:
                    $query->orderBy('maintenance_assets.created_at', $sortDirection);  // Specify table name
                    break;
            }
            
            $damagedAssets = $query->get();
            
        } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
            // Show data from kaur lab (approved) and staff logistik
            $query = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('maintenance_assets.status', 'Menunggu Persetujuan')  // Specify table name
                ->where(function($q) {
                    // From staff logistik
                    $q->where('requested_by_role', 'staff_logistik')
                    ->whereNull('kaur_keuangan_approved_at');
                })->orWhere(function($q) {
                    // From staff lab that has been approved by kaur lab
                    $q->where('maintenance_assets.status', 'Menunggu Persetujuan')  // Add this line
                    ->where('requested_by_role', 'staff_laboratorium')
                    ->whereNotNull('kaur_lab_approved_at')
                    ->whereNull('kaur_keuangan_approved_at');
                });
            
            // Apply filters BEFORE getting the data
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
            
            // Apply sorting BEFORE getting the data
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }
            
            switch ($sortField) {
                case 'estimasi_biaya':
                    $query->join('damaged_assets', 'maintenance_assets.damage_id', '=', 'damaged_assets.damage_id')
                        ->orderBy('damaged_assets.estimasi_biaya', $sortDirection)
                        ->select('maintenance_assets.*');
                    break;
                case 'priority':
                    $query->orderByRaw("COALESCE(priority_score, 0) {$sortDirection}");
                    break;
                default:
                    $query->orderBy('maintenance_assets.created_at', $sortDirection);  // Specify table name
                    break;
            }
            
            $damagedAssets = $query->get();
            
        } elseif ($user->hasRole('wakil_dekan_2')) {
            // Wakil Dekan 2 sees all maintenance requests that need approval
            $query = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('maintenance_assets.status', 'Menunggu Persetujuan');  // Specify table name
            
            // Apply filters BEFORE getting the data
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
            
            // Apply sorting BEFORE getting the data
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }
            
            switch ($sortField) {
                case 'estimasi_biaya':
                    $query->join('damaged_assets', 'maintenance_assets.damage_id', '=', 'damaged_assets.damage_id')
                        ->orderBy('damaged_assets.estimasi_biaya', $sortDirection)
                        ->select('maintenance_assets.*');
                    break;
                case 'priority':
                    $query->orderByRaw("COALESCE(priority_score, 0) {$sortDirection}");
                    break;
                default:
                    $query->orderBy('maintenance_assets.created_at', $sortDirection);  // Specify table name
                    break;
            }
            
            $damagedAssets = $query->get();
            
        } else {
            // Default empty collection for other roles
            $damagedAssets = collect();
        }
    
        // Apply filters for staff roles only (since other roles already applied filters above)
        if ($user->hasRole(['staff_laboratorium', 'staff_logistik'])) {
            if ($request->has('lokasi') && $request->lokasi) {
                $damagedAssets = $damagedAssets->filter(function($item) use ($request) {
                    return $item->asset->lokasi == $request->lokasi;
                });
            }
            
            if ($request->has('tingkat_kerusakan') && $request->tingkat_kerusakan) {
                $damagedAssets = $damagedAssets->filter(function($item) use ($request) {
                    return $item->tingkat_kerusakan == $request->tingkat_kerusakan;
                });
            }
        }
        
        // Calculate and store priority scores consistently (MOVED UP BEFORE SORTING)
        $priorityScores = [];
        if ($user->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']) && isset($damagedAssets) && $damagedAssets->count() > 0) {
            
            // Always use stored priority scores first
            $priorityScores = $this->getStoredPriorityScores($damagedAssets);
            
            // If no stored scores and not Wakil Dekan 2, check if we can calculate
            if (empty($priorityScores) && !$user->hasRole('wakil_dekan_2')) {
                // Get AHP criteria weights from database instead of session
                $ahpCriteriaWeights = AhpWeight::getActiveWeightsForTopsis();
                
                if ($ahpCriteriaWeights) {
                    // Automatically calculate TOPSIS with AHP weights
                    Log::info('Automatically calculating TOPSIS priorities', [
                        'user' => $user->name,
                        'assets_count' => $damagedAssets->count()
                    ]);
                    
                    try {
                        // Calculate TOPSIS scores
                        $priorityScores = $this->topsisService->calculatePriorityWithWeights($damagedAssets, $ahpCriteriaWeights);
                        
                        // The calculatePriorityWithWeights method already updates the database
                        // so we don't need to update again here
                        
                    } catch (\Exception $e) {
                        Log::error('Auto TOPSIS calculation failed', [
                            'error' => $e->getMessage(),
                            'user' => $user->name
                        ]);
                        
                        // Continue without priority scores
                        $priorityScores = [];
                    }
                } else {
                    Log::info('No AHP weights available for TOPSIS calculation', [
                        'user' => $user->name
                    ]);
                }
            }
        }
    
        // Sort by priority if available for kaur roles
        if (!empty($priorityScores) && $user->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2'])) {
            // Only sort if priority sorting wasn't already applied in the query
            $sortField = $request->get('sort', 'created_at');
            if ($sortField !== 'priority' && $sortField !== 'estimasi_biaya') {
                // Sort the collection by priority score (descending - highest priority first)
                $damagedAssets = $damagedAssets->sortByDesc(function($item) use ($priorityScores) {
                    return $priorityScores[$item->id]['score'] ?? 0;
                })->values();
            }
        }
        
        // Handle sorting for staff roles only (other roles already sorted above)
        if ($user->hasRole(['staff_laboratorium', 'staff_logistik'])) {
            $sortField = $request->get('sort', 'tanggal_pelaporan');
            $sortDirection = $request->get('direction', 'desc');
            
            // Validate sort direction
            if (!in_array($sortDirection, ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }
            
            // Apply sorting for collections
            switch ($sortField) {
                case 'estimasi_biaya':
                    $damagedAssets = $sortDirection === 'asc' 
                        ? $damagedAssets->sortBy('estimasi_biaya')
                        : $damagedAssets->sortByDesc('estimasi_biaya');
                    break;
                case 'priority':
                    if (!empty($priorityScores)) {
                        $damagedAssets = $sortDirection === 'asc' 
                            ? $damagedAssets->sortBy(function($item) use ($priorityScores) {
                                return $priorityScores[$item->id]['score'] ?? 0;
                            })
                            : $damagedAssets->sortByDesc(function($item) use ($priorityScores) {
                                return $priorityScores[$item->id]['score'] ?? 0;
                            });
                    }
                    break;
                default:
                    $damagedAssets = $sortDirection === 'asc' 
                        ? $damagedAssets->sortBy('tanggal_pelaporan')
                        : $damagedAssets->sortByDesc('tanggal_pelaporan');
                    break;
            }
            $damagedAssets = $damagedAssets->values(); // Reset keys
        }
        
        // Convert to paginated result for consistency
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
        
        return view('pengajuan.create', compact('maintenanceAssets', 'locations', 'tingkatKerusakanOptions', 'priorityScores'));
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
            
            // Handle multiple photo uploads
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    
                    // Store in storage/app/public/maintenance_photos
                    $path = $photo->storeAs('maintenance_photos', $filename, 'public');
                    
                    $uploadedPhotos[] = [
                        'filename' => $filename,
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
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
            return Excel::download(new AssetTemplateExport, 'template_data_kerusakan_aset.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
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
            
            // Import the Excel file
            $import = new AssetDamageImport();
            Excel::import($import, $file);
            
            DB::commit();
            
            return redirect()->route('pengajuan.create')
                ->with('success', 'File Excel berhasil diproses. Data kerusakan aset telah ditambahkan.');
                
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            return back()->with('error', 'Validasi gagal: ' . implode('<br>', $errorMessages));
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error for debugging
            \Log::error('Excel import error: ' . $e->getMessage());
            
            return back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
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

    private function getStoredPriorityScores($damagedAssets)
    {
        $priorityScores = [];
        
        foreach ($damagedAssets as $asset) {
            if ($asset instanceof MaintenanceAsset && $asset->priority_score !== null) {
                $priorityScores[$asset->id] = [
                    'score' => $asset->priority_score,
                    'rank' => 1 // Will be recalculated based on current dataset
                ];
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
        }
        
        return $priorityScores;
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
            
            if ($pendingAssets->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada pengajuan yang perlu dikalkulasi prioritasnya.'
                ]);
            }
            
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
            
            if (empty($priorityScores)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghitung prioritas. Tidak ada data yang valid untuk dikalkulasi.'
                ]);
            }
            
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
            $filePath = storage_path('app/public/' . $photo['path']);
            
            // Check if file exists
            if (!file_exists($filePath)) {
                abort(404, 'Photo file not found.');
            }
            
            // Get original filename or create a default one
            $filename = $photo['original_name'] ?? 'foto-perbaikan-' . ($photoIndex + 1) . '.jpg';
            
            // Return file download response
            return response()->download($filePath, $filename);
            
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
            
            // Add each photo to the ZIP
            foreach ($maintenanceAsset->photos as $index => $photo) {
                $filePath = storage_path('app/public/' . $photo['path']);
                
                if (file_exists($filePath)) {
                    $filename = $photo['original_name'] ?? 'foto-perbaikan-' . ($index + 1) . '.jpg';
                    $zip->addFile($filePath, $filename);
                }
            }
            
            $zip->close();
            
            // Return ZIP file download and then delete it
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            abort(500, 'Could not create photo archive.');
        }
    }
    
    public function destroy($id)
    {
        $maintenanceAsset = MaintenanceAsset::findOrFail($id);
                    
        $maintenanceAsset->delete();

        return redirect()->route('pengajuan.index')
            ->with('success', 'Pengajuan berhasil dihapus.');        
    }
}