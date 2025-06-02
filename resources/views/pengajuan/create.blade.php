<!-- pengajuan/create.blade.php -->
@extends('layouts.app')
@section('header', 'Manajemen Aset')
@section('content')

@php
    // Define sort variables at the top level to avoid undefined variable errors
    $currentSort = request('sort');
    $currentDirection = request('direction');
@endphp

<div class="container mx-auto">
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('pengajuan.baru') }}" method="GET" id="filter-form">
            <!-- Preserve sort parameters -->
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('direction'))
                <input type="hidden" name="direction" value="{{ request('direction') }}">
            @endif
            
            <div class="grid grid-cols-3 gap-6 items-end">
                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">
                        @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                            Pilih Departemen
                        @else
                            Pilih Lokasi Aset
                        @endif
                    </label>
                    <select id="lokasi" name="lokasi" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                            <option value="">Semua Departemen</option>
                        @else
                            <option value="">Semua Lokasi</option>
                        @endif
                        @foreach($locations as $location)
                            <option value="{{ $location }}" {{ request('lokasi') == $location ? 'selected' : '' }}>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tingkat_kerusakan" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tingkat Kerusakan</label>
                    <select id="tingkat_kerusakan" name="tingkat_kerusakan" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Tingkat</option>
                        @foreach($tingkatKerusakanOptions as $tingkat)
                            <option value="{{ $tingkat }}" {{ request('tingkat_kerusakan') == $tingkat ? 'selected' : '' }}>
                                {{ $tingkat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <a href="{{ route('pengajuan.baru') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Hapus Filter
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Content Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
                <h3 class="text-lg font-medium text-gray-900">Langkah 1 : Pilih aset yang akan diajukan</h3>
                @if(!$maintenanceAssets->isEmpty())
                <button type="submit" form="maintenance-form" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    Submit
                </button>
                @endif
            @else
                <div class="flex items-center justify-between w-full">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Pengajuan Perbaikan</h3>
                    @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']) && !empty($priorityScores))
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Prioritas TOPSIS telah dihitung</span>
                        </div>
                        <a href="{{ route('kriteria.create') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            Ubah Kriteria AHP
                        </a>
                    </div>
                    @endif
                </div>
            @endif
        </div>

        @if($maintenanceAssets->isEmpty())
            <!-- Empty State -->
            <div class="px-6 py-16 text-center">
                <div class="flex flex-col items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-xl font-medium text-gray-900 mb-2">No Data Available</p>
                    @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
                        <p class="text-gray-500 mb-6">Tidak ada aset yang tersedia untuk diajukan perbaikan.</p>
                    @else
                        <p class="text-gray-500 mb-6">Tidak ada pengajuan yang memerlukan persetujuan Anda.</p>
                    @endif
                </div>
            </div>
        @else
            <!-- Data Table -->
            @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
                <form action="{{ route('pengajuan.store') }}" method="POST" id="maintenance-form">
                    @csrf
            @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik', 'kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                                <th scope="col" class="px-6 py-3 text-center">
                                    @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
                                        <input type="checkbox" id="select-all" class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    @else
                                        <input type="checkbox" id="select-all" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    @endif
                                </th>
                                @endif
                                @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ID Aset
                                    </th>
                                @endif
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Aset
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                                        Departemen
                                    @else
                                        Lokasi
                                    @endif
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tingkat Kerusakan
                                </th>
                                @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']) && !empty($priorityScores))
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'priority', 'direction' => request('sort') == 'priority' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                    class="flex items-center justify-center hover:text-gray-700 group">
                                        Nilai Prioritas
                                        <div class="ml-1 flex flex-col">
                                            @php
                                                $isPrioritySort = $currentSort === 'priority';
                                            @endphp
                                            
                                            <!-- Up Arrow -->
                                            <svg class="w-3 h-3 {{ $isPrioritySort && $currentDirection === 'asc' ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-gray-600" 
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                            </svg>
                                            
                                            <!-- Down Arrow -->
                                            <svg class="w-3 h-3 -mt-1 {{ $isPrioritySort && $currentDirection === 'desc' ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-gray-600" 
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </a>
                                </th>
                                @endif
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'estimasi_biaya', 'direction' => request('sort') == 'estimasi_biaya' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                                    class="flex items-center justify-end hover:text-gray-700 group">
                                        Estimasi Biaya
                                        <div class="ml-1 flex flex-col">
                                            @php
                                                $isBiayaSort = $currentSort === 'estimasi_biaya';
                                            @endphp
                                            
                                            <!-- Up Arrow -->
                                            <svg class="w-3 h-3 {{ $isBiayaSort && $currentDirection === 'asc' ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-gray-600" 
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                            </svg>
                                            
                                            <!-- Down Arrow -->
                                            <svg class="w-3 h-3 -mt-1 {{ $isBiayaSort && $currentDirection === 'desc' ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-gray-600" 
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </a>
                                </th>
                                @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($maintenanceAssets as $item)
                            @php
                                // Determine if this is a DamagedAsset or MaintenanceAsset
                                if ($item instanceof \App\Models\MaintenanceAsset) {
                                    $asset = $item->asset;
                                    $damagedAsset = $item->damagedAsset;
                                    $maintenanceId = $item->id;
                                    $isMaintenanceAsset = true;
                                } else {
                                    $asset = $item->asset;
                                    $damagedAsset = $item;
                                    $maintenanceId = null;
                                    $isMaintenanceAsset = false;
                                }
                                
                                // Determine department
                                $department = 'Logistik';
                                if (str_contains($asset->lokasi, 'Laboratorium')) {
                                    $department = 'Laboratorium';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50">
                                @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik', 'kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
                                        <input type="checkbox" name="damaged_asset_ids[]" value="{{ $damagedAsset->id }}" 
                                            class="asset-checkbox h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                    @else
                                        <input type="checkbox" 
                                            class="asset-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                            data-maintenance-id="{{ $maintenanceId }}"
                                            data-cost="{{ $damagedAsset->estimasi_biaya }}">
                                    @endif
                                </td>
                                @endif
                                @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $asset->asset_id }}
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        {{ $asset->nama_asset }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                                        {{ $department }}
                                    @else
                                        {{ $asset->lokasi }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $tingkatKerusakan = $damagedAsset->tingkat_kerusakan;
                                        $badgeClasses = [
                                            'Ringan' => 'bg-blue-100 text-blue-800',
                                            'Sedang' => 'bg-yellow-100 text-yellow-800',
                                            'Berat' => 'bg-red-100 text-red-800'
                                        ];
                                        $class = $badgeClasses[$tingkatKerusakan] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $class }}">
                                        {{ $tingkatKerusakan }}
                                    </span>
                                </td>
                                @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']) && !empty($priorityScores))
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if(isset($priorityScores[$item->id]))
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ number_format($priorityScores[$item->id]['score'], 4) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    Rp {{ number_format($damagedAsset->estimasi_biaya ?? 0, 0, ',', '.') }}
                                </td>
                                @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']) && $maintenanceId)
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button type="button" onclick="viewDetail({{ $maintenanceId }})" 
                                            class="text-gray-600 hover:text-gray-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </button>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Bottom Actions -->
                @if(!$maintenanceAssets->isEmpty() && Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']))
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end items-center">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 min-w-64">
                        <div class="space-y-3">
                            <!-- Asset Count -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600">Aset Terpilih</span>
                                <span id="selected-count" class="text-lg font-bold text-gray-900">0</span>
                            </div>
                            
                            <!-- Total Cost -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600 mr-3">Total Estimasi Biaya</span>
                                <span id="total-cost" class="text-lg font-bold text-blue-600">Rp 0</span>
                            </div>
                            
                            <!-- Action Button -->
                            @if(Auth::user()->hasRole('kaur_laboratorium'))
                                <button type="button" onclick="approveSelected()" class="w-full px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-colors duration-200">
                                    Setujui dan Ajukan ke Kaur Keuangan Logistik SDM
                                </button>
                            @elseif(Auth::user()->hasRole('kaur_keuangan_logistik_sdm'))
                                <button type="button" onclick="approveSelected()" class="w-full px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-colors duration-200">
                                    Setujui Pengajuan Perbaikan
                                </button>
                            @elseif(Auth::user()->hasRole('wakil_dekan_2'))
                                <button type="button" onclick="approveSelected()" class="w-full px-4 py-3 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-colors duration-200">
                                    Setujui Pengajuan Perbaikan
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Pagination -->
                @if(method_exists($maintenanceAssets, 'hasPages') && $maintenanceAssets->hasPages())
                <div class="px-6 py-3 border-t flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        @if($maintenanceAssets->onFirstPage())
                            <span class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400 cursor-not-allowed">Previous</span>
                        @else
                            <a href="{{ $maintenanceAssets->appends(request()->query())->previousPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">Previous</a>
                        @endif
                        
                        <span class="px-3 py-1 bg-blue-500 text-white rounded text-sm">{{ $maintenanceAssets->currentPage() }}</span>
                        
                        @if($maintenanceAssets->hasMorePages())
                            <a href="{{ $maintenanceAssets->appends(request()->query())->nextPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">Next</a>
                        @else
                            <span class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-400 cursor-not-allowed">Next</span>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700">
                            Menampilkan {{ $maintenanceAssets->firstItem() ?? 0 }} - {{ $maintenanceAssets->lastItem() ?? 0 }} 
                            dari {{ $maintenanceAssets->total() }} data
                        </span>
                    </div>
                </div>
                @endif
            @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
            </form>
            @endif
        @endif

        <!-- Bottom Section for Staff -->
        @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
        @can('create_excel')
            <div class="px-6 py-6 bg-gray-50 border-t">
                <p class="text-sm text-gray-600 mb-6">Jika data kriteria tidak ada di database lanjutkan langkah di bawah (optional)</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Step 2: Download Template -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Langkah 2 : Unduh Template</h4>
                            <a href="{{ route('pengajuan.template.download') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                Download Template
                            </a>
                            <p class="text-xs text-gray-500 mt-2">Template file excel dengan kriteria dinamis</p>
                        </div>
                    </div>

                    <!-- Step 3: Upload File -->
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Langkah 3 : Unggah File</h4>
                            <button type="button" onclick="openModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                Upload File
                            </button>
                            <p class="text-xs text-gray-500 mt-2">Hanya file excel dengan kriteria yang sesuai</p>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        @endif
    </div>

    <!-- Upload Modal -->
    @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
    <div id="excelUploadModal" class="hidden fixed inset-0 overflow-y-auto h-full w-full z-50 flex items-center justify-center" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="relative mx-auto p-6 w-full max-w-md bg-white rounded-lg shadow-xl">
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Upload File</h3>
                <p class="text-sm text-gray-600 mb-5">Select your files (.xlsx) - Max 10MB</p>
                
                <form action="{{ route('pengajuan.template.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="mb-5">
                        <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 flex flex-col items-center justify-center h-48 cursor-pointer hover:bg-gray-50 transition-colors duration-150" id="dropzone">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-700">Drag or drop your files here to add them</p>
                                <p class="text-xs text-gray-500 mt-1" id="file-name"></p>
                            </div>
                            <input type="file" id="excel_file" name="excel_file" 
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                accept=".xlsx,.xls" required>
                        </div>
                        <p class="text-xs text-gray-500 text-left mt-1">Maximum file size: 10 MB, Maximum number file: 1</p>
                    </div>
                    
                    <div class="flex justify-between space-x-3">
                        <button type="button" onclick="closeModal()" class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50 transition-colors duration-150">
                            Cancel
                        </button>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-colors duration-150">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables for Kaur roles
    @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']))
    const selectedAssets = new Map();
    
    // Update totals function
    function updateTotals() {
        let count = 0;
        let totalCost = 0;
        
        selectedAssets.forEach((data, id) => {
            if (data.checked) {
                count++;
                totalCost += data.cost;
            }
        });
        
        document.getElementById('selected-count').textContent = count;
        document.getElementById('total-cost').textContent = 'Rp ' + totalCost.toLocaleString('id-ID');
    }
    @endif
    
    // Select All functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const assetCheckboxes = document.querySelectorAll('.asset-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            assetCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                
                @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']))
                // Update selected assets data
                const row = checkbox.closest('tr');
                const maintenanceId = checkbox.dataset.maintenanceId;
                const costText = row.querySelector('td:nth-last-child(2)').textContent;
                const cost = parseInt(costText.replace(/[^0-9]/g, ''));
                
                selectedAssets.set(maintenanceId, {
                    checked: this.checked,
                    cost: cost
                });
                @endif
            });
            
            @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']))
            updateTotals();
            @endif
        });
        
        // Update select all when individual checkboxes change
        assetCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedCount = document.querySelectorAll('.asset-checkbox:checked').length;
                selectAllCheckbox.checked = checkedCount === assetCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < assetCheckboxes.length;
                
                @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']))
                // Update selected assets data
                const row = checkbox.closest('tr');
                const maintenanceId = checkbox.dataset.maintenanceId;
                const costText = row.querySelector('td:nth-last-child(2)').textContent;
                const cost = parseInt(costText.replace(/[^0-9]/g, ''));
                
                selectedAssets.set(maintenanceId, {
                    checked: checkbox.checked,
                    cost: cost
                });
                
                updateTotals();
                @endif
            });
        });
    }
    
    // Auto-submit filter form
    const lokasiSelect = document.getElementById('lokasi');
    const tingkatSelect = document.getElementById('tingkat_kerusakan');
    
    if (lokasiSelect) {
        lokasiSelect.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    }
    
    if (tingkatSelect) {
        tingkatSelect.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    }
});

