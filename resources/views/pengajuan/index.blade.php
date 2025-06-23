<!-- pengajuan/index.blade.php -->
@extends('layouts.app')
@section('header', 'Manajemen Pengajuan Perbaikan')
@section('content')
<div class="container mx-auto">
    <!-- Date Filter Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('pengajuan.index') }}" method="GET" class="grid grid-cols-4 gap-6">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div class="relative">
                <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">
                    Lokasi
                    <span class="text-xs text-gray-500">(ketik atau pilih)</span>
                </label>
                
                <div class="relative">
                    <input type="text" 
                        id="lokasi" 
                        name="lokasi" 
                        value="{{ request('lokasi') }}"
                        placeholder="Ketik atau pilih lokasi..."
                        class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                        autocomplete="on">
                    
                    <!-- Dropdown trigger button -->
                    <button type="button" 
                            id="lokasi-dropdown-btn"
                            class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600">
                        <svg id="dropdown-arrow" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Dropdown options -->
                    <div id="lokasi-dropdown" 
                        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-auto">
                        <div class="py-1">
                            <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer border-b location-option" 
                                data-location="">
                                <span class="font-medium">Semua Lokasi</span>
                                <span class="text-gray-500 text-xs block">Tampilkan semua data</span>
                            </div>
                            @foreach($locations as $location)
                            <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer location-option" 
                                data-location="{{ $location }}">
                                {{ $location }}
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                
                <a href="{{ route('pengajuan.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Enhanced Stats Cards - Role-based display -->
    @if(Auth::user()->hasRole('wakil_dekan_2'))
        <div class="grid grid-cols-3 gap-6 mb-6">
            <!-- Highest Repair Cost -->
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Biaya Perbaikan Tertinggi</p>
                    <p class="text-2xl font-bold text-red-600">Rp{{ number_format($stats['highest_repair_cost'] ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">{{ $stats['highest_cost_asset'] ?? '-' }}</p>
                </div>
            </div>
            
            <!-- Repair Requests by Department -->
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Pengajuan Perbaikan</p>
                    <div class="flex flex-col">
                        <span class="text-sm text-gray-600">Laboratorium ({{ $stats['lab_requests'] ?? 0 }})</span>
                        <span class="text-sm text-gray-600">Logistik dan SDM ({{ $stats['logistic_requests'] ?? 0 }})</span>
                    </div>
                </div>
            </div>
            
            <!-- Repair Status Overview -->
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Status Perbaikan</p>
                    <div class="grid grid-cols-2 gap-1 text-xs">
                        <span class="text-green-600">Selesai ({{ $stats['completed'] ?? 0 }})</span>
                        <span class="text-yellow-600">Dikerjakan ({{ $stats['in_progress'] ?? 0 }})</span>
                        <span class="text-blue-600">Diterima ({{ $stats['received'] ?? 0 }})</span>
                        <span class="text-red-600">Ditolak ({{ $stats['rejected'] ?? 0 }})</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-4 gap-6 mb-6">
            <!-- Original Stats Cards for other roles -->
            <!-- Completed Tasks -->
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Selesai</p>
                    <p class="text-2xl font-bold">{{ $stats['completed'] ?? 0 }}</p>
                </div>
            </div>
            
            <!-- In Progress -->
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Dikerjakan</p>
                    <p class="text-2xl font-bold">{{ $stats['in_progress'] ?? 0 }}</p>
                </div>
            </div>
            
            <!-- Received -->
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l-4-4m4 4l4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Diterima</p>
                    <p class="text-2xl font-bold">{{ $stats['received'] ?? 0 }}</p>
                </div>
            </div>
            
            <!-- Total Expenditure -->
            <div class="bg-white rounded-lg shadow p-6 flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Pengeluaran</p>
                    <p class="text-2xl font-bold text-purple-600">Rp {{ number_format($stats['total_expenditure'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Enhanced Maintenance Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Table Header with Actions -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                Daftar Perbaikan Aset
                <a href="{{ route('pengajuan.detailed') }}" class="ml-2 text-blue-600 hover:text-blue-800 transition-colors duration-150" title="Lihat Detail">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </h3>
        </div>
        
        <x-table>
            <x-slot name="thead">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Perbaikan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama Aset
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Lokasi
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Pengajuan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Teknisi
                    </th>
                </tr>
            </x-slot>
            
            @forelse ($maintenanceRequests as $request)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $request->maintenance_id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->asset->nama_asset }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->asset->lokasi }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @php
                        $status = $request->status;
                        $statusClasses = [
                            'Menunggu Persetujuan' => 'bg-yellow-100 text-yellow-800',
                            'Diterima' => 'bg-blue-100 text-blue-800',
                            'Dikerjakan' => 'bg-gray-100 text-gray-800', 
                            'Selesai' => 'bg-green-100 text-green-800',
                            'Ditolak' => 'bg-red-100 text-red-800'
                        ];
                        $class = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $class }}">
                        {{ $status }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->tanggal_pengajuan ? $request->tanggal_pengajuan->format('d/m/Y') : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->teknisi ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ Auth::user()->canApprove() ? '8' : '7' }}" class="px-6 py-10 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 text-lg font-medium">Tidak Ada Data Pengajuan</p>
                        <p class="text-gray-400 text-sm mt-1">Belum ada pengajuan perbaikan yang dibuat.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </x-table>
        
        <!-- Pagination -->
        @if($maintenanceRequests->hasPages())
        <div class="px-6 py-3 border-t flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @if($maintenanceRequests->onFirstPage())
                    <span class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400 cursor-not-allowed">Previous</span>
                @else
                    <a href="{{ $maintenanceRequests->appends(request()->query())->previousPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">Previous</a>
                @endif
                
                <span class="px-3 py-1 bg-blue-500 text-white rounded text-sm">{{ $maintenanceRequests->currentPage() }}</span>
                
                @if($maintenanceRequests->hasMorePages())
                    <a href="{{ $maintenanceRequests->appends(request()->query())->nextPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">Next</a>
                @else
                    <span class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400 cursor-not-allowed">Next</span>
                @endif
            </div>
            
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-700">
                    Menampilkan {{ $maintenanceRequests->firstItem() ?? 0 }} - {{ $maintenanceRequests->lastItem() ?? 0 }} 
                    dari {{ $maintenanceRequests->total() }} data
                </span>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Pengajuan page loaded');
    
    // Location filter functionality
    const lokasiInput = document.getElementById('lokasi');
    const dropdownBtn = document.getElementById('lokasi-dropdown-btn');
    const dropdown = document.getElementById('lokasi-dropdown');
    const dropdownArrow = document.getElementById('dropdown-arrow');
    const locationOptions = document.querySelectorAll('.location-option');
    
    let isDropdownOpen = false;

    // Toggle dropdown
    function toggleDropdown() {
        isDropdownOpen = !isDropdownOpen;
        dropdown.classList.toggle('hidden', !isDropdownOpen);
        dropdownArrow.style.transform = isDropdownOpen ? 'rotate(180deg)' : 'rotate(0deg)';
        
        if (isDropdownOpen) {
            filterOptions();
        }
    }

    // Filter options based on input
    function filterOptions() {
        const searchValue = lokasiInput.value.toLowerCase();
        
        locationOptions.forEach(option => {
            const locationValue = option.getAttribute('data-location').toLowerCase();
            const locationText = option.textContent.toLowerCase();
            
            if (searchValue === '' || locationText.includes(searchValue) || locationValue.includes(searchValue)) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
    }

    // Select location
    function selectLocation(locationValue) {
        lokasiInput.value = locationValue;
        closeDropdown();
    }

    // Close dropdown
    function closeDropdown() {
        isDropdownOpen = false;
        dropdown.classList.add('hidden');
        dropdownArrow.style.transform = 'rotate(0deg)';
    }

    // Event listeners for location filter
    if (dropdownBtn) {
        dropdownBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown();
        });
    }

    if (lokasiInput) {
        lokasiInput.addEventListener('input', function() {
            if (isDropdownOpen) {
                filterOptions();
            }
        });

        lokasiInput.addEventListener('focus', function() {
            if (!isDropdownOpen) {
                toggleDropdown();
            }
        });

        lokasiInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!isDropdownOpen) {
                    toggleDropdown();
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });
    }

    // Location option clicks
    locationOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const locationValue = this.getAttribute('data-location');
            selectLocation(locationValue);
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (dropdown && !dropdown.contains(e.target) && 
            lokasiInput && !lokasiInput.contains(e.target) && 
            dropdownBtn && !dropdownBtn.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Auto-hide success/error messages after 5 seconds
    const alerts = document.querySelectorAll('.alert-message');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

@if(Auth::user()->canApprove())
function openApprovalModal(maintenanceId) {
    document.getElementById('maintenanceId').value = maintenanceId;
    document.getElementById('approvalModal').classList.remove('hidden');
}
function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('approvalForm').reset();
}
// Handle approval form submission
document.getElementById('approvalForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const maintenanceId = formData.get('maintenance_id');
    
    try {
        const response = await fetch(`/pengajuan/${maintenanceId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            alert(data.message);
            closeApprovalModal();
            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert(data.message || 'Terjadi kesalahan');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses persetujuan');
    }
});
// Close modal when clicking outside
document.getElementById('approvalModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApprovalModal();
    }
});
@endif
</script>
@endsection