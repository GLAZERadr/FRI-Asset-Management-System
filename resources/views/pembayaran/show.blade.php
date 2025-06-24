@extends('layouts.app')
@section('header', 'Detail Pembayaran')
@section('content')
<div class="container mx-auto max-w-6xl">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6 border-b pb-4">
                <h3 class="text-xl font-semibold text-gray-900">Detail Pembayaran #{{ $payment->no_invoice }}</h3>
            </div>

            <!-- Payment Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Left Column - Payment Information -->
                <div class="space-y-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Informasi Pembayaran</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">No Invoice</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $payment->no_invoice }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Vendor</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->vendor }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Tagihan</label>
                                <p class="mt-1 text-lg font-semibold text-green-600">{{ $payment->getFormattedTotalTagihanAttribute() }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jatuh Tempo</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->jatuh_tempo->format('d/m/Y') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipe Pembayaran</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->getTipeLabel() }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jenis Pembayaran</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->getJenisLabel() }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    @php
                                        $statusClasses = [
                                            'belum_dibayar' => 'bg-yellow-100 text-yellow-800',
                                            'sudah_dibayar' => 'bg-green-100 text-green-800',
                                            'terlambat' => 'bg-red-100 text-red-800',
                                            'dibatalkan' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $class = $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-800';
                                        $statusText = $payment->getStatusLabel();
                                    @endphp
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $class }}">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>

                            @if($payment->tanggal_pembayaran)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Pembayaran</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $payment->tanggal_pembayaran->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column - Files -->
                <div class="space-y-6">
                    <!-- Invoice File -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">File Invoice</h4>
                        @if($payment->file_invoice)
                            <div class="border rounded-lg p-4 bg-white">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $invoiceExtension = pathinfo($payment->file_invoice, PATHINFO_EXTENSION);
                                        $isInvoiceImage = in_array(strtolower($invoiceExtension), ['jpg', 'jpeg', 'png', 'gif']);
                                    @endphp
                                    
                                    @if($isInvoiceImage)
                                        <img src="{{ Storage::url($payment->file_invoice) }}" 
                                             alt="Invoice" 
                                             class="w-16 h-16 object-cover rounded border cursor-pointer"
                                             onclick="openImageModal('{{ Storage::url($payment->file_invoice) }}', 'Invoice')">
                                    @else
                                        <svg class="w-12 h-12 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                    
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ basename($payment->file_invoice) }}</p>
                                        <p class="text-xs text-gray-500">File Invoice ({{ strtoupper($invoiceExtension) }})</p>
                                    </div>
                                    <a href="{{ route('pembayaran.download-invoice', $payment) }}" 
                                       class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-blue-600 hover:text-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm">Tidak ada file invoice</p>
                            </div>
                        @endif
                    </div>

                    <!-- Payment Photo -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Bukti Pembayaran</h4>
                        @if($payment->photo_pembayaran)
                            <div class="border rounded-lg p-4 bg-white">
                                @php
                                    $photoExtension = pathinfo($payment->photo_pembayaran, PATHINFO_EXTENSION);
                                    $isPhotoImage = in_array(strtolower($photoExtension), ['jpg', 'jpeg', 'png', 'gif']);
                                @endphp
                                
                                @if($isPhotoImage)
                                    <!-- Image Preview -->
                                    <div class="space-y-3">
                                        <img src="{{ Storage::url($payment->photo_pembayaran) }}" 
                                             alt="Bukti Pembayaran" 
                                             class="w-full h-48 object-cover rounded border cursor-pointer hover:opacity-90 transition-opacity"
                                             onclick="openImageModal('{{ Storage::url($payment->photo_pembayaran) }}', 'Bukti Pembayaran')">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ basename($payment->photo_pembayaran) }}</p>
                                                <p class="text-xs text-gray-500">Bukti Pembayaran</p>
                                            </div>
                                            <a href="{{ route('pembayaran.download-payment-photo', $payment) }}" 
                                               class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-green-600 hover:text-green-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <!-- PDF or other file -->
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-12 h-12 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ basename($payment->photo_pembayaran) }}</p>
                                            <p class="text-xs text-gray-500">Bukti Pembayaran ({{ strtoupper($photoExtension) }})</p>
                                        </div>
                                        <a href="{{ route('pembayaran.download-payment-photo', $payment) }}" 
                                           class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-green-600 hover:text-green-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm">Belum ada bukti pembayaran</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Audit Trail -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Audit</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dibuat Pada</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Diperbarui Pada</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->updated_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if($payment->updater)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Diperbarui Oleh</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->updater->name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Additional Notes -->
            @if($payment->alasan_revisi)
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Alasan Revisi</h3>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">{{ $payment->alasan_revisi }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="border-t pt-6 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                <a href="{{ route('pembayaran.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>

                <div class="flex flex-wrap space-x-2">
                    @if($payment->status === 'belum_dibayar' && Auth::user()->hasAnyRole(['staff_keuangan']))
                        <button type="button" onclick="showPaymentPhotoModal({{ $payment->id }})" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Bayar
                        </button>

                        <button type="button" onclick="showRevisionModal({{ $payment->id }})" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Revisi
                        </button>
                    @endif

                    @can('edit_payment')
                        @if($payment->status !== 'sudah_dibayar')
                            <a href="{{ route('pembayaran.edit', $payment) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center hidden z-50" onclick="closeImageModal()">
    <div class="relative max-w-4xl max-h-screen p-4" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-white text-lg font-medium"></h3>
            <button onclick="closeImageModal()" class="text-white hover:text-gray-300 text-2xl font-bold">
                ×
            </button>
        </div>
        <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded">
        <div class="flex justify-center mt-4">
            <button onclick="closeImageModal()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Payment Photo Upload Modal -->
