@extends('layouts.app')

@section('header', 'Data Aset')

@section('content')
<div class="container mx-auto max-w-7xl">
    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-4 lg:p-6 mb-4 lg:mb-6">
        <form action="{{ route('pemantauan.index') }}" method="GET" class="space-y-4 lg:space-y-0 lg:grid lg:grid-cols-4 lg:gap-4 lg:items-end">
            <div>
                <label for="nama_asset" class="block text-sm font-medium text-gray-700 mb-1">Nama Asset</label>
                <input type="text" id="nama_asset" name="nama_asset" value="{{ request('nama_asset') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                       placeholder="Cari nama asset...">
            </div>
            
            <div>
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select id="kategori" name="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('kategori') == $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="tahun_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Tahun Perolehan</label>
                <select id="tahun_perolehan" name="tahun_perolehan" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('tahun_perolehan') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('pemantauan.index') }}" class="flex-1 sm:flex-none px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Action Buttons -->
    <div class="mb-4 lg:mb-6 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3">
        <a href="{{ route('pemantauan.export-pdf', request()->all()) }}" target="_blank" 
           class="px-4 py-2 bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span class="hidden sm:inline">Cetak</span>
            <span class="sm:hidden">PDF</span>
        </a>

        <a href="{{ route('pemantauan.create') }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span class="hidden sm:inline">Tambah Data Aset</span>
            <span class="sm:hidden">Tambah</span>
        </a>
    </div>

    <!-- Data Aset Tab -->
    <div class="mb-4">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <a href="#" class="border-b-2 border-green-500 py-2 px-4 text-sm font-medium text-green-600">
                    Data Aset
                </a>
            </nav>
        </div>
    </div>

    <!-- Assets Table - Mobile Cards / Desktop Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Aset</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Ruangan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Perolehan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Perolehan</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($assets as $index => $asset)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $assets->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $asset->asset_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->nama_asset }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->kategori }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->kode_ruangan }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($asset->tgl_perolehan)->format('d-m-Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            Rp{{ number_format($asset->nilai_perolehan, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-2">
                                <!-- View Button -->
                                <a href="{{ route('pemantauan.show', $asset->asset_id) }}" class="text-gray-600 hover:text-gray-900" title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-5 h-5 fill-current">
                                        <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
                                    </svg>
                                </a>
                                
                                <!-- QR Code Download Button -->
                                @if($asset->asset_id)
                                    <a href="{{ route('pemantauan.qr-download', ['asset_id' => $asset->asset_id]) }}" class="text-green-600 hover:text-green-900" title="Download QR Code">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-gray-400" title="Kode ruangan tidak tersedia">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900">Tidak Ada Data</h3>
                                <p class="text-gray-500">Belum ada aset yang ditambahkan ke sistem.</p>
                                <a href="{{ route('pemantauan.create') }}" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    Tambah Aset Baru
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden">
            @forelse ($assets as $index => $asset)
            <div class="border-b border-gray-200 p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <!-- Asset ID and Name -->
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $asset->asset_id }}
                            </span>
                            @if($asset->foto_asset)
                                <img src="{{ asset('storage/' . $asset->foto_asset) }}" alt="Asset Image" class="h-8 w-8 rounded object-cover">
                            @endif
                        </div>
                        
                        <h3 class="text-sm font-medium text-gray-900 mb-1 truncate">
                            {{ $asset->nama_asset }}
                        </h3>
                        
                        <!-- Key Information -->
                        <div class="space-y-1 text-xs text-gray-500">
                            <div class="flex justify-between">
                                <span>Kategori:</span>
                                <span class="font-medium">{{ $asset->kategori }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Ruangan:</span>
                                <span class="font-medium">{{ $asset->kode_ruangan }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tanggal:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($asset->tgl_perolehan)->format('d-m-Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Nilai:</span>
                                <span class="font-medium text-green-600">Rp{{ number_format($asset->nilai_perolehan, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <!-- Expandable Details -->
                        <div class="mt-2" x-data="{ expanded: false }">
                            <button @click="expanded = !expanded" class="text-xs text-blue-600 hover:text-blue-800">
                                <span x-text="expanded ? 'Sembunyikan Detail' : 'Lihat Detail'"></span>
                            </button>
                            <div x-show="expanded" x-transition class="mt-2 space-y-1 text-xs text-gray-500" style="display: none;">
                                <div><strong>Spesifikasi:</strong> {{ $asset->spesifikasi }}</div>
                                <div><strong>Sumber Perolehan:</strong> {{ $asset->sumber_perolehan }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col space-y-2 ml-4">
                        <a href="{{ route('pemantauan.show', $asset->asset_id) }}" 
                           class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-md" 
                           title="Lihat Detail">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-4 h-4 fill-current">
                                <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
                            </svg>
                        </a>
                        
                        @if($asset->asset_id)
                            <a href="{{ route('pemantauan.qr-download', ['asset_id' => $asset->asset_id]) }}" 
                               class="p-2 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-md" 
                               title="Download QR Code">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </a>
                        @else
                            <span class="p-2 text-gray-400" title="QR Code tidak tersedia">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                </svg>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Data</h3>
                <p class="text-gray-500 mb-4">Belum ada aset yang ditambahkan ke sistem.</p>
                <a href="{{ route('pemantauan.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Aset Baru
                </a>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($assets->hasPages())
        <div class="px-4 lg:px-6 py-3 border-t border-gray-200">
            {{ $assets->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection