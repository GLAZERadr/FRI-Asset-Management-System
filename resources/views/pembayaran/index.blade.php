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
                
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Hapus Filter
                    </button>
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
                <a href="{{ route('pembayaran.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
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
                            @php
                                $statusClasses = [
                                    'belum_dibayar' => 'bg-red-100 text-red-800',
                                    'sudah_dibayar' => 'bg-green-100 text-green-800',
                                    'terlambat' => 'bg-red-100 text-red-800',
                                    'dibatalkan' => 'bg-gray-100 text-gray-800'
                                ];
                                $class = $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-800';
                                
                                $statusLabels = [
                                    'belum_dibayar' => 'Belum dibayar',
                                    'sudah_dibayar' => 'Sudah dibayar',
                                    'terlambat' => 'Terlambat',
                                    'dibatalkan' => 'Dibatalkan'
                                ];
                                $statusText = $statusLabels[$payment->status] ?? $payment->status;
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $class }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                @if($payment->status == 'belum_dibayar' && Auth::user()->hasAnyRole(['kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                                <form action="{{ route('pembayaran.mark-paid', $payment) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-green-600 hover:text-green-900" title="Tandai Sudah Dibayar" onclick="return confirm('Tandai pembayaran ini sebagai sudah dibayar?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                                
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit filter form when filters change
    document.getElementById('tipe_pembayaran').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
    
    document.getElementById('status').addEventListener('change', function() {
        document.getElementById('filter-form').submit();
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
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