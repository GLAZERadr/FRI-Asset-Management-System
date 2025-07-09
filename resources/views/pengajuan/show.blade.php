<!-- resources/views/pengajuan/show.blade.php -->
@extends('layouts.app')
@section('header', 'Detail Pengajuan Perbaikan')
@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Informasi Pengajuan</h2>
                <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-500">Status:</span>
                    @php
                        $statusClasses = [
                            'Diterima' => 'bg-blue-100 text-blue-800',
                            'Dikerjakan' => 'bg-yellow-100 text-yellow-800',
                            'Selesai' => 'bg-green-100 text-green-800',
                            'Ditolak' => 'bg-red-100 text-red-800'
                        ];
                        $statusClass = $statusClasses[$maintenanceAsset->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                        {{ $maintenanceAsset->status }}
                    </span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Aset</h3>
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500 w-1/3">ID Aset</td>
                                <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->asset->asset_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Nama Aset</td>
                                <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->asset->nama_asset ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Lokasi</td>
                                <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->asset->lokasi ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Kategori</td>
                                <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->asset->kategori ?? 'Tidak ada' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Kerusakan</h3>
                    @if($maintenanceAsset->damagedAsset)
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500 w-1/3">ID Kerusakan</td>
                                <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->damagedAsset->damage_id }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Tingkat Kerusakan</td>
                                <td class="py-2">
                                    @if($maintenanceAsset->damagedAsset->tingkat_kerusakan)
                                        @php
                                            $tingkatKerusakan = $maintenanceAsset->damagedAsset->tingkat_kerusakan;
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
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            N/A
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Deskripsi Kerusakan</td>
                                <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->damagedAsset->deskripsi_kerusakan ?? 'Tidak ada deskripsi' }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Estimasi Biaya</td>
                                <td class="py-2 text-sm text-gray-900">
                                    @if($maintenanceAsset->damagedAsset->estimasi_biaya)
                                        Rp {{ number_format($maintenanceAsset->damagedAsset->estimasi_biaya, 0, ',', '.') }}
                                    @else
                                        Belum ditentukan
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Tanggal Pelaporan</td>
                                <td class="py-2 text-sm text-gray-900">
                                    @if($maintenanceAsset->damagedAsset->tanggal_pelaporan)
                                        {{ \Carbon\Carbon::parse($maintenanceAsset->damagedAsset->tanggal_pelaporan)->format('d/m/Y') }}
                                    @else
                                        Tidak ada data
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Pelapor</td>
                                <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->damagedAsset->pelapor ?? 'Tidak diketahui' }}</td>
                            </tr>
                            @if($maintenanceAsset->damagedAsset->vendor)
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-500">Vendor</td>
                                <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->damagedAsset->vendor }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-2">
                            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">Tidak ada data kerusakan tersedia</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Pengajuan Perbaikan</h3>
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="py-2 text-sm font-medium text-gray-500 w-1/4">ID Pengajuan</td>
                            <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->maintenance_id }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 text-sm font-medium text-gray-500">Tanggal Pengajuan</td>
                            <td class="py-2 text-sm text-gray-900">
                                @if($maintenanceAsset->tanggal_pengajuan)
                                    {{ \Carbon\Carbon::parse($maintenanceAsset->tanggal_pengajuan)->format('d/m/Y H:i') }}
                                @else
                                    Tidak ada data
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-sm font-medium text-gray-500">Tanggal Perbaikan</td>
                            <td class="py-2 text-sm text-gray-900">
                                @if($maintenanceAsset->tanggal_perbaikan)
                                    {{ \Carbon\Carbon::parse($maintenanceAsset->tanggal_perbaikan)->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">Belum ditentukan</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-sm font-medium text-gray-500">Tanggal Selesai</td>
                            <td class="py-2 text-sm text-gray-900">
                                @if($maintenanceAsset->tanggal_selesai)
                                    {{ \Carbon\Carbon::parse($maintenanceAsset->tanggal_selesai)->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">Belum selesai</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-sm font-medium text-gray-500">Estimasi Waktu Perbaikan</td>
                            <td class="py-2 text-sm text-gray-900">
                                @if($maintenanceAsset->estimasi_waktu_perbaikan)
                                    {{ $maintenanceAsset->estimasi_waktu_perbaikan }}
                                @else
                                    <span class="text-gray-400">Belum ditentukan</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 text-sm font-medium text-gray-500">Teknisi</td>
                            <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->teknisi ?? 'Belum ditentukan' }}</td>
                        </tr>
                        @if($maintenanceAsset->notes)
                        <tr>
                            <td class="py-2 text-sm font-medium text-gray-500">Catatan</td>
                            <td class="py-2 text-sm text-gray-900">{{ $maintenanceAsset->notes }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Photo Gallery Section -->
            @if($maintenanceAsset->status === 'Selesai' && $maintenanceAsset->photos && count($maintenanceAsset->photos) > 0)
            <div class="mt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Foto Penyelesaian Perbaikan</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">{{ count($maintenanceAsset->photos) }} foto</span>
                        <button onclick="downloadAllPhotos()" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download All
                        </button>
                    </div>
                </div>

                <!-- Photo Carousel -->
                <div class="relative">
                    <div class="photo-carousel bg-gray-100 rounded-lg overflow-hidden">
                        <!-- Main Image Display -->
                        <div id="mainImageContainer" class="relative h-96 bg-gray-900">
                            <img id="mainImage" 
                                 src="{{ $maintenanceAsset->photos[0]['path'] }}" 
                                 alt="Foto perbaikan {{ $maintenanceAsset->photos[0]['original_name'] ?? 'foto-1' }}"
                                 class="w-full h-full object-contain">
                            
                            <!-- Navigation Arrows -->
                            @if(count($maintenanceAsset->photos) > 1)
                            <div class="p-4 bg-white">
                                <div class="flex space-x-2 overflow-x-auto">
                                    @foreach($maintenanceAsset->photos as $index => $photo)
                                    <div class="flex-shrink-0">
                                        <button onclick="showPhoto({{ $index }})" 
                                                class="thumbnail-btn block w-20 h-20 rounded-lg overflow-hidden border-2 transition-all {{ $index === 0 ? 'border-blue-500' : 'border-gray-300 hover:border-gray-400' }}"
                                                data-index="{{ $index }}">
                                            <img src="{{ $photo['path'] }}" 
                                                alt="Thumbnail {{ $index + 1 }}"
                                                class="w-full h-full object-cover"
                                                onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Photo Counter -->
                            <div class="absolute top-4 right-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
                                <span id="photoCounter">1</span> / {{ count($maintenanceAsset->photos) }}
                            </div>

                            <!-- Download Button for Current Photo -->
                            <div class="absolute bottom-4 right-4">
                                <button onclick="downloadCurrentPhoto()" class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition-all" title="Download foto ini">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Thumbnail Navigation -->
                        @if(count($maintenanceAsset->photos) > 1)
                        <div class="p-4 bg-white">
                            <div class="flex space-x-2 overflow-x-auto">
                                @foreach($maintenanceAsset->photos as $index => $photo)
                                <div class="flex-shrink-0">
                                    <button onclick="showPhoto({{ $index }})" 
                                            class="thumbnail-btn block w-20 h-20 rounded-lg overflow-hidden border-2 transition-all {{ $index === 0 ? 'border-blue-500' : 'border-gray-300 hover:border-gray-400' }}"
                                            data-index="{{ $index }}">
                                        <img src="{{ $photo['path'] }}" 
                                             alt="Thumbnail {{ $index + 1 }}"
                                             class="w-full h-full object-cover">
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Photo Info -->
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p id="photoName" class="text-sm font-medium text-gray-900">{{ $maintenanceAsset->photos[0]['original_name'] ?? 'foto-1.jpg' }}</p>
                                <p id="photoDate" class="text-xs text-gray-500">
                                    Diupload: {{ isset($maintenanceAsset->photos[0]['uploaded_at']) ? \Carbon\Carbon::parse($maintenanceAsset->photos[0]['uploaded_at'])->format('d/m/Y H:i') : 'Tidak diketahui' }}
                                </p>
                            </div>
                            <button onclick="openFullscreen()" class="text-gray-600 hover:text-gray-800 transition-colors" title="Lihat fullscreen">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-between">
                <div>
                    <a href="{{ route('pengajuan.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Modal -->
<div id="fullscreenModal" class="fixed inset-0 bg-black bg-opacity-90 hidden z-50 flex items-center justify-center">
    <div class="relative w-full h-full flex items-center justify-center p-4">
        <img id="fullscreenImage" src="" alt="" class="max-w-full max-h-full object-contain">
        
        <!-- Close Button -->
        <button onclick="closeFullscreen()" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Navigation in Fullscreen -->
        @if($maintenanceAsset->status === 'Selesai' && $maintenanceAsset->photos && count($maintenanceAsset->photos) > 1)
        <button onclick="previousPhoto()" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button onclick="nextPhoto()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 transition-colors">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
        @endif
    </div>
</div>

<script>
@if($maintenanceAsset->status === 'Selesai' && $maintenanceAsset->photos && count($maintenanceAsset->photos) > 0)
// Photo data
const photos = @json($maintenanceAsset->photos);
let currentPhotoIndex = 0;

// Initialize carousel
document.addEventListener('DOMContentLoaded', function() {
    showPhoto(0);
});

// Show specific photo
function showPhoto(index) {
    if (index < 0 || index >= photos.length) return;
    
    currentPhotoIndex = index;
    const photo = photos[index];
    
    // Fix: Use photo.path directly (it should be a full Cloudinary URL)
    // Or construct it properly if it's a relative path
    let photoPath = photo.path;
    
    // If photo.path is not a full URL (doesn't start with http), construct it
    if (!photoPath.startsWith('http')) {
        // If it's stored as a relative path, construct the full URL
        photoPath = "{{ asset('storage/') }}/" + photo.path;
    }
    
    // Update main image
    const mainImage = document.getElementById('mainImage');
    mainImage.src = photoPath;
    mainImage.alt = photo.original_name || `foto-${index + 1}`;
    
    // Update fullscreen image
    const fullscreenImage = document.getElementById('fullscreenImage');
    fullscreenImage.src = photoPath;
    fullscreenImage.alt = photo.original_name || `foto-${index + 1}`;
    
    // Update counter
    document.getElementById('photoCounter').textContent = index + 1;
    
    // Update photo info
    document.getElementById('photoName').textContent = photo.original_name || `foto-${index + 1}.jpg`;
    const uploadDate = photo.uploaded_at ? new Date(photo.uploaded_at).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) : 'Tidak diketahui';
    document.getElementById('photoDate').textContent = `Diupload: ${uploadDate}`;
    
    // Update thumbnail selection
    document.querySelectorAll('.thumbnail-btn').forEach((btn, i) => {
        if (i === index) {
            btn.classList.remove('border-gray-300');
            btn.classList.add('border-blue-500');
        } else {
            btn.classList.remove('border-blue-500');
            btn.classList.add('border-gray-300');
        }
    });
}

// Navigation functions
function nextPhoto() {
    const nextIndex = (currentPhotoIndex + 1) % photos.length;
    showPhoto(nextIndex);
}

function previousPhoto() {
    const prevIndex = currentPhotoIndex === 0 ? photos.length - 1 : currentPhotoIndex - 1;
    showPhoto(prevIndex);
}

// Fullscreen functions
function openFullscreen() {
    document.getElementById('fullscreenModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFullscreen() {
    document.getElementById('fullscreenModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Download functions
function downloadCurrentPhoto() {
    const maintenanceId = "{{ $maintenanceAsset->id }}";
    const downloadUrl = `/pengajuan/${maintenanceId}/photo/${currentPhotoIndex}/download`;
    
    // Create invisible link and trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function downloadAllPhotos() {
    if (confirm(`Download semua ${photos.length} foto dalam file ZIP?`)) {
        const maintenanceId = "{{ $maintenanceAsset->id }}";
        const downloadUrl = `/pengajuan/${maintenanceId}/photos/download-all`;
        
        // Show loading indicator
        const downloadBtn = document.querySelector('button[onclick="downloadAllPhotos()"]');
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Membuat ZIP...';
        downloadBtn.disabled = true;
        
        // Create invisible link and trigger download
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button after 3 seconds
        setTimeout(() => {
            downloadBtn.innerHTML = originalText;
            downloadBtn.disabled = false;
        }, 3000);
    }
}


function downloadFile(url, filename) {
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const fullscreenModal = document.getElementById('fullscreenModal');
    
    if (!fullscreenModal.classList.contains('hidden')) {
        if (e.key === 'Escape') {
            closeFullscreen();
        } else if (e.key === 'ArrowLeft') {
            previousPhoto();
        } else if (e.key === 'ArrowRight') {
            nextPhoto();
        }
    }
});

// Close fullscreen when clicking outside image
document.getElementById('fullscreenModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFullscreen();
    }
});
@endif

function updateStatus(newStatus) {
    if (confirm('Apakah Anda yakin ingin mengubah status menjadi ' + newStatus + '?')) {
        document.getElementById('status-input').value = newStatus;
        document.getElementById('status-update-form').submit();
    }
}
</script>
@endsection