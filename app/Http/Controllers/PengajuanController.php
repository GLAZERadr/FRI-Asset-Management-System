<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceAsset;
use App\Models\DamagedAsset;
use App\Models\Asset;
use App\Models\User;
use App\Models\ApprovalLog;
use App\Services\NotificationService;
use App\Services\TopsisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
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
        } elseif ($user->hasRole(['staff_logistik', 'kaur_keuangan_logistik_sdm'])) {
            // Show only logistic assets (approved or not approved by kaur keuangan)
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
            });
        }
        // For wakil_dekan_2, show all data (no filter needed)
        
        // Apply filters
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('tanggal_pengajuan', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('tanggal_pengajuan', '<=', $request->end_date);
        }
        
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
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
        
        // Different logic based on user role
        if ($user->hasRole(['staff_laboratorium', 'staff_logistik'])) {
            // Get damaged assets without maintenance requests or not completed
            $query = DamagedAsset::with(['asset', 'maintenanceAsset'])
                ->whereDoesntHave('maintenanceAsset') // No maintenance request at all
                ->orWhereHas('maintenanceAsset', function($q) {
                    $q->whereIn('status', ['Ditolak']); // Only show rejected ones
                });
            
            // Filter by division
            if ($user->hasRole('staff_laboratorium')) {
                $query->whereHas('asset', function($q) {
                    $q->where('lokasi', 'LIKE', '%Laboratorium%');
                });
            } else {
                $query->whereHas('asset', function($q) {
                    $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
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
                
        } elseif ($user->hasRole('kaur_laboratorium')) {
            // Show maintenance requests submitted by staff lab
            $query = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('requested_by_role', 'staff_laboratorium')
                ->whereNull('kaur_lab_approved_at')
                ->where('status', 'Menunggu Persetujuan');
                
            $damagedAssets = $query->get();
            
        } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
            // Show data from kaur lab (approved) and staff logistik
            $query = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Menunggu Persetujuan')
                ->where(function($q) {
                    // From staff logistik
                    $q->where('requested_by_role', 'staff_logistik')
                      ->whereNull('kaur_keuangan_approved_at');
                })->orWhere(function($q) {
                    // From staff lab that has been approved by kaur lab
                    $q->where('requested_by_role', 'staff_laboratorium')
                      ->whereNotNull('kaur_lab_approved_at')
                      ->whereNull('kaur_keuangan_approved_at');
                });
                
            $damagedAssets = $query->get();
        } elseif ($user->hasRole('wakil_dekan_2')) {
            // Wakil Dekan 2 sees all maintenance requests that need approval
            $query = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Menunggu Persetujuan');
                
            $damagedAssets = $query->get();
        } else {
            // Default empty collection for other roles
            $damagedAssets = collect();
        }

        // Apply filters
        if ($request->has('lokasi') && $request->lokasi) {
            if ($user->hasRole(['staff_laboratorium', 'staff_logistik'])) {
                $damagedAssets = $damagedAssets->filter(function($item) use ($request) {
                    return $item->asset->lokasi == $request->lokasi;
                });
            } else {
                $query->whereHas('asset', function($q) use ($request) {
                    $q->where('lokasi', $request->lokasi);
                });
                $damagedAssets = $query->get();
            }
        }
        
        if ($request->has('tingkat_kerusakan') && $request->tingkat_kerusakan) {
            if ($user->hasRole(['staff_laboratorium', 'staff_logistik'])) {
                $damagedAssets = $damagedAssets->filter(function($item) use ($request) {
                    return $item->tingkat_kerusakan == $request->tingkat_kerusakan;
                });
            } else {
                $query->whereHas('damagedAsset', function($q) use ($request) {
                    $q->where('tingkat_kerusakan', $request->tingkat_kerusakan);
                });
                $damagedAssets = $query->get();
            }
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
            ['path' => $request->url()]
        );
        
        // Calculate priority scores using TOPSIS if weights are available and user can approve
        $priorityScores = [];
        if (Auth::user()->canApprove() && $maintenanceAssets->count() > 0) {
            $ahpWeights = session('ahp_weights');
            
            if ($ahpWeights && $request->has('with_topsis')) {
                $priorityScores = $this->topsisService->calculatePriorityWithWeights($maintenanceAssets, $ahpWeights);
            } else {
                $priorityScores = $this->calculateDamagedAssetPriority($maintenanceAssets);
            }
        }
        
        $locations = Asset::distinct()->pluck('lokasi');
        $tingkatKerusakanOptions = ['Ringan', 'Sedang', 'Berat'];
        
        return view('pengajuan.create', compact('maintenanceAssets', 'locations', 'tingkatKerusakanOptions', 'priorityScores'));
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
            
            foreach ($validated['damaged_asset_ids'] as $damagedAssetId) {
                $damagedAsset = DamagedAsset::findOrFail($damagedAssetId);
                
                // Check if already has maintenance request
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
                
                $createdAssets[] = $maintenanceAsset;
            }
            
            // Send notification to approver
            $approver = $user->getApprover();
            if ($approver) {
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
                    // Rejected by kaur lab
                    $maintenanceAsset->update(['status' => 'Ditolak']);
                }
                
            } elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
                // Kaur keuangan final approval
                $maintenanceAsset->update([
                    'kaur_keuangan_approved_at' => now(),
                    'kaur_keuangan_approved_by' => $user->username,
                    'status' => $status
                ]);
            }
            
            // Log the approval action
            ApprovalLog::create([
                'maintenance_asset_id' => $maintenanceAsset->id,
                'action' => $action === 'approve' ? 'approved' : 'rejected',
                'performed_by' => $user->username,
                'role' => $user->roles->first()->name,
                'notes' => $validated['notes'] ?? null
            ]);
            
            // Send notification to original requester
            $requester = User::find($maintenanceAsset->requested_by);
            if ($requester) {
                $this->notificationService->sendApprovalResult(
                    $maintenanceAsset,
                    $requester,
                    $status,
                    $user->roles->first()->name
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $action === 'approve' ? 'Pengajuan berhasil disetujui' : 'Pengajuan ditolak'
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
        $validated = $request->validate([
            'criteria' => 'required|array',
            'weights' => 'required|array',
            'consistency_ratio' => 'required|numeric'
        ]);
        
        // Store weights in session for use in TOPSIS
        session([
            'ahp_weights' => $validated['weights'],
            'ahp_criteria' => $validated['criteria'],
            'consistency_ratio' => $validated['consistency_ratio']
        ]);
        
        return response()->json(['success' => true]);
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
        $query = MaintenanceAsset::query();
        
        // Filter based on user role for stats
        if ($user->hasRole(['staff_laboratorium', 'kaur_laboratorium'])) {
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'LIKE', '%Laboratorium%');
            });
        } elseif ($user->hasRole(['staff_logistik', 'kaur_keuangan_logistik_sdm'])) {
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
            });
        }
        
        return [
            'completed' => (clone $query)->where('status', 'Selesai')->count(),
            'in_progress' => (clone $query)->where('status', 'Dikerjakan')->count(),
            'received' => (clone $query)->where('status', 'Diterima')->count(),
            'total_expenditure' => (clone $query)->where('status', 'Selesai')
                ->join('damaged_assets', 'maintenance_assets.damage_id', '=', 'damaged_assets.damage_id')
                ->sum('damaged_assets.estimasi_biaya') ?? 0
        ];
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
        // Users can view their own requests
        if ($maintenanceAsset->requested_by == $user->id) {
            return true;
        }
        
        // Kaur roles can view requests they need to approve
        if ($user->hasRole('kaur_laboratorium') && $maintenanceAsset->requested_by_role == 'staff_laboratorium') {
            return true;
        }
        
        if ($user->hasRole('kaur_keuangan_logistik_sdm')) {
            return true;
        }
        
        if ($user->hasRole('wakil_dekan_2')) {
            return true;
        }
        
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
        $query = MaintenanceAsset::with(['asset', 'damagedAsset']);
        
        // Apply filters
        if ($request->filled('lokasi')) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
            });
        }
        
        if ($request->filled('petugas')) {
            $query->where('teknisi', $request->petugas);
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
        
        return view('pengajuan.detailed', compact('maintenanceRequests', 'locations'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Diterima,Dikerjakan,Selesai,Ditolak'
        ]);
        
        $maintenanceAsset = MaintenanceAsset::findOrFail($id);
        
        // Only update if current status allows it
        if (!in_array($maintenanceAsset->status, ['Selesai', 'Ditolak'])) {
            $maintenanceAsset->update(['status' => $validated['status']]);
            
            return back()->with('success', 'Status berhasil diperbarui.');
        }
        
        return back()->with('error', 'Status tidak dapat diubah.');
    }
    
    public function destroy($id)
    {
        $maintenanceAsset = MaintenanceAsset::findOrFail($id);
        
        // Only allow deletion if not yet approved
        if ($maintenanceAsset->status == 'Menunggu Persetujuan' && 
            $maintenanceAsset->requested_by == Auth::id()) {
            
            $maintenanceAsset->delete();
            return redirect()->route('pengajuan.index')
                ->with('success', 'Pengajuan berhasil dihapus.');
        }
        
        return back()->with('error', 'Pengajuan tidak dapat dihapus.');
    }
}