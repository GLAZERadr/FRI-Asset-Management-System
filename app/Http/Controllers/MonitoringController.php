<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetMonitoring;
use App\Models\DamagedAsset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MonitoringController extends Controller
{
    public function showMonitoring($kodeRuangan)
    {
        $assets = Asset::where('kode_ruangan', $kodeRuangan)->get();
        
        if ($assets->isEmpty()) {
            return redirect()->back()->with('error', 'No assets found for this room code: ' . $kodeRuangan);
        }
        
        return view('monitoring.form', compact('assets', 'kodeRuangan'));
    }

    public function storeMonitoring(Request $request)
    {
        $request->validate([
            'kode_ruangan' => 'required|string',
            'nama_pelapor' => 'required|string',
            'tanggal_laporan' => 'required|date',
            'asset_data' => 'required|array',
            'asset_data.*.asset_id' => 'required|string',
            'asset_data.*.status' => 'required|in:baik,butuh_perawatan',
            'asset_data.*.deskripsi' => 'nullable|string',
            'asset_data.*.foto' => 'nullable|image|max:2048'
        ]);
    
        // Generate unique report ID based on user role
        $user = auth()->user();
        $idLaporan = AssetMonitoring::generateIdLaporan($user);
    
        // Handle file uploads and prepare monitoring data
        $monitoringData = [];
        $damagedAssets = []; // Track damaged assets for DamagedAsset model
        
        foreach ($request->asset_data as $key => $assetData) {
            $photoPath = null;
            if (isset($assetData['foto']) && $assetData['foto']) {
                $photoPath = $assetData['foto']->store('monitoring-photos', 'public');
            }
            
            $monitoringData[] = [
                'asset_id' => $assetData['asset_id'],
                'status' => $assetData['status'],
                'verification' => 'not_verified',
                'deskripsi' => $assetData['deskripsi'] ?? null,
                'foto_path' => $photoPath,
                'id_laporan' => $idLaporan // Add report ID to each asset data
            ];
    
            // If asset needs maintenance, prepare for DamagedAsset record
            if ($assetData['status'] === 'butuh_perawatan') {
                $damagedAssets[] = [
                    'asset_id' => $assetData['asset_id'],
                    'deskripsi_kerusakan' => $assetData['deskripsi'] ?? 'Butuh perawatan dari monitoring',
                    'foto_path' => $photoPath,
                    'id_laporan' => $idLaporan
                ];
            }
        }
    
        // Create monitoring record
        $monitoring = AssetMonitoring::create([
            'id_laporan' => $idLaporan,
            'kode_ruangan' => $request->kode_ruangan,
            'nama_pelapor' => $request->nama_pelapor,
            'tanggal_laporan' => $request->tanggal_laporan,
            'monitoring_data' => $monitoringData,
            'user_id' => auth()->id()
        ]);
    
        // Create DamagedAsset records for assets that need maintenance
        foreach ($damagedAssets as $damagedData) {
            DamagedAsset::create([
                'damage_id' => 'DMG-' . date('Ymd') . '-' . Str::random(6),
                'asset_id' => $damagedData['asset_id'],
                'tingkat_kerusakan' => 'Sedang', // Default level
                'estimasi_biaya' => 0, // To be filled later
                'deskripsi_kerusakan' => $damagedData['deskripsi_kerusakan'],
                'tanggal_pelaporan' => $request->tanggal_laporan,
                'pelapor' => $request->nama_pelapor,
                'vendor' => null, // To be assigned later
                'id_laporan' => $idLaporan // Link to monitoring report
            ]);
        }
    
        return redirect()->route('dashboard')->with('success', 
            "Monitoring report submitted successfully! Report ID: {$idLaporan}" . 
            (count($damagedAssets) > 0 ? ' ' . count($damagedAssets) . ' damaged assets have been reported.' : ''));
    }

    public function index(Request $request)
    {
        $query = AssetMonitoring::with('user');
        
        // Apply filters
        if ($request->has('kode_ruangan') && $request->kode_ruangan) {
            $query->where('kode_ruangan', 'like', '%' . $request->kode_ruangan . '%');
        }
        
        if ($request->has('pelapor') && $request->pelapor) {
            $query->where('nama_pelapor', 'like', '%' . $request->pelapor . '%');
        }
        
        if ($request->has('tanggal_laporan') && $request->tanggal_laporan) {
            $query->whereDate('tanggal_laporan', $request->tanggal_laporan);
        }
        
        $monitoringReports = $query->orderBy('tanggal_laporan', 'desc')->paginate(15);
        
        // Get all assets for reference
        $assets = Asset::all();
        
        // Get unique locations for filter
        $locations = Asset::distinct()->whereNotNull('lokasi')->pluck('lokasi')->filter();
        
        // Filter by location if specified
        if ($request->has('lokasi') && $request->lokasi) {
            $filteredAssetIds = Asset::where('lokasi', $request->lokasi)->pluck('asset_id');
            $monitoringReports = $monitoringReports->filter(function ($report) use ($filteredAssetIds) {
                $reportAssetIds = collect($report->monitoring_data)->pluck('asset_id');
                return $reportAssetIds->intersect($filteredAssetIds)->isNotEmpty();
            });
        }
        
        return view('monitoring.index', compact('monitoringReports', 'assets', 'locations'));
    }

    public function verify(Request $request)
    {
        $query = AssetMonitoring::with('user');
        
        $monitoringReports = $query->orderBy('tanggal_laporan', 'desc')->paginate(15);
        
        // Get all assets for reference
        $assets = Asset::all();
        
        // Get unique locations for filter
        $locations = Asset::distinct()->whereNotNull('lokasi')->pluck('lokasi')->filter();
        
        // Filter by location if specified
        if ($request->has('lokasi') && $request->lokasi) {
            $filteredAssetIds = Asset::where('lokasi', $request->lokasi)->pluck('asset_id');
            $monitoringReports = $monitoringReports->filter(function ($report) use ($filteredAssetIds) {
                $reportAssetIds = collect($report->monitoring_data)->pluck('asset_id');
                return $reportAssetIds->intersect($filteredAssetIds)->isNotEmpty();
            });
        }
        
        return view('monitoring.verify.verify', compact('monitoringReports', 'assets', 'locations'));
    }

    // Optional: Method to view specific monitoring report
    public function show($id)
    {
        $monitoring = AssetMonitoring::with('user')->findOrFail($id);
        $assets = $monitoring->getMonitoredAssets();

        return view('monitoring.show', compact('monitoring', 'assets'));
    }
}