@extends('layouts.app')

@section('header', 'Tambah Rekomendasi Perbaikan')

@section('content')
<div class="container mx-auto max-w-6xl">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 lg:p-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Tambah Rekomendasi Perbaikan</h2>
                <a href="{{ route('perbaikan.status.done') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
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
                        @if($maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->damaged_image)
                            <img src="{{ $maintenanceAsset->damagedAsset->damaged_image }}" 
                                 alt="Foto Kerusakan" 
                                 class="w-full h-64 object-cover rounded-lg mx-auto">
                            <p class="text-sm text-gray-500 mt-2">Foto Kerusakan</p>
                        @elseif($maintenanceAsset->asset && $maintenanceAsset->asset->foto_asset)
                            <img src="{{ $maintenanceAsset->asset->foto_asset }}" 
                                 alt="{{ $maintenanceAsset->asset->nama_asset }}" 
                                 class="w-full h-64 object-cover rounded-lg mx-auto">
                            <p class="text-sm text-gray-500 mt-2">Foto Aset</p>
                        @else
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Tidak Ada Foto</p>
                        @endif
                    </div>
                </div>
                
                <!-- Asset Information Section -->
                <div class="lg:col-span-2">
                    <div class="space-y-4">
                        <!-- Basic Asset Information (Read Only) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">ID Maintenance</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->maintenance_id }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">ID Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->damagedAsset->asset_id ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Nama Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->asset->nama_asset ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kategori Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->asset->kategori ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Teknisi</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->teknisi ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Selesai</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ $maintenanceAsset->tanggal_selesai ? $maintenanceAsset->tanggal_selesai->format('d-m-Y') : '-' }}
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Hasil Perbaikan</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    @if($maintenanceAsset->hasil_perbaikan)
                                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $maintenanceAsset->hasil_perbaikan === 'Sukses' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $maintenanceAsset->hasil_perbaikan }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        {{ $maintenanceAsset->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Deskripsi Kerusakan (Read Only) -->
                        @if($maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->deskripsi_kerusakan)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-2">Deskripsi Kerusakan</label>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $maintenanceAsset->damagedAsset->deskripsi_kerusakan }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Penyebab Kerusakan (Read Only) -->
                        @if($maintenanceAsset->penyebab_kerusakan)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-2">Penyebab Kerusakan</label>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $maintenanceAsset->penyebab_kerusakan }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Deskripsi Perbaikan (Read Only) -->
                        @if($maintenanceAsset->deskripsi_perbaikan)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-2">Deskripsi Perbaikan</label>
                            <div class="bg-white p-3 rounded border">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $maintenanceAsset->deskripsi_perbaikan }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Rekomendasi Form -->
                        <form action="{{ route('perbaikan.status.recommendation.update', $maintenanceAsset->maintenance_id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="bg-white p-4 rounded-lg border border-blue-200">
                                <label for="rekomendasi" class="block text-sm font-medium text-gray-700 mb-2">Rekomendasi</label>
                                <textarea id="rekomendasi" 
                                          name="rekomendasi" 
                                          rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('rekomendasi') border-red-500 @enderror"
                                          placeholder="Berikan rekomendasi untuk pencegahan kerusakan di masa depan atau saran perbaikan...">{{ old('rekomendasi', $maintenanceAsset->rekomendasi) }}</textarea>
                                @error('rekomendasi')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex flex-col sm:flex-row sm:justify-between gap-3 sm:gap-2">
                                <a href="{{ route('perbaikan.status.done') }}" 
                                   class="w-full sm:w-auto px-6 py-3 lg:py-2 bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 text-center font-medium">
                                    Kembali
                                </a>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-6 py-3 lg:py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 font-medium">
                                    Simpan Rekomendasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Additional mobile-specific styles */
@media (max-width: 768px) {
    .form-field {
        margin-bottom: 1rem;
    }
    
    input:focus, select:focus, textarea:focus {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
    
    textarea {
        font-size: 16px;
    }
    
    button, .button {
        min-height: 48px;
        font-size: 16px;
    }
}

input, select, textarea, button {
    transition: all 0.2s ease;
}

input:focus, select:focus, textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
@endsection