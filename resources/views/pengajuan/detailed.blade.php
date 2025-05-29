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
                    <label for="petugas" class="block text-sm font-medium text-gray-700 mb-1">Pilih Petugas</label>
                    <select id="petugas" name="petugas" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Petugas</option>
                        <option value="Vendor" {{ request('petugas') == 'Vendor' ? 'selected' : '' }}>Vendor</option>
                        <option value="Staf" {{ request('petugas') == 'Staf' ? 'selected' : '' }}>Staf</option>
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
                            'Diterima' => 'bg-blue-100 text-blue-800',
                            'Dikerjakan' => 'bg-yellow-100 text-yellow-800', 
                            'Selesai' => 'bg-green-100 text-green-800',
                            'Ditolak' => 'bg-red-100 text-red-800'
                        ];
                        $class = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                        $isDisabled = in_array($status, ['Selesai', 'Ditolak']);
                    @endphp
                    @if($isDisabled)
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $class }}">
                            {{ $status }}
                        </span>
                    @else
                        <form action="{{ route('pengajuan.update-status', $request->id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()" 
                                    class="text-xs font-semibold rounded-full border-0 focus:ring-0 {{ $class }} appearance-none pr-6 pl-2 py-1">
                                <option value="Diterima" {{ $status == 'Diterima' ? 'selected' : '' }}>Diterima</option>
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
                    @if($request->estimasi_waktu_perbaikan)
                        <div class="flex flex-col items-end">
                            <span class="font-medium text-gray-900">
                                {{ $request->estimasi_waktu_perbaikan }}
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
                    {{ $request->teknisi ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        @if($request->status == 'Diterima')
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