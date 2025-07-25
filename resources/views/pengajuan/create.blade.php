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
                <div class="relative">
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">
                        @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                            Pilih Departemen
                        @else
                            Pilih Lokasi Aset
                        @endif
                        <span class="text-xs text-gray-500">(ketik atau pilih)</span>
                    </label>

                    <div class="relative">
                        <input type="text" 
                            id="lokasi" 
                            name="lokasi" 
                            value="{{ request('lokasi') }}"
                            placeholder="Ketik atau pilih..."
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                            autocomplete="on">

                        <!-- Trigger dropdown -->
                        <button type="button" 
                                id="lokasi-dropdown-btn"
                                class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600">
                            <svg id="lokasi-dropdown-arrow" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown options -->
                        <div id="lokasi-dropdown" 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-auto">
                            <div class="py-1">
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer border-b lokasi-option" 
                                    data-lokasi="">
                                    <span class="font-medium">
                                        @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                                            Semua Departemen
                                        @else
                                            Semua Lokasi
                                        @endif
                                    </span>
                                    <span class="text-gray-500 text-xs block">Tampilkan semua data</span>
                                </div>
                                @foreach($locations as $location)
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer lokasi-option" 
                                    data-lokasi="{{ $location }}">
                                    {{ $location }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>


                <div class="relative">
                    <label for="tingkat_kerusakan" class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Tingkat Kerusakan
                        <span class="text-xs text-gray-500">(ketik atau pilih)</span>
                    </label>
                    
                    <div class="relative">
                        <input type="text" 
                            id="tingkat_kerusakan" 
                            name="tingkat_kerusakan" 
                            value="{{ request('tingkat_kerusakan') }}"
                            placeholder="Ketik atau pilih tingkat_kerusakan..."
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                            autocomplete="on">
                        
                        <button type="button" 
                                id="tingkat_kerusakan-dropdown-btn"
                                class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600">
                            <svg id="tingkat_kerusakan-dropdown-arrow" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div id="tingkat_kerusakan-dropdown" 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-auto">
                            <div class="py-1">
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer border-b tingkat_kerusakan-option" 
                                    data-tingkat_kerusakan="">
                                    <span class="font-medium">Semua tingkat_kerusakan</span>
                                    <span class="text-gray-500 text-xs block">Tampilkan semua data</span>
                                </div>
                                @foreach($tingkatKerusakanOptions as $tingkat_kerusakan)
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer tingkat_kerusakan-option" 
                                    data-tingkat_kerusakan="{{ $tingkat_kerusakan }}">
                                    {{ $tingkat_kerusakan }}
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
                    
                    <a href="{{ route('pengajuan.baru') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
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
                            <div class="space-y-2">
                                <!-- Download Selected Assets -->
                                <div>
                                    <button type="button" onclick="downloadSelectedAssets()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Unduh
                                        <span id="selected-download-count" class="ml-2 px-2 py-1 bg-green-700 text-xs rounded-full">0</span>
                                    </button>
                                    <p class="text-xs text-gray-500 mt-1">Download excel berisi data aset yang dipilih via checkbox</p>
                                </div>
                            </div>
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
                        <!-- Upload Area (shown when no file selected) -->
                        <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 flex flex-col items-center justify-center h-48 cursor-pointer hover:bg-gray-50 transition-colors duration-150" id="dropzone">
                            <div class="text-center" id="upload-area">
                                <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-700">Drag or drop your files here to add them</p>
                                <p class="text-xs text-gray-500 mt-1">or click to browse</p>
                            </div>
                            
                            <!-- File Preview (shown when file selected) -->
                            <div class="hidden text-center w-full" id="file-preview">
                                <div class="flex items-center justify-center space-x-3 p-4 bg-gray-50 rounded-lg border">
                                    <!-- Excel File Icon -->
                                    <div class="flex-shrink-0">
                                        <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                            <path d="M9.5,11.5L11,13.8L12.5,11.5H14.5L12,15L14.5,18.5H12.5L11,16.2L9.5,18.5H7.5L10,15L7.5,11.5H9.5Z" fill="white"/>
                                        </svg>
                                    </div>
                                    
                                    <!-- File Info -->
                                    <div class="flex-1 text-left min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate" id="file-name-display"></p>
                                        <p class="text-xs text-gray-500" id="file-size-display"></p>
                                    </div>
                                    
                                    <!-- Remove Button -->
                                    <div class="flex-shrink-0">
                                        <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700 focus:outline-none">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
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
                        <button type="submit" id="submit-btn" disabled class="w-full px-4 py-2 bg-gray-400 text-white rounded-md cursor-not-allowed transition-colors duration-150">
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
function updateDownloadCount() {
    const checkedAssets = document.querySelectorAll('.asset-checkbox:checked').length;
    const downloadCountSpan = document.getElementById('selected-download-count');
    if (downloadCountSpan) {
        downloadCountSpan.textContent = checkedAssets;
    }
}

async function downloadSelectedAssets() {
    const checkboxes = document.querySelectorAll('.asset-checkbox:checked');
    const selectedAssets = [];
    let assetType = 'damaged_assets'; // default
    
    // Determine asset type and collect IDs
    @if(Auth::user()->hasRole(['staff_laboratorium', 'staff_logistik']))
        assetType = 'damaged_assets';
        checkboxes.forEach(checkbox => {
            selectedAssets.push(checkbox.value);
        });
    @else
        assetType = 'maintenance_assets';
        checkboxes.forEach(checkbox => {
            const maintenanceId = checkbox.dataset.maintenanceId;
            if (maintenanceId) {
                selectedAssets.push(maintenanceId);
            }
        });
    @endif
    
    try {
        // Create a form and submit it for file download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("pengajuan.template.download-selected") }}';
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfToken);
        
        // Add asset type
        const assetTypeInput = document.createElement('input');
        assetTypeInput.type = 'hidden';
        assetTypeInput.name = 'asset_type';
        assetTypeInput.value = assetType;
        form.appendChild(assetTypeInput);
        
        // Add selected assets
        selectedAssets.forEach(assetId => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_assets[]';
            input.value = assetId;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
    } catch (error) {
        console.error('Error downloading selected assets:', error);
        alert('Terjadi kesalahan saat mengunduh data aset terpilih');
    }
}

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

                updateDownloadCount();
                
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

    updateDownloadCount();
    
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
    document.getElementById('excelUploadModal').classList.add('hidden');
    // Reset the form when closing
    document.getElementById('excel_file').value = '';
    
    const uploadArea = document.getElementById('upload-area');
    const filePreview = document.getElementById('file-preview');
    const dropzone = document.getElementById('dropzone');
    const submitBtn = document.getElementById('submit-btn');
    
    if (uploadArea) uploadArea.classList.remove('hidden');
    if (filePreview) filePreview.classList.add('hidden');
    if (dropzone) {
        dropzone.classList.add('border-dashed', 'hover:bg-gray-50');
        dropzone.classList.remove('border-solid', 'border-green-300');
    }
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700', 'focus:ring-2', 'focus:ring-green-500', 'focus:ring-opacity-50');
    }
}

