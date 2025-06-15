<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class MaintenanceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::query();
        
        if ($request->has('lokasi') && $request->lokasi) {
            $query->where('lokasi', 'like', '%' . $request->lokasi . '%');
        }
        
        $schedules = $query->orderBy('tanggal_pemeliharaan', 'desc')->paginate(10);
        $locations = MaintenanceSchedule::distinct()->pluck('lokasi');
        
        return view('perbaikan.pemeliharaan-berkala.index', compact('schedules', 'locations'));
    }

    // Add this method for basic schedule creation
    public function storeBasic(Request $request)
    {
        $request->validate([
            'lokasi' => 'required|string|max:255',
            'tanggal_pemeliharaan' => 'required|date'
        ]);

        $reportId = MaintenanceSchedule::generateId();

        MaintenanceSchedule::create([
            'schedule_id' => $reportId,
            'lokasi' => $request->lokasi,
            'tanggal_pemeliharaan' => $request->tanggal_pemeliharaan,
            'status' => 'scheduled',
            'created_by' => Auth::user()->name ?? 'System'
        ]);

        return redirect()->route('perbaikan.pemeliharaan-berkala.index')->with('success', 'Jadwal pemeliharaan berhasil ditambahkan');
    }

    // Keep the existing update method for detailed updates
    public function updateDetails(Request $request, $id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        $request->validate([
            'deskripsi_pemeliharaan' => 'nullable|string',
            'catatan_tindak_lanjut' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $photoPaths = $schedule->photos ?? [];
        
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('maintenance-photos', 'public');
                $photoPaths[] = $path;
            }
        }

        $schedule->update([
            'deskripsi_pemeliharaan' => $request->deskripsi_pemeliharaan,
            'catatan_tindak_lanjut' => $request->catatan_tindak_lanjut,
            'photos' => $photoPaths,
            'status' => 'completed'
        ]);

        return redirect()->route('perbaikan.pemeliharaan-berkala.index')->with('success', 'Detail pemeliharaan berhasil diperbarui');
    }

    public function update(Request $request, $id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        $request->validate([
            'lokasi' => 'required|string|max:255',
            'tanggal_pemeliharaan' => 'required|date',
            'deskripsi_pemeliharaan' => 'nullable|string',
            'catatan_tindak_lanjut' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $photoPaths = $schedule->photos ?? [];
        
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('maintenance-photos', 'public');
                $photoPaths[] = $path;
            }
        }

        $schedule->update([
            'lokasi' => $request->lokasi,
            'tanggal_pemeliharaan' => $request->tanggal_pemeliharaan,
            'deskripsi_pemeliharaan' => $request->deskripsi_pemeliharaan,
            'catatan_tindak_lanjut' => $request->catatan_tindak_lanjut,
            'photos' => $photoPaths
        ]);

        return redirect()->route('perbaikan.pemeliharaan-berkala.index')->with('success', 'Jadwal pemeliharaan berhasil diperbarui');
    }

    public function report(Request $request)
    {
        $user = Auth::user(); // Add this
        
        $query = MaintenanceSchedule::query();
        if ($user->hasRole(['wakil_dekan_2'])) {
            $query->where('status', 'completed');
        } 
        
        if ($request->has('lokasi') && $request->lokasi) {
            $query->where('lokasi', 'like', '%' . $request->lokasi . '%');
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
        $locations = MaintenanceSchedule::distinct()->pluck('lokasi');
        
        // Get distinct months and years
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
        
        return view('perbaikan.pemeliharaan-berkala.report', compact('schedules', 'locations', 'months', 'years'));
    }

    public function showReport($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        return view('perbaikan.pemeliharaan-berkala.show-report', compact('schedule'));
    }

    public function destroy($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        // Delete photos from storage
        if ($schedule->photos) {
            foreach ($schedule->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }
        
        $schedule->delete();
        
        return redirect()->route('perbaikan.pemeliharaan-berkala.index')->with('success', 'Jadwal pemeliharaan berhasil dihapus');
    }

    public function downloadReportPdf($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        // Convert photos to base64 for PDF
        $photosBase64 = [];
        if ($schedule->photos) {
            foreach ($schedule->photos as $index => $photo) {
                $imagePath = storage_path('app/public/' . $photo);
                if (file_exists($imagePath)) {
                    $imageData = file_get_contents($imagePath);
                    $imageInfo = getimagesize($imagePath);
                    $mimeType = $imageInfo['mime'] ?? 'image/jpeg';
                    $photosBase64[] = [
                        'index' => $index + 1,
                        'base64' => 'data:' . $mimeType . ';base64,' . base64_encode($imageData),
                        'filename' => basename($photo)
                    ];
                }
            }
        }
        
        $pdf = PDF::loadView('perbaikan.pemeliharaan-berkala.pdf-report', compact('schedule', 'photosBase64'));
        
        $reportId = 'LP-' . date('Ymd', strtotime($schedule->tanggal_pemeliharaan)) . '-' . str_pad($schedule->id, 3, '0', STR_PAD_LEFT);
        
        return $pdf->download('laporan-pemeliharaan-' . $reportId . '.pdf');
    }
}