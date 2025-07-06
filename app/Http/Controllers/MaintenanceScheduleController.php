<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\Asset;
use App\Models\DamagedAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class MaintenanceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::with('asset');
        
        // Search by asset ID or name
        if ($request->has('asset_search') && $request->asset_search) {
            $search = $request->asset_search;
            $query->where(function($q) use ($search) {
                $q->where('asset_id', 'like', '%' . $search . '%')
                  ->orWhereHas('asset', function($assetQuery) use ($search) {
                      $assetQuery->where('nama_asset', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $schedules = $query->orderBy('tanggal_pemeliharaan', 'desc')->paginate(10);
        
        return view('perbaikan.pemeliharaan-berkala.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|string|exists:assets,asset_id',
            'tanggal_pemeliharaan' => 'required|date|after_or_equal:today',
            'jenis_pemeliharaan' => 'required|in:Rutin,Tambahan,Khusus',
            'alasan_penjadwalan' => 'nullable|string|max:1000',
            'penanggung_jawab' => 'nullable|string|max:255',
            'status' => 'nullable|in:Dijadwalkan,Selesai,Dibatalkan',
            'catatan_tambahan' => 'nullable|string|max:1000'
        ]);

        $scheduleId = MaintenanceSchedule::generateId();

        MaintenanceSchedule::create([
            'schedule_id' => $scheduleId,
            'asset_id' => $request->asset_id,
            'tanggal_pemeliharaan' => $request->tanggal_pemeliharaan,
            'jenis_pemeliharaan' => $request->jenis_pemeliharaan,
            'alasan_penjadwalan' => $request->alasan_penjadwalan,
            'status' => $request->status ?? 'Dijadwalkan',
            'penanggung_jawab' => $request->penanggung_jawab,
            'catatan_tambahan' => $request->catatan_tambahan,
            'auto_generated' => false,
            'created_by' => Auth::user()->name ?? 'System'
        ]);

        return redirect()->route('perbaikan.pemeliharaan-berkala.index')
                        ->with('success', 'Jadwal pemeliharaan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        return response()->json([
            'asset_id' => $schedule->asset_id,
            'tanggal_pemeliharaan' => $schedule->tanggal_pemeliharaan->format('Y-m-d'),
            'jenis_pemeliharaan' => $schedule->jenis_pemeliharaan,
            'alasan_penjadwalan' => $schedule->alasan_penjadwalan,
            'status' => $schedule->status,
            'penanggung_jawab' => $schedule->penanggung_jawab,
            'catatan_tambahan' => $schedule->catatan_tambahan
        ]);
    }

    public function update(Request $request, $id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        $request->validate([
            'asset_id' => 'required|string|exists:assets,asset_id',
            'tanggal_pemeliharaan' => 'required|date',
            'jenis_pemeliharaan' => 'required|in:Rutin,Tambahan,Khusus',
            'alasan_penjadwalan' => 'nullable|string|max:1000',
            'penanggung_jawab' => 'nullable|string|max:255',
            'status' => 'nullable|in:Dijadwalkan,Selesai,Dibatalkan',
            'catatan_tambahan' => 'nullable|string|max:1000'
        ]);

        $schedule->update([
            'asset_id' => $request->asset_id,
            'tanggal_pemeliharaan' => $request->tanggal_pemeliharaan,
            'jenis_pemeliharaan' => $request->jenis_pemeliharaan,
            'alasan_penjadwalan' => $request->alasan_penjadwalan,
            'status' => $request->status,
            'penanggung_jawab' => $request->penanggung_jawab,
            'catatan_tambahan' => $request->catatan_tambahan
        ]);

        return redirect()->route('perbaikan.pemeliharaan-berkala.index')
                        ->with('success', 'Jadwal pemeliharaan berhasil diperbarui');
    }

    public function getDetails($id)
    {
        $schedule = MaintenanceSchedule::with('asset')->findOrFail($id);
        $previousNotes = $schedule->getPreviousMaintenanceNotes();
        
        return response()->json([
            'asset_id' => $schedule->asset_id,
            'asset' => $schedule->asset,
            'tanggal_pemeliharaan_formatted' => $schedule->tanggal_pemeliharaan->format('d-m-Y'),
            'jenis_pemeliharaan' => $schedule->jenis_pemeliharaan,
            'alasan_penjadwalan' => $schedule->alasan_penjadwalan,
            'status' => $schedule->status,
            'penanggung_jawab' => $schedule->penanggung_jawab,
            'catatan_tambahan' => $schedule->catatan_tambahan,
            'auto_generated' => $schedule->auto_generated,
            'previous_notes' => $previousNotes
        ]);
    }

    /**
     * Auto-generate maintenance schedules based on damage frequency
     */
    public function autoGenerate()
    {
        try {
            $generatedCount = MaintenanceSchedule::autoGenerateFromDamageReports();
            
            if ($generatedCount > 0) {
                return redirect()->route('perbaikan.pemeliharaan-berkala.index')
                                ->with('auto_generated_count', $generatedCount)
                                ->with('success', "{$generatedCount} jadwal pemeliharaan otomatis berhasil dibuat");
            } else {
                return redirect()->route('perbaikan.pemeliharaan-berkala.index')
                                ->with('info', 'Tidak ada aset yang memerlukan jadwal pemeliharaan otomatis saat ini');
            }
        } catch (\Exception $e) {
            Log::error('Auto-generate maintenance schedules failed: ' . $e->getMessage());
            return redirect()->route('perbaikan.pemeliharaan-berkala.index')
                            ->with('error', 'Gagal membuat jadwal otomatis: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $schedule->delete();
        
        return redirect()->route('perbaikan.pemeliharaan-berkala.index')
                        ->with('success', 'Jadwal pemeliharaan berhasil dihapus');
    }

    public function report(Request $request)
    {
        $user = Auth::user();
        
        $query = MaintenanceSchedule::with('asset');
        
        // Only show completed schedules for certain roles
        if ($user->hasRole(['wakil_dekan_2'])) {
            $query->where('status', 'Selesai');
        } 
        
        // Search filters
        if ($request->has('asset_search') && $request->asset_search) {
            $search = $request->asset_search;
            $query->where(function($q) use ($search) {
                $q->where('asset_id', 'like', '%' . $search . '%')
                  ->orWhereHas('asset', function($assetQuery) use ($search) {
                      $assetQuery->where('nama_asset', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Apply month filter
        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('tanggal_pemeliharaan', $request->bulan);
        }
        
        // Apply year filter
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('tanggal_pemeliharaan', $request->tahun);
        }
        
        $schedules = $query->orderBy('tanggal_pemeliharaan', 'desc')->paginate(10);
        
        // Get distinct months and years for filters
        $months = MaintenanceSchedule::selectRaw('MONTH(tanggal_pemeliharaan) as month')
            ->whereNotNull('tanggal_pemeliharaan')
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
                
        $years = MaintenanceSchedule::selectRaw('YEAR(tanggal_pemeliharaan) as year')
            ->whereNotNull('tanggal_pemeliharaan')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');        
        
        return view('perbaikan.pemeliharaan-berkala.report', compact('schedules', 'months', 'years'));
    }

    public function showReport($id)
    {
        $schedule = MaintenanceSchedule::with('asset')->findOrFail($id);
        $previousNotes = $schedule->getPreviousMaintenanceNotes();
        
        return view('perbaikan.pemeliharaan-berkala.show-report', compact('schedule', 'previousNotes'));
    }

    public function downloadReportPdf($id)
    {
        $schedule = MaintenanceSchedule::with('asset')->findOrFail($id);
        $previousNotes = $schedule->getPreviousMaintenanceNotes();
        
        $pdf = PDF::loadView('perbaikan.pemeliharaan-berkala.pdf-report', compact('schedule', 'previousNotes'));
        
        $reportId = 'LP-' . date('Ymd', strtotime($schedule->tanggal_pemeliharaan)) . '-' . str_pad($schedule->id, 3, '0', STR_PAD_LEFT);
        
        return $pdf->download('laporan-pemeliharaan-' . $reportId . '.pdf');
    }

    /**
     * Get maintenance statistics for dashboard
     */
    public function getMaintenanceStats()
    {
        $stats = [
            'total_scheduled' => MaintenanceSchedule::scheduled()->count(),
            'completed_this_month' => MaintenanceSchedule::completed()
                ->whereMonth('tanggal_pemeliharaan', now()->month)
                ->whereYear('tanggal_pemeliharaan', now()->year)
                ->count(),
            'auto_generated' => MaintenanceSchedule::autoGenerated()->count(),
            'overdue' => MaintenanceSchedule::scheduled()
                ->where('tanggal_pemeliharaan', '<', now())
                ->count()
        ];
        
        return response()->json($stats);
    }

    /**
     * Check asset maintenance history
     */
    public function getAssetHistory($assetId)
    {
        $history = MaintenanceSchedule::getAssetMaintenanceHistory($assetId);
        $needsMaintenance = MaintenanceSchedule::checkAssetNeedsMaintenance($assetId);
        
        return response()->json([
            'history' => $history,
            'needs_maintenance' => $needsMaintenance,
            'damage_count_last_2_months' => DamagedAsset::where('asset_id', $assetId)
                ->where('tanggal_pelaporan', '>=', now()->subMonths(2))
                ->count()
        ]);
    }

    /**
     * Get assets that need maintenance attention
     */
    public function getAssetsNeedingMaintenance()
    {
        $twoMonthsAgo = now()->subMonths(2);
        
        $assets = DamagedAsset::where('tanggal_pelaporan', '>=', $twoMonthsAgo)
            ->groupBy('asset_id')
            ->havingRaw('COUNT(*) > 3')
            ->with('asset')
            ->select('asset_id', \DB::raw('COUNT(*) as damage_count'))
            ->get()
            ->map(function($damage) {
                return [
                    'asset_id' => $damage->asset_id,
                    'asset_name' => $damage->asset->nama_asset ?? 'Unknown',
                    'damage_count' => $damage->damage_count,
                    'has_scheduled_maintenance' => MaintenanceSchedule::where('asset_id', $damage->asset_id)
                        ->scheduled()
                        ->where('tanggal_pemeliharaan', '>=', now())
                        ->exists()
                ];
            });
        
        return response()->json($assets);
    }
}