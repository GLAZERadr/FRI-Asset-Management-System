<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetMonitoring;
use App\Models\DamagedAsset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Base query
        $query = AssetMonitoring::with('user');
        
        // Role-based filtering with improved logic
        if ($user->hasRole(['kaur_laboratorium', 'staff_laboratorium'])) {
            // Show only lab assets (LAB reports)
            $query->where('id_laporan', 'LIKE', '%-LAB-%');
        } elseif ($user->hasRole(['kaur_keuangan_logistik_sdm', 'staff_logistik'])) {
            // Show only logistic assets (LOG reports) - exclude LAB reports
            $query->where('id_laporan', 'LIKE', '%-LOG-%')
                  ->where('id_laporan', 'NOT LIKE', '%-LAB-%');
        } elseif ($user->hasRole(['wakil_dekan_2'])) {
            // Wakil Dekan 2 sees all reports - no filtering needed here
            // The view will handle the tabbed display
        }
        
        // FIXED: Only restrict logistic staff to their own reports
        // Laboratory staff should see all LAB reports, not just their own
        if ($user->hasRole(['staff_logistik'])) {
            $query->where('user_id', $user->id);
        }
        // Remove the staff_laboratorium restriction to allow them to see all LAB reports
        
        // Apply validation status filter
        if ($request->has('validation_status') && $request->validation_status !== '') {
            $query->where('validated', $request->validation_status);
        }
        
        // Apply existing filters
        if ($request->has('kode_ruangan') && $request->kode_ruangan) {
            $query->where('kode_ruangan', 'like', '%' . $request->kode_ruangan . '%');
        }
        
        if ($request->has('pelapor') && $request->pelapor) {
            $query->where('nama_pelapor', 'like', '%' . $request->pelapor . '%');
        }
        
        if ($request->has('tanggal_laporan') && $request->tanggal_laporan) {
            $query->whereDate('tanggal_laporan', $request->tanggal_laporan);
        }
        
        // Year filter for Wakil Dekan 2
        if ($request->has('year') && $request->year) {
            $query->whereYear('tanggal_laporan', $request->year);
        }
        
        // Prepare data based on user role
        $data = [
            'assets' => Asset::all(),
            'locations' => Asset::distinct()->whereNotNull('lokasi')->pluck('lokasi')->filter(),
        ];
        
        if ($user->hasRole(['wakil_dekan_2'])) {
            // For Wakil Dekan 2, get separate logistik and laboratorium reports
            $logistikQuery = AssetMonitoring::with('user')
                ->where('id_laporan', 'LIKE', '%-LOG-%')
                ->where('id_laporan', 'NOT LIKE', '%-LAB-%');
            
            $laboratoriumQuery = AssetMonitoring::with('user')
                ->where('id_laporan', 'LIKE', '%-LAB-%');
            
            // Apply filters to both queries
            if ($request->has('validation_status') && $request->validation_status !== '') {
                $logistikQuery->where('validated', $request->validation_status);
                $laboratoriumQuery->where('validated', $request->validation_status);
            }
            
            if ($request->has('year') && $request->year) {
                $logistikQuery->whereYear('tanggal_laporan', $request->year);
                $laboratoriumQuery->whereYear('tanggal_laporan', $request->year);
            }
            
            $logistikReports = $logistikQuery->orderBy('tanggal_laporan', 'desc')->get();
            $laboratoriumReports = $laboratoriumQuery->orderBy('tanggal_laporan', 'desc')->get();
            
            // Paginate manually for each type
            $currentPage = $request->get('page', 1);
            $perPage = 10;
            
            $data['logistikReports'] = $this->paginateCollection($logistikReports, $perPage, $currentPage, 'logistik');
            $data['laboratoriumReports'] = $this->paginateCollection($laboratoriumReports, $perPage, $currentPage, 'laboratorium');
            $data['monitoringReports'] = $data['logistikReports']; // Default to logistik
            $data['availableYears'] = $this->getAvailableYears();
            
        } else {
            // For other roles, use standard pagination
            $data['monitoringReports'] = $query->orderBy('tanggal_laporan', 'desc')->paginate(15);
        }
        
        // Apply location filter for all roles (post-query filtering)
        if ($request->has('lokasi') && $request->lokasi && !$user->hasRole(['wakil_dekan_2'])) {
            $filteredAssetIds = Asset::where('lokasi', $request->lokasi)->pluck('asset_id');
            $data['monitoringReports'] = $data['monitoringReports']->filter(function ($report) use ($filteredAssetIds) {
                if (!$report->monitoring_data) {
                    return false;
                }
                $reportAssetIds = collect($report->monitoring_data)->pluck('asset_id');
                return $reportAssetIds->intersect($filteredAssetIds)->isNotEmpty();
            });
        }
        
        return view('monitoring.index', $data);
    }
    
    /**
     * Helper method to paginate collections manually
     */
    private function paginateCollection($collection, $perPage, $currentPage, $pageName = 'page')
    {
        $total = $collection->count();
        $offset = ($currentPage - 1) * $perPage;
        $items = $collection->slice($offset, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }
    
    /**
     * Get available years for filtering
     */
    private function getAvailableYears()
    {
        return AssetMonitoring::selectRaw('YEAR(tanggal_laporan) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

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
                try {
                    Log::info('Processing monitoring photo upload', [
                        'file_name' => $assetData['foto']->getClientOriginalName(),
                        'file_size' => $assetData['foto']->getSize(),
                        'file_mime' => $assetData['foto']->getMimeType()
                    ]);
                    
                    // Configure Cloudinary directly (proven to work)
                    Configuration::instance([
                        'cloud' => [
                            'cloud_name' => config('cloudinary.cloud_name'),
                            'api_key' => config('cloudinary.api_key'),
                            'api_secret' => config('cloudinary.api_secret'),
                        ],
                        'url' => [
                            'secure' => true
                        ]
                    ]);
            
                    // Upload to Cloudinary
                    $upload = new UploadApi();
                    $result = $upload->upload($assetData['foto']->getRealPath(), [
                        'folder' => 'monitoring-photos', // Organize in monitoring-photos folder
                        'public_id' => 'monitoring_' . time() . '_' . uniqid(),
                        'quality' => 'auto',
                        'fetch_format' => 'auto',
                        'transformation' => [
                            'width' => 1200,
                            'height' => 1200,
                            'crop' => 'limit',
                            'quality' => 'auto'
                        ]
                    ]);
            
                    $photoPath = $result['secure_url'];
                    
                    Log::info('Monitoring photo uploaded successfully to Cloudinary', [
                        'photo_path' => $photoPath,
                        'cloudinary_public_id' => $result['public_id'] ?? null
                    ]);
            
                } catch (\Exception $e) {
                    Log::error('Cloudinary upload failed for monitoring photo', [
                        'error' => $e->getMessage(),
                        'file_name' => $assetData['foto']->getClientOriginalName()
                    ]);
                    throw new \Exception('Failed to upload monitoring photo: ' . $e->getMessage());
                }
            }
            
            $monitoringData[] = [
                'asset_id' => $assetData['asset_id'],
                'status' => $assetData['status'],
                'verification' => 'not_verified',
                'deskripsi' => $assetData['deskripsi'] ?? null,
                'foto_path' => $photoPath,
                'id_laporan' => $idLaporan // Add report ID to each asset data
            ];
        }
    
        // Create monitoring record
        $monitoring = AssetMonitoring::create([
            'id_laporan' => $idLaporan,
            'kode_ruangan' => $request->kode_ruangan,
            'nama_pelapor' => $request->nama_pelapor,
            'tanggal_laporan' => $request->tanggal_laporan,
            'monitoring_data' => $monitoringData,
            'reviewer' => auth()->user()->name,
            'validated' => 'not_validated',
            'validated_at' => null,
            'user_id' => auth()->id()
        ]);
    
        return redirect()->route('dashboard')->with('success', 
            "Monitoring report submitted successfully! Report ID: {$idLaporan}" . 
            (count($damagedAssets) > 0 ? ' ' . count($damagedAssets) . ' damaged assets have been reported.' : ''));
    }

    public function verify(Request $request)
    {
        $query = AssetMonitoring::with('user');
        
        // Get all monitoring reports first
        $allReports = $query->orderBy('tanggal_laporan', 'desc')->get();
        
        // Filter and modify reports to only include not_verified assets (for verification tab)
        $notVerifiedReports = $allReports->map(function ($report) {
            $monitoringData = $report->monitoring_data;
            
            // Filter monitoring_data to only include not_verified assets
            $notVerifiedAssets = array_filter($monitoringData, function ($assetData) {
                return isset($assetData['verification']) && $assetData['verification'] === 'not_verified';
            });
            
            // If there are not_verified assets, update the report's monitoring_data
            if (!empty($notVerifiedAssets)) {
                $report->monitoring_data = array_values($notVerifiedAssets); // Re-index array
                return $report;
            }
            
            return null; // No not_verified assets in this report
        })->filter(); // Remove null reports
        
        // Filter and modify reports to only include verified assets (for history tab)
        $verifiedReports = $allReports->map(function ($report) {
            $monitoringData = $report->monitoring_data;
            
            // Filter monitoring_data to only include verified assets
            $verifiedAssets = array_filter($monitoringData, function ($assetData) {
                return isset($assetData['verification']) && $assetData['verification'] === 'verified';
            });
            
            // If there are verified assets, update the report's monitoring_data
            if (!empty($verifiedAssets)) {
                $report->monitoring_data = array_values($verifiedAssets); // Re-index array
                return $report;
            }
            
            return null; // No verified assets in this report
        })->filter(); // Remove null reports
        
        // Apply location filter if specified
        if ($request->has('lokasi') && $request->lokasi) {
            $filteredAssetIds = Asset::where('lokasi', $request->lokasi)->pluck('asset_id');
            
            // Filter not verified reports by location
            $notVerifiedReports = $notVerifiedReports->map(function ($report) use ($filteredAssetIds) {
                $monitoringData = $report->monitoring_data;
                
                $locationFilteredAssets = array_filter($monitoringData, function ($assetData) use ($filteredAssetIds) {
                    return $filteredAssetIds->contains($assetData['asset_id']) && 
                           isset($assetData['verification']) && 
                           $assetData['verification'] === 'not_verified';
                });
                
                if (!empty($locationFilteredAssets)) {
                    $report->monitoring_data = array_values($locationFilteredAssets);
                    return $report;
                }
                
                return null;
            })->filter();
            
            // Filter verified reports by location
            $verifiedReports = $verifiedReports->map(function ($report) use ($filteredAssetIds) {
                $monitoringData = $report->monitoring_data;
                
                $locationFilteredAssets = array_filter($monitoringData, function ($assetData) use ($filteredAssetIds) {
                    return $filteredAssetIds->contains($assetData['asset_id']) && 
                           isset($assetData['verification']) && 
                           $assetData['verification'] === 'verified';
                });
                
                if (!empty($locationFilteredAssets)) {
                    $report->monitoring_data = array_values($locationFilteredAssets);
                    return $report;
                }
                
                return null;
            })->filter();
        }
        
        // Convert to paginated collections
        $page = $request->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        
        // Paginate not verified reports (default for verification tab)
        $paginatedNotVerified = $notVerifiedReports->slice($offset, $perPage);
        $monitoringReports = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedNotVerified,
            $notVerifiedReports->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'page'
            ]
        );
        
        // Paginate verified reports (for history tab)
        $paginatedVerified = $verifiedReports->slice($offset, $perPage);
        $verifiedMonitoringReports = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedVerified,
            $verifiedReports->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'page'
            ]
        );
        
        // Get all assets for reference
        $assets = Asset::all();
        
        // Get unique locations for filter
        $locations = Asset::distinct()->whereNotNull('lokasi')->pluck('lokasi')->filter();
        
        // Append query parameters to pagination links
        if ($request->has('lokasi')) {
            $monitoringReports->appends(['lokasi' => $request->lokasi]);
            $verifiedMonitoringReports->appends(['lokasi' => $request->lokasi]);
        }
        
        return view('monitoring.verify.verify', compact(
            'monitoringReports', 
            'verifiedMonitoringReports', 
            'assets', 
            'locations'
        ));
    }

    public function verifying($id_laporan, $asset_id)
    {
        $report = AssetMonitoring::where('id_laporan', $id_laporan)->firstOrFail();
        
        // Find the specific asset data in monitoring_data
        $assetMonitoringData = collect($report->monitoring_data)->firstWhere('asset_id', $asset_id);
        
        if (!$assetMonitoringData) {
            return redirect()->route('pemantauan.monitoring.verify')
                           ->with('error', 'Asset not found in this monitoring report.');
        }
        
        // Get asset details
        $asset = Asset::where('asset_id', $asset_id)->first();
        
        return view('monitoring.verify.show', compact('report', 'asset', 'assetMonitoringData'));
    }
    
    public function updateVerification(Request $request, $id_laporan, $asset_id)
    {
        $request->validate([
            'verification_status' => 'required|in:verified,not_verified'
        ]);
        
        $report = AssetMonitoring::where('id_laporan', $id_laporan)->firstOrFail();
        
        // Generate verification ID if status is verified
        $verificationId = null;
        $verificationDate = null;
        $verifierName = null;
        
        if ($request->verification_status === 'verified') {
            // Generate verification ID with format VER-YEAR-NO
            $currentYear = date('Y');
            
            // Get the latest verification number for this year
            $latestVerification = AssetMonitoring::where('monitoring_data', 'like', '%verification_id%')
                ->where('monitoring_data', 'like', "%VER-{$currentYear}-%")
                ->get()
                ->flatMap(function ($report) {
                    return collect($report->monitoring_data)->filter(function ($data) {
                        return isset($data['verification_id']) && $data['verification_id'];
                    });
                })
                ->sortByDesc('verification_id')
                ->first();
            
            $nextNumber = 1;
            if ($latestVerification && isset($latestVerification['verification_id'])) {
                // Extract number from VER-YYYY-XXX format
                $parts = explode('-', $latestVerification['verification_id']);
                if (count($parts) === 3) {
                    $nextNumber = intval($parts[2]) + 1;
                }
            }
            
            $verificationId = 'VER-' . $currentYear . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $verificationDate = now()->format('Y-m-d H:i:s');
            $verifierName = auth()->user()->name;
        }
        
        // Update monitoring data with verification status for specific asset
        $monitoringData = $report->monitoring_data;
        
        foreach ($monitoringData as $index => $data) {
            if ($data['asset_id'] === $asset_id) {
                $monitoringData[$index]['verification'] = $request->verification_status;
                
                if ($request->verification_status === 'verified') {
                    $monitoringData[$index]['verification_id'] = $verificationId;
                    $monitoringData[$index]['verification_date'] = $verificationDate;
                    $monitoringData[$index]['verifier_name'] = $verifierName;
                } else {
                    // Remove verification data if status is not_verified
                    unset($monitoringData[$index]['verification_id']);
                    unset($monitoringData[$index]['verification_date']);
                    unset($monitoringData[$index]['verifier_name']);
                }
                break;
            }
        }
        
        // Update the report
        $report->update([
            'monitoring_data' => $monitoringData
        ]);
        
        $statusText = $request->verification_status === 'verified' ? 'verified' : 'rejected';
        $message = "Asset {$asset_id} has been {$statusText} successfully.";
        
        if ($request->verification_status === 'verified') {
            $message .= " Verification ID: {$verificationId}";
        }
        
        return redirect()->route('pemantauan.monitoring.show', $id_laporan)
            ->with('success', $message);
    }

    // Optional: Method to view specific monitoring report
    public function show($id_laporan)
    {
        // Find by id_laporan instead of primary key id
        $monitoring = AssetMonitoring::with('user')
            ->where('id_laporan', $id_laporan)
            ->firstOrFail();
            
        $assets = $monitoring->getMonitoredAssets();
    
        return view('monitoring.show', compact('monitoring', 'assets'));
    }

    public function printLaporan(Request $request)
    {
        // Get all the same data that your main monitoring page uses
        $user = auth()->user();
        
        // Apply filters if any
        $locationFilter = $request->get('lokasi');
        $yearFilter = $request->get('year');
        
        // Get monitoring reports with filters applied
        $monitoringReportsQuery = AssetMonitoring::with(['user'])
            ->orderBy('tanggal_laporan', 'desc');
        
        if ($locationFilter) {
            $monitoringReportsQuery->where('kode_ruangan', $locationFilter);
        }
        
        if ($yearFilter) {
            $monitoringReportsQuery->whereYear('tanggal_laporan', $yearFilter);
        }
        
        $monitoringReports = $monitoringReportsQuery->get();
        
        // Get all assets
        $assets = Asset::all();
        
        // Get available locations for filter
        $locations = Asset::distinct('lokasi')
            ->whereNotNull('lokasi')
            ->pluck('lokasi')
            ->filter()
            ->sort()
            ->values();
        
        // Return the PDF view
        return view('monitoring.pdf', compact(
            'monitoringReports',
            'assets',
            'locations'
        ));
    }

    /**
     * Delete entire monitoring report from verification history
     */
    public function destroyVerificationReport($id_laporan)
    {
        try {
            // Find the monitoring report by id_laporan instead of primary key id
            $monitoringReport = AssetMonitoring::where('id_laporan', $id_laporan)->firstOrFail();
            
            // Store report info for success message
            $reportDate = $monitoringReport->tanggal_laporan;
            $reportLocation = $monitoringReport->kode_ruangan;
            
            // Delete the monitoring report
            $monitoringReport->delete();
            
            return redirect()->route('pemantauan.monitoring.index')
                ->with('success', "Laporan verifikasi {$id_laporan} tanggal {$reportDate} untuk ruangan {$reportLocation} berhasil dihapus.");
                
        } catch (\Exception $e) {
            \Log::error('Error deleting verification report: ' . $e->getMessage());
            
            return redirect()->route('pemantauan.monitoring.index')
                ->with('error', 'Gagal menghapus laporan verifikasi. Silakan coba lagi.');
        }
    }

    /**
     * Delete specific asset verification from a monitoring report
     */
    public function destroyAssetVerification($reportId, $assetId)
    {
        try {
            // Find the monitoring report
            $monitoringReport = AssetMonitoring::findOrFail($reportId);
            
            // Get current monitoring data
            $monitoringData = $monitoringReport->monitoring_data;
            
            // Find and remove the specific asset from monitoring data
            $updatedData = array_filter($monitoringData, function($assetData) use ($assetId) {
                return $assetData['asset_id'] !== $assetId;
            });
            
            // If no assets left in the report, delete the entire report
            if (empty($updatedData)) {
                $monitoringReport->delete();
                return redirect()->route('pemantauan.monitoring.verify')
                    ->with('success', 'Asset terakhir dihapus. Laporan monitoring telah dihapus.');
            }
            
            // Update the monitoring report with remaining assets
            $monitoringReport->monitoring_data = array_values($updatedData); // Re-index array
            $monitoringReport->save();
            
            return redirect()->route('pemantauan.monitoring.verify')
                ->with('success', 'Verifikasi asset berhasil dihapus dari laporan.');
                
        } catch (\Exception $e) {
            \Log::error('Error deleting asset verification: ' . $e->getMessage());
            
            return redirect()->route('pemantauan.monitoring.verify')
                ->with('error', 'Gagal menghapus verifikasi asset. Silakan coba lagi.');
        }
    }

    /**
     * Bulk delete verified reports (for cleaning up history)
     */
    public function bulkDeleteVerified(Request $request)
    {
        try {
            $reportIds = $request->input('report_ids', []);
            
            if (empty($reportIds)) {
                return redirect()->route('pemantauan.monitoring.verify')
                    ->with('error', 'Tidak ada laporan yang dipilih untuk dihapus.');
            }
            
            // Delete selected reports
            $deletedCount = AssetMonitoring::whereIn('id', $reportIds)->delete();
            
            return redirect()->route('pemantauan.monitoring.verify')
                ->with('success', "{$deletedCount} laporan verifikasi berhasil dihapus.");
                
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting verification reports: ' . $e->getMessage());
            
            return redirect()->route('pemantauan.monitoring.verify')
                ->with('error', 'Gagal menghapus laporan verifikasi. Silakan coba lagi.');
        }
    }
}