<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetMonitoring;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MonitoringValidationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AssetMonitoring::query();
        
        // Filter based on user role according to the new rules
        if ($user->hasRole(['kaur_laboratorium'])) {
            // Show only lab assets (approved or not approved by kaur keuangan)
            $query->where(function($q) {
                $q->where('id_laporan', 'LIKE', '%-LAB-%')
                  ->Where('validated', 'not_validated');
            });
        } elseif ($user->hasRole(['kaur_keuangan_logistik_sdm'])) {
            // Show only logistic assets (approved or not approved by kaur keuangan)
            $query->where(function($q) {
                $q->where('id_laporan', 'LIKE', '%-LOG-%')
                  ->Where('validated', 'not_validated');
            });
        }
        
        // Add search functionality if needed
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id_laporan', 'LIKE', "%{$search}%")
                  ->orWhere('kode_ruangan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_pelapor', 'LIKE', "%{$search}%")
                  ->orWhere('reviewer', 'LIKE', "%{$search}%");
            });
        }
        
        // Filter by year if provided
        if ($request->has('year') && !empty($request->year)) {
            $query->whereYear('tanggal_laporan', $request->year);
        }
        
        // Order by latest reports first
        $query->orderBy('tanggal_laporan', 'desc')
              ->orderBy('created_at', 'desc');
        
        // Get paginated results
        $reports = $query->paginate(10);
        
        // Transform the data to match the table structure
        $validationData = [];
        
        foreach ($reports as $report) {
            // Get monitored assets from monitoring_data
            $monitoringData = $report->monitoring_data ?? [];
            
            foreach ($monitoringData as $index => $assetData) {
                // Get asset details
                $asset = null;
                if (isset($assetData['asset_id'])) {
                    $asset = Asset::where('asset_id', $assetData['asset_id'])->first();
                }
                
                $validationData[] = [
                    'id_laporan' => $report->id_laporan,
                    'nama_ruangan' => $report->kode_ruangan,
                    'kode_ruangan' => $report->kode_ruangan,
                    'jumlah_unit_aset' => count($monitoringData) . ' unit',
                    'periode_monitoring' => $report->tanggal_laporan ? $report->tanggal_laporan->format('M-Y') : 'N/A',
                    'reviewer' => $report->reviewer ?? 'Belum ditentukan',
                    'validated' => $report->validated,
                    'validated_at' => $report->validated_at,
                    'report_id' => $report->id,
                    'asset_name' => $asset ? $asset->nama_asset : ($assetData['asset_name'] ?? 'Unknown Asset'),
                    'asset_code' => $asset ? $asset->kode_aset : ($assetData['asset_code'] ?? 'N/A'),
                    'condition' => $assetData['condition'] ?? 'N/A',
                    'notes' => $assetData['notes'] ?? ''
                ];
            }
            
            // If no monitoring data, still show the report
            if (empty($monitoringData)) {
                $validationData[] = [
                    'id_laporan' => $report->id_laporan,
                    'nama_ruangan' => $report->kode_ruangan,
                    'kode_ruangan' => $report->kode_ruangan,
                    'jumlah_unit_aset' => '0 unit',
                    'periode_monitoring' => $report->tanggal_laporan ? $report->tanggal_laporan->format('M-Y') : 'N/A',
                    'reviewer' => $report->reviewer ?? 'Belum ditentukan',
                    'validated' => $report->validated,
                    'validated_at' => $report->validated_at,
                    'report_id' => $report->id,
                    'asset_name' => 'No assets monitored',
                    'asset_code' => 'N/A',
                    'condition' => 'N/A',
                    'notes' => ''
                ];
            }
        }
        
        // Get available years for filter dropdown
        $availableYears = AssetMonitoring::selectRaw('YEAR(tanggal_laporan) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter();
        
        return view('monitoring.validate.index', compact(
            'validationData', 
            'reports', 
            'availableYears',
            'user'
        ));
    }
    
    public function create($id_laporan)
    {
        // Find the monitoring report by ID Laporan
        $report = AssetMonitoring::where('id_laporan', $id_laporan)->firstOrFail();
        
        // Get monitoring data from the report
        $monitoringData = $report->monitoring_data ?? [];
        
        // Get all assets that were monitored in this report
        $assetsData = [];
        
        foreach ($monitoringData as $assetData) {
            if (isset($assetData['asset_id'])) {
                // Find the asset by asset_id
                $asset = Asset::where('asset_id', $assetData['asset_id'])->first();
                
                if ($asset) {
                    // Calculate status kelayakan based on masa pakai maksimum
                    $statusKelayakan = 'Layak'; // Default
                    
                    if ($asset->masa_pakai_maksimum && now()->gt($asset->masa_pakai_maksimum)) {
                        $statusKelayakan = 'Tidak Layak';
                    }
                    
                    // Get the asset condition from monitoring data
                    $condition = $assetData['condition'] ?? 'Baik';
                    $notes = $assetData['deskripsi'] ?? $assetData['notes'] ?? '';
                    
                    // Override status if asset condition is poor
                    if (in_array(strtolower($condition), ['rusak', 'tidak berfungsi', 'buruk'])) {
                        $statusKelayakan = 'Tidak Layak';
                    }
                    
                    $assetsData[] = [
                        'asset' => $asset,
                        'condition' => $condition,
                        'notes' => $notes,
                        'status_kelayakan' => $statusKelayakan,
                        'monitoring_data' => $assetData
                    ];
                }
            }
        }
        
        // If no monitoring data or assets found, get all assets from the room
        if (empty($assetsData) && $report->kode_ruangan) {
            $roomAssets = Asset::where('kode_ruangan', $report->kode_ruangan)->get();
            
            foreach ($roomAssets as $asset) {
                // Calculate status kelayakan
                $statusKelayakan = 'Layak';
                
                if ($asset->masa_pakai_maksimum && now()->gt($asset->masa_pakai_maksimum)) {
                    $statusKelayakan = 'Tidak Layak';
                }
                
                $assetsData[] = [
                    'asset' => $asset,
                    'condition' => 'Tidak Dimonitor',
                    'notes' => '',
                    'status_kelayakan' => $statusKelayakan,
                    'monitoring_data' => null
                ];
            }
        }
        
        return view('monitoring.validate.status-kelayakan', compact('report', 'assetsData'));
    }
    
    public function store(Request $request, $id_laporan)
    {
        try {
            $request->validate([
                'assets' => 'required|array',
                'assets.*.status_kelayakan' => 'required|in:Layak,Tidak Layak',
            ]);
            
            // Find the monitoring report
            $report = AssetMonitoring::where('id_laporan', $id_laporan)->firstOrFail();
            
            // Update each asset's status kelayakan
            foreach ($request->assets as $assetId => $assetData) {
                $asset = Asset::where('asset_id', $assetId)->first();
                
                if ($asset) {
                    $asset->update([
                        'status_kelayakan' => $assetData['status_kelayakan']
                    ]);
                }
            }
            
            // Update monitoring data with validation info
            $monitoringData = $report->monitoring_data ?? [];
            foreach ($monitoringData as &$data) {
                if (isset($request->assets[$data['asset_id']])) {
                    $data['status_kelayakan'] = $request->assets[$data['asset_id']]['status_kelayakan'];
                }
            }
            
            $report->update([
                'monitoring_data' => $monitoringData
            ]);
            
            // Return JSON response for AJAX
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status kelayakan berhasil disimpan'
                ]);
            }
            
            return redirect()->back()->with('success', 'Status kelayakan berhasil disimpan');
            
        } catch (\Exception $e) {
            \Log::error('Error in FixValidationController@store: ' . $e->getMessage());
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id_laporan)
    {
        try {
            $request->validate([
                'catatan' => 'nullable|string|max:1000'
            ]);
            
            $report = AssetMonitoring::where('id_laporan', $id_laporan)->firstOrFail();
            
            $report->update([
                'validated' => "valid",
                'catatan' => $request->input('catatan'),
                'validated_at' => now()
            ]);
            
            return redirect()->route('fix-validation.index')->with('success', 'Laporan berhasil divalidasi');
            
        } catch (\Exception $e) {
            \Log::error('Error in FixValidationController@approve: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function printValidated(Request $request)
    {
        $user = auth()->user();
        
        // Apply year filter if provided
        $yearFilter = $request->get('year');
        
        // Get validation data with the same logic as your index method
        $reportsQuery = AssetMonitoring::with(['user'])
            ->where('validated', 'not_validated')
            ->orderBy('tanggal_laporan', 'desc');
        
        if ($yearFilter) {
            $reportsQuery->whereYear('tanggal_laporan', $yearFilter);
        }
        
        $reports = $reportsQuery->get();
        
        // Prepare validation data in the same format as your view
        $validationData = [];
        foreach ($reports as $report) {
            $assetCount = $report->monitoring_data ? count($report->monitoring_data) : 0;
            
            $validationData[] = [
                'id_laporan' => $report->id_laporan,
                'nama_ruangan' => $report->nama_ruangan,
                'kode_ruangan' => $report->kode_ruangan,
                'jumlah_unit_aset' => $assetCount . ' unit',
                'periode_monitoring' => $report->tanggal_laporan->format('M-Y'),
                'reviewer' => $report->reviewer_name ?? 'Belum ditentukan',
            ];
        }
        
        // Get available years for filter display
        $availableYears = AssetMonitoring::selectRaw('YEAR(tanggal_laporan) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Return the PDF view with validation data
        return view('monitoring.validate.pdf', compact(
            'validationData',
            'availableYears'
        ));
    }
}