// File upload handling  
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('excel_file');
    const dropzone = document.getElementById('dropzone');
    const uploadArea = document.getElementById('upload-area');
    const filePreview = document.getElementById('file-preview');
    const fileNameDisplay = document.getElementById('file-name-display');
    const fileSizeDisplay = document.getElementById('file-size-display');
    const submitBtn = document.getElementById('submit-btn');
    
    let selectedFile = null;

    // File input change event
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            handleFileSelect(e.target.files[0]);
        });
    }

    // Drag and drop events
    if (dropzone) {
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropzone.classList.add('border-green-400', 'bg-green-50');
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropzone.classList.remove('border-green-400', 'bg-green-50');
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.classList.remove('border-green-400', 'bg-green-50');
            handleFileSelect(e.dataTransfer.files[0]);
        });
    }

    function handleFileSelect(file) {
        if (!file) return;
        
        // Validate file type
        const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
            alert('Please select a valid Excel file (.xlsx or .xls)');
            resetFileInput();
            return;
        }
        
        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB');
            resetFileInput();
            return;
        }
        
        selectedFile = file;
        showFilePreview(file);
        enableSubmitButton();
    }

    function showFilePreview(file) {
        if (uploadArea && filePreview && fileNameDisplay && fileSizeDisplay) {
            // Hide upload area, show preview
            uploadArea.classList.add('hidden');
            filePreview.classList.remove('hidden');
            
            // Update file info
            fileNameDisplay.textContent = file.name;
            fileSizeDisplay.textContent = formatFileSize(file.size);
            
            // Update dropzone styling
            dropzone.classList.remove('border-dashed', 'hover:bg-gray-50');
            dropzone.classList.add('border-solid', 'border-green-300');
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function resetFileInput() {
        selectedFile = null;
        if (fileInput) fileInput.value = '';
        
        if (uploadArea && filePreview) {
            // Show upload area, hide preview
            uploadArea.classList.remove('hidden');
            filePreview.classList.add('hidden');
        }
        
        if (dropzone) {
            // Reset dropzone styling
            dropzone.classList.add('border-dashed', 'hover:bg-gray-50');
            dropzone.classList.remove('border-solid', 'border-green-300');
        }
        
        disableSubmitButton();
    }

    function enableSubmitButton() {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            submitBtn.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:ring-2', 'focus:ring-green-500', 'focus:ring-opacity-50');
        }
    }

    function disableSubmitButton() {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700', 'focus:ring-2', 'focus:ring-green-500', 'focus:ring-opacity-50');
        }
    }

    // Make removeFile function global
    window.removeFile = function() {
        resetFileInput();
    };
    
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tingkatKerusakanInput = document.getElementById('tingkat_kerusakan');
    const tingkatKerusakanDropdownBtn = document.getElementById('tingkat_kerusakan-dropdown-btn');
    const tingkatKerusakanDropdown = document.getElementById('tingkat_kerusakan-dropdown');
    const tingkatKerusakanOptions = document.querySelectorAll('.tingkat_kerusakan-option');
    const tingkatKerusakanDropdownArrow = document.getElementById('tingkat_kerusakan-dropdown-arrow');
    let isTingkatKerusakanOpen = false;

    function toggleTingkatKerusakanDropdown() {
        isTingkatKerusakanOpen = !isTingkatKerusakanOpen;
        tingkatKerusakanDropdown.classList.toggle('hidden', !isTingkatKerusakanOpen);
        tingkatKerusakanDropdownArrow.style.transform = isTingkatKerusakanOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    function closeTingkatKerusakanDropdown() {
        isTingkatKerusakanOpen = false;
        tingkatKerusakanDropdown.classList.add('hidden');
        tingkatKerusakanDropdownArrow.style.transform = 'rotate(0deg)';
    }

    function filterTingkatKerusakanOptions() {
        const search = tingkatKerusakanInput.value.toLowerCase();
        tingkatKerusakanOptions.forEach(option => {
            const value = option.getAttribute('data-tingkat_kerusakan').toLowerCase();
            const text = option.textContent.toLowerCase();
            option.style.display = (text.includes(search) || value.includes(search)) ? 'block' : 'none';
        });
    }

    function selectTingkatKerusakan(value) {
        tingkatKerusakanInput.value = value;
        closeTingkatKerusakanDropdown();
    }

    tingkatKerusakanDropdownBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleTingkatKerusakanDropdown();
    });

    tingkatKerusakanInput.addEventListener('input', function () {
        if (isTingkatKerusakanOpen) filterTingkatKerusakanOptions();
    });

    tingkatKerusakanInput.addEventListener('focus', function () {
        if (!isTingkatKerusakanOpen) toggleTingkatKerusakanDropdown();
    });

    tingkatKerusakanInput.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!isTingkatKerusakanOpen) toggleTingkatKerusakanDropdown();
        } else if (e.key === 'Escape') {
            closeTingkatKerusakanDropdown();
        }
    });

    tingkatKerusakanOptions.forEach(option => {
        option.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            selectTingkatKerusakan(this.getAttribute('data-tingkat_kerusakan'));
        });
    });

    // === Lokasi Dropdown ===
    const lokasiInput = document.getElementById('lokasi');
    const lokasiDropdownBtn = document.getElementById('lokasi-dropdown-btn');
    const lokasiDropdown = document.getElementById('lokasi-dropdown');
    const lokasiOptions = document.querySelectorAll('.lokasi-option');
    const lokasiDropdownArrow = document.getElementById('lokasi-dropdown-arrow');
    let isLokasiOpen = false;

    function toggleLokasiDropdown() {
        isLokasiOpen = !isLokasiOpen;
        lokasiDropdown.classList.toggle('hidden', !isLokasiOpen);
        lokasiDropdownArrow.style.transform = isLokasiOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    function closeLokasiDropdown() {
        isLokasiOpen = false;
        lokasiDropdown.classList.add('hidden');
        lokasiDropdownArrow.style.transform = 'rotate(0deg)';
    }

    function filterLokasiOptions() {
        const search = lokasiInput.value.toLowerCase();
        lokasiOptions.forEach(option => {
            const value = option.getAttribute('data-lokasi').toLowerCase();
            const text = option.textContent.toLowerCase();
            option.style.display = (text.includes(search) || value.includes(search)) ? 'block' : 'none';
        });
    }

    function selectLokasi(value) {
        lokasiInput.value = value;
        closeLokasiDropdown();
    }

    lokasiDropdownBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleLokasiDropdown();
    });

    lokasiInput.addEventListener('input', function () {
        if (isLokasiOpen) filterLokasiOptions();
    });

    lokasiOptions.forEach(option => {
        option.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            selectLokasi(this.getAttribute('data-lokasi'));
        });
    });

    lokasiInput.addEventListener('focus', function () {
        if (!isLokasiOpen) toggleLokasiDropdown();
    });

    lokasiInput.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            toggleLokasiDropdown();
        } else if (e.key === 'Escape') {
            closeLokasiDropdown();
        }
    });

    // Tambahkan ke global event listener luar
    document.addEventListener('click', function (e) {
        if (!lokasiDropdown.contains(e.target) &&
            !lokasiInput.contains(e.target) &&
            !lokasiDropdownBtn.contains(e.target)) {
            closeLokasiDropdown();
        }
    });


    document.addEventListener('click', function (e) {
        if (!tingkatKerusakanDropdown.contains(e.target) &&
            !tingkatKerusakanInput.contains(e.target) &&
            !tingkatKerusakanDropdownBtn.contains(e.target)) {
            closeTingkatKerusakanDropdown();
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
<script>
// Auto-calculate missing priority scores on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(Auth::user()->hasRole(['kaur_laboratorium', 'kaur_keuangan_logistik_sdm']) && !$maintenanceAssets->isEmpty())
    // Check if there are any assets with missing priority scores (showing as "-")
    const priorityCells = document.querySelectorAll('td:nth-child(5)'); // Priority column
    let hasMissingScores = false;
    
    priorityCells.forEach(cell => {
        if (cell.textContent.trim() === '-') {
            hasMissingScores = true;
        }
    });
    
    if (hasMissingScores) {
        console.log('Detected missing priority scores, auto-calculating...');
        
        // Show a subtle loading indicator
        const priorityColumn = document.querySelector('th:nth-child(5)');
        if (priorityColumn) {
            const originalText = priorityColumn.textContent;
            priorityColumn.innerHTML = originalText + ' <span class="text-blue-600">⟳</span>';
        }
        
        // Auto-trigger calculation
        fetch('{{ route("pengajuan.ensure-priority-scores") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.calculated > 0) {
                console.log('Priority scores calculated successfully:', data);
                // Reload the page to show updated scores
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                console.log('No new calculations needed:', data);
                // Remove loading indicator
                if (priorityColumn) {
                    priorityColumn.innerHTML = originalText;
                }
            }
        })
        .catch(error => {
            console.error('Auto-calculation failed:', error);
            // Remove loading indicator
            if (priorityColumn) {
                priorityColumn.innerHTML = originalText;
            }
        });
    }
    @endif
    
    // Rest of existing JavaScript code...
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

                updateDownloadCount();
                
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

    updateDownloadCount();
    
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

// Add manual trigger button functionality
async function triggerPriorityCalculation() {
    try {
        console.log('Manually triggering priority calculation...');
        
        const response = await fetch('{{ route("pengajuan.ensure-priority-scores") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Priority calculation completed! Calculated scores for ' + data.calculated + ' assets.');
            if (data.calculated > 0) {
                window.location.reload();
            }
        } else {
            alert('❌ Priority calculation failed: ' + data.message);
        }
    } catch (error) {
        console.error('Priority calculation failed:', error);
        alert('❌ Priority calculation failed: ' + error.message);
    }
}
</script>
@endsection