<div id="paymentPhotoModal" class="fixed inset-0 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Upload Bukti Pembayaran</h3>
                <button type="button" id="closePaymentModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="mt-4">
                <form id="paymentPhotoForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="paymentId" name="payment_id" value="">
                    <input type="hidden" name="status" value="sudah_dibayar">
                    
                    <!-- Drag and Drop Area -->
                    <div id="paymentDropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors cursor-pointer">
                        <div id="paymentDropZoneContent">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="mt-4">
                                <p class="text-lg text-gray-600">Seret dan letakkan bukti pembayaran di sini</p>
                                <p class="text-sm text-gray-500 mt-1">atau</p>
                                <button type="button" id="selectPaymentFileBtn" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                                    Pilih File
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF, PDF hingga 5MB (Hanya 1 file)</p>
                        </div>
                    </div>
                    <!-- Hidden File Input -->
                    <input type="file" id="paymentFileInput" name="payment_photo" accept="image/*,application/pdf" class="hidden">
                    <!-- Preview Container -->
                    <div id="paymentPreviewContainer" class="mt-4 hidden">
                        <h4 class="text-md font-medium text-gray-700 mb-2">File yang akan diupload:</h4>
                        <div id="paymentPreview" class="border rounded-lg p-4"></div>
                    </div>
                    <!-- Upload Progress -->
                    <div id="paymentUploadProgress" class="mt-4 hidden">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div id="paymentProgressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="paymentProgressText" class="text-sm text-gray-600 mt-1">Uploading...</p>
                    </div>
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 border-t mt-6">
                        <button type="button" id="cancelPaymentBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 mr-2">
                            Batal
                        </button>
                        <button type="submit" id="uploadPaymentBtn" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50" disabled>
                            Upload & Konfirmasi Bayar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div id="revisionModal" class="fixed inset-0 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Revisi Pembayaran</h3>
                <button type="button" id="closeRevisionModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="mt-4">
                <form id="revisionForm">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="revisionPaymentId" name="payment_id" value="">
                    
                    <div class="mb-4">
                        <label for="alasanRevisi" class="block text-sm font-medium text-gray-700 mb-2">
                            Alasan Revisi <span class="text-red-500">*</span>
                        </label>
                        <textarea id="alasanRevisi" name="alasan_revisi" rows="4" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500" 
                                placeholder="Masukkan alasan revisi pembayaran..."
                                required></textarea>
                        <p class="text-xs text-gray-500 mt-1">Maksimal 1000 karakter</p>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Pembayaran akan harus dilakukan revisi dan dapat diperbaiki berdasarkan alasan revisi yang diberikan.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 border-t">
                        <button type="button" id="cancelRevisionBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 mr-2">
                            Batal
                        </button>
                        <button type="submit" id="submitRevisionBtn" class="px-6 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                            Kirim Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Image Modal Functions
