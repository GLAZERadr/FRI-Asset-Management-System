<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;

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
            try {
                // Configure Cloudinary once before the loop (proven to work)
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

                $upload = new UploadApi();
                $uploadedCount = 0;
                $failedCount = 0;

                foreach ($request->file('photos') as $index => $photo) {
                    try {
                        // Generate unique filename for each photo
                        $fileName = 'maintenance_' . time() . '_' . ($index + 1) . '_' . uniqid();
                        
                        Log::info('Processing maintenance photo upload', [
                            'index' => $index + 1,
                            'original_name' => $photo->getClientOriginalName(),
                            'generated_name' => $fileName,
                            'file_size' => $photo->getSize()
                        ]);

                        // Upload to Cloudinary
                        $result = $upload->upload($photo->getRealPath(), [
                            'folder' => 'maintenance-photos', // Organize in maintenance-photos folder
                            'public_id' => $fileName,
                            'quality' => 'auto', // Automatic quality optimization
                            'fetch_format' => 'auto', // Automatic format optimization
                            'transformation' => [
                                'width' => 1200,
                                'height' => 1200,
                                'crop' => 'limit', // Don't upscale, only downscale if needed
                                'quality' => 'auto'
                            ]
                        ]);

                        $photoPaths[] = $result['secure_url'];
                        $uploadedCount++;
                        
                        Log::info('Maintenance photo uploaded successfully', [
                            'index' => $index + 1,
                            'photo_url' => $result['secure_url'],
                            'cloudinary_public_id' => $result['public_id'] ?? null
                        ]);

                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error('Failed to upload maintenance photo', [
                            'index' => $index + 1,
                            'error' => $e->getMessage(),
                            'file_name' => $photo->getClientOriginalName()
                        ]);
                        
                        // Continue with other photos even if one fails
                        continue;
                    }
                }

                // Log summary
                Log::info('Maintenance photos upload completed', [
                    'total_photos' => count($request->file('photos')),
                    'uploaded_successfully' => $uploadedCount,
                    'failed_uploads' => $failedCount,
                    'uploaded_urls' => $photoPaths
                ]);

                // Show user feedback
                if ($uploadedCount > 0 && $failedCount > 0) {
                    session()->flash('warning', "{$uploadedCount} foto berhasil diupload, {$failedCount} foto gagal diupload.");
                } elseif ($failedCount > 0) {
                    session()->flash('error', "Gagal mengupload {$failedCount} foto maintenance.");
                } elseif ($uploadedCount > 0) {
                    session()->flash('success', "{$uploadedCount} foto maintenance berhasil diupload.");
                }

            } catch (\Exception $e) {
                Log::error('Cloudinary configuration failed for maintenance photos', [
                    'error' => $e->getMessage()
                ]);
                return back()->withInput()->with('error', 'Failed to configure photo upload: ' . $e->getMessage());
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
            try {
                Configuration::instance([
                    'cloud' => [
                        'cloud_name' => config('cloudinary.cloud_name'),
                        'api_key' => config('cloudinary.api_key'),
                        'api_secret' => config('cloudinary.api_secret'),
                    ],
                    'url' => ['secure' => true]
                ]);
        
                $upload = new UploadApi();
                foreach ($request->file('photos') as $index => $photo) {
                    try {
                        $result = $upload->upload($photo->getRealPath(), [
                            'folder' => 'maintenance-photos',
                            'public_id' => 'maintenance_' . time() . '_' . ($index + 1) . '_' . uniqid(),
                            'quality' => 'auto',
                            'fetch_format' => 'auto'
                        ]);
                        $photoPaths[] = $result['secure_url']; // Add to existing array
                    } catch (\Exception $e) {
                        Log::error('Photo upload failed: ' . $e->getMessage());
                        continue;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Cloudinary config failed: ' . $e->getMessage());
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
        
        // Convert Cloudinary photos to base64 for PDF
        $photosBase64 = [];
        if ($schedule->photos) {
            foreach ($schedule->photos as $index => $photoUrl) {
                try {
                    // Download image from Cloudinary URL
                    $imageData = file_get_contents($photoUrl);
                    if ($imageData !== false) {
                        // Get image info from URL
                        $pathInfo = pathinfo(parse_url($photoUrl, PHP_URL_PATH));
                        $extension = strtolower($pathInfo['extension'] ?? 'jpg');
                        $mimeType = $extension === 'png' ? 'image/png' : 'image/jpeg';
                        
                        $photosBase64[] = [
                            'index' => $index + 1,
                            'base64' => 'data:' . $mimeType . ';base64,' . base64_encode($imageData),
                            'filename' => $pathInfo['filename'] ?? 'photo_' . ($index + 1)
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to download photo for PDF: ' . $e->getMessage());
                    continue;
                }
            }
        }
        
        $pdf = PDF::loadView('perbaikan.pemeliharaan-berkala.pdf-report', compact('schedule', 'photosBase64'));
        
        $reportId = 'LP-' . date('Ymd', strtotime($schedule->tanggal_pemeliharaan)) . '-' . str_pad($schedule->id, 3, '0', STR_PAD_LEFT);
        
        return $pdf->download('laporan-pemeliharaan-' . $reportId . '.pdf');
    }
}