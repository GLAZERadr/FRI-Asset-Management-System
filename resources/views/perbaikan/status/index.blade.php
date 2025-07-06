<!-- perbaikan/status/index -->
@extends('layouts.app')
@section('header', 'Status Perbaikan Aset')
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
                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Pilih Lokasi Aset</label>
                    <select id="lokasi" name="lokasi" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" {{ request('lokasi') == $location ? 'selected' : '' }}>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Pilih Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Status</option>
                        <option value="Diterima" {{ request('status') == 'Diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="Dikerjakan" {{ request('status') == 'Dikerjakan' ? 'selected' : '' }}>Dikerjakan</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
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
                        ID Laporan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama Aset
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Lokasi
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status Kerusakan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Pelapor
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Perbaikan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Petugas
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status Perbaikan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Selesai
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
                    {{ $request->asset->kode_ruangan }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @php
                        $tingkatKerusakan = $request->damagedAsset->tingkat_kerusakan;
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->damagedAsset->reporter_role ? $request->damagedAsset->reporter_role : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->tanggal_perbaikan ? date('d/m/Y', strtotime($request->tanggal_perbaikan)) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->damagedAsset->petugas ? $request->damagedAsset->petugas : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @php
                        $status = $request->status;
                        $statusClasses = [
                            'Diterima' => 'bg-blue-100 text-blue-800',
                            'Dikerjakan' => 'bg-yellow-100 text-yellow-800', 
                            'Selesai' => 'bg-green-100 text-green-800',
                            'Ditolak' => 'bg-red-100 text-red-800'
                        ];
                        $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                        $isDisabled = in_array($status, ['Selesai', 'Ditolak']);
                    @endphp
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                        {{ $status }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $request->tanggal_selesai ? date('d/m/Y', strtotime($request->tanggal_selesai)) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    @php
                        // Check individual fields
                        $deskripsiKerusakan = $request->damagedAsset->deskripsi_kerusakan ?? '';
                        $penyebabKerusakan = $request->penyebab_kerusakan ?? '';
                        $deskripsiPerbaikan = $request->deskripsi_perbaikan ?? '';
                        $hasilPerbaikan = $request->hasil_perbaikan ?? '';
                        
                        $allFieldsFilled = !empty($deskripsiKerusakan) && 
                                        !empty($penyebabKerusakan) && 
                                        !empty($deskripsiPerbaikan) && 
                                        !empty($hasilPerbaikan);
                        
                        // Count missing fields for tooltip
                        $missingFields = [];
                        if (empty($deskripsiKerusakan)) $missingFields[] = 'Deskripsi Kerusakan';
                        if (empty($penyebabKerusakan)) $missingFields[] = 'Penyebab Kerusakan';
                        if (empty($deskripsiPerbaikan)) $missingFields[] = 'Deskripsi Perbaikan';
                        if (empty($hasilPerbaikan)) $missingFields[] = 'Hasil Perbaikan';
                        
                        $tooltipText = $allFieldsFilled ? 'Semua detail telah lengkap' : 'Perlu melengkapi: ' . implode(', ', $missingFields);
                    @endphp
                    
                    @if($allFieldsFilled)
                        <!-- Show eye icon if all fields are filled -->
                        <a href="{{ route('perbaikan.status.show-done', $request->maintenance_id) }}" class="text-gray-600 hover:text-gray-900" title="{{ $tooltipText }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    @else
                        <!-- Show edit icon if fields are not complete -->
                        <a href="{{ route('perbaikan.status.show', $request->maintenance_id) }}" class="text-orange-600 hover:text-orange-900" title="{{ $tooltipText }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    @endif
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Detailed Pengajuan page loaded');
    
    // Auto-submit form when filters change
    document.getElementById('lokasi').addEventListener('change', function() {
        this.form.submit();
    });
    
    document.getElementById('petugas').addEventListener('change', function() {
        this.form.submit();
    });
    
    document.getElementById('status').addEventListener('change', function() {
        this.form.submit();
    });
    
    // Show success/error messages if any
    @if(session('success'))
        alert('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        alert('{{ session('error') }}');
    @endif
});
</script>
@endsection