function openImageModal(imageSrc, title) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Payment Photo Modal Functions
const paymentModal = document.getElementById('paymentPhotoModal');
const closePaymentModalBtn = document.getElementById('closePaymentModal');
const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
const selectPaymentFileBtn = document.getElementById('selectPaymentFileBtn');
const paymentFileInput = document.getElementById('paymentFileInput');
const paymentDropZone = document.getElementById('paymentDropZone');
const paymentPreviewContainer = document.getElementById('paymentPreviewContainer');
const paymentPreview = document.getElementById('paymentPreview');
const paymentPhotoForm = document.getElementById('paymentPhotoForm');
const uploadPaymentBtn = document.getElementById('uploadPaymentBtn');
const paymentIdInput = document.getElementById('paymentId');
const paymentUploadProgress = document.getElementById('paymentUploadProgress');
const paymentProgressBar = document.getElementById('paymentProgressBar');
const paymentProgressText = document.getElementById('paymentProgressText');

// Revision Modal Functions
const revisionModal = document.getElementById('revisionModal');
const closeRevisionModalBtn = document.getElementById('closeRevisionModal');
const cancelRevisionBtn = document.getElementById('cancelRevisionBtn');
const revisionForm = document.getElementById('revisionForm');
const revisionPaymentIdInput = document.getElementById('revisionPaymentId');
const alasanRevisiTextarea = document.getElementById('alasanRevisi');
const submitRevisionBtn = document.getElementById('submitRevisionBtn');

let selectedPaymentFile = null;
let currentPaymentId = null;

// Show Payment Photo Modal
function showPaymentPhotoModal(paymentId) {
    currentPaymentId = paymentId;
    paymentIdInput.value = paymentId;
    resetPaymentModal();
    paymentModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// Show Revision Modal
function showRevisionModal(paymentId) {
    currentPaymentId = paymentId;
    revisionPaymentIdInput.value = paymentId;
    alasanRevisiTextarea.value = '';
    revisionModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Focus on textarea
    setTimeout(() => alasanRevisiTextarea.focus(), 100);
}

// Close Payment Modal
function closePaymentModal() {
    paymentModal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    resetPaymentModal();
}

// Close Revision Modal
function closeRevisionModal() {
    revisionModal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    alasanRevisiTextarea.value = '';
}

// Reset Modal
function resetPaymentModal() {
    selectedPaymentFile = null;
    paymentPreviewContainer.classList.add('hidden');
    paymentPreview.innerHTML = '';
    uploadPaymentBtn.disabled = true;
    paymentUploadProgress.classList.add('hidden');
    paymentProgressBar.style.width = '0%';
    paymentFileInput.value = '';
}

// Event Listeners
closePaymentModalBtn.addEventListener('click', closePaymentModal);
cancelPaymentBtn.addEventListener('click', closePaymentModal);
selectPaymentFileBtn.addEventListener('click', () => paymentFileInput.click());

// Revision Modal Event Listeners
closeRevisionModalBtn.addEventListener('click', closeRevisionModal);
cancelRevisionBtn.addEventListener('click', closeRevisionModal);

// File Input Change
paymentFileInput.addEventListener('change', function(e) {
    handlePaymentFile(e.target.files[0]);
});

// Drag and Drop
paymentDropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('border-blue-400', 'bg-blue-50');
});

paymentDropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('border-blue-400', 'bg-blue-50');
});

paymentDropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('border-blue-400', 'bg-blue-50');
    handlePaymentFile(e.dataTransfer.files[0]);
});

// Handle File
function handlePaymentFile(file) {
    if (!file) return;
    
    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        alert('File harus berupa gambar (PNG, JPG, GIF) atau PDF');
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran file maksimal 5MB');
        return;
    }
    
    selectedPaymentFile = file;
    showPaymentPreview();
    updatePaymentUploadBtn();
}

