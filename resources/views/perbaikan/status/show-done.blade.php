@extends('layouts.app')

@section('header', 'Detail Laporan Akhir Perbaikan Aset')

@section('content')
<div class="container mx-auto max-w-6xl">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 lg:p-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Detail Laporan Akhir Perbaikan Aset</h2>
                    <p class="text-sm text-gray-500 mt-1">Laporan Lengkap - Status: 
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Selesai
                        </span>
                    </p>
                </div>
                <a href="{{ route('perbaikan.status.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Asset Photo Section -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-100 rounded-lg p-4 text-center mb-4">
                        @if($maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->damaged_image)
                            <img src="{{ $maintenanceAsset->damagedAsset->damaged_image }}" 
                                 alt="Foto Kerusakan" 
                                 class="w-full h-64 object-cover rounded-lg mx-auto cursor-pointer"
                                 onclick="openImageModal('{{ $maintenanceAsset->damagedAsset->damaged_image }}')">
                            <p class="text-sm text-gray-500 mt-2">Foto Kerusakan</p>
                        @elseif($maintenanceAsset->asset && $maintenanceAsset->asset->foto_asset)
                            <img src="{{ $maintenanceAsset->asset->foto_asset }}" 
                                 alt="{{ $maintenanceAsset->asset->nama_asset }}" 
                                 class="w-full h-64 object-cover rounded-lg mx-auto cursor-pointer"
                                 onclick="openImageModal('{{ $maintenanceAsset->asset->foto_asset }}')">
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

                    <!-- Additional Photos if available -->
                    @if($maintenanceAsset->photos && count($maintenanceAsset->photos) > 0)
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Foto Perbaikan</h4>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($maintenanceAsset->photos as $photo)
                                <img src="{{ $photo }}" 
                                     alt="Foto Perbaikan" 
                                     class="w-full h-20 object-cover rounded cursor-pointer hover:opacity-80"
                                     onclick="openImageModal('{{ $photo }}')">
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Asset Information Section -->
                <div class="lg:col-span-2">
                    <div class="space-y-6">
                        <!-- Basic Asset Information -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Informasi Aset
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white p-3 rounded-lg border">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">ID Aset</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->damagedAsset->asset_id ?? '-' }}</p>
                                </div>
                                
                                <div class="bg-white p-3 rounded-lg border">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Nama Aset</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->asset->nama_asset ?? '-' }}</p>
                                </div>
                                
                                <div class="bg-white p-3 rounded-lg border">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Kategori Aset</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->asset->kategori ?? '-' }}</p>
                                </div>
                                
                                <div class="bg-white p-3 rounded-lg border">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Lokasi</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->asset->kode_ruangan ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Information -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-yellow-900 mb-4 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Informasi Perbaikan
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-white p-3 rounded-lg border">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Teknisi</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $maintenanceAsset->teknisi ?? '-' }}</p>
                                </div>
                                
                                <div class="bg-white p-3 rounded-lg border">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Perbaikan</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $maintenanceAsset->tanggal_perbaikan ? $maintenanceAsset->tanggal_perbaikan->format('d-m-Y') : '-' }}
                                    </p>
                                </div>
                                
                                <div class="bg-white p-3 rounded-lg border">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Biaya Perbaikan</label>
                                    <p class="text-lg font-semibold text-green-600">
                                        {{ $maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->estimasi_biaya ? 'Rp ' . number_format($maintenanceAsset->damagedAsset->estimasi_biaya, 0, ',', '.') : '-' }}
                                    </p>
                                </div>
                                
                                <div class="bg-white p-3 rounded-lg border">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Selesai</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        {{ $maintenanceAsset->tanggal_selesai ? $maintenanceAsset->tanggal_selesai->format('d-m-Y') : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Descriptions -->
                        <div class="space-y-4">
                            <!-- Deskripsi Kerusakan -->
                            @if($maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->deskripsi_kerusakan)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <label class="block text-sm font-medium text-red-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                    Deskripsi Kerusakan
                                </label>
                                <div class="bg-white p-3 rounded border">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $maintenanceAsset->damagedAsset->deskripsi_kerusakan }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Penyebab Kerusakan -->
                            @if($maintenanceAsset->penyebab_kerusakan)
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                                <label class="block text-sm font-medium text-orange-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Penyebab Kerusakan
                                </label>
                                <div class="bg-white p-3 rounded border">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $maintenanceAsset->penyebab_kerusakan }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Deskripsi Perbaikan -->
                            @if($maintenanceAsset->deskripsi_perbaikan)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <label class="block text-sm font-medium text-blue-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Deskripsi Perbaikan
                                </label>
                                <div class="bg-white p-3 rounded border">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $maintenanceAsset->deskripsi_perbaikan }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Hasil Perbaikan -->
                            @if($maintenanceAsset->hasil_perbaikan)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <label class="block text-sm font-medium text-green-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Hasil Perbaikan
                                </label>
                                <div class="bg-white p-3 rounded border">
                                    @php
                                        $resultClass = $maintenanceAsset->hasil_perbaikan === 'Sukses' 
                                            ? 'bg-green-100 text-green-800 border-green-200' 
                                            : 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                    @endphp
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full border {{ $resultClass }}">
                                        {{ $maintenanceAsset->hasil_perbaikan }}
                                    </span>
                                </div>
                            </div>
                            @endif

                            <!-- Rekomendasi if available -->
                            @if($maintenanceAsset->rekomendasi)
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <label class="block text-sm font-medium text-purple-700 mb-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    Rekomendasi
                                </label>
                                <div class="bg-white p-3 rounded border">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $maintenanceAsset->rekomendasi }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal for enlarged view -->
<div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50" onclick="closeImageModal()">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Foto Detail</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <img id="modalImage" src="" alt="Foto Detail" class="w-full h-auto rounded max-h-96 object-contain">
        </div>
    </div>
</div>

<style>
/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white;
    }
    
    .container {
        max-width: none;
        margin: 0;
        padding: 0;
    }
    
    .shadow-md {
        box-shadow: none;
    }
    
    .bg-gray-100, .bg-blue-50, .bg-yellow-50, .bg-red-50, .bg-orange-50, .bg-green-50, .bg-purple-50 {
        background: #f8f9fa !important;
    }
}

/* Responsive styles */
@media (max-width: 768px) {
    .grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    .space-y-6 > * + * {
        margin-top: 1rem;
    }
}

/* Hover effects */
.cursor-pointer:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}
</style>

<script>
function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endsection