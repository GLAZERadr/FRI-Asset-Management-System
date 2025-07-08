<!-- resources/views/payments/index.blade.php -->
@extends('layouts.app')
@section('header', 'Manajemen Aset')
@section('content')
<div class="container mx-auto">
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('pembayaran.index') }}" method="GET" id="filter-form">
            <div class="grid grid-cols-4 gap-6 items-end">
                <div>
                    <label for="tipe_pembayaran" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tipe</label>
                    <select id="tipe_pembayaran" name="tipe_pembayaran" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Tipe</option>
                        @foreach($paymentTypes as $key => $type)
                            <option value="{{ $key }}" {{ request('tipe_pembayaran') == $key ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative">
                    <label for="vendor" class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Vendor
                        <span class="text-xs text-gray-500">(ketik atau pilih)</span>
                    </label>
                    
                    <div class="relative">
                        <input type="text" 
                            id="vendor" 
                            name="vendor" 
                            value="{{ request('vendor') }}"
                            placeholder="Ketik atau pilih vendor..."
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                            autocomplete="on">
                        
                        <button type="button" 
                                id="vendor-dropdown-btn"
                                class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600">
                            <svg id="vendor-dropdown-arrow" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div id="vendor-dropdown" 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-auto">
                            <div class="py-1">
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer border-b vendor-option" 
                                    data-vendor="">
                                    <span class="font-medium">Semua vendor</span>
                                    <span class="text-gray-500 text-xs block">Tampilkan semua data</span>
                                </div>
                                @foreach($vendors as $vendor)
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer vendor-option" 
                                    data-vendor="{{ $vendor }}">
                                    {{ $vendor }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Pilih Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $key => $statusLabel)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter
                    </button>
                    
                    <a href="{{ route('pembayaran.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Content Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Header -->
        @if(Auth::user()->hasRole(['staff_logistik', 'staff_laboratorium']))
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Daftar Pembayaran Perbaikan</h3>
                <a href="{{ route('pembayaran.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Tambah Pembayaran
                </a>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-4 mx-6 mt-4 rounded-md">
                {{ session('error') }}
            </div>
        @endif

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No Invoice
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jatuh Tempo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vendor
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Tagihan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            File Invoice
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tipe
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $payment->no_invoice }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->jatuh_tempo->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->vendor }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($payment->total_tagihan, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($payment->file_invoice)
                                <a href="{{ route('pembayaran.download-invoice', $payment) }}" class="text-blue-600 hover:text-blue-900">
                                    {{ basename($payment->file_invoice) }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->getTipeLabel() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="mt-1">
                                @php
                                    $statusClasses = [
                                        'belum_dibayar' => 'bg-yellow-100 text-yellow-800',
                                        'sudah_dibayar' => 'bg-green-100 text-green-800',
                                        'menunggu_verifikasi' =>  'bg-yellow-100 text-yellow-800',
                                        'terlambat' => 'bg-red-100 text-red-800',
                                        'dibatalkan' => 'bg-gray-100 text-gray-800',
                                        'revisi' => 'bg-orange-100 text-orange-800'
                                    ];

                                    $statusLabels = [
                                        'belum_dibayar' => 'Belum dibayar',
                                        'sudah_dibayar' => 'Sudah dibayar',
                                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                        'terlambat' => 'Terlambat',
                                        'dibatalkan' => 'Dibatalkan',
                                        'revisi' => 'Revisi'
                                    ];
                                    $statusText = $statusLabels[$payment->status] ?? $payment->status;
                                    $class = $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $class }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                @can('delete_payment')
                                    @if($payment->status != 'sudah_dibayar')
                                    <form action="{{ route('pembayaran.destroy', $payment) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pembayaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                @endcan
                                
                                <a href="{{ route('pembayaran.show', $payment) }}" class="text-gray-500 hover:text-gray-700" title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-gray-500 text-lg font-medium">Tidak Ada Data Pembayaran</p>
                                <p class="text-gray-400 text-sm mt-1">Belum ada pembayaran yang dibuat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payments->hasPages())
        <div class="px-6 py-3 border-t flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @if($payments->onFirstPage())
                    <span class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400 cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $payments->previousPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">Previous</a>
                @endif
                
                <span class="px-3 py-1 bg-blue-500 text-white rounded text-sm">{{ $payments->currentPage() }}</span>
                
                @if($payments->hasMorePages())
                    <a href="{{ $payments->nextPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">Next</a>
                @else
                    <span class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400 cursor-not-allowed">Next</span>
                @endif
            </div>
            <a href="{{ route('pengajuan.index') }}" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Back</a>
        </div>
        @endif
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit filter form when filters change
    document.getElementById('tipe_pembayaran').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    document.getElementById('status').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    // === VENDOR DROPDOWN FUNCTIONALITY ===
    const vendorInput = document.getElementById('vendor');
    const vendorDropdownBtn = document.getElementById('vendor-dropdown-btn');
    const vendorDropdown = document.getElementById('vendor-dropdown');
    const vendorOptions = document.querySelectorAll('.vendor-option');
    const vendorDropdownArrow = document.getElementById('vendor-dropdown-arrow');
    let isvendorOpen = false;

    function togglevendorDropdown() {
        isvendorOpen = !isvendorOpen;
        vendorDropdown.classList.toggle('hidden', !isvendorOpen);
        vendorDropdownArrow.style.transform = isvendorOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    function closevendorDropdown() {
        isvendorOpen = false;
        vendorDropdown.classList.add('hidden');
        vendorDropdownArrow.style.transform = 'rotate(0deg)';
    }

    function filtervendorOptions() {
        const search = vendorInput.value.toLowerCase();
        vendorOptions.forEach(option => {
            const value = option.getAttribute('data-vendor').toLowerCase();
            const text = option.textContent.toLowerCase();
            option.style.display = (text.includes(search) || value.includes(search)) ? 'block' : 'none';
        });
    }

    function selectvendor(value) {
        vendorInput.value = value;
        closevendorDropdown();
    }

    vendorDropdownBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        togglevendorDropdown();
    });

    vendorInput.addEventListener('input', function () {
        if (isvendorOpen) filtervendorOptions();
    });

    vendorInput.addEventListener('focus', function () {
        if (!isvendorOpen) togglevendorDropdown();
    });

    vendorInput.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!isvendorOpen) togglevendorDropdown();
        } else if (e.key === 'Escape') {
            closevendorDropdown();
        }
    });

    vendorOptions.forEach(option => {
        option.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            selectvendor(this.getAttribute('data-vendor'));
        });
    });

    document.addEventListener('click', function (e) {
        if (!vendorDropdown.contains(e.target) &&
            !vendorInput.contains(e.target) &&
            !vendorDropdownBtn.contains(e.target)) {
            closevendorDropdown();
        }
    });

    // === STATUS UPDATE FUNCTIONALITY ===
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
    let currentPaymentId = null;

    // Handle status change for all select elements
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function(e) {
            const selectedStatus = this.value;
            const form = this.closest('.status-form');
            const paymentId = form.getAttribute('data-payment-id');
            const currentStatus = this.getAttribute('data-current-status');
            
            if (selectedStatus === 'sudah_dibayar') {
                e.preventDefault();
                // Reset the select to previous value temporarily
                this.value = currentStatus;
                
                // Show photo upload modal
                showPaymentPhotoModal(paymentId);
            } else {
                // For other statuses, submit form via AJAX
                submitStatusChange(form, selectedStatus);
            }
        });
    });

    // Submit status change via AJAX
    function submitStatusChange(form, status) {
        const formData = new FormData(form);
        formData.set('status', status);
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating status');
        });
    }

    // Show Payment Photo Modal
    function showPaymentPhotoModal(paymentId) {
        currentPaymentId = paymentId;
        paymentIdInput.value = paymentId;
        resetPaymentModal();
        paymentModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Close Modal
    function closePaymentModal() {
        paymentModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        resetPaymentModal();
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

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !paymentModal.classList.contains('hidden')) {
            closePaymentModal();
        }
    });

    // Optional: Auto-hide alerts
    const alerts = document.querySelectorAll('.alert-message');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});
</script>
@endsection