// Show Preview
function showPaymentPreview() {
    if (!selectedPaymentFile) {
        paymentPreviewContainer.classList.add('hidden');
        return;
    }
    
    paymentPreviewContainer.classList.remove('hidden');
    
    if (selectedPaymentFile.type === 'application/pdf') {
        paymentPreview.innerHTML = `
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded">
                <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-medium text-gray-900">${selectedPaymentFile.name}</p>
                    <p class="text-sm text-gray-500">${(selectedPaymentFile.size / 1024 / 1024).toFixed(2)} MB</p>
                </div>
                <button type="button" onclick="removePaymentFile()" class="text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        `;
    } else {
        const reader = new FileReader();
        reader.onload = function(e) {
            paymentPreview.innerHTML = `
                <div class="relative">
                    <img src="${e.target.result}" class="w-full h-48 object-cover rounded-lg border">
                    <button type="button" onclick="removePaymentFile()" 
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                        ×
                    </button>
                    <p class="text-sm text-gray-600 mt-2">${selectedPaymentFile.name}</p>
                    <p class="text-xs text-gray-500">${(selectedPaymentFile.size / 1024 / 1024).toFixed(2)} MB</p>
                </div>
            `;
        };
        reader.readAsDataURL(selectedPaymentFile);
    }
}

// Remove File
function removePaymentFile() {
    selectedPaymentFile = null;
    paymentFileInput.value = '';
    showPaymentPreview();
    updatePaymentUploadBtn();
}

// Update Upload Button
function updatePaymentUploadBtn() {
    uploadPaymentBtn.disabled = !selectedPaymentFile;
}

// Form Submit
paymentPhotoForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!selectedPaymentFile) {
        alert('Pilih file bukti pembayaran untuk diupload');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('status', 'sudah_dibayar');
    formData.append('payment_photo', selectedPaymentFile);
    
    // Show progress
    paymentUploadProgress.classList.remove('hidden');
    uploadPaymentBtn.disabled = true;
    
    // Upload with XMLHttpRequest for progress tracking
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            paymentProgressBar.style.width = percentComplete + '%';
            paymentProgressText.textContent = `Uploading... ${Math.round(percentComplete)}%`;
        }
    });
    
    xhr.addEventListener('load', function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                closePaymentModal();
                location.reload(); // Refresh page to show updated status
            } else {
                alert('Error: ' + response.message);
                uploadPaymentBtn.disabled = false;
                paymentUploadProgress.classList.add('hidden');
            }
        } else {
            alert('Upload failed. Please try again.');
            uploadPaymentBtn.disabled = false;
            paymentUploadProgress.classList.add('hidden');
        }
    });
    
    xhr.addEventListener('error', function() {
        alert('Upload failed. Please check your connection.');
        uploadPaymentBtn.disabled = false;
        paymentUploadProgress.classList.add('hidden');
    });
    
    xhr.open('POST', `/pembayaran/${currentPaymentId}/update-photo`);
    xhr.send(formData);
});

// Revision Form Submit
revisionForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const alasanRevisi = alasanRevisiTextarea.value.trim();
    if (!alasanRevisi) {
        alert('Alasan revisi harus diisi');
        alasanRevisiTextarea.focus();
        return;
    }
    
    if (alasanRevisi.length > 1000) {
        alert('Alasan revisi maksimal 1000 karakter');
        alasanRevisiTextarea.focus();
        return;
    }
    
    // Disable submit button
    submitRevisionBtn.disabled = true;
    submitRevisionBtn.textContent = 'Mengirim...';
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('_method', 'PATCH');
    formData.append('alasan_revisi', alasanRevisi);
    
    fetch(`/pembayaran/${currentPaymentId}/cancel`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeRevisionModal();
            location.reload(); // Refresh page to show updated status
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
            submitRevisionBtn.disabled = false;
            submitRevisionBtn.textContent = 'Kirim Revisi';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengirim revisi');
        submitRevisionBtn.disabled = false;
        submitRevisionBtn.textContent = 'Kirim Revisi';
    });
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!paymentModal.classList.contains('hidden')) {
            closePaymentModal();
        } else if (!revisionModal.classList.contains('hidden')) {
            closeRevisionModal();
        } else {
            closeImageModal();
        }
    }
});
</script>
@endsection