<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\DamagedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;

class FixVerificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DamagedAsset::with('asset');
        
        if ($user->hasRole(['staff_laboratorium'])) {
            // Show only lab assets reported by assistants that need validation
            $query->where(function($q) {
                $q->where(function($subQ) {
                    $subQ->where('verified', 'No')
                         ->where('reporter_role', 'asisten');
                })
                ->orWhere(function($subQ) {
                    $subQ->where('verified', 'Yes')
                         ->where('reporter_role', 'asisten')
                         ->whereIn('status', ['Ditolak', 'Menunggu Persetujuan Kaur']);
                });
            });
        } elseif ($user->hasRole(['staff_logistik'])) {
            // Show only logistic assets reported by dosen, mahasiswa, or staff that need validation
            $query->where(function($q) {
                $q->where(function($subQ) {
                    $subQ->where('verified', 'No')
                         ->whereIn('reporter_role', ['dosen', 'mahasiswa', 'staff']); // Fixed: use whereIn instead of where
                })
                ->orWhere(function($subQ) {
                    $subQ->where('verified', 'Yes')
                         ->whereIn('reporter_role', ['dosen', 'mahasiswa', 'staff'])
                         ->whereIn('status', ['Ditolak', 'Menunggu Persetujuan Kaur']);
                });
            });
        } else {
            // For other roles, show only unvalidated records
            $query->where('validated', 'No');
        }
        
        // Apply filters if they exist
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
            });
        }
        
        // Make sure to use paginate() to get a LengthAwarePaginator instance
        $damagedAssets = $query->orderBy('tanggal_pelaporan', 'desc')->paginate(10);
        
        // Get locations for filter dropdown
        $status = DamagedAsset::distinct()->pluck('status');
        $locations = Asset::distinct()->pluck('lokasi');
        
        return view('verifikasi-laporan-perbaikan.index', compact('damagedAssets', 'status', 'locations'));
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        
        $query = DamagedAsset::with('asset');
        
        $query->where('verified', 'Yes')
              ->whereIn('status', ['Diterima', 'Ditolak']);
              
        if ($user->hasRole(['staff_laboratorium'])) {
            $query->where('reporter_role', 'asisten');
        } elseif ($user->hasRole(['staff_logistik'])) {
            $query->whereIn('reporter_role', ['dosen', 'mahasiswa', 'staff']); // Fixed: use whereIn instead of where
        }
        
        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        // Apply location filter
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
            });
        }
        
        // Order by verification date (most recent first)
        $damagedAssets = $query->orderBy('verified_at', 'desc')->paginate(15);
        
        // Get filter options
        $status = ['Diterima', 'Ditolak']; // Only show relevant statuses
        $locations = Asset::distinct()->pluck('lokasi');
        
        return view('verifikasi-laporan-perbaikan.history', compact('damagedAssets', 'status', 'locations'));
    }

    public function create($damage_id)
    {
        $tingkat_kerusakan = ['Ringan', 'Sedang', 'Berat'];
        $petugas = ['Vendor', 'Staf'];

        $damagedAsset = DamagedAsset::where('damage_id', $damage_id)->firstOrFail();
        
        return view('verifikasi-laporan-perbaikan.create', compact('damagedAsset', 'tingkat_kerusakan', 'petugas'));
    }

    public function show($id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->firstOrFail();
        return view('verifikasi-laporan-perbaikan.show', compact('damagedAsset'));
    }

    public function update(Request $request, $damage_id)
    {
        $request->validate([
            'petugas' => 'required|in:Vendor,Staf',
            'tingkat_kerusakan' => 'required|in:Ringan,Sedang,Berat',
            'estimasi_biaya' => 'required|numeric|min:0',
            'estimasi_waktu_perbaikan' => 'required|date|after_or_equal:today',
            'verified_at' => 'required|date',
            'verified' => 'required|in:Yes,No',
        ], [
            'tingkat_kerusakan.required' => 'Tingkat kerusakan harus dipilih',
            'estimasi_biaya.required' => 'Estimasi biaya harus diisi',
            'estimasi_biaya.numeric' => 'Estimasi biaya harus berupa angka',
            'estimasi_biaya.min' => 'Estimasi biaya tidak boleh kurang dari 0',
            'estimasi_waktu_perbaikan.required' => 'Estimasi waktu perbaikan harus dipilih',
            'estimasi_waktu_perbaikan.date' => 'Format tanggal tidak valid',
            'estimasi_waktu_perbaikan.after_or_equal' => 'Tanggal estimasi tidak boleh kurang dari hari ini',
            'verified_at.required' => 'Tanggal verifikasi harus diisi',
            'verified_at.date' => 'Format tanggal verifikasi tidak valid',
            'verified.required' => 'Status verifikasi harus dipilih',
        ]);
    
        $damagedAsset = DamagedAsset::where('damage_id', $damage_id)->firstOrFail();
    
        // Prepare update data
        $updateData = [
            'petugas' => $request->petugas,
            'tingkat_kerusakan' => $request->tingkat_kerusakan,
            'estimasi_biaya' => $request->estimasi_biaya,
            'estimasi_waktu_perbaikan' => Carbon::parse($request->estimasi_waktu_perbaikan),
            'reviewer' => Auth::user()->name ?? 'System',
            'verified' => $request->verified,
            'verified_at' => Carbon::parse($request->verified_at),
        ];
    
        // Set status based on verification
        if ($request->verified === 'Yes') {
            $updateData['status'] = 'Menunggu Persetujuan Kaur';
        } else {
            $updateData['status'] = 'Ditolak';
        }
    
        // Update the damaged asset
        $damagedAsset->update($updateData);
    
        $message = $request->verified === 'Yes' 
            ? 'Verifikasi berhasil disetujui dan dikirim ke Kaur untuk persetujuan' 
            : 'Laporan berhasil ditolak';
    
        return redirect()->route('fix-verification.index')
            ->with('success', $message);
    }

    public function downloadPdf(Request $request)
    {
        $query = DamagedAsset::with('asset');
        
        // Apply the same filters as index method
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', 'like', '%' . $request->lokasi . '%');
            });
        }
        
        $damagedAssets = $query->orderBy('tanggal_pelaporan', 'desc')->get();
        $filterInfo = [
            'lokasi' => $request->lokasi ?? 'Semua Lokasi',
            'total' => $damagedAssets->count(),
            'generated_at' => \Carbon\Carbon::now()->format('d F Y, H:i')
        ];
        
        $pdf = PDF::loadView('verifikasi-laporan-perbaikan.pdf', compact('damagedAssets', 'filterInfo'));
        
        return $pdf->download('laporan-pemantauan-aset-' . date('Y-m-d') . '.pdf');
    }
}
