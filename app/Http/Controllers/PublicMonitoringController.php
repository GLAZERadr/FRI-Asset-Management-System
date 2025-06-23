<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Asset;
use App\Models\AssetMonitoring;
use App\Models\DamagedAsset;

class PublicMonitoringController extends Controller
{
    /**
     * Public QR Scanner Landing Page
     */
    public function index()
    {
        return view('public.scan');
    }
    
    /**
     * Process QR code for public access
     */
    public function processQR(Request $request)
    {
        try {
            $request->validate([
                'qr_data' => 'required|string'
            ]);
    
            $qrData = trim($request->input('qr_data'));
            
            // Log the QR scan attempt (no user_id for public access)
            \Log::info('Public QR Code scanned', ['qr_data' => $qrData, 'ip' => $request->ip()]);
    
            // Check if QR data is a kode_ruangan (room code)
            $roomAssets = Asset::where('kode_ruangan', $qrData)->get();
            
            if ($roomAssets->isNotEmpty()) {
                // QR code contains a room code - redirect to public monitoring form
                \Log::info('Room code found via public QR scan', [
                    'kode_ruangan' => $qrData,
                    'asset_count' => $roomAssets->count(),
                    'ip' => $request->ip()
                ]);
    
                return response()->json([
                    'success' => true,
                    'type' => 'room_monitoring',
                    'kode_ruangan' => $qrData,
                    'asset_count' => $roomAssets->count(),
                    'redirect_url' => route('public.monitoring.form', ['kodeRuangan' => $qrData]),
                    'message' => "Ruangan ditemukan! {$roomAssets->count()} asset akan dimonitoring."
                ]);
            }
    
            // Try to find individual asset
            $asset = $this->findAssetByQRData($qrData);
    
            if ($asset) {
                // Individual asset found - redirect to room monitoring for that asset's room
                \Log::info('Asset found via public QR scan', [
                    'asset_id' => $asset->asset_id,
                    'kode_ruangan' => $asset->kode_ruangan,
                    'ip' => $request->ip()
                ]);
    
                // For public access, always redirect to room monitoring
                return response()->json([
                    'success' => true,
                    'type' => 'asset_found',
                    'asset_id' => $asset->asset_id,
                    'asset_name' => $asset->nama_asset,
                    'kode_ruangan' => $asset->kode_ruangan,
                    'redirect_url' => route('public.monitoring.form', ['kodeRuangan' => $asset->kode_ruangan]),
                    'message' => "Asset '{$asset->nama_asset}' ditemukan! Mengarahkan ke monitoring ruangan {$asset->kode_ruangan}."
                ]);
            }
    
            // Nothing found
            \Log::warning('No asset or room found for public QR data', ['qr_data' => $qrData, 'ip' => $request->ip()]);
            
            return response()->json([
                'success' => false,
                'message' => 'QR code tidak dikenali. Pastikan QR code valid untuk asset atau ruangan.',
                'qr_data' => $qrData
            ], 404);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data QR code tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Public QR processing error', [
                'error' => $e->getMessage(),
                'qr_data' => $request->input('qr_data'),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses QR code. Silakan coba lagi.'
            ], 500);
        }
    }
    
    /**
     * Find asset by various QR data formats
     */
    private function findAssetByQRData($qrData)
    {
        // Try exact match with asset_id
        $asset = Asset::where('asset_id', $qrData)->first();
        if ($asset) return $asset;
    
        // Try exact match with kode_ruangan  
        $asset = Asset::where('kode_ruangan', $qrData)->first();
        if ($asset) return $asset;
    
        // Try case-insensitive search
        $asset = Asset::whereRaw('LOWER(asset_id) = ?', [strtolower($qrData)])
                     ->orWhereRaw('LOWER(kode_ruangan) = ?', [strtolower($qrData)])
                     ->first();
        if ($asset) return $asset;
    
        // Try to extract patterns from QR data
        $patterns = [
            '/([A-Z]\d+-[A-Z]{3}-\d{3})/i',    // T0901-FUR-001 format
            '/([A-Z]+-\d+)/i',                  // TULT-0901 format
            '/asset[_-]?([A-Z0-9-]+)/i',       // asset_T0901-FUR-001
            '/room[_-]?([A-Z0-9-]+)/i',        // room_TULT-0901
            '/([A-Z]{4}-\d{4})/i',             // TULT-0901 pattern
        ];
    
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $qrData, $matches)) {
                $extractedValue = $matches[1];
                
                // Try as asset_id
                $asset = Asset::where('asset_id', $extractedValue)->first();
                if ($asset) return $asset;
                
                // Try as kode_ruangan
                $asset = Asset::where('kode_ruangan', $extractedValue)->first();
                if ($asset) return $asset;
            }
        }
    
        // If QR data looks like JSON, try to parse it
        if (str_starts_with($qrData, '{') && str_ends_with($qrData, '}')) {
            try {
                $qrJson = json_decode($qrData, true);
                if (isset($qrJson['asset_id'])) {
                    return Asset::where('asset_id', $qrJson['asset_id'])->first();
                }
                if (isset($qrJson['kode_ruangan'])) {
                    return Asset::where('kode_ruangan', $qrJson['kode_ruangan'])->first();
                }
            } catch (\Exception $e) {
                // Invalid JSON, continue
            }
        }
    
        return null;
    }
    
    /**
     * Show public monitoring form
     */
    public function showMonitoring($kodeRuangan)
    {
        try {
            // Add debugging
            \Log::info('showMonitoring called', [
                'kodeRuangan' => $kodeRuangan,
                'url' => request()->url()
            ]);
            
            // Get assets for this room
            $assets = Asset::where('kode_ruangan', $kodeRuangan)
                          ->orderBy('asset_id')
                          ->get();
            
            \Log::info('Assets found', [
                'kodeRuangan' => $kodeRuangan,
                'asset_count' => $assets->count(),
                'assets' => $assets->pluck('asset_id')->toArray()
            ]);
            
            if ($assets->isEmpty()) {
                // Try case-insensitive search
                $assets = Asset::whereRaw('LOWER(kode_ruangan) = ?', [strtolower($kodeRuangan)])
                              ->orderBy('asset_id')
                              ->get();
                              
                \Log::info('Case-insensitive search results', [
                    'kodeRuangan' => $kodeRuangan,
                    'asset_count' => $assets->count()
                ]);
                
                if ($assets->isEmpty()) {
                    \Log::warning('No assets found for room', ['kodeRuangan' => $kodeRuangan]);
                    
                    return redirect()->route('public.index')
                        ->with('error', "Ruangan {$kodeRuangan} tidak ditemukan atau tidak memiliki aset");
                }
            }
            
            \Log::info('Rendering monitoring form', [
                'kodeRuangan' => $kodeRuangan,
                'asset_count' => $assets->count()
            ]);
            
            return view('public.form', compact('assets', 'kodeRuangan'));
            
        } catch (\Exception $e) {
            \Log::error('Public monitoring form error: ' . $e->getMessage(), [
                'kodeRuangan' => $kodeRuangan,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('public.index')
                ->with('error', 'Terjadi kesalahan saat memuat form monitoring: ' . $e->getMessage());
        }
    }
    
    /**
     * Store public monitoring report
     */
    public function storeMonitoring(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'kode_ruangan' => 'required|string',
                'nama_pelapor' => 'required|string|max:255',
                'tanggal_laporan' => 'required|date',
                'asset_data' => 'required|array',
                'asset_data.*.asset_id' => 'required|string',
                'asset_data.*.status' => 'required|in:baik,butuh_perawatan',
                'asset_data.*.deskripsi' => 'nullable|string',
                'asset_data.*.foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            // Generate unique report ID for public access
            $idLaporan = AssetMonitoring::generateIdLaporanCS();

            // Handle file uploads and prepare monitoring data
            $monitoringData = [];
            $damagedAssets = [];
            
            foreach ($request->asset_data as $key => $assetData) {
                $photoPath = null;
                if (isset($assetData['foto']) && $assetData['foto']) {
                    $photoPath = $assetData['foto']->store('monitoring-photos/public', 'public');
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
            }
        
            // Create monitoring record
            $monitoring = AssetMonitoring::create([
                'id_laporan' => $idLaporan,
                'kode_ruangan' => $request->kode_ruangan,
                'nama_pelapor' => $request->nama_pelapor,
                'tanggal_laporan' => $request->tanggal_laporan,
                'monitoring_data' => $monitoringData,
                'reviewer' => 'Customer Service',
                'validated' => 'not_validated',
                'validated_at' => null,
                'user_id' => null
            ]);

            // Create damaged asset records for assets that need maintenance
            foreach ($damagedAssets as $damagedData) {
                DamagedAsset::create($damagedData);
            }
            
            return redirect()->route('public.monitoring.success', ['id' => $monitoring->id])
                ->with('success', 'Laporan monitoring berhasil dikirim!');
                
        } catch (\Exception $e) {
            \Log::error('Public monitoring store error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Terjadi kesalahan saat menyimpan laporan')
                        ->withInput();
        }
    }
    
    /**
     * Show monitoring success page
     */
    public function monitoringSuccess($id)
    {
        try {
            // Find the monitoring report by ID (for AssetMonitoring model)
            $laporan = AssetMonitoring::where('id', $id)
                                    ->whereNull('user_id') // Public reports have null user_id
                                    ->firstOrFail();
            
            // Get monitored assets details
            $monitoredAssets = collect($laporan->monitoring_data)->map(function ($data) {
                $asset = Asset::where('asset_id', $data['asset_id'])->first();
                return [
                    'asset_id' => $data['asset_id'],
                    'asset_name' => $asset ? $asset->nama_asset : 'Asset Not Found',
                    'status' => $data['status'],
                    'deskripsi' => $data['deskripsi'] ?? null,
                    'foto_path' => $data['foto_path'] ?? null,
                ];
            });
            
            return view('public.success', compact('laporan', 'monitoredAssets'));
            
        } catch (\Exception $e) {
            \Log::error('Public monitoring success error: ' . $e->getMessage());
            
            return redirect()->route('public.index')
                ->with('error', 'Laporan tidak ditemukan');
        }
    }
}