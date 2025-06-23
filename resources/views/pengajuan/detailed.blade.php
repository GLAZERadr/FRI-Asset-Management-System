<!-- resources/views/pengajuan/detailed.blade.php -->
@extends('layouts.app')
@section('header', 'Manajemen Aset')
@section('content')
<div class="container mx-auto">
    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('pengajuan.detailed') }}" method="GET" id="filter-form">
            <!-- Preserve sort parameters -->
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('direction'))
                <input type="hidden" name="direction" value="{{ request('direction') }}">
            @endif
            
            <div class="grid grid-cols-4 gap-6 items-end">
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
                
                <div class="relative">
                    <label for="petugas" class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Petugas
                        <span class="text-xs text-gray-500">(ketik atau pilih)</span>
                    </label>
                    
                    <div class="relative">
                        <input type="text" 
                            id="petugas" 
                            name="petugas" 
                            value="{{ request('petugas') }}"
                            placeholder="Ketik atau pilih petugas..."
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                            autocomplete="on">
                        
                        <button type="button" 
                                id="petugas-dropdown-btn"
                                class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600">
                            <svg id="petugas-dropdown-arrow" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div id="petugas-dropdown" 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-auto">
                            <div class="py-1">
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer border-b petugas-option" 
                                    data-petugas="">
                                    <span class="font-medium">Semua Petugas</span>
                                    <span class="text-gray-500 text-xs block">Tampilkan semua data</span>
                                </div>
                                @foreach($petugasList as $petugas)
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer petugas-option" 
                                    data-petugas="{{ $petugas }}">
                                    {{ $petugas }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Status
                        <span class="text-xs text-gray-500">(ketik atau pilih)</span>
                    </label>
                    
                    <div class="relative">
                        <input type="text" 
                            id="status" 
                            name="status" 
                            value="{{ request('status') }}"
                            placeholder="Ketik atau pilih status..."
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                            autocomplete="on">
                        
                        <button type="button" 
                                id="status-dropdown-btn"
                                class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600">
                            <svg id="status-dropdown-arrow" class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <div id="status-dropdown" 
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-auto">
                            <div class="py-1">
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer border-b status-option" 
                                    data-status="">
                                    <span class="font-medium">Semua Status</span>
                                    <span class="text-gray-500 text-xs block">Tampilkan semua data</span>
                                </div>
                                @foreach($statusList as $status)
                                <div class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer status-option" 
                                    data-status="{{ $status }}">
                                    {{ $status }}
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
                    
                    <!-- Clear Filters Button -->
                    <a href="{{ route('pengajuan.detailed') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Hapus Filter
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Enhanced Maintenance Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Daftar Perbaikan Aset</h3>
        </div>
        
        <x-table>
            <x-slot name="thead">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Kerusakan
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
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'estimasi_waktu', 'direction' => request('sort') == 'estimasi_waktu' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" 
                        class="flex items-center justify-end hover:text-gray-700 group">
                            Estimasi Waktu
                            <div class="ml-1 flex flex-col">
                                @php
                                    $currentSort = request('sort');
                                    $currentDirection = request('direction');
                                    $isEstimasiSort = $currentSort === 'estimasi_waktu';
                                @endphp
                                
                                <!-- Up Arrow -->
                                <svg class="w-3 h-3 {{ $isEstimasiSort && $currentDirection === 'asc' ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-gray-600" 
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                
                                <!-- Down Arrow -->
                                <svg class="w-3 h-3 -mt-1 {{ $isEstimasiSort && $currentDirection === 'desc' ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-gray-600" 
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </a>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Petugas
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </x-slot>
            
            @forelse ($maintenanceRequests as $request)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $request->damage_id }}
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
                        $isDisabled = in_array($status, ['Selesai', 'Ditolak', 'Menunggu Persetujuan']);
                    @endphp
                    @if($isDisabled)
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $class }}">
                            {{ $status }}
                        </span>
                        @else
                        <form action="{{ route('pengajuan.update-status', $request->id) }}" method="POST" class="inline-block status-form" data-maintenance-id="{{ $request->id }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" data-current-status="{{ $status }}"
                                    class="text-xs font-semibold rounded-full border-0 focus:ring-0 {{ $class }} appearance-none pr-6 pl-2 py-1 status-select">
                                <option value="Diterima" {{ $status == 'Diterima' ? 'selected' : '' }}>Diterima</option>
                                <option value="Menunggu Persetujuan" {{ $status == 'Menunggu Persetujuan' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                                <option value="Dikerjakan" {{ $status == 'Dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                                <option value="Selesai" {{ $status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="Ditolak" {{ $status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </form>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->tanggal_pengajuan ? date('d/m/Y', strtotime($request->tanggal_pengajuan)) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                    @if($request->damagedAsset->estimasi_waktu_perbaikan)
                        <div class="flex flex-col items-end">
                            <span class="font-medium text-gray-900">
                                {{ $request->damagedAsset->estimasi_waktu_perbaikan }}
                            </span>
                            @if($request->status === 'Selesai' && $request->tanggal_perbaikan && $request->tanggal_selesai)
                                @php
                                    $startDate = \Carbon\Carbon::parse($request->tanggal_perbaikan);
                                    $endDate = \Carbon\Carbon::parse($request->tanggal_selesai);
                                    $actualDays = $startDate->diffInDays($endDate);
                                @endphp
                            @endif
                        </div>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->damagedAsset->petugas ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        @if(in_array($request->status, ['Selesai', 'Ditolak']))
                        <form action="{{ route('pengajuan.destroy', $request->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                        @endif
                        
                        <a href="{{ route('pengajuan.show', $request->id) }}" class="text-gray-500 hover:text-gray-700" title="Lihat Detail">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-5 h-5 fill-current">
                                <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
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
                <a href="{{ route('pengajuan.daftar') }}" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Back</a>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Photo Upload Modal -->
<div id="photoUploadModal" class="fixed inset-0 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Upload Foto Penyelesaian Perbaikan</h3>
                <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-4">
                <form id="photoUploadForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="maintenanceId" name="maintenance_id" value="">
                    <input type="hidden" name="status" value="Selesai">
                    
                    <!-- Drag and Drop Area -->
                    <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors cursor-pointer">
                        <div id="dropZoneContent">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="mt-4">
                                <p class="text-lg text-gray-600">Seret dan letakkan foto di sini</p>
                                <p class="text-sm text-gray-500 mt-1">atau</p>
                                <button type="button" id="selectFilesBtn" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                                    Pilih File
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF hingga 5MB (Maksimal 10 foto)</p>
                        </div>
                    </div>

                    <!-- Hidden File Input -->
                    <input type="file" id="fileInput" name="photos[]" multiple accept="image/*" class="hidden">

                    <!-- Preview Container -->
                    <div id="previewContainer" class="mt-4 hidden">
                        <h4 class="text-md font-medium text-gray-700 mb-2">Foto yang akan diupload:</h4>
                        <div id="previewGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"></div>
                    </div>

                    <!-- Upload Progress -->
                    <div id="uploadProgress" class="mt-4 hidden">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="progressText" class="text-sm text-gray-600 mt-1">Uploading...</p>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end pt-4 border-t mt-6">
                        <button type="button" id="cancelBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 mr-2">
                            Batal
                        </button>
                        <button type="submit" id="uploadBtn" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50" disabled>
                            Upload & Selesaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
        
        if (!isDropdownOpen) {
            closeDropdown();
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

    // === Petugas Dropdown ===
    const petugasInput = document.getElementById('petugas');
    const petugasDropdownBtn = document.getElementById('petugas-dropdown-btn');
    const petugasDropdown = document.getElementById('petugas-dropdown');
    const petugasOptions = document.querySelectorAll('.petugas-option');
    const petugasDropdownArrow = document.getElementById('petugas-dropdown-arrow');
    let isPetugasOpen = false;

    function togglePetugasDropdown() {
        isPetugasOpen = !isPetugasOpen;
        petugasDropdown.classList.toggle('hidden', !isPetugasOpen);
        petugasDropdownArrow.style.transform = isPetugasOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    function closePetugasDropdown() {
        isPetugasOpen = false;
        petugasDropdown.classList.add('hidden');
        petugasDropdownArrow.style.transform = 'rotate(0deg)';
    }

    function filterPetugasOptions() {
        const search = petugasInput.value.toLowerCase();
        petugasOptions.forEach(option => {
            const value = option.getAttribute('data-petugas').toLowerCase();
            const text = option.textContent.toLowerCase();
            option.style.display = (text.includes(search) || value.includes(search)) ? 'block' : 'none';
        });
    }

    function selectPetugas(value) {
        petugasInput.value = value;
        closePetugasDropdown();
    }

    if (petugasDropdownBtn) {
        petugasDropdownBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            togglePetugasDropdown();
        });
    }

    if (petugasInput) {
        petugasInput.addEventListener('input', function () {
            if (isPetugasOpen) filterPetugasOptions();
        });

        petugasInput.addEventListener('focus', function() {
            if (!isPetugasOpen) {
                togglePetugasDropdown();
            }
        });

        petugasInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!isPetugasOpen) {
                    togglePetugasDropdown();
                }
            } else if (e.key === 'Escape') {
                closePetugasDropdown();
            }
        });
    }

    petugasOptions.forEach(option => {
        option.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            selectPetugas(this.getAttribute('data-petugas'));
        });
    });

    // === Status Dropdown ===
    const statusInput = document.getElementById('status');
    const statusDropdownBtn = document.getElementById('status-dropdown-btn');
    const statusDropdown = document.getElementById('status-dropdown');
    const statusOptions = document.querySelectorAll('.status-option');
    const statusDropdownArrow = document.getElementById('status-dropdown-arrow');
    let isStatusOpen = false;

    function toggleStatusDropdown() {
        isStatusOpen = !isStatusOpen;
        statusDropdown.classList.toggle('hidden', !isStatusOpen);
        statusDropdownArrow.style.transform = isStatusOpen ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    function closeStatusDropdown() {
        isStatusOpen = false;
        statusDropdown.classList.add('hidden');
        statusDropdownArrow.style.transform = 'rotate(0deg)';
    }

    function filterStatusOptions() {
        const search = statusInput.value.toLowerCase();
        statusOptions.forEach(option => {
            const value = option.getAttribute('data-status').toLowerCase();
            const text = option.textContent.toLowerCase();
            option.style.display = (text.includes(search) || value.includes(search)) ? 'block' : 'none';
        });
    }

    function selectStatus(value) {
        statusInput.value = value;
        closeStatusDropdown();
    }

    if (statusDropdownBtn) {
        statusDropdownBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            toggleStatusDropdown();
        });
    }

    if (statusInput) {
        statusInput.addEventListener('input', function () {
            if (isStatusOpen) filterStatusOptions();
        });

        statusInput.addEventListener('focus', function() {
            if (!isStatusOpen) {
                toggleStatusDropdown();
            }
        });

        statusInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!isStatusOpen) {
                    toggleStatusDropdown();
                }
            } else if (e.key === 'Escape') {
                closeStatusDropdown();
            }
        });
    }

    statusOptions.forEach(option => {
        option.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            selectStatus(this.getAttribute('data-status'));
        });
    });

    // === Close dropdowns when clicking outside ===
    document.addEventListener('click', function (e) {
        if (petugasDropdown && !petugasDropdown.contains(e.target) &&
            petugasInput && !petugasInput.contains(e.target) &&
            petugasDropdownBtn && !petugasDropdownBtn.contains(e.target)) {
            closePetugasDropdown();
        }

        if (statusDropdown && !statusDropdown.contains(e.target) &&
            statusInput && !statusInput.contains(e.target) &&
            statusDropdownBtn && !statusDropdownBtn.contains(e.target)) {
            closeStatusDropdown();
        }

        if (dropdown && !dropdown.contains(e.target) && 
            lokasiInput && !lokasiInput.contains(e.target) && 
            dropdownBtn && !dropdownBtn.contains(e.target)) {
            closeDropdown();
        }
    });

    // === PHOTO UPLOAD MODAL FUNCTIONALITY ===
    const modal = document.getElementById('photoUploadModal');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const selectFilesBtn = document.getElementById('selectFilesBtn');
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const previewContainer = document.getElementById('previewContainer');
    const previewGrid = document.getElementById('previewGrid');
    const uploadForm = document.getElementById('photoUploadForm');
    const uploadBtn = document.getElementById('uploadBtn');
    const maintenanceIdInput = document.getElementById('maintenanceId');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    let selectedFiles = [];
    let currentMaintenanceId = null;

    console.log('Photo modal elements found:', {
        modal: !!modal,
        closeModalBtn: !!closeModalBtn,
        selectFilesBtn: !!selectFilesBtn,
        fileInput: !!fileInput,
        dropZone: !!dropZone
    });

    // Handle status change for all select elements
    document.querySelectorAll('.status-select').forEach(select => {
        console.log('Found status select:', select);
        select.addEventListener('change', function(e) {
            console.log('Status changed to:', this.value);
            const selectedStatus = this.value;
            const form = this.closest('.status-form');
            const maintenanceId = form.getAttribute('data-maintenance-id');
            const currentStatus = this.getAttribute('data-current-status');

            if (selectedStatus === 'Selesai') {
                console.log('Selesai selected, showing modal for maintenance ID:', maintenanceId);
                e.preventDefault();
                // Reset the select to previous value temporarily
                this.value = currentStatus;
                
                // Show photo upload modal
                showPhotoModal(maintenanceId);
            } else {
                console.log('Other status selected, submitting form');
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

    // Show Photo Modal
    function showPhotoModal(maintenanceId) {
        console.log('Showing photo modal for maintenance ID:', maintenanceId);
        
        if (!modal) {
            console.error('Modal element not found!');
            return;
        }

        currentMaintenanceId = maintenanceId;
        maintenanceIdInput.value = maintenanceId;
        resetModal();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        console.log('Modal should now be visible');
    }

    // Close Modal
    function closeModal() {
        console.log('Closing modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            resetModal();
        }
    }

    // Reset Modal
    function resetModal() {
        selectedFiles = [];
        if (previewContainer) previewContainer.classList.add('hidden');
        if (previewGrid) previewGrid.innerHTML = '';
        if (uploadBtn) uploadBtn.disabled = true;
        if (uploadProgress) uploadProgress.classList.add('hidden');
        if (progressBar) progressBar.style.width = '0%';
        if (fileInput) fileInput.value = '';
    }

    // Event Listeners
    if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (selectFilesBtn) selectFilesBtn.addEventListener('click', () => {
        console.log('Select files clicked');
        if (fileInput) fileInput.click();
    });

    // File Input Change
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            console.log('Files selected:', e.target.files.length);
            handleFiles(e.target.files);
        });
    }

    // Drag and Drop
    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-blue-400', 'bg-blue-50');
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-400', 'bg-blue-50');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-blue-400', 'bg-blue-50');
            console.log('Files dropped:', e.dataTransfer.files.length);
            handleFiles(e.dataTransfer.files);
        });
    }

    // Handle Files
    function handleFiles(files) {
        console.log('Handling files:', files.length);
        const fileArray = Array.from(files);
        const imageFiles = fileArray.filter(file => file.type.startsWith('image/'));
        
        if (imageFiles.length === 0) {
            alert('Pilih file gambar yang valid (PNG, JPG, GIF)');
            return;
        }

        if (selectedFiles.length + imageFiles.length > 10) {
            alert('Maksimal 10 foto yang dapat diupload');
            return;
        }

        // Check file sizes
        const oversizedFiles = imageFiles.filter(file => file.size > 5 * 1024 * 1024);
        if (oversizedFiles.length > 0) {
            alert('Beberapa file melebihi ukuran maksimal 5MB');
            return;
        }

        selectedFiles = [...selectedFiles, ...imageFiles];
        updateFileInput();
        showPreview();
        updateUploadBtn();
    }

    // Update File Input
    function updateFileInput() {
        if (!fileInput) return;
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }

    // Show Preview
    function showPreview() {
        if (selectedFiles.length === 0) {
            if (previewContainer) previewContainer.classList.add('hidden');
            return;
        }

        if (previewContainer) previewContainer.classList.remove('hidden');
        if (previewGrid) previewGrid.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" class="w-full h-24 object-cover rounded-lg border">
                    <button type="button" onclick="removeFile(${index})" 
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                        ×
                    </button>
                    <p class="text-xs text-gray-600 mt-1 truncate">${file.name}</p>
                `;
                if (previewGrid) previewGrid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }

    // Remove File (make it global)
    window.removeFile = function(index) {
        console.log('Removing file at index:', index);
        selectedFiles.splice(index, 1);
        updateFileInput();
        showPreview();
        updateUploadBtn();
    };

    // Update Upload Button
    function updateUploadBtn() {
        if (uploadBtn) {
            uploadBtn.disabled = selectedFiles.length === 0;
        }
    }

    // Form Submit
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted with', selectedFiles.length, 'files');
            
            if (selectedFiles.length === 0) {
                alert('Pilih minimal 1 foto untuk diupload');
                return;
            }

            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            formData.append('status', 'Selesai');
            
            selectedFiles.forEach(file => {
                formData.append('photos[]', file);
            });

            // Show progress
            if (uploadProgress) uploadProgress.classList.remove('hidden');
            if (uploadBtn) uploadBtn.disabled = true;

            // Upload with XMLHttpRequest for progress tracking
            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    if (progressBar) progressBar.style.width = percentComplete + '%';
                    if (progressText) progressText.textContent = `Uploading... ${Math.round(percentComplete)}%`;
                }
            });

            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log('Upload successful');
                        closeModal();
                        location.reload(); // Refresh page to show updated status
                    } else {
                        alert('Error: ' + response.message);
                        if (uploadBtn) uploadBtn.disabled = false;
                        if (uploadProgress) uploadProgress.classList.add('hidden');
                    }
                } else {
                    alert('Upload failed. Please try again.');
                    if (uploadBtn) uploadBtn.disabled = false;
                    if (uploadProgress) uploadProgress.classList.add('hidden');
                }
            });

            xhr.addEventListener('error', function() {
                alert('Upload failed. Please check your connection.');
                if (uploadBtn) uploadBtn.disabled = false;
                if (uploadProgress) uploadProgress.classList.add('hidden');
            });

            xhr.open('POST', `/pengajuan/${currentMaintenanceId}/update-photos`);
            xhr.send(formData);
        });
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeModal();
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
</script>
@endsection