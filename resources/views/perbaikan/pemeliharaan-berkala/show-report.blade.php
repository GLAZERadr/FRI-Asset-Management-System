@extends('layouts.app')
@section('header', 'Detail Laporan Pemeliharaan')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Detail Laporan Pemeliharaan</h2>
                <a href="{{ route('perbaikan.pemeliharaan-berkala.report') }}" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Photo Carousel Section -->
                <div class="lg:col-span-1">
                    @if($schedule->photos && count($schedule->photos) > 0)
                        <div class="bg-gray-100 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Dokumentasi Pemeliharaan</h3>
                            
                            <!-- Carousel Container -->
                            <div class="relative">
                                <div id="carousel" class="overflow-hidden rounded-lg">
                                    <div id="carousel-inner" class="flex transition-transform duration-300 ease-in-out">
                                        @foreach($schedule->photos as $index => $photo)
                                        <div class="w-full flex-shrink-0">
                                            <img src="{{ $photo }}" 
                                                 alt="Dokumentasi {{ $index + 1 }}" 
                                                 class="w-full h-full object-cover rounded-lg">
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- Carousel Controls -->
                                @if(count($schedule->photos) > 1)
                                <button id="prev-btn" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <button id="next-btn" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                
                                <!-- Dots Indicator -->
                                <div class="flex justify-center mt-3 space-x-2">
                                    @foreach($schedule->photos as $index => $photo)
                                    <button class="carousel-dot w-2 h-2 rounded-full bg-gray-400 hover:bg-gray-600 transition-colors {{ $index === 0 ? 'bg-gray-700' : '' }}" 
                                            data-index="{{ $index }}"></button>
                                    @endforeach
                                </div>
                                @endif
                                
                                <!-- Photo Counter -->
                                <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm">
                                    <span id="current-photo">1</span> / {{ count($schedule->photos) }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-100 rounded-lg p-4 text-center">
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Tidak ada dokumentasi</p>
                        </div>
                    @endif
                </div>
                
                <!-- Report Information Section -->
                <div class="lg:col-span-2">
                    <div class="space-y-4">
                        <!-- Status Info -->
                        <div class="bg-gray-50 p-4 rounded-lg border-l-4 
                            @if($schedule->status == 'completed') border-green-500 
                            @else border-yellow-500 @endif">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($schedule->status == 'completed')
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium 
                                        @if($schedule->status == 'completed') text-green-800 
                                        @else text-yellow-800 @endif">
                                        Status Pemeliharaan: 
                                        @if($schedule->status == 'completed') Selesai
                                        @else Terjadwal
                                        @endif
                                    </h3>
                                    @if($schedule->updated_at)
                                    <p class="text-sm text-gray-600">
                                        Terakhir diperbarui: {{ $schedule->updated_at->format('d F Y, H:i') }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">ID Laporan</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    LP-{{ date('Ymd', strtotime($schedule->tanggal_pemeliharaan)) }}-{{ str_pad($schedule->id, 3, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Lokasi</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->lokasi }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Pemeliharaan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->tanggal_pemeliharaan->format('d F Y') }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Dibuat Oleh</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->created_by ?? 'System' }}</p>
                            </div>
                        </div>
                        
                        <!-- Description and Notes -->
                        @if($schedule->deskripsi_pemeliharaan)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Deskripsi Pemeliharaan</label>
                            <p class="text-lg text-gray-900">{{ $schedule->deskripsi_pemeliharaan }}</p>
                        </div>
                        @endif
                        
                        @if($schedule->catatan_tindak_lanjut)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Catatan/Tindak Lanjut</label>
                            <p class="text-lg text-gray-900">{{ $schedule->catatan_tindak_lanjut }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Carousel -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('carousel-inner');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const dots = document.querySelectorAll('.carousel-dot');
    const currentPhotoSpan = document.getElementById('current-photo');
    
    if (!carousel) return; // Exit if no carousel
    
    const totalPhotos = {{ count($schedule->photos ?? []) }};
    let currentIndex = 0;

    function updateCarousel() {
        const translateX = -currentIndex * 100;
        carousel.style.transform = `translateX(${translateX}%)`;
        
        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.toggle('bg-gray-700', index === currentIndex);
            dot.classList.toggle('bg-gray-400', index !== currentIndex);
        });
        
        // Update counter
        if (currentPhotoSpan) {
            currentPhotoSpan.textContent = currentIndex + 1;
        }
    }

    function nextPhoto() {
        currentIndex = (currentIndex + 1) % totalPhotos;
        updateCarousel();
    }

    function prevPhoto() {
        currentIndex = (currentIndex - 1 + totalPhotos) % totalPhotos;
        updateCarousel();
    }

    // Event listeners
    if (nextBtn) {
        nextBtn.addEventListener('click', nextPhoto);
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', prevPhoto);
    }

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentIndex = index;
            updateCarousel();
        });
    });

    // Auto-slide (optional)
    setInterval(nextPhoto, 5000); // Change slide every 5 seconds
});
</script>

@endsection