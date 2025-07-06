<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\MaintenanceAsset;
use App\Models\Asset;
use App\Models\DamagedAsset;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF; 
use Carbon\Carbon;
use App\Services\NotificationService;

class FixStatusController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MaintenanceAsset::with(['asset', 'damagedAsset']);
        
        if ($user->hasRole(['staff_laboratorium'])) {
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'LIKE', '%Laboratorium%');
            });
        } elseif ($user->hasRole(['staff_logistik'])) {
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
            });
        }
        
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
            });
        }
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $maintenanceRequests = $query->orderBy('tanggal_pengajuan', 'desc')->paginate(10);
        
        $locations = Asset::distinct()->pluck('lokasi')->filter();
        
        return view('perbaikan.status.index', compact('maintenanceRequests', 'locations'));
    }

    public function show($maintenance_id)
    {
        // Find maintenance asset by maintenance_id
        $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset'])
                                          ->where('maintenance_id', $maintenance_id)
                                          ->firstOrFail();
        
        // Calculate maintenance count for the same asset
        $maintenanceCount = MaintenanceAsset::whereHas('damagedAsset', function($query) use ($maintenanceAsset) {
            $query->where('asset_id', $maintenanceAsset->damagedAsset->asset_id);
        })->count();
        
        // Calculate total cost for the same asset
        $totalCost = DamagedAsset::whereHas('maintenanceAsset')
                                ->where('asset_id', $maintenanceAsset->damagedAsset->asset_id)
                                ->sum('estimasi_biaya');
        
        return view('perbaikan.status.show', compact('maintenanceAsset', 'maintenanceCount', 'totalCost'));
    }

    public function showDone($maintenance_id)
    {
        // Find maintenance asset by maintenance_id
        $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset'])
                                          ->where('maintenance_id', $maintenance_id)
                                          ->firstOrFail();
        
        // Calculate maintenance count for the same asset
        $maintenanceCount = MaintenanceAsset::whereHas('damagedAsset', function($query) use ($maintenanceAsset) {
            $query->where('asset_id', $maintenanceAsset->damagedAsset->asset_id);
        })->count();
        
        // Calculate total cost for the same asset
        $totalCost = DamagedAsset::whereHas('maintenanceAsset')
                                ->where('asset_id', $maintenanceAsset->damagedAsset->asset_id)
                                ->sum('estimasi_biaya');
        
        return view('perbaikan.status.show-done', compact('maintenanceAsset', 'maintenanceCount', 'totalCost'));
    }

    public function update(Request $request, $maintenance_id)
    {
        // Validate the request
        $request->validate([
            'penyebab_kerusakan' => 'required|string|min:10',
            'deskripsi_perbaikan' => 'required|string|min:10',
            'hasil_perbaikan' => 'required|in:Sukses,Perlu Tindak Lanjut',
            'photos' => 'nullable|array',
        ], [
            'penyebab_kerusakan.required' => 'Penyebab kerusakan harus diisi',
            'penyebab_kerusakan.min' => 'Penyebab kerusakan minimal 10 karakter',
            'deskripsi_perbaikan.required' => 'Deskripsi perbaikan harus diisi',
            'deskripsi_perbaikan.min' => 'Deskripsi perbaikan minimal 10 karakter',
            'hasil_perbaikan.required' => 'Hasil perbaikan harus dipilih',
            'hasil_perbaikan.in' => 'Hasil perbaikan harus berupa Sukses atau Perlu Tindak Lanjut',
        ]);

        // Find the maintenance asset
        $maintenanceAsset = MaintenanceAsset::where('maintenance_id', $maintenance_id)
                                          ->firstOrFail();

        // Prepare update data
        $updateData = [
            'penyebab_kerusakan' => $request->penyebab_kerusakan,
            'deskripsi_perbaikan' => $request->deskripsi_perbaikan,
            'hasil_perbaikan' => $request->hasil_perbaikan,
        ];

        // Update the maintenance asset
        $maintenanceAsset->update($updateData);

        return redirect()->route('perbaikan.status.index')
                        ->with('success', 'Laporan akhir perbaikan berhasil disimpan.');
    }

    public function fixHasDone(Request $request)
    {
        $user = Auth::user();
        $query = MaintenanceAsset::with(['asset', 'damagedAsset']);

        $query->where('status', 'Selesai');
        
        if ($user->hasRole(['kaur_laboratorium'])) {
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'LIKE', '%Laboratorium%');
            });
        } elseif ($user->hasRole(['kaur_keuangan_logistik_sdm'])) {
            $query->whereHas('asset', function($q) {
                $q->where('lokasi', 'NOT LIKE', '%Laboratorium%');
            });
        }
        
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
            });
        }

        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('verified_at', $request->bulan);
        }
        
        // Apply year filter for verified_at if provided
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('verified_at', $request->tahun);
        }

        $months = MaintenanceAsset::selectRaw('MONTH(tanggal_selesai) as month')
            ->whereNotNull('tanggal_selesai')
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->mapWithKeys(function($month) {
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                return [$month => $monthNames[$month]];
            });
            
        $years = MaintenanceAsset::selectRaw('YEAR(tanggal_selesai) as year')
            ->whereNotNull('tanggal_selesai')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');    
        
        $maintenanceRequests = $query->orderBy('tanggal_pengajuan', 'desc')->paginate(10);
        
        $locations = Asset::distinct()->pluck('lokasi')->filter();
        
        return view('perbaikan.status.done', compact('maintenanceRequests', 'locations', 'months', 'years'));
    }

    public function showRecommendation($maintenance_id)
    {
        // Find maintenance asset by maintenance_id
        $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset'])
                                          ->where('maintenance_id', $maintenance_id)
                                          ->firstOrFail();
        
        // Calculate maintenance count for the same asset
        $maintenanceCount = MaintenanceAsset::whereHas('damagedAsset', function($query) use ($maintenanceAsset) {
            $query->where('asset_id', $maintenanceAsset->damagedAsset->asset_id);
        })->count();
        
        // Calculate total cost for the same asset
        $totalCost = DamagedAsset::whereHas('maintenanceAsset')
                                ->where('asset_id', $maintenanceAsset->damagedAsset->asset_id)
                                ->sum('estimasi_biaya');
        
        return view('perbaikan.status.rekomendasi', compact('maintenanceAsset', 'maintenanceCount', 'totalCost'));
    }

    public function updateRecommendation(Request $request, $maintenance_id)
    {
        // Validate the request
        $request->validate([
            'rekomendasi' => 'nullable|string',
        ]);

        $maintenanceAsset = MaintenanceAsset::where('maintenance_id', $maintenance_id)
                                          ->firstOrFail();

        // Prepare update data
        $updateData = [
            'rekomendasi' => $request->rekomendasi,
        ];

        // Update the maintenance asset
        $maintenanceAsset->update($updateData);

        return redirect()->route('perbaikan.status.done')
                        ->with('success', 'Laporan akhir perbaikan berhasil disimpan.');
    }

    public function downloadPdf($maintenance_id)
    {
        // Find maintenance asset by maintenance_id
        $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset'])
                                          ->where('maintenance_id', $maintenance_id)
                                          ->firstOrFail();
        
        // Calculate maintenance count for the same asset
        $maintenanceCount = MaintenanceAsset::whereHas('damagedAsset', function($query) use ($maintenanceAsset) {
            $query->where('asset_id', $maintenanceAsset->damagedAsset->asset_id);
        })->count();
        
        // Calculate total cost for the same asset
        $totalCost = DamagedAsset::whereHas('maintenanceAsset')
                                ->where('asset_id', $maintenanceAsset->damagedAsset->asset_id)
                                ->sum('estimasi_biaya');
        
        // Generate PDF
        $pdf = PDF::loadView('perbaikan.status.pdf', compact('maintenanceAsset', 'maintenanceCount', 'totalCost'));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename
        $filename = 'laporan-perbaikan-' . $maintenanceAsset->maintenance_id . '.pdf';
        
        return $pdf->download($filename);
    }

    public function report(Request $request)
    {
        $user = Auth::user();
        $query = MaintenanceAsset::with(['asset', 'damagedAsset']);

        $query->whereNotNull('rekomendasi');;
        
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
            });
        }

        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('verified_at', $request->bulan);
        }
        
        // Apply year filter for verified_at if provided
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('verified_at', $request->tahun);
        }

        $months = MaintenanceAsset::selectRaw('MONTH(tanggal_selesai) as month')
            ->whereNotNull('tanggal_selesai')
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->mapWithKeys(function($month) {
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                return [$month => $monthNames[$month]];
            });
            
        $years = MaintenanceAsset::selectRaw('YEAR(tanggal_selesai) as year')
            ->whereNotNull('tanggal_selesai')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');    
        
        $maintenanceRequests = $query->orderBy('tanggal_pengajuan', 'desc')->paginate(10);
        
        $locations = Asset::distinct()->pluck('lokasi')->filter();
        
        return view('perbaikan.status.report', compact('maintenanceRequests', 'locations', 'months', 'years'));
    }

    public function showReport($maintenance_id)
    {
        // Find maintenance asset by maintenance_id
        $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset'])
        ->where('maintenance_id', $maintenance_id)
        ->firstOrFail();

        // Calculate maintenance count for the same asset
        $maintenanceCount = MaintenanceAsset::whereHas('damagedAsset', function($query) use ($maintenanceAsset) {
        $query->where('asset_id', $maintenanceAsset->damagedAsset->asset_id);
        })->count();

        // Calculate total cost for the same asset
        $totalCost = DamagedAsset::whereHas('maintenanceAsset')
            ->where('asset_id', $maintenanceAsset->damagedAsset->asset_id)
            ->sum('estimasi_biaya');

        return view('perbaikan.status.show-catatan', compact('maintenanceAsset', 'maintenanceCount', 'totalCost'));
    }

    public function updateCatatan(Request $request, $maintenance_id)
    {
        // Validate the request
        $request->validate([
            'catatan' => 'nullable|string',
        ]);
    
        $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset'])
                                          ->where('maintenance_id', $maintenance_id)
                                          ->firstOrFail();
    
        // Check if catatan is actually being updated (not empty)
        $originalCatatan = $maintenanceAsset->catatan;
        $newCatatan = $request->catatan;
        
        // Prepare update data
        $updateData = [
            'catatan' => $newCatatan,
        ];
    
        // Update the maintenance asset
        $maintenanceAsset->update($updateData);
    
        // Send notification to kaur if catatan was added or modified
        if (!empty($newCatatan) && $newCatatan !== $originalCatatan) {
            $notificationService = app(NotificationService::class);
            $notificationService->sendMaintenanceUpdate($maintenanceAsset, 'catatan');
        }
    
        return redirect()->route('perbaikan.status.report')
                        ->with('success', 'Laporan akhir perbaikan berhasil disimpan dan notifikasi telah dikirim.');
    }

    public function downloadReportPdf($maintenance_id)
    {
        // Find maintenance asset by maintenance_id
        $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset'])
                                          ->where('maintenance_id', $maintenance_id)
                                          ->firstOrFail();
        
        // Calculate maintenance count for the same asset
        $maintenanceCount = MaintenanceAsset::whereHas('damagedAsset', function($query) use ($maintenanceAsset) {
            $query->where('asset_id', $maintenanceAsset->damagedAsset->asset_id);
        })->count();
        
        // Calculate total cost for the same asset
        $totalCost = DamagedAsset::whereHas('maintenanceAsset')
                                ->where('asset_id', $maintenanceAsset->damagedAsset->asset_id)
                                ->sum('estimasi_biaya');
        
        // Generate PDF
        $pdf = PDF::loadView('perbaikan.status.pdf-report', compact('maintenanceAsset', 'maintenanceCount', 'totalCost'));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename
        $filename = 'laporan-perbaikan-' . $maintenanceAsset->maintenance_id . '.pdf';
        
        return $pdf->download($filename);
    }
}
