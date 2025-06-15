<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DamagedAsset; 
use App\Models\Asset; 
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF; 
use Carbon\Carbon;

class FixValidationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = DamagedAsset::with('asset');
        
        // Apply role-based filtering
        if ($user->hasRole(['kaur_laboratorium'])) {
            // Show only lab assets reported by assistants that need validation
            $query->where('validated', 'No')
                  ->where('reporter_role', 'asisten');
        } elseif ($user->hasRole(['kaur_keuangan_logistik_sdm'])) {
            // Show only logistic assets reported by dosen, mahasiswa, or staff that need validation
            $query->where('validated', 'No')
                  ->whereIn('reporter_role', ['dosen', 'mahasiswa', 'staff']);
        } else {
            // For other roles, show only unvalidated records
            $query->where('validated', 'No');
        }
        
        // Apply location filter if provided
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', 'like', '%' . $request->lokasi . '%');
            });
        }
        
        // Apply month filter for verified_at if provided
        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('verified_at', $request->bulan);
        }
        
        // Apply year filter for verified_at if provided
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('verified_at', $request->tahun);
        }
        
        // Get paginated results
        $reports = $query->orderBy('tanggal_pelaporan', 'desc')->paginate(10);
        
        $locations = Asset::distinct()->pluck('lokasi');
        
        // Get distinct months and years from verified_at
        $months = DamagedAsset::selectRaw('MONTH(verified_at) as month')
            ->whereNotNull('verified_at')
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
            
        $years = DamagedAsset::selectRaw('YEAR(verified_at) as year')
            ->whereNotNull('verified_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');        
        
        return view('perbaikan.validasi.index', compact('reports', 'locations', 'months', 'years'));
    }

    public function show($validation_id)
    {
        $report = DamagedAsset::where('validation_id', $validation_id)->firstOrFail();
        return view('perbaikan.validasi.show', compact('report'));
    }

    public function action($validation_id)
    {
        $report = DamagedAsset::where('validation_id', $validation_id)->firstOrFail();
        return view('perbaikan.validasi.action', compact('report'));
    }
    
    public function update(Request $request, $validation_id)
    {
        $request->validate([
            'tingkat_kerusakan' => 'required|in:Ringan,Sedang,Berat',
            'validated' => 'required|in:Yes,Reject',
            'alasan_penolakan' => 'nullable|string|max:500'
        ]);
    
        $report = DamagedAsset::where('validation_id', $validation_id)->firstOrFail();
        
        // Update each field individually
        $report->tingkat_kerusakan = $request->tingkat_kerusakan;
        $report->validated = $request->validated;
        $report->validated_at = now();
        $report->alasan_penolakan = $request->alasan_penolakan;
        $report->status = $request->validated === 'Yes' ? 'Diterima' : 'Ditolak';
        
        $report->save();
    
        return redirect()->route('perbaikan.validation.index')->with('success', 'Validasi berhasil diperbarui');
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        
        $query = DamagedAsset::with('asset');
        
        // Show only validated records (both accepted and rejected)
        $query->whereIn('validated', ['Yes', 'Reject']);
        
        // Apply role-based filtering
        if ($user->hasRole(['kaur_laboratorium'])) {
            $query->where('reporter_role', 'asisten');
        } elseif ($user->hasRole(['kaur_keuangan_logistik_sdm'])) {
            $query->whereIn('reporter_role', ['dosen', 'mahasiswa', 'staff']);
        }
        
        // Apply location filter if provided
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', 'like', '%' . $request->lokasi . '%');
            });
        }
        
        // Apply month filter for validated_at if provided
        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('validated_at', $request->bulan);
        }
        
        // Apply year filter for validated_at if provided
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('validated_at', $request->tahun);
        }
        
        // Apply status filter if provided
        if ($request->has('status') && $request->status) {
            $query->where('validated', $request->status);
        }
        
        // Get paginated results ordered by validation date (newest first)
        $reports = $query->orderBy('validated_at', 'desc')->paginate(10);
        
        $locations = Asset::distinct()->pluck('lokasi');
        
        // Get distinct months and years from validated_at
        $months = DamagedAsset::selectRaw('MONTH(validated_at) as month')
            ->whereNotNull('validated_at')
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
            
        $years = DamagedAsset::selectRaw('YEAR(validated_at) as year')
            ->whereNotNull('validated_at')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
            
        // Status options for filtering
        $statuses = [
            'Yes' => 'Diterima',
            'Reject' => 'Ditolak'
        ];
        
        return view('perbaikan.validasi.history', compact('reports', 'locations', 'months', 'years', 'statuses'));
    }

    public function downloadPdf($validation_id)
    {
        $report = DamagedAsset::with('asset')->where('validation_id', $validation_id)->firstOrFail();
        
        $pdf = PDF::loadView('perbaikan.validasi.pdf', compact('report'));
        
        return $pdf->download('laporan-validasi-' . $validation_id . '.pdf');
    }
}
