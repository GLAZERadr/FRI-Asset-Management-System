<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
            $file = $request->file('file_invoice');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('invoices', $fileName, 'public');
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
            // Delete old file
            if ($payment->file_invoice && Storage::disk('public')->exists($payment->file_invoice)) {
                Storage::disk('public')->delete($payment->file_invoice);
            }

            $file = $request->file('file_invoice');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $validated['file_invoice'] = $file->storeAs('invoices', $fileName, 'public');
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
    public function cancel(Payment $payment)
    {
        if ($payment->status === 'sudah_dibayar') {
            return redirect()->back()
                ->with('error', 'Pembayaran yang sudah dibayar tidak dapat dibatalkan.');
        }
    
        $payment->update([
            'status' => 'dibatalkan',
            'tanggal_pembayaran' => null,
        ]);
    
        return redirect()->route('pembayaran.show', $payment->id)
            ->with('success', 'Pembayaran berhasil dibatalkan.');
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

        // Delete associated file
        if ($payment->file_invoice && Storage::disk('public')->exists($payment->file_invoice)) {
            Storage::disk('public')->delete($payment->file_invoice);
        }

        $payment->delete();

        return redirect()->route('pembayaran.index')
            ->with('success', 'Data pembayaran berhasil dihapus.');
    }

    /**
     * Download invoice file
     */
    public function downloadInvoice(Payment $payment)
    {
        if (!$payment->file_invoice || !Storage::disk('public')->exists($payment->file_invoice)) {
            return redirect()->back()
                ->with('error', 'File invoice tidak ditemukan.');
        }
    
        return Storage::disk('public')->download($payment->file_invoice);
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