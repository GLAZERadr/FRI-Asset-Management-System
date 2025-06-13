<?php

namespace App\Http\Controllers;

use App\Models\DamagedAsset;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class DamagedAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = DamagedAsset::with('asset');
        
        // Apply filters if they exist
        if ($request->has('tingkat_kerusakan') && $request->tingkat_kerusakan) {
            $query->where('tingkat_kerusakan', $request->tingkat_kerusakan);
        }
        
        if ($request->has('lokasi') && $request->lokasi) {
            $query->whereHas('asset', function($q) use ($request) {
                $q->where('lokasi', $request->lokasi);
            });
        }
        
        // Staff logistik only sees their own reports
        if (Auth::user()->hasRole('staff_logistik')) {
            $query->where('pelapor', Auth::user()->division);
        }

        // Make sure to use paginate() to get a LengthAwarePaginator instance
        $damagedAssets = $query->orderBy('tanggal_pelaporan', 'desc')->paginate(10);
        
        // Get locations for filter dropdown
        $locations = Asset::distinct()->pluck('lokasi');
        
        return view('damaged_assets.index', compact('damagedAssets', 'locations'));
    }
    
    public function show($id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->with('asset')->firstOrFail();
        
        // Staff logistik can only view their own reports
        if (Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor != Auth::user()->division) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('damaged_assets.show', compact('damagedAsset'));
    }
    
    public function create()
    {
        $assets = Asset::all();
        $locations = Asset::distinct()->pluck('lokasi');
        
        return view('damaged_assets.create', compact('assets', 'locations'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,asset_id',
            'tingkat_kerusakan' => 'required|in:Ringan,Sedang,Berat',
            'estimasi_biaya' => 'required|numeric|min:0',
            'deskripsi_kerusakan' => 'required|string',
            'vendor' => 'nullable|string',
        ]);
        
        // Generate a unique damage ID
        $latestDamage = DamagedAsset::latest()->first();
        $damageNumber = $latestDamage ? intval(substr($latestDamage->damage_id, 4)) + 1 : 1;
        $damageId = 'DMG-' . str_pad($damageNumber, 5, '0', STR_PAD_LEFT);
        
        $damagedAsset = DamagedAsset::create([
            'damage_id' => $damageId,
            'asset_id' => $validated['asset_id'],
            'tingkat_kerusakan' => $validated['tingkat_kerusakan'],
            'estimasi_biaya' => $validated['estimasi_biaya'],
            'deskripsi_kerusakan' => $validated['deskripsi_kerusakan'],
            'tanggal_pelaporan' => now(),
            'pelapor' => Auth::user()->division,
            'vendor' => $validated['vendor'] ?? null,
        ]);
        
        return redirect()->route('perbaikan.aset')
            ->with('success', 'Kerusakan aset berhasil dilaporkan.');
    }
    
    public function edit($id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->with('asset')->firstOrFail();
        
        // Staff logistik can only edit their own reports
        if (Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor != Auth::user()->division) {
            abort(403, 'Unauthorized action.');
        }
        
        $assets = Asset::all();
        
        return view('damaged_assets.edit', compact('damagedAsset', 'assets'));
    }
    
    public function update(Request $request, $id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->firstOrFail();
        
        // Staff logistik can only update their own reports
        if (Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor != Auth::user()->division) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'tingkat_kerusakan' => 'required|in:Ringan,Sedang,Berat',
            'estimasi_biaya' => 'required|numeric|min:0',
            'deskripsi_kerusakan' => 'required|string',
            'vendor' => 'nullable|string',
        ]);
        
        $damagedAsset->update([
            'tingkat_kerusakan' => $validated['tingkat_kerusakan'],
            'estimasi_biaya' => $validated['estimasi_biaya'],
            'deskripsi_kerusakan' => $validated['deskripsi_kerusakan'],
            'vendor' => $validated['vendor'] ?? null,
        ]);
        
        return redirect()->route('perbaikan.aset')
            ->with('success', 'Kerusakan aset berhasil diperbarui.');
    }

        /**
     * Process QR code for damage reporting (guest access)
     */
    public function processQRForDamage(Request $request)
    {
        try {
            $request->validate([
                'qr_data' => 'required|string'
            ]);

            $qrData = trim($request->input('qr_data'));
            
            Log::info('Processing QR for damage report', ['qr_data' => $qrData]);
            
            // Extract asset ID from QR data
            $assetId = $this->extractAssetIdFromQR($qrData);

            if (!$assetId) {
                Log::warning('No asset ID extracted from QR', ['qr_data' => $qrData]);
                return response()->json([
                    'success' => false,
                    'message' => 'QR code tidak mengandung asset ID yang valid.',
                    'qr_data' => $qrData
                ], 404);
            }

            // Search for the asset
            $asset = Asset::where('asset_id', $assetId)
                         ->select('asset_id', 'nama_asset', 'lokasi')
                         ->first();

            if (!$asset) {
                Log::warning('Asset not found', ['asset_id' => $assetId]);
                return response()->json([
                    'success' => false,
                    'message' => "Asset dengan ID '{$assetId}' tidak ditemukan.",
                    'extracted_asset_id' => $assetId
                ], 404);
            }

            // Store asset data in session for the damage report form
            Session::put('damage_report_asset', [
                'asset_id' => $asset->asset_id,
                'nama_asset' => $asset->nama_asset,
                'lokasi' => $asset->lokasi
            ]);

            Log::info('Asset found for damage report', ['asset_id' => $asset->asset_id]);

            return response()->json([
                'success' => true,
                'asset_id' => $asset->asset_id,
                'nama_asset' => $asset->nama_asset,
                'lokasi' => $asset->lokasi,
                'message' => "Asset '{$asset->nama_asset}' ditemukan."
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data QR code tidak valid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('QR processing error for damage report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'qr_data' => $request->input('qr_data')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses QR code.'
            ], 500);
        }
    }

    /**
     * Show damage report form (guest access)
     */
    public function createDamageReport(Request $request)
    {
        // Get asset data from session or request parameter
        $assetData = Session::get('damage_report_asset');
        
        if (!$assetData && $request->has('asset_id')) {
            // Fallback: get asset data from asset_id parameter
            $asset = Asset::where('asset_id', $request->asset_id)
                         ->select('asset_id', 'nama_asset', 'lokasi')
                         ->first();
            
            if ($asset) {
                $assetData = [
                    'asset_id' => $asset->asset_id,
                    'nama_asset' => $asset->nama_asset,
                    'lokasi' => $asset->lokasi
                ];
                Session::put('damage_report_asset', $assetData);
            }
        }

        if (!$assetData) {
            return redirect()->route('login')->with('error', 'Data asset tidak ditemukan. Silakan scan QR code terlebih dahulu.');
        }

        return view('perbaikan.form', compact('assetData'));
    }

    public function storeDamageReport(Request $request)
    {
        Log::info('Starting damage report storage', [
            'request_data' => $request->all(),
            'user_authenticated' => Auth::check(),
            'user_id' => Auth::id()
        ]);
    
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,asset_id',
            'nama_aset' => 'required|string',
            'lokasi' => 'required|string',
            'tanggal_laporan' => 'required|date|before_or_equal:today',
            'role_pelapor' => 'required|string|in:mahasiswa,dosen,staff',
            'deskripsi_kerusakan' => 'required|string|min:10',
            'foto_kerusakan' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);
    
        Log::info('Validation passed', ['validated_data' => $validated]);
    
        try {
            // Generate a unique damage ID
            $latestDamage = DamagedAsset::latest('id')->first();
            Log::info('Latest damage record', ['latest_damage' => $latestDamage]);
            
            $damageNumber = $latestDamage ? intval(substr($latestDamage->damage_id, 4)) + 1 : 1;
            $damageId = 'DMG-' . str_pad($damageNumber, 5, '0', STR_PAD_LEFT);
            
            Log::info('Generated damage ID', [
                'damage_number' => $damageNumber,
                'damage_id' => $damageId
            ]);
    
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('foto_kerusakan')) {
                Log::info('Processing file upload', [
                    'file_name' => $request->file('foto_kerusakan')->getClientOriginalName(),
                    'file_size' => $request->file('foto_kerusakan')->getSize(),
                    'file_mime' => $request->file('foto_kerusakan')->getMimeType()
                ]);
                
                $photoPath = $request->file('foto_kerusakan')->store('damage-reports', 'public');
                Log::info('File uploaded successfully', ['photo_path' => $photoPath]);
            } else {
                Log::warning('No file uploaded despite validation');
            }
    
            // Get current user info or use guest info
            $pelapor = Auth::check() ? Auth::user()->division : 'Guest - ' . ucfirst($validated['role_pelapor']);
            $userInfo = Auth::check() ? Auth::user()->name : 'Guest Reporter';
            
            Log::info('User info prepared', [
                'pelapor' => $pelapor,
                'user_info' => $userInfo,
                'is_authenticated' => Auth::check()
            ]);
    
            $createData = [
                'damage_id' => $damageId,
                'asset_id' => $validated['asset_id'],
                'tingkat_kerusakan' => null,
                'estimasi_biaya' => 0,
                'deskripsi_kerusakan' => $validated['deskripsi_kerusakan'],
                'tanggal_pelaporan' => $validated['tanggal_laporan'],
                'pelapor' => $pelapor,
                'reporter_name' => $userInfo,
                'reporter_role' => $validated['role_pelapor'],
                'damaged_image' => $photoPath,
                'status' => 'Baru',
                'verified' => 'No',
                'vendor' => null,
            ];
    
            Log::info('Attempting to create damage record', ['create_data' => $createData]);
    
            $damagedAsset = DamagedAsset::create($createData);
            
            Log::info('Damage record created successfully', [
                'created_record_id' => $damagedAsset->id,
                'damage_id' => $damagedAsset->damage_id
            ]);
    
            // Clear session data
            Session::forget('damage_report_asset');
            Log::info('Session data cleared');
    
            Log::info('Damage report creation completed successfully', [
                'damage_id' => $damageId,
                'asset_id' => $validated['asset_id'],
                'reporter_role' => $validated['role_pelapor']
            ]);
    
            // Redirect to success page
            return redirect()->route('damage-report.success', ['damage_id' => $damageId])
                           ->with('success', 'Laporan kerusakan berhasil dikirim.');
    
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error during damage report storage', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'sql_error_code' => $e->errorInfo[1] ?? null,
                'sql_error_message' => $e->errorInfo[2] ?? null,
                'asset_id' => $validated['asset_id'],
                'damage_id' => $damageId ?? 'not_generated'
            ]);
    
            return back()->with('error', 'Database error: ' . $e->getMessage())
                        ->withInput();
    
        } catch (\Exception $e) {
            Log::error('General error during damage report storage', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'asset_id' => $validated['asset_id'],
                'damage_id' => $damageId ?? 'not_generated',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
    
            return back()->with('error', 'Terjadi kesalahan saat menyimpan laporan: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Show success page after damage report submission
     */
    public function damageReportSuccess($damageId)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $damageId)
                                  ->with('asset')
                                  ->first();

        if (!$damagedAsset) {
            return redirect()->route('login')->with('error', 'Laporan tidak ditemukan.');
        }

        return view('perbaikan.success', compact('damagedAsset'));
    }
    

    
    public function destroy($id)
    {
        $damagedAsset = DamagedAsset::where('damage_id', $id)->firstOrFail();
        
        // Staff logistik can only delete their own reports
        if (Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor != Auth::user()->division) {
            abort(403, 'Unauthorized action.');
        }
        
        $damagedAsset->delete();
        
        return redirect()->route('perbaikan.aset')
            ->with('success', 'Kerusakan aset berhasil dihapus.');
    }

        /**
     * Extract asset ID from various QR data formats
     */
    private function extractAssetIdFromQR($qrData)
    {
        // 1. Direct match - if QR data is already an asset_id
        if ($this->isValidAssetIdFormat($qrData)) {
            return $qrData;
        }

        // 2. Try to extract from common patterns
        $assetIdPatterns = [
            '/([A-Z]\d+-[A-Z]{3}-\d{3})/i',     // T0901-FUR-001 format
            '/([A-Z]\d+-[A-Z]{2,4}-\d{1,4})/i', // Flexible asset_id format
            '/asset[_-]?([A-Z0-9-]+)/i',        // asset_T0901-FUR-001
            '/id[_-]?([A-Z0-9-]+)/i',          // id_T0901-FUR-001
        ];

        foreach ($assetIdPatterns as $pattern) {
            if (preg_match($pattern, $qrData, $matches)) {
                $extractedValue = $matches[1];
                
                if ($this->isValidAssetIdFormat($extractedValue)) {
                    return $extractedValue;
                }
            }
        }

        // 3. Try JSON parsing
        if ((str_starts_with($qrData, '{') && str_ends_with($qrData, '}')) || 
            (str_starts_with($qrData, '[') && str_ends_with($qrData, ']'))) {
            try {
                $qrJson = json_decode($qrData, true);
                if (is_array($qrJson)) {
                    $assetIdKeys = ['asset_id', 'assetId', 'id', 'asset-id'];
                    
                    foreach ($assetIdKeys as $key) {
                        if (isset($qrJson[$key]) && $this->isValidAssetIdFormat($qrJson[$key])) {
                            return $qrJson[$key];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Invalid JSON, continue
            }
        }

        // 4. URL decode and try again
        $decodedData = urldecode($qrData);
        if ($decodedData !== $qrData) {
            return $this->extractAssetIdFromQR($decodedData);
        }

        return null;
    }

    /**
     * Validate if a string matches expected asset_id format
     */
    private function isValidAssetIdFormat($value)
    {
        if (empty($value) || !is_string($value)) {
            return false;
        }

        // Primary format: T0901-FUR-001
        $primaryPattern = '/^[A-Z]\d+-[A-Z]{3}-\d{3}$/i';
        if (preg_match($primaryPattern, $value)) {
            return true;
        }

        // Secondary flexible format
        $flexiblePattern = '/^[A-Z]+\d*-[A-Z]{2,4}-\d{1,4}$/i';
        if (preg_match($flexiblePattern, $value)) {
            return true;
        }

        return false;
    }
}