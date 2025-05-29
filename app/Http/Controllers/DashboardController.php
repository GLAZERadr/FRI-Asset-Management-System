<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaintenanceAsset;
use App\Models\DamagedAsset;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
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
            'total_expenditure' => 0 // We'll calculate this separately
        ];
        
        // Calculate total expenditure safely
        try {
            $expenditureQuery = (clone $statsQuery)->where('status', 'Selesai');
            $stats['total_expenditure'] = $expenditureQuery
                ->join('damaged_assets', 'maintenance_assets.damage_id', '=', 'damaged_assets.damage_id')
                ->sum('damaged_assets.estimasi_biaya') ?? 0;
        } catch (\Exception $e) {
            Log::error('Error calculating expenditure: ' . $e->getMessage());
            $stats['total_expenditure'] = 0;
        }
        
        // Get recent maintenance requests based on user role
        $recentRequests = collect();
        $role = 'general'; // Default role
        
        // Check user roles and assign appropriate data
        if ($user->hasRole('staff_logistik')) {
            Log::info('User has staff_logistik role, filtering by division: ' . $user->division);
            
            $recentRequests = MaintenanceAsset::with(['asset', 'damagedAsset'])
                ->whereHas('damagedAsset', function($q) use ($user) {
                    $q->where('pelapor', $user->division);
                })
                ->when($request->filled('start_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '>=', $request->start_date);
                })
                ->when($request->filled('end_date'), function($q) use ($request) {
                    $q->whereDate('tanggal_pengajuan', '<=', $request->end_date);
                })
                ->latest('tanggal_pengajuan')
                ->take(10)
                ->get();
            $role = 'staff_logistik';
            
            Log::info('Found ' . $recentRequests->count() . ' requests for staff_logistik');
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
        
        // Check if we have any maintenance assets at all
        $totalMaintenanceAssets = MaintenanceAsset::count();
        
        return view('dashboard.dashboard', compact('stats', 'recentRequests', 'role'));
    }

    /**
     * Get monthly expenditure data for chart
     */
    public function getMonthlyExpenditure()
    {
        try {
            $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
            
            // Check if we have completion dates in our data
            $hasCompletionDates = MaintenanceAsset::where('status', 'Selesai')
                ->whereNotNull('tanggal_selesai')
                ->count();
                
            if ($hasCompletionDates == 0) {
                // Fallback to using tanggal_pengajuan if no completion dates
                $monthlyData = MaintenanceAsset::where('status', 'Selesai')
                    ->where('tanggal_pengajuan', '>=', $sixMonthsAgo)
                    ->join('damaged_assets', 'maintenance_assets.damage_id', '=', 'damaged_assets.damage_id')
                    ->select(
                        DB::raw('MONTH(tanggal_pengajuan) as month'),
                        DB::raw('YEAR(tanggal_pengajuan) as year'),
                        DB::raw('SUM(estimasi_biaya) as total')
                    )
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
            } else {
                // Use completion dates
                $monthlyData = MaintenanceAsset::where('status', 'Selesai')
                    ->where('tanggal_selesai', '>=', $sixMonthsAgo)
                    ->join('damaged_assets', 'maintenance_assets.damage_id', '=', 'damaged_assets.damage_id')
                    ->select(
                        DB::raw('MONTH(tanggal_selesai) as month'),
                        DB::raw('YEAR(tanggal_selesai) as year'),
                        DB::raw('SUM(estimasi_biaya) as total')
                    )
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();
            }
            
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
            Log::error('Error in getMonthlyExpenditure: ' . $e->getMessage());
            
            // Return empty data on error
            return response()->json([
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'values' => [0, 0, 0, 0, 0, 0]
            ]);
        }
    }
}