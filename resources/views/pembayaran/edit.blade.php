@extends('layouts.app')
@section('header', 'Edit Pembayaran')
@section('content')
<div class="container mx-auto max-w-4xl">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            @if(session('error'))
                <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4">
                    {{ session('error') }}
                </div>
            @endif
            <form action="{{ route('pembayaran.update', $payment) }}" method="POST" enctype="multipart/form-data" id="editPaymentForm">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <x-form-input 
                                name="no_invoice" 
                                label="No Invoice" 
                                required 
                                value="{{ old('no_invoice', $payment->no_invoice) }}"
                                placeholder="Masukkan nomor invoice"
                            />
                        </div>
                        
                        <div>
                            <x-form-input 
                                name="jatuh_tempo" 
                                label="Jatuh Tempo" 
                                type="date" 
                                required 
                                value="{{ old('jatuh_tempo', $payment->jatuh_tempo->format('Y-m-d')) }}"
                            />
                        </div>
                        
                        <div>
                            <x-form-input 
                                name="vendor" 
                                label="Vendor" 
                                required 
                                value="{{ old('vendor', $payment->vendor) }}"
                                placeholder="Nama vendor/perusahaan"
                            />
                        </div>
                        
                        <div>
                            <x-form-input 
                                name="total_tagihan" 
                                label="Total Tagihan" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                value="{{ old('total_tagihan', $payment->total_tagihan) }}"
                                placeholder="0.00"
                            />
                            <p class="mt-1 text-xs text-gray-500">Masukkan dalam format angka (contoh: 1500000)</p>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label for="tipe_pembayaran" class="block text-sm font-medium text-gray-700">
                                Tipe Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select name="tipe_pembayaran" id="tipe_pembayaran" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 @error('tipe_pembayaran') border-red-300 @enderror" 
                                required>
                                <option value="">Pilih tipe pembayaran</option>
                                @foreach($paymentTypes as $key => $label)
                                    <option value="{{ $key }}" {{ (old('tipe_pembayaran', $payment->tipe_pembayaran) == $key) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipe_pembayaran')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="jenis_pembayaran" class="block text-sm font-medium text-gray-700">
                                Jenis Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_pembayaran" id="jenis_pembayaran" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 @error('jenis_pembayaran') border-red-300 @enderror" 
                                required>
                                <option value="">Pilih jenis pembayaran</option>
                                @foreach(\App\Models\Payment::JENIS_PEMBAYARAN as $key => $label)
                                    <option value="{{ $key }}" {{ old('jenis_pembayaran') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_pembayaran')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 @error('status') border-red-300 @enderror" 
                                required
                                data-current-status="{{ $payment->status }}">
                                <option value="">Pilih status</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" {{ (old('status', $payment->status) == $key) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="file_invoice" class="block text-sm font-medium text-gray-700">
                                File Invoice
                            </label>
                            @if($payment->file_invoice)
                                <div class="mt-1 mb-2 text-sm text-gray-600">
                                    File saat ini: 
                                    <a href="{{ route('pembayaran.download-invoice', $payment) }}" class="text-blue-600 hover:text-blue-900">
                                        {{ basename($payment->file_invoice) }}
                                    </a>
                                </div>
                            @endif
                            <input type="file" 
                                name="file_invoice" 
                                id="file_invoice" 
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('file_invoice') border-red-300 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Format yang didukung: PDF, JPG, PNG, DOC, DOCX (Max: 10MB). Kosongkan jika tidak ingin mengubah file.</p>
                            @error('file_invoice')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Conditional field for payment date -->
                        <div id="tanggal_pembayaran_field" style="{{ ($payment->status === 'sudah_dibayar') ? 'display: block;' : 'display: none;' }}">
                            <x-form-input 
                                name="tanggal_pembayaran" 
                                label="Tanggal Pembayaran" 
                                type="datetime-local"
                                value="{{ old('tanggal_pembayaran', $payment->tanggal_pembayaran ? $payment->tanggal_pembayaran->format('Y-m-d\TH:i') : '') }}"
                            />
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-between items-center border-t pt-6">
                    <a href="{{ route('pembayaran.show', $payment) }}" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        Batal
                    </a>
                    
                    <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Update Pembayaran
                    </button>
                </div>
            </form>
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
                    <input type="hidden" id="paymentId" name="payment_id" value="{{ $payment->id }}">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const tanggalPembayaranField = document.getElementById('tanggal_pembayaran_field');
    const editPaymentForm = document.getElementById('editPaymentForm');
    
    // Payment Photo Modal elements
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
    
    let selectedPaymentFile = null;
    let currentPaymentId = {{ $payment->id }};
    let isPhotoModalSubmission = false;

    function toggleTanggalPembayaran() {
        if (statusSelect.value === 'sudah_dibayar') {
            tanggalPembayaranField.style.display = 'block';
        } else {
            tanggalPembayaranField.style.display = 'none';
        }
    }
    
    // Initial check
    toggleTanggalPembayaran();
    
    // Handle status change
    statusSelect.addEventListener('change', function(e) {
        const selectedStatus = this.value;
        const currentStatus = this.getAttribute('data-current-status');
        
        toggleTanggalPembayaran();
        
        // If changing TO 'sudah_dibayar' status, show photo upload modal
        if (selectedStatus === 'sudah_dibayar' && currentStatus !== 'sudah_dibayar') {
            e.preventDefault();
            showPaymentPhotoModal();
        }
    });

    // Handle form submission
    editPaymentForm.addEventListener('submit', function(e) {
        const selectedStatus = statusSelect.value;
        const currentStatus = statusSelect.getAttribute('data-current-status');
        
        // If changing TO 'sudah_dibayar' and not coming from photo modal, prevent submission
        if (selectedStatus === 'sudah_dibayar' && currentStatus !== 'sudah_dibayar' && !isPhotoModalSubmission) {
            e.preventDefault();
            showPaymentPhotoModal();
            return false;
        }
    });

    // Show Payment Photo Modal
    function showPaymentPhotoModal() {
        paymentIdInput.value = currentPaymentId;
        resetPaymentModal();
        paymentModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close Modal
    function closePaymentModal() {
        paymentModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        resetPaymentModal();
        // Reset status select to previous value
        const currentStatus = statusSelect.getAttribute('data-current-status');
        statusSelect.value = currentStatus;
        toggleTanggalPembayaran();
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
        isPhotoModalSubmission = false;
    }

    // Event Listeners
    closePaymentModalBtn.addEventListener('click', closePaymentModal);
    cancelPaymentBtn.addEventListener('click', closePaymentModal);
    selectPaymentFileBtn.addEventListener('click', () => paymentFileInput.click());

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

    // Remove File (make it global)
    window.removePaymentFile = function() {
        selectedPaymentFile = null;
        paymentFileInput.value = '';
        showPaymentPreview();
        updatePaymentUploadBtn();
    };

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
                    // Close modal and update form
                    closePaymentModal();
                    
                    // Mark that this is a photo modal submission
                    isPhotoModalSubmission = true;
                    
                    // Update the status in the form
                    statusSelect.value = 'sudah_dibayar';
                    statusSelect.setAttribute('data-current-status', 'sudah_dibayar');
                    toggleTanggalPembayaran();
                    
                    // Auto-fill current date/time for tanggal_pembayaran if not set
                    const tanggalPembayaranInput = document.querySelector('input[name="tanggal_pembayaran"]');
                    if (tanggalPembayaranInput && !tanggalPembayaranInput.value) {
                        const now = new Date();
                        const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
                        tanggalPembayaranInput.value = localDateTime.toISOString().slice(0, 16);
                    }
                    
                    // Submit the main form
                    editPaymentForm.submit();
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

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !paymentModal.classList.contains('hidden')) {
            closePaymentModal();
        }
    });
});
</script>
@endsection