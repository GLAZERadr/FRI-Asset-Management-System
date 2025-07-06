<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['updater']);
        
        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('vendor') && $request->vendor) {
            $query->where('vendor', 'like', '%' . $request->vendor . '%');
        }
        
        if ($request->has('tipe_pembayaran') && $request->tipe_pembayaran) {
            $query->where('tipe_pembayaran', $request->tipe_pembayaran);
        }
        
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('jatuh_tempo', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('jatuh_tempo', '<=', $request->end_date);
        }
        
        // Check for overdue payments and update status
        $this->updateOverduePayments();
        
        $payments = $query->orderBy('jatuh_tempo', 'desc')->paginate(10);
        
        // Get filter options
        $vendors = Payment::distinct()->pluck('vendor')->filter();
        $statuses = Payment::STATUS;
        $paymentTypes = Payment::TIPE_PEMBAYARAN;
        
        // Calculate statistics
        $stats = [
            'total_pending' => Payment::pending()->count(),
            'total_paid' => Payment::paid()->count(),
            'total_overdue' => Payment::overdue()->count(),
            'total_amount_pending' => Payment::pending()->sum('total_tagihan'),
            'total_amount_paid' => Payment::paid()->sum('total_tagihan'),
        ];
        
        return view('pembayaran.index', compact(
            'payments', 
            'vendors', 
            'statuses', 
            'paymentTypes', 
            'stats'
        ));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create()
    {
        $paymentTypes = Payment::TIPE_PEMBAYARAN;
        
        return view('pembayaran.create', compact('paymentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_invoice' => 'required|string',
            'jatuh_tempo' => 'required|date|after_or_equal:today',
            'vendor' => 'required|string|max:255',
            'total_tagihan' => 'required|numeric|min:0',
            'file_invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'tipe_pembayaran' => ['required', Rule::in(array_keys(Payment::TIPE_PEMBAYARAN))],
            'status' => ['nullable', Rule::in(array_keys(Payment::STATUS))],
            'tanggal_pembayaran' => 'nullable|date',
        ], [
            'jatuh_tempo.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum hari ini.',
            'file_invoice.mimes' => 'File invoice harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_invoice.max' => 'Ukuran file invoice maksimal 10MB.',
            'tipe_pembayaran.in' => 'Tipe pembayaran tidak valid.',
            'status.in' => 'Status pembayaran tidak valid.',
        ]);

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('file_invoice')) {
            try {
                $file = $request->file('file_invoice');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                Log::info('Processing invoice file upload', [
                    'original_file_name' => $file->getClientOriginalName(),
                    'generated_file_name' => $fileName,
                    'file_size' => $file->getSize(),
                    'file_mime' => $file->getMimeType()
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
        
                // Upload to Cloudinary (handles documents, not just images)
                $upload = new UploadApi();
                $result = $upload->upload($file->getRealPath(), [
                    'folder' => 'invoices', // Organize in invoices folder
                    'public_id' => pathinfo($fileName, PATHINFO_FILENAME), // Remove extension from public_id
                    'resource_type' => 'auto', // Automatically detect file type (image, video, raw)
                    'use_filename' => true,
                    'unique_filename' => true,
                ]);
        
                $filePath = $result['secure_url'];
                
                Log::info('Invoice file uploaded successfully to Cloudinary', [
                    'file_path' => $filePath,
                    'cloudinary_public_id' => $result['public_id'] ?? null,
                    'resource_type' => $result['resource_type'] ?? null
                ]);
        
            } catch (\Exception $e) {
                Log::error('Cloudinary upload failed for invoice file', [
                    'error' => $e->getMessage(),
                    'file_name' => $file->getClientOriginalName()
                ]);
                return back()->withInput()->with('error', 'Failed to upload invoice file: ' . $e->getMessage());
            }
        }

        // Determine status - default to 'belum_dibayar' if not provided
        $status = $validated['status'] ?? 'belum_dibayar';
        
        // If status is 'sudah_dibayar' and no payment date provided, set current datetime
        $tanggalPembayaran = null;
        if ($status === 'sudah_dibayar') {
            $tanggalPembayaran = $validated['tanggal_pembayaran'] ?? now();
        }

        // Create payment
        $payment = Payment::create([
            'no_invoice' => $validated['no_invoice'],
            'jatuh_tempo' => $validated['jatuh_tempo'],
            'vendor' => $validated['vendor'],
            'total_tagihan' => $validated['total_tagihan'],
            'file_invoice' => $filePath,
            'tipe_pembayaran' => $validated['tipe_pembayaran'],
            'status' => $status,
            'tanggal_pembayaran' => $tanggalPembayaran,
        ]);

        return redirect()->route('pembayaran.index', $payment->id)
            ->with('success', 'Data pembayaran berhasil dibuat.');
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        return view('pembayaran.show', compact('payment'));
    }

    /**
     * Show the form for editing the payment
     */
    public function edit(Payment $payment)
    {
        // Prevent editing paid payments
        if ($payment->status === 'sudah_dibayar') {
            return redirect()->route('pembayaran.show', $payment->id)
                ->with('error', 'Pembayaran yang sudah dibayar tidak dapat diubah.');
        }
        
        $paymentTypes = Payment::TIPE_PEMBAYARAN;
        $statusOptions = Payment::STATUS;
        
        return view('pembayaran.edit', compact('payment', 'paymentTypes', 'statusOptions'));
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        // Prevent updating paid payments
        if ($payment->status === 'sudah_dibayar') {
            return redirect()->route('pembayaran.show', $payment->id)
                ->with('error', 'Pembayaran yang sudah dibayar tidak dapat diubah.');
        }
        
        $validated = $request->validate([
            'no_invoice' => [
                'required',
                'string',
                Rule::unique('payments')->ignore($payment->id),
            ],
            'jatuh_tempo' => 'required|date',
            'vendor' => 'required|string|max:255',
            'total_tagihan' => 'required|numeric|min:0',
            'file_invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'tipe_pembayaran' => ['required', Rule::in(array_keys(Payment::TIPE_PEMBAYARAN))],
            'status' => ['required', Rule::in(array_keys(Payment::STATUS))],
            'tanggal_pembayaran' => 'nullable|date',
        ], [
            'no_invoice.unique' => 'Nomor invoice sudah digunakan.',
            'file_invoice.mimes' => 'File invoice harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_invoice.max' => 'Ukuran file invoice maksimal 10MB.',
            'tipe_pembayaran.in' => 'Tipe pembayaran tidak valid.',
            'status.in' => 'Status pembayaran tidak valid.',
        ]);
        
        // Handle file upload
        if ($request->hasFile('file_invoice')) {
            try {
                $file = $request->file('file_invoice');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                Log::info('Processing invoice file update (keeping old file)', [
                    'payment_id' => $payment->id,
                    'original_file_name' => $file->getClientOriginalName(),
                    'generated_file_name' => $fileName,
                    'old_file' => $payment->file_invoice
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
        
                // Upload new file to Cloudinary (old file remains)
                $upload = new UploadApi();
                $result = $upload->upload($file->getRealPath(), [
                    'folder' => 'invoices',
                    'public_id' => pathinfo($fileName, PATHINFO_FILENAME), // Remove extension from public_id
                    'resource_type' => 'auto', // Handles documents, images, videos
                    'use_filename' => true,
                    'unique_filename' => true,
                ]);
        
                $validated['file_invoice'] = $result['secure_url'];
                
                Log::info('Invoice file updated successfully (old file preserved)', [
                    'payment_id' => $payment->id,
                    'new_file_path' => $validated['file_invoice'],
                    'cloudinary_public_id' => $result['public_id'] ?? null,
                    'old_file_preserved' => $payment->file_invoice
                ]);
        
            } catch (\Exception $e) {
                Log::error('Failed to update invoice file', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                    'file_name' => $file->getClientOriginalName()
                ]);
                return back()->withInput()->with('error', 'Failed to upload invoice file: ' . $e->getMessage());
            }
        }
        
        // Handle payment date logic
        if ($validated['status'] === 'sudah_dibayar' && !$validated['tanggal_pembayaran']) {
            $validated['tanggal_pembayaran'] = now();
        } elseif ($validated['status'] !== 'sudah_dibayar') {
            $validated['tanggal_pembayaran'] = null;
        }
        
        $payment->update($validated);
        
        return redirect()->route('pembayaran.index', $payment->id)
            ->with('success', 'Data pembayaran berhasil diperbarui.');
    }

    /**
     * Update payment status (for AJAX calls from dropdown)
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Payment::STATUS))],
        ]);

        // For 'sudah_dibayar' status without photo, don't update yet
        if ($validated['status'] === 'sudah_dibayar' && !$request->hasFile('payment_photo')) {
            return response()->json([
                'success' => false, 
                'message' => 'Bukti pembayaran diperlukan untuk status sudah dibayar'
            ], 400);
        }

        $updateData = [
            'status' => $validated['status']
        ];

        // Set payment date if status is 'sudah_dibayar'
        if ($validated['status'] === 'sudah_dibayar') {
            $updateData['tanggal_pembayaran'] = now();
        } elseif ($validated['status'] !== 'sudah_dibayar') {
            $updateData['tanggal_pembayaran'] = null;
        }

        $payment->update($updateData);

        return response()->json([
            'success' => true, 
            'message' => 'Status pembayaran berhasil diperbarui'
        ]);
    }

    /**
     * Update payment photo (for 'sudah_dibayar' status)
     */
    public function updatePhoto(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'payment_photo' => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:5120', // 5MB max
            'status' => 'required|in:sudah_dibayar',
        ], [
            'payment_photo.required' => 'Bukti pembayaran diperlukan.',
            'payment_photo.mimes' => 'File harus berformat JPG, PNG, GIF, atau PDF.',
            'payment_photo.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('payment_photo')) {
                try {
                    $file = $request->file('payment_photo');
                    $fileName = 'payment_' . $payment->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    
                    Log::info('Processing payment photo update (keeping old photo)', [
                        'payment_id' => $payment->id,
                        'original_file_name' => $file->getClientOriginalName(),
                        'generated_file_name' => $fileName,
                        'file_size' => $file->getSize(),
                        'old_photo' => $payment->photo_pembayaran
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
            
                    // Upload new photo to Cloudinary (old photo remains)
                    $upload = new UploadApi();
                    $result = $upload->upload($file->getRealPath(), [
                        'folder' => 'payment-photos', // Organize in payment-photos folder
                        'public_id' => pathinfo($fileName, PATHINFO_FILENAME), // Remove extension from public_id
                        'quality' => 'auto', // Automatic quality optimization
                        'fetch_format' => 'auto', // Automatic format optimization
                        'transformation' => [
                            'width' => 1200,
                            'height' => 1200,
                            'crop' => 'limit', // Don't upscale, only downscale if needed
                            'quality' => 'auto'
                        ]
                    ]);
            
                    $photoPath = $result['secure_url'];
                    
                    Log::info('Payment photo updated successfully (old photo preserved)', [
                        'payment_id' => $payment->id,
                        'new_photo_path' => $photoPath,
                        'cloudinary_public_id' => $result['public_id'] ?? null,
                        'old_photo_preserved' => $payment->photo_pembayaran
                    ]);
            
                } catch (\Exception $e) {
                    Log::error('Failed to update payment photo', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                        'file_name' => $file->getClientOriginalName()
                    ]);
                    return back()->withInput()->with('error', 'Failed to upload payment photo: ' . $e->getMessage());
                }
            }

            // Update payment with photo and status
            $payment->update([
                'photo_pembayaran' => $photoPath,
                'status' => 'sudah_dibayar',
                'tanggal_pembayaran' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil diupload dan status diperbarui'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupload bukti pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(Payment $payment)
    {
        if ($payment->status === 'sudah_dibayar') {
            return redirect()->back()
                ->with('info', 'Pembayaran sudah ditandai sebagai dibayar.');
        }

        $payment->update([
            'status' => 'sudah_dibayar',
            'tanggal_pembayaran' => now(),
        ]);

        return redirect()->route('pembayaran.show', $payment->id)
            ->with('success', 'Pembayaran berhasil ditandai sebagai sudah dibayar.');
    }

    /**
     * Cancel payment
     */
    public function cancel(Request $request, Payment $payment)
    {
        if ($payment->status === 'sudah_dibayar') {
            return redirect()->back()
                ->with('error', 'Pembayaran yang sudah dibayar tidak dapat dibatalkan.');
        }
    
        // Validate revision reason
        $validated = $request->validate([
            'alasan_revisi' => 'required|string|max:1000',
        ], [
            'alasan_revisi.required' => 'Alasan revisi harus diisi.',
            'alasan_revisi.max' => 'Alasan revisi maksimal 1000 karakter.',
        ]);
    
        $payment->update([
            'status' => 'revisi',
            'alasan_revisi' => $validated['alasan_revisi'],
            'tanggal_pembayaran' => null,
        ]);
    
        // Handle AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil direvisi.'
            ]);
        }
    
        return redirect()->route('pembayaran.show', $payment->id)
            ->with('success', 'Pembayaran berhasil direvisi.');
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment)
    {
        // Prevent deleting paid payments
        if ($payment->status === 'paid') {
            return redirect()->route('payments.index')
                ->with('error', 'Pembayaran yang sudah dibayar tidak dapat dihapus.');
        }
        
        // Delete associated files
        if ($payment->file_invoice && Storage::disk('public')->exists($payment->file_invoice)) {
            Storage::disk('public')->delete($payment->file_invoice);
        }
        
        if ($payment->photo_pembayaran && Storage::disk('public')->exists($payment->photo_pembayaran)) {
            Storage::disk('public')->delete($payment->photo_pembayaran);
        }
        
        $payment->delete();
        
        return redirect()->route('pembayaran.index')
            ->with('success', 'Data pembayaran berhasil dihapus.');
    }

    /**
     * Download invoice file from Cloudinary
     */
    public function downloadInvoice(Payment $payment)
    {
        if (!$payment->file_invoice) {
            return redirect()->back()
                ->with('error', 'File invoice tidak ditemukan.');
        }
    
        // Check if it's a Cloudinary URL
        if (!str_contains($payment->file_invoice, 'cloudinary.com')) {
            return redirect()->back()
                ->with('error', 'File invoice tidak valid.');
        }
    
        try {
            // Method 1: Direct redirect to Cloudinary URL (simplest)
            return redirect($payment->file_invoice);
            
            // Method 2: Download through Laravel (uncomment if you prefer this approach)
            // return $this->downloadFromCloudinary(
            //     $payment->file_invoice, 
            //     'invoice_' . $payment->id . '_' . time()
            // );
            
        } catch (\Exception $e) {
            Log::error('Invoice download failed', [
                'payment_id' => $payment->id,
                'file_url' => $payment->file_invoice,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Gagal mengunduh file invoice.');
        }
    }
    
    /**
     * Download payment photo from Cloudinary
     */
    public function downloadPaymentPhoto(Payment $payment)
    {
        if (!$payment->photo_pembayaran) {
            return redirect()->back()
                ->with('error', 'Bukti pembayaran tidak ditemukan.');
        }
    
        // Check if it's a Cloudinary URL
        if (!str_contains($payment->photo_pembayaran, 'cloudinary.com')) {
            return redirect()->back()
                ->with('error', 'Bukti pembayaran tidak valid.');
        }
    
        try {
            // Method 1: Direct redirect to Cloudinary URL (simplest)
            return redirect($payment->photo_pembayaran);
            
            // Method 2: Download through Laravel (uncomment if you prefer this approach)
            // return $this->downloadFromCloudinary(
            //     $payment->photo_pembayaran, 
            //     'payment_photo_' . $payment->id . '_' . time() . '.jpg'
            // );
            
        } catch (\Exception $e) {
            Log::error('Payment photo download failed', [
                'payment_id' => $payment->id,
                'photo_url' => $payment->photo_pembayaran,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Gagal mengunduh bukti pembayaran.');
        }
    }
    
    /**
     * Helper method to download file from Cloudinary through Laravel
     * Use this if you want more control over the download process
     */
    private function downloadFromCloudinary($cloudinaryUrl, $suggestedFileName = null)
    {
        try {
            // Get file info from URL
            $pathInfo = pathinfo(parse_url($cloudinaryUrl, PHP_URL_PATH));
            $extension = $pathInfo['extension'] ?? '';
            $originalName = $pathInfo['filename'] ?? 'file';
            
            // Generate filename
            $fileName = $suggestedFileName ?? ($originalName . '.' . $extension);
            if (!pathinfo($fileName, PATHINFO_EXTENSION) && $extension) {
                $fileName .= '.' . $extension;
            }
            
            // Determine content type
            $contentType = $this->getContentType($extension);
            
            // Create streamed response to download file from Cloudinary
            return new StreamedResponse(function() use ($cloudinaryUrl) {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 60, // 60 seconds timeout
                        'user_agent' => 'Laravel App'
                    ]
                ]);
                
                $handle = fopen($cloudinaryUrl, 'rb', false, $context);
                
                if ($handle === false) {
                    throw new \Exception('Failed to open file from Cloudinary');
                }
                
                while (!feof($handle)) {
                    echo fread($handle, 8192); // Read in 8KB chunks
                    flush();
                }
                
                fclose($handle);
            }, 200, [
                'Content-Type' => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cloudinary download failed', [
                'url' => $cloudinaryUrl,
                'suggested_filename' => $suggestedFileName,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Failed to download file from Cloudinary: ' . $e->getMessage());
        }
    }
    
    /**
     * Get content type based on file extension
     */
    private function getContentType($extension)
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];
        
        return $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
    }

    /**
     * Update overdue payments status
     */
    private function updateOverduePayments()
    {
        Payment::where('status', 'pending')
            ->where('jatuh_tempo', '<', now())
            ->update(['status' => 'overdue']);
    }

    /**
     * Export payments to Excel
     */
    public function export(Request $request)
    {
        // Implementation for exporting payments data
        // This would typically use Laravel Excel package
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
}