<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceAsset;
use App\Models\AssetMonitoring;
use App\Models\DamagedAsset;
use App\Models\Asset;
use App\Models\Payment; // ← Add Payment model import
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is staff_laboratorium and accessing from mobile
        if (
            $user->hasAnyRole(['staff_laboratorium', 'staff_logistik']) 
            && $this->isMobileRequest($request)
        ) {
            return $this->mobileStaffLaboratoriumDashboard($request);
        }
        
        
        // Debug info
        Log::info('Dashboard accessed by user:', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_division' => $user->division,
            'user_roles' => $user->getRoleNames()->toArray()
        ]);
        
        // Apply date filters if provided
        $query = MaintenanceAsset::query();
        
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_pengajuan', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_pengajuan', '<=', $request->end_date);
        }
        
        // Basic dashboard stats with date filtering
        $statsQuery = clone $query;
        $stats = [
            'completed' => (clone $statsQuery)->where('status', 'Selesai')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'Dikerjakan')->count(),
            'received' => (clone $statsQuery)->where('status', 'Diterima')->count(),
            'total_expenditure' => 0
        ];
        
        // ADD NEW: Role-specific statistics
        if ($user->hasRole('staff_logistik')) {
            $stats = array_merge($stats, $this->getStaffLogistikStats($request));
        } elseif ($user->hasRole('staff_laboratorium')) {
            $stats = array_merge($stats, $this->getStaffLaboratoriumStats($request));
        }
        
        // Calculate total expenditure from Payment model
        try {
            $paymentQuery = Payment::whereIn('status', ['sudah_dibayar', 'menunggu_verifikasi']);
            
            if ($request->filled('start_date')) {
                $paymentQuery->whereDate('tanggal_pembayaran', '>=', $request->start_date);
            }
            
            if ($request->filled('end_date')) {
                $paymentQuery->whereDate('tanggal_pembayaran', '<=', $request->end_date);
            }
            
            $stats['total_expenditure'] = $paymentQuery->sum('total_tagihan') ?? 0;
            
        } catch (\Exception $e) {
            Log::error('Error calculating expenditure from payments: ' . $e->getMessage());
            $stats['total_expenditure'] = 0;
        }
        
        // ADD NEW: Payment-specific statistics
        $stats = array_merge($stats, $this->getPaymentStats($request));
        
        // FIXED: Get role-specific data
        $recentRequests = collect();
        $role = 'general';
        
        // Check user roles and assign appropriate data
        if ($user->hasRole('staff_logistik')) {
            Log::info('User has staff_logistik role, fetching AssetMonitoring data');
            
            // FIXED: Fetch AssetMonitoring data instead of MaintenanceAsset
            $recentRequests = AssetMonitoring::query()
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '<=', $request->end_date);
                })
                ->latest('tanggal_laporan')
                ->take(10)
                ->get();
            $role = 'staff_logistik';
            
            Log::info('Found ' . $recentRequests->count() . ' monitoring reports for staff_logistik');
        } 
        elseif ($user->hasRole('staff_laboratorium')) {
            Log::info('User has staff_laboratorium role, fetching AssetMonitoring data');
            
            // FIXED: Fetch AssetMonitoring data for staff_laboratorium too
            $recentRequests = AssetMonitoring::query()
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_laporan', '<=', $request->end_date);
                })
                ->latest('tanggal_laporan')
                ->take(10)
                ->get();
            $role = 'staff_laboratorium';
            
            Log::info('Found ' . $recentRequests->count() . ' monitoring reports for staff_laboratorium');
        }
        elseif ($user->hasRole('kaur_laboratorium')) {
            $recentRequests = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Diterima')
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '<=', $request->end_date);
                })
                ->latest('tanggal_pengajuan')
                ->take(10)
                ->get();
            $role = 'kaur_laboratorium';
        } 
        elseif ($user->hasRole('kaur_keuangan_logistik_sdm')) {
            $recentRequests = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->where('status', 'Dikerjakan')
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '<=', $request->end_date);
                })
                ->latest('tanggal_pengajuan')
                ->take(10)
                ->get();
            $role = 'kaur_keuangan_logistik_sdm';
        } 
        elseif ($user->hasRole('wakil_dekan_2')) {
            $recentRequests = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->whereIn('status', ['Diterima', 'Dikerjakan'])
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '<=', $request->end_date);
                })
                ->latest('tanggal_pengajuan')
                ->take(10)
                ->get();
            $role = 'wakil_dekan_2';
        }
        else {
            $recentRequests = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '<=', $request->end_date);
                })
                ->latest('tanggal_pengajuan')
                ->take(10)
                ->get();
            $role = 'general';
        }
        
        return view('dashboard.dashboard', compact('stats', 'recentRequests', 'role'));
    }

    // ADD NEW: Payment-specific statistics
    private function getPaymentStats(Request $request)
    {
        $query = Payment::query();
        
        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_pembayaran', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_pembayaran', '<=', $request->end_date);
        }
        
        return [
            'total_payments' => (clone $query)->count(),
            'payments_paid' => (clone $query)->where('status', 'sudah_dibayar')->count(),
            'payments_pending_verification' => (clone $query)->where('status', 'menunggu_verifikasi')->count(),
            'payments_unpaid' => (clone $query)->where('status', 'belum_dibayar')->count(),
            'payments_overdue' => (clone $query)->where('status', 'belum_dibayar')
                ->where('jatuh_tempo', '<', now())->count(),
            'total_paid_amount' => (clone $query)->whereIn('status', ['sudah_dibayar', 'menunggu_verifikasi'])
                ->sum('total_tagihan') ?? 0,
            'total_unpaid_amount' => (clone $query)->where('status', 'belum_dibayar')
                ->sum('total_tagihan') ?? 0,
        ];
    }

    // ADD NEW: Staff Logistik specific statistics
    private function getStaffLogistikStats(Request $request)
    {
        $today = Carbon::today();
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : $today;
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : $today;
        
        // Reports that need verification (not validated yet)
        $reportsNeedVerification = AssetMonitoring::where('validated', false)
            ->orWhereNull('validated')
            ->whereBetween('tanggal_laporan', [$startDate, $endDate])
            ->count();
        
        // Reports that need verification today specifically
        $reportsNeedVerificationToday = AssetMonitoring::where('validated', false)
            ->orWhereNull('validated')
            ->whereDate('tanggal_laporan', $today)
            ->count();
        
        // Daily breakdown for the selected period
        $dailyVerificationNeeded = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dailyCount = AssetMonitoring::where('validated', false)
                ->orWhereNull('validated')
                ->whereDate('tanggal_laporan', $currentDate)
                ->count();
            
            $dailyVerificationNeeded[] = [
                'date' => $currentDate->format('Y-m-d'),
                'count' => $dailyCount
            ];
            
            $currentDate->addDay();
        }
        
        return [
            'reports_need_verification' => $reportsNeedVerification,
            'reports_need_verification_today' => $reportsNeedVerificationToday,
            'daily_verification_needed' => $dailyVerificationNeeded
        ];
    }

    // ADD NEW: Staff Laboratorium specific statistics  
    private function getStaffLaboratoriumStats(Request $request)
    {
        $today = Carbon::today();
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : $today;
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : $today;
        
        // Assets monitored in the selected period
        $assetsMonitoredQuery = AssetMonitoring::whereBetween('tanggal_laporan', [$startDate, $endDate]);
        
        // Get unique assets monitored (from monitoring_data JSON)
        $monitoringReports = $assetsMonitoredQuery->get();
        $uniqueAssetsMonitored = collect();
        
        foreach ($monitoringReports as $report) {
            if ($report->monitoring_data) {
                $assetIds = collect($report->monitoring_data)->pluck('asset_id');
                $uniqueAssetsMonitored = $uniqueAssetsMonitored->concat($assetIds);
            }
        }
        
        $totalAssetsMonitored = $uniqueAssetsMonitored->unique()->count();
        
        // Assets monitored today
        $assetsMonitoredToday = 0;
        $todayReports = AssetMonitoring::whereDate('tanggal_laporan', $today)->get();
        $todayAssetsMonitored = collect();
        
        foreach ($todayReports as $report) {
            if ($report->monitoring_data) {
                $assetIds = collect($report->monitoring_data)->pluck('asset_id');
                $todayAssetsMonitored = $todayAssetsMonitored->concat($assetIds);
            }
        }
        
        $assetsMonitoredToday = $todayAssetsMonitored->unique()->count();
        
        // Daily breakdown for the selected period
        $dailyAssetsMonitored = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $dayReports = AssetMonitoring::whereDate('tanggal_laporan', $currentDate)->get();
            $dayAssetsMonitored = collect();
            
            foreach ($dayReports as $report) {
                if ($report->monitoring_data) {
                    $assetIds = collect($report->monitoring_data)->pluck('asset_id');
                    $dayAssetsMonitored = $dayAssetsMonitored->concat($assetIds);
                }
            }
            
            $dailyAssetsMonitored[] = [
                'date' => $currentDate->format('Y-m-d'),
                'count' => $dayAssetsMonitored->unique()->count()
            ];
            
            $currentDate->addDay();
        }
        
        return [
            'assets_monitored_total' => $totalAssetsMonitored,
            'assets_monitored_today' => $assetsMonitoredToday,
            'daily_assets_monitored' => $dailyAssetsMonitored
        ];
    }

    /**
     * UPDATED: Get monthly expenditure data from Payment model
     */
    public function getMonthlyExpenditure()
    {
        try {
            $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
            
            // Get monthly payment data from Payment model
            $monthlyData = Payment::whereIn('status', ['sudah_dibayar', 'menunggu_verifikasi'])
                ->where('tanggal_pembayaran', '>=', $sixMonthsAgo)
                ->whereNotNull('tanggal_pembayaran')
                ->select(
                    DB::raw('MONTH(tanggal_pembayaran) as month'),
                    DB::raw('YEAR(tanggal_pembayaran) as year'),
                    DB::raw('SUM(total_tagihan) as total')
                )
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();
            
            $labels = [];
            $values = [];
            
            // Create data for the last 6 months
            for ($i = 0; $i < 6; $i++) {
                $date = Carbon::now()->subMonths(5 - $i)->startOfMonth();
                $monthYear = $date->format('M Y');
                $labels[] = $monthYear;
                
                // Find if we have data for this month
                $found = false;
                foreach ($monthlyData as $data) {
                    if ($data->month == $date->month && $data->year == $date->year) {
                        $values[] = (float) $data->total;
                        $found = true;
                        break;
                    }
                }
                
                // If no data found for this month, add 0
                if (!$found) {
                    $values[] = 0;
                }
            }
            
            return response()->json([
                'labels' => $labels,
                'values' => $values
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getMonthlyExpenditure from Payment model: ' . $e->getMessage());
            
            // Return empty data on error
            return response()->json([
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'values' => [0, 0, 0, 0, 0, 0]
            ]);
        }
    }

    /**
     * NEW: Get payment statistics breakdown
     */
    public function getPaymentStatistics(Request $request)
    {
        try {
            $query = Payment::query();
            
            // Apply date filters if provided
            if ($request->filled('start_date')) {
                $query->whereDate('tanggal_pembayaran', '>=', $request->start_date);
            }
            
            if ($request->filled('end_date')) {
                $query->whereDate('tanggal_pembayaran', '<=', $request->end_date);
            }
            
            $stats = [
                'total_payments' => (clone $query)->count(),
                'paid_verified' => (clone $query)->where('status', 'sudah_dibayar')->count(),
                'pending_verification' => (clone $query)->where('status', 'menunggu_verifikasi')->count(),
                'unpaid' => (clone $query)->where('status', 'belum_dibayar')->count(),
                'overdue' => (clone $query)->where('status', 'belum_dibayar')
                    ->where('jatuh_tempo', '<', now())->count(),
                'total_amount_paid' => (clone $query)->whereIn('status', ['sudah_dibayar', 'menunggu_verifikasi'])
                    ->sum('total_tagihan'),
                'total_amount_unpaid' => (clone $query)->where('status', 'belum_dibayar')
                    ->sum('total_tagihan'),
            ];
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            Log::error('Error in getPaymentStatistics: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch payment statistics'], 500);
        }
    }

    /**
     * Mobile dashboard for staff_laboratorium
     */
    private function mobileStaffLaboratoriumDashboard(Request $request)
    {
        $user = Auth::user();
        
        // Get asset statistics for staff_laboratorium
        $assetStats = [
            'total_assets' => Asset::count(),
            'layak' => Asset::where('status_kelayakan', 'Layak')->count(),
            'tidak_layak' => Asset::where('status_kelayakan', 'Tidak Layak')->count(),
        ];
        
        // Get damage statistics
        $damageStats = [
            'total_damaged' => DamagedAsset::count(),
            'ringan' => DamagedAsset::where('tingkat_kerusakan', 'Ringan')->count(),
            'sedang' => DamagedAsset::where('tingkat_kerusakan', 'Sedang')->count(),
            'berat' => DamagedAsset::where('tingkat_kerusakan', 'Berat')->count(),
        ];
        
        // Get upcoming maintenance schedule
        $upcomingMaintenance = MaintenanceAsset::with(['asset', 'damagedAsset'])
            ->whereIn('status', ['Diterima', 'Dikerjakan'])
            ->orderBy('tanggal_pengajuan', 'asc')
            ->take(5)
            ->get();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        return view('dashboard.mobile', compact(
            'assetStats', 
            'damageStats', 
            'upcomingMaintenance', 
            'recentActivities'
        ));
    }

    /**
     * Get recent activities for mobile dashboard
     */
    private function getRecentActivities()
    {
        $activities = [];
        
        // Recent asset additions
        $recentAssets = Asset::latest()->take(3)->get();
        foreach ($recentAssets as $asset) {
            $activities[] = [
                'type' => 'asset_added',
                'message' => "Aset {$asset->asset_id} berhasil ditambahkan",
                'time' => $asset->created_at,
                'color' => 'green'
            ];
        }
        
        // Recent maintenance requests
        $recentMaintenance = MaintenanceAsset::latest()->take(3)->get();
        foreach ($recentMaintenance as $maintenance) {
            $activities[] = [
                'type' => 'maintenance_request',
                'message' => "Pengajuan perbaikan {$maintenance->asset_id} menunggu persetujuan",
                'time' => $maintenance->created_at,
                'color' => 'yellow'
            ];
        }
        
        // Recent damage reports
        $recentDamages = DamagedAsset::latest()->take(2)->get();
        foreach ($recentDamages as $damage) {
            $activities[] = [
                'type' => 'damage_report',
                'message' => "Laporan monitoring telah dikirim",
                'time' => $damage->created_at,
                'color' => 'blue'
            ];
        }
        
        // Sort by time and take latest 5
        usort($activities, function($a, $b) {
            return $b['time']->timestamp - $a['time']->timestamp;
        });
        
        return array_slice($activities, 0, 5);
    }

    /**
     * Check if request is from mobile device
     */
    private function isMobileRequest(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        return preg_match('/Mobile|Android|iPhone|iPad|BlackBerry|Opera Mini/', $userAgent) || 
               $request->header('Accept') === 'application/mobile' ||
               $request->has('mobile') ||
               $request->has('force-mobile');
    }

    /**
     * Get asset statistics data for mobile dashboard
     */
    public function getAssetStats()
    {
        $stats = [
            'layak' => Asset::where('status_kelayakan', 'Layak')->count(),
            'tidak_layak' => Asset::where('status_kelayakan', 'Tidak Layak')->count(),
        ];
        
        return response()->json($stats);
    }

    /**
     * Get damage statistics data for mobile dashboard
     */
    public function getDamageStats()
    {
        $stats = [
            'ringan' => DamagedAsset::where('tingkat_kerusakan', 'Ringan')->count(),
            'sedang' => DamagedAsset::where('tingkat_kerusakan', 'Sedang')->count(),
            'berat' => DamagedAsset::where('tingkat_kerusakan', 'Berat')->count(),
        ];
        
        return response()->json($stats);
    }
}