// View detail function
function viewDetail(maintenanceId) {
    window.location.href = `/pengajuan/${maintenanceId}`;
}

// Approve selected function for Kaur roles
@if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']))
async function approveSelected() {
    const checkboxes = document.querySelectorAll('.asset-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Silakan pilih minimal satu aset untuk disetujui');
        return;
    }
    
    const maintenanceIds = Array.from(checkboxes).map(cb => cb.dataset.maintenanceId).filter(id => id);
    
    if (maintenanceIds.length === 0) {
        alert('Tidak ada pengajuan yang valid untuk disetujui');
        return;
    }
    
    if (!confirm(`Apakah Anda yakin ingin menyetujui ${maintenanceIds.length} pengajuan? Data yang tidak dipilih akan dihapus.`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'approve');
        formData.append('notes', 'Disetujui melalui bulk action');
        maintenanceIds.forEach(id => {
            formData.append('maintenance_ids[]', id);
        });
        
        const response = await fetch('/pengajuan/bulk-approve', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses persetujuan: ' + error.message);
    }
}
@endif

// Modal functions for staff
@if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
function openModal() {
    const modal = document.getElementById('excelUploadModal');
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('excelUploadModal');
    modal.classList.add('hidden');
}

// File upload handling
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('excel_file');
    const fileNameDisplay = document.getElementById('file-name');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                const fileSize = (fileInput.files[0].size / 1024 / 1024).toFixed(2);
                
                fileNameDisplay.textContent = `${fileName} (${fileSize} MB)`;
                dropzone.classList.add('border-green-500', 'bg-green-50');
                dropzone.classList.remove('border-gray-300');
            } else {
                resetDropzone();
            }
        });
        
        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, function() {
                dropzone.classList.add('border-green-500', 'bg-green-50');
                dropzone.classList.remove('border-gray-300');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, function() {
                dropzone.classList.remove('border-green-500', 'bg-green-50');
                dropzone.classList.add('border-gray-300');
            }, false);
        });
        
        dropzone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                fileInput.files = files;
                const fileName = files[0].name;
                const fileSize = (files[0].size / 1024 / 1024).toFixed(2);
                
                fileNameDisplay.textContent = `${fileName} (${fileSize} MB)`;
                dropzone.classList.add('border-green-500', 'bg-green-50');
                dropzone.classList.remove('border-gray-300');
            }
        }, false);
        
        function resetDropzone() {
            fileNameDisplay.textContent = '';
            dropzone.classList.remove('border-green-500', 'bg-green-50');
            dropzone.classList.add('border-gray-300');
        }
    }
    
    // Close modal when clicking outside
    const modal = document.getElementById('excelUploadModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
});
@endif
</script>
@endsection