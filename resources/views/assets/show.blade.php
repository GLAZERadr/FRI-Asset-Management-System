@extends('layouts.app')

@section('header', 'Detail Aset')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Detail Aset</h2>
                <a href="{{ route('pemantauan.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Asset Photo Section -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-100 rounded-lg p-4 text-center">
                        @if($asset->foto_asset)
                            <img src="{{ Storage::url($asset->foto_asset) }}" 
                                 alt="{{ $asset->nama_asset }}" 
                                 class="w-full h-full object-cover rounded-lg mx-auto">
                        @else
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        <p class="text-sm text-gray-500 mt-2">Foto Aset</p>
                    </div>
                </div>
                
                <!-- Asset Information Section -->
                <div class="lg:col-span-2">
                    <div class="space-y-4">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">ID Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset->asset_id }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Nama Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset->nama_asset }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kategori Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset->kategori ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kode Ruangan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset->kode_ruangan ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Lokasi</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset->lokasi ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kondisi Aset</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        {{ $asset->status_kelayakan === 'Layak' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $asset->status_kelayakan ?? 'Tidak Diketahui' }}
                                    </span>
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Perolehan</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $asset->tgl_perolehan ? $asset->tgl_perolehan->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Sumber Perolehan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset->sumber_perolehan ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Nilai Perolehan</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $asset->nilai_perolehan ? 'Rp ' . number_format($asset->nilai_perolehan, 0, ',', '.') : '-' }}
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Masa Pakai</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $asset->formatted_masa_pakai ?? '-' }}
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Masa Pakai Maksimum</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $asset->masa_pakai_maksimum ? $asset->masa_pakai_maksimum->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Tingkat Kepentingan</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    @if($asset->tingkat_kepentingan_asset)
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            {{ $asset->tingkat_kepentingan_asset >= 8 ? 'bg-red-100 text-red-800' : 
                                               ($asset->tingkat_kepentingan_asset >= 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $asset->tingkat_kepentingan_asset }}/10
                                        </span>
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <!-- Specifications Section -->
                        @if($asset->spesifikasi)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-2">Spesifikasi</label>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $asset->spesifikasi }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection