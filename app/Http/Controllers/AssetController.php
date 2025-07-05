<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();
        
        // Apply filters
        if ($request->has('nama_asset') && $request->nama_asset) {
            $query->where('nama_asset', 'like', '%' . $request->nama_asset . '%');
        }
        
        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }
        
        if ($request->has('tahun_perolehan') && $request->tahun_perolehan) {
            $query->whereYear('tgl_perolehan', $request->tahun_perolehan);
        }
        
        $assets = $query->paginate(10);
        
        // Get filter options
        $categories = ['Furnitur', 'Elektronik', 'Mesin'];
        $years = Asset::selectRaw('YEAR(tgl_perolehan) as year')
                     ->distinct()
                     ->orderBy('year', 'desc')
                     ->pluck('year')
                     ->filter();
        
        return view('assets.index', compact('assets', 'categories', 'years'));
    }
    
    public function exportPdf(Request $request)
    {
        $query = Asset::query();
        
        // Apply same filters as index
        if ($request->has('nama_asset') && $request->nama_asset) {
            $query->where('nama_asset', 'like', '%' . $request->nama_asset . '%');
        }
        
        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }
        
        if ($request->has('tahun_perolehan') && $request->tahun_perolehan) {
            $query->whereYear('tgl_perolehan', $request->tahun_perolehan);
        }
        
        $assets = $query->get();
        
        $pdf = PDF::loadView('assets.pdf', compact('assets'));
        return $pdf->download('data-aset.pdf');
    }
    
    public function downloadQrCode($asset_id)
    {
        \Log::info('=== QR CODE GENERATION START ===');
        \Log::info('Asset ID requested: ' . $asset_id);
        \Log::info('PHP GD loaded: ' . (extension_loaded('gd') ? 'YES' : 'NO'));
        \Log::info('PHP Imagick loaded: ' . (extension_loaded('imagick') ? 'YES' : 'NO'));
    
        // Validate that asset_id is not empty
        if (empty($asset_id)) {
            \Log::error('Asset ID is empty, aborting with 404');
            abort(404, 'Asset ID tidak ditemukan');
        }
    
        try {
            \Log::info('Searching for asset in database...');
            
            // Get the asset by asset_id
            $asset = Asset::where('asset_id', $asset_id)->first();
            
            if (!$asset) {
                \Log::error('Asset not found in database: ' . $asset_id);
                abort(404, 'Asset tidak ditemukan');
            }
    
            \Log::info('Asset found: ' . $asset->nama_asset . ' (Room: ' . $asset->kode_ruangan . ')');
            \Log::info('Starting QR code generation with BaconQrCode 2.0.8...');
    
            // Use BaconQrCode 2.0.8 - it will automatically use GD since Imagick is not available
            $renderer = new ImageRenderer(
                new RendererStyle(300, 10)
            );
            $writer = new Writer($renderer);
            $qrCode = $writer->writeString($asset_id);
    
            \Log::info('QR code generated successfully, size: ' . strlen($qrCode) . ' bytes');
            \Log::info('Creating image canvas...');
    
            // Create image canvas with text
            $canvas = imagecreatetruecolor(400, 450);
            if (!$canvas) {
                \Log::error('Failed to create image canvas');
                throw new \Exception('Failed to create image canvas');
            }
    
            \Log::info('Canvas created successfully');
    
            $white = imagecolorallocate($canvas, 255, 255, 255);
            $black = imagecolorallocate($canvas, 0, 0, 0);
            $blue = imagecolorallocate($canvas, 0, 102, 204);
            
            // Fill background with white
            imagefill($canvas, 0, 0, $white);
            
            \Log::info('Canvas colors allocated and background filled');
            \Log::info('Loading QR code image from string...');
            
            // Load QR code image from string
            $qrImage = imagecreatefromstring($qrCode);
            if (!$qrImage) {
                \Log::error('Failed to create QR image from string');
                throw new \Exception('Failed to create QR image from string');
            }
    
            \Log::info('QR image loaded successfully');
            \Log::info('Compositing QR image onto canvas...');
            
            // Center the QR code (400px wide canvas, 300px QR = 50px margin each side)
            $copyResult = imagecopy($canvas, $qrImage, 50, 20, 0, 0, 300, 300);
            if (!$copyResult) {
                \Log::error('Failed to copy QR image to canvas');
                throw new \Exception('Failed to copy QR image to canvas');
            }
    
            \Log::info('QR image composited successfully');
            \Log::info('Adding text labels...');
            
            // Add text below QR code
            $font = 4; // Built-in font size
            $textY = 340;
            
            // Asset ID
            $assetIdText = "ID Asset: " . $asset->asset_id;
            $textWidth = strlen($assetIdText) * imagefontwidth($font);
            $textX = (400 - $textWidth) / 2;
            imagestring($canvas, $font, $textX, $textY, $assetIdText, $blue);
            
            // Asset Name
            $assetNameText = "Nama: " . (strlen($asset->nama_asset) > 35 ? substr($asset->nama_asset, 0, 32) . "..." : $asset->nama_asset);
            $textWidth = strlen($assetNameText) * imagefontwidth($font);
            $textX = (400 - $textWidth) / 2;
            imagestring($canvas, $font, $textX, $textY + 20, $assetNameText, $black);
            
            // Room Code
            $roomCodeText = "Kode Ruangan: " . $asset->kode_ruangan;
            $textWidth = strlen($roomCodeText) * imagefontwidth($font);
            $textX = (400 - $textWidth) / 2;
            imagestring($canvas, $font, $textX, $textY + 40, $roomCodeText, $black);
            
            \Log::info('Text labels added successfully');
            
            // Generate filename
            $filename = "qr-" . $asset->asset_id . "-" . $asset->kode_ruangan . ".png";
            
            \Log::info('Generating final PNG image...');
            
            // Output image
            ob_start();
            $pngResult = imagepng($canvas);
            if (!$pngResult) {
                ob_end_clean();
                \Log::error('Failed to generate PNG image');
                throw new \Exception('Failed to generate PNG image');
            }
            
            $imageData = ob_get_contents();
            ob_end_clean();
            
            \Log::info('PNG image generated successfully, final size: ' . strlen($imageData) . ' bytes');
            \Log::info('Cleaning up memory...');
            
            // Clean up memory
            imagedestroy($canvas);
            imagedestroy($qrImage);
            
            \Log::info('Memory cleaned up');
            \Log::info('Returning response with filename: ' . $filename);
            \Log::info('=== QR CODE GENERATION SUCCESS ===');
            
            return response($imageData)
                   ->header('Content-Type', 'image/png')
                   ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                   
        } catch (\Exception $e) {
            \Log::error('=== QR CODE GENERATION FAILED ===');
            \Log::error('Error message: ' . $e->getMessage());
            \Log::error('Error file: ' . $e->getFile());
            \Log::error('Error line: ' . $e->getLine());
            \Log::error('Asset ID: ' . $asset_id);
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== END ERROR DETAILS ===');
            
            abort(500, 'Gagal membuat QR Code: ' . $e->getMessage());
        }
    }
    
    public function show($id)
    {
        $asset = Asset::where('asset_id', $id)->firstOrFail();
        return view('assets.show', compact('asset'));
    }
    
    public function create()
    {
        $categories = ['Furnitur', 'Elektronik', 'Mesin'];
        $sumberPerolehan = ['Hibah', 'Yayasan Universitas Telkom', 'Bantuan Pemerintah'];
        
        return view('assets.create', compact('categories', 'sumberPerolehan'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_asset' => 'required|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'kategori' => 'required|string|in:Furnitur,Elektronik,Mesin',
            'tingkat_kepentingan_asset' => 'required|integer|min:1',
            'spesifikasi' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'kode_ruangan' => 'required|string|max:255',
            'tgl_perolehan' => 'required|date',
            'masa_pakai_maksimum' => 'required|integer|min:1',
            'masa_pakai_unit' => 'required|string|in:hari,bulan,tahun',
            'nilai_perolehan' => 'required|numeric|min:0',
            'sumber_perolehan' => 'required|string|in:Hibah,Yayasan Universitas Telkom,Bantuan Pemerintah',
            'status_kelayakan' => 'required|string|in:Layak,Tidak Layak',
            'foto_asset' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Extract room prefix from kode_ruangan (e.g., "TULT-0901" → "T0901")
        $kodeRuangan = $validated['kode_ruangan'];
        $roomParts = explode('-', $kodeRuangan);
        $roomPrefix = substr($roomParts[0], 0, 1) . (isset($roomParts[1]) ? $roomParts[1] : '');
        
        // Get category abbreviation
        $categoryAbbr = strtoupper(substr($validated['kategori'], 0, 3));
        
        // Generate unique asset ID by finding the next available number for this room-category combination
        $basePattern = $roomPrefix . '-' . $categoryAbbr . '-';
        $latestAsset = Asset::where('asset_id', 'LIKE', $basePattern . '%')
                          ->orderBy('asset_id', 'desc')
                          ->first();
        
        if ($latestAsset) {
            // Extract the number from the last asset ID
            $lastNumber = intval(substr($latestAsset->asset_id, strlen($basePattern)));
            $assetNumber = $lastNumber + 1;
        } else {
            $assetNumber = 1;
        }
        
        $assetId = $basePattern . str_pad($assetNumber, 3, '0', STR_PAD_LEFT);
        
        // Ensure uniqueness (additional safety check)
        while (Asset::where('asset_id', $assetId)->exists()) {
            $assetNumber++;
            $assetId = $basePattern . str_pad($assetNumber, 3, '0', STR_PAD_LEFT);
        }
        
        // Calculate masa_pakai_maksimum date based on unit
        $tglPerolehan = Carbon::parse($validated['tgl_perolehan']);
        $masaPakaiMaksimum = $this->calculateMaxUsageDate($tglPerolehan, $validated['masa_pakai_maksimum'], $validated['masa_pakai_unit']);
        
        // Handle file upload
        $fotoPath = null;
        if ($request->hasFile('foto_asset')) {
            $fotoPath = $request->file('foto_asset')->store('assets', 'public');
        }
        
        Asset::create([
            'asset_id' => $assetId,
            'nama_asset' => $validated['nama_asset'],
            'vendor' => $validated['vendor'],
            'kategori' => $validated['kategori'],
            'spesifikasi' => $validated['spesifikasi'],
            'lokasi' => $validated['lokasi'],
            'tingkat_kepentingan_asset' => $validated['tingkat_kepentingan_asset'],
            'kode_ruangan' => $validated['kode_ruangan'],
            'tgl_perolehan' => $validated['tgl_perolehan'],
            'masa_pakai_maksimum' => $masaPakaiMaksimum,
            'masa_pakai_duration' => $validated['masa_pakai_maksimum'],
            'masa_pakai_unit' => $validated['masa_pakai_unit'],
            'nilai_perolehan' => $validated['nilai_perolehan'],
            'sumber_perolehan' => $validated['sumber_perolehan'],
            'status_kelayakan' => $validated['status_kelayakan'],
            'foto_asset' => $fotoPath,
        ]);
        
        return redirect()->route('pemantauan.index')
            ->with('success', 'Asset berhasil ditambahkan.');
    }
    
    public function edit($id)
    {
        $asset = Asset::where('asset_id', $id)->firstOrFail();
        $categories = ['Furnitur', 'Elektronik', 'Mesin'];
        $sumberPerolehan = ['Hibah', 'Yayasan Universitas Telkom', 'Bantuan Pemerintah'];
        
        return view('assets.edit', compact('asset', 'categories', 'sumberPerolehan'));
    }
    
    public function update(Request $request, $id)
    {
        $asset = Asset::where('asset_id', $id)->firstOrFail();
        
        $validated = $request->validate([
            'nama_asset' => 'required|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'kategori' => 'required|string|in:Furnitur,Elektronik,Mesin',
            'spesifikasi' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'tingkat_kepentingan_asset' => 'required|integer|min:1',
            'kode_ruangan' => 'required|string|max:255',
            'tgl_perolehan' => 'required|date',
            'masa_pakai_maksimum' => 'required|integer|min:1',
            'masa_pakai_unit' => 'required|string|in:hari,bulan,tahun',
            'nilai_perolehan' => 'required|numeric|min:0',
            'sumber_perolehan' => 'required|string|in:Hibah,Yayasan Universitas Telkom,Bantuan Pemerintah',
            'status_kelayakan' => 'required|string|in:Layak,Tidak Layak',
            'foto_asset' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Calculate masa_pakai_maksimum date based on unit
        $tglPerolehan = Carbon::parse($validated['tgl_perolehan']);
        $masaPakaiMaksimum = $this->calculateMaxUsageDate($tglPerolehan, $validated['masa_pakai_maksimum'], $validated['masa_pakai_unit']);
        
        // Handle file upload
        $fotoPath = $asset->foto_asset;
        if ($request->hasFile('foto_asset')) {
            // Delete old file if exists
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto_asset')->store('assets', 'public');
        }
        
        $asset->update([
            'nama_asset' => $validated['nama_asset'],
            'vendor' => $validated['vendor'],
            'kategori' => $validated['kategori'],
            'spesifikasi' => $validated['spesifikasi'],
            'lokasi' => $validated['lokasi'],
            'tingkat_kepentingan_asset' => $validated['tingkat_kepentingan_asset'],
            'kode_ruangan' => $validated['kode_ruangan'],
            'tgl_perolehan' => $validated['tgl_perolehan'],
            'masa_pakai_maksimum' => $masaPakaiMaksimum,
            'masa_pakai_duration' => $validated['masa_pakai_maksimum'],
            'masa_pakai_unit' => $validated['masa_pakai_unit'],
            'nilai_perolehan' => $validated['nilai_perolehan'],
            'sumber_perolehan' => $validated['sumber_perolehan'],
            'status_kelayakan' => $validated['status_kelayakan'],
            'foto_asset' => $fotoPath,
        ]);
        
        return redirect()->route('pemantauan.index')
            ->with('success', 'Asset berhasil diperbarui.');
    }

    public function processQR(Request $request)
    {
        try {
            $request->validate([
                'qr_data' => 'required|string'
            ]);
    
            $qrData = trim($request->input('qr_data'));
            
            // Log the QR scan attempt
            \Log::info('QR Code scanned', ['qr_data' => $qrData, 'user_id' => auth()->id()]);
    
            // Check if QR data is a kode_ruangan (room code)
            $roomAssets = Asset::where('kode_ruangan', $qrData)->get();
            
            if ($roomAssets->isNotEmpty()) {
                // QR code contains a room code - redirect to monitoring form
                \Log::info('Room code found via QR scan', [
                    'kode_ruangan' => $qrData,
                    'asset_count' => $roomAssets->count(),
                    'user_id' => auth()->id()
                ]);
    
                return response()->json([
                    'success' => true,
                    'type' => 'room_monitoring',
                    'kode_ruangan' => $qrData,
                    'asset_count' => $roomAssets->count(),
                    'redirect_url' => route('pemantauan.monitoring.form', ['kodeRuangan' => $qrData]),
                    'message' => "Ruangan ditemukan! {$roomAssets->count()} asset akan dimonitoring."
                ]);
            }
    
            // Try to find individual asset
            $asset = $this->findAssetByQRData($qrData);
    
            if ($asset) {
                // Individual asset found - redirect to monitoring for that room
                \Log::info('Asset found via QR scan', [
                    'asset_id' => $asset->asset_id,
                    'kode_ruangan' => $asset->kode_ruangan,
                    'user_id' => auth()->id()
                ]);
    
                return response()->json([
                    'success' => true,
                    'type' => 'asset_monitoring',
                    'asset_id' => $asset->asset_id,
                    'asset_name' => $asset->nama_asset,
                    'kode_ruangan' => $asset->kode_ruangan,
                    'redirect_url' => route('pemantauan.monitoring.form', ['kodeRuangan' => $asset->kode_ruangan]),
                    'message' => "Asset '{$asset->nama_asset}' ditemukan! Redirecting ke monitoring ruangan {$asset->kode_ruangan}."
                ]);
            }
    
            // Nothing found
            \Log::warning('No asset or room found for QR data', ['qr_data' => $qrData]);
            
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
            \Log::error('QR processing error', [
                'error' => $e->getMessage(),
                'qr_data' => $request->input('qr_data'),
                'user_id' => auth()->id()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses QR code. Silakan coba lagi.'
            ], 500);
        }
    }
    
    /**
     * Find asset by various QR data formats using your actual database structure
     */
    private function findAssetByQRData($qrData)
    {
        // Try exact match with asset_id (your primary identifier)
        $asset = Asset::where('asset_id', $qrData)->first();
        if ($asset) return $asset;
    
        // Try exact match with kode_ruangan (room code from QR)
        $asset = Asset::where('kode_ruangan', $qrData)->first();
        if ($asset) return $asset;
    
        // Try case-insensitive search
        $asset = Asset::whereRaw('LOWER(asset_id) = ?', [strtolower($qrData)])
                     ->orWhereRaw('LOWER(kode_ruangan) = ?', [strtolower($qrData)])
                     ->first();
        if ($asset) return $asset;
    
        // Try to extract patterns from QR data
        $patterns = [
            '/([A-Z]\d+-[A-Z]{3}-\d{3})/i',    // T0901-FUR-001 format (your asset_id pattern)
            '/([A-Z]+-\d+)/i',                  // TULT-0901 format (your kode_ruangan pattern)
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
     * Calculate maximum usage date based on acquisition date, duration, and unit
     */
    private function calculateMaxUsageDate(Carbon $acquisitionDate, int $duration, string $unit): Carbon
    {
        $maxUsageDate = $acquisitionDate->copy();
        
        switch ($unit) {
            case 'hari':
                $maxUsageDate->addDays($duration);
                break;
            case 'bulan':
                $maxUsageDate->addMonths($duration);
                break;
            case 'tahun':
                $maxUsageDate->addYears($duration);
                break;
            default:
                // Default to months if unit is not recognized
                $maxUsageDate->addMonths($duration);
                break;
        }
        
        return $maxUsageDate;
    }
    
    /**
     * Generate location prefix from kode_ruangan
     * Examples: TULT-0901 -> T0901, GACUK-101 -> G101, TULT-0902 -> T0902
     */
    private function generateLocationPrefix(string $kodeRuangan): string
    {
        // Split by dash and get both parts
        $parts = explode('-', $kodeRuangan);
        
        if (count($parts) >= 2) {
            $building = $parts[0]; // TULT, GACUK, etc.
            $room = $parts[1];     // 0901, 101, 0902, etc.
            
            // Take first letter of building + room number
            $prefix = substr($building, 0, 1) . $room;
            
            return strtoupper($prefix);
        }
        
        // Fallback: if format is unexpected, use first 5 characters
        return strtoupper(substr(str_replace('-', '', $kodeRuangan), 0, 5));
    }
}