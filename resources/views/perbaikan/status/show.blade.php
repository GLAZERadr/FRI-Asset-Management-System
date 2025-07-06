@extends('layouts.app')

@section('header', 'Detail Laporan Akhir Perbaikan Aset')

@section('content')
<div class="container mx-auto max-w-6xl">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 lg:p-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Detail Laporan Akhir Perbaikan Aset</h2>
                <a href="{{ route('perbaikan.status.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>

            <form action="{{ route('perbaikan.status.update', $maintenanceAsset->maintenance_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Asset Photo Section -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-100 rounded-lg p-4 text-center mb-4">
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
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Biaya Perbaikan</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->estimasi_biaya ? 'Rp ' . number_format($maintenanceAsset->damagedAsset->estimasi_biaya, 0, ',', '.') : '-' }}
                                    </p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Perbaikan</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $maintenanceAsset->tanggal_perbaikan ? $maintenanceAsset->tanggal_perbaikan->format('d-m-Y') : '-' }}
                                    </p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Jumlah Perbaikan</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $maintenanceCount }}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Total Biaya Perbaikan</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $totalCost ? 'Rp ' . number_format($totalCost, 0, ',', '.') : 'Rp 0' }}
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

                            <!-- Editable Fields -->
                            <div class="grid grid-cols-1 gap-4">
                                <!-- Penyebab Kerusakan -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <label for="penyebab_kerusakan" class="block text-sm font-medium text-gray-700 mb-2">Penyebab Kerusakan *</label>
                                    <textarea id="penyebab_kerusakan" 
                                              name="penyebab_kerusakan" 
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('penyebab_kerusakan') border-red-500 @enderror"
                                              placeholder="Jelaskan penyebab kerusakan..."
                                              required>{{ old('penyebab_kerusakan', $maintenanceAsset->penyebab_kerusakan) }}</textarea>
                                    @error('penyebab_kerusakan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Deskripsi Perbaikan -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <label for="deskripsi_perbaikan" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Perbaikan *</label>
                                    <textarea id="deskripsi_perbaikan" 
                                              name="deskripsi_perbaikan" 
                                              rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('deskripsi_perbaikan') border-red-500 @enderror"
                                              placeholder="Jelaskan perbaikan yang dilakukan..."
                                              required>{{ old('deskripsi_perbaikan', $maintenanceAsset->deskripsi_perbaikan) }}</textarea>
                                    @error('deskripsi_perbaikan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Hasil Perbaikan -->
                                <div class="bg-white p-4 rounded-lg border">
                                    <label for="hasil_perbaikan" class="block text-sm font-medium text-gray-700 mb-2">Hasil Perbaikan *</label>
                                    <select id="hasil_perbaikan" 
                                            name="hasil_perbaikan" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('hasil_perbaikan') border-red-500 @enderror"
                                            required>
                                        <option value="">Pilih Hasil Perbaikan</option>
                                        <option value="Sukses" {{ old('hasil_perbaikan', $maintenanceAsset->hasil_perbaikan) == 'Sukses' ? 'selected' : '' }}>Sukses</option>
                                        <option value="Perlu Tindak Lanjut" {{ old('hasil_perbaikan', $maintenanceAsset->hasil_perbaikan) == 'Perlu Tindak Lanjut' ? 'selected' : '' }}>Perlu Tindak Lanjut</option>
                                    </select>
                                    @error('hasil_perbaikan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-6 lg:mt-8 flex flex-col sm:flex-row sm:justify-end gap-3 sm:gap-2">
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-3 lg:py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 font-medium">
                        Simpan Laporan
                    </button>
                </div>
            </form>
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
    
    input[type="text"],
    input[type="file"],
    select,
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