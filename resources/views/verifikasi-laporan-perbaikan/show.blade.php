@extends('layouts.app')
@section('header', 'Detail Laporan Perbaikan')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Detail Laporan Perbaikan</h2>
                <a href="{{ route('pemantauan.index') }}" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
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
                        @if($damagedAsset->damaged_image)
                            <img src="{{ Storage::url($damagedAsset->damaged_image) }}" 
                                 alt="{{ $damagedAsset->asset->nama_asset }}" 
                                 class="w-full h-full object-cover rounded-lg mx-auto">
                        @else
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                        <p class="text-sm text-gray-500 mt-2">Foto Kerusakan Aset</p>
                    </div>
                </div>
                
                <!-- Asset Information Section -->
                <div class="lg:col-span-2">
                    <div class="space-y-4">
                        <!-- Verification Status -->
                        <div class="bg-gray-50 p-4 rounded-lg border-l-4 
                            @if($damagedAsset->verified == 'Yes') border-green-500 
                            @elseif($damagedAsset->verified == 'No') border-red-500 
                            @else border-yellow-500 @endif">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($damagedAsset->verified == 'Yes')
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @elseif($damagedAsset->verified == 'No')
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium 
                                        @if($damagedAsset->verified == 'Yes') text-green-800 
                                        @elseif($damagedAsset->verified == 'No') text-red-800 
                                        @else text-yellow-800 @endif">
                                        Status Verifikasi: 
                                        @if($damagedAsset->verified == 'Yes') Terverifikasi
                                        @elseif($damagedAsset->verified == 'No') Ditolak
                                        @else Pending
                                        @endif
                                    </h3>
                                    @if($damagedAsset->verified_at)
                                    <p class="text-sm text-gray-600">
                                        Diverifikasi pada: {{ \Carbon\Carbon::parse($damagedAsset->verified_at)->format('d F Y') }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">ID Verifikasi</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->damage_id ?? 'VER-' . str_pad($damagedAsset->id, 4, '0', STR_PAD_LEFT) }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">ID Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->asset->asset_id ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Nama Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->asset->nama_asset ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kategori Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->asset->kategori ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Lokasi Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->asset->lokasi ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kode Ruangan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->asset->kode_ruangan ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Pelapor</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->reporter_name ?? $damagedAsset->reporter_role ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Role Pelapor</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->reporter_role ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Pelaporan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->tanggal_pelaporan ? \Carbon\Carbon::parse($damagedAsset->tanggal_pelaporan)->format('d F Y') : '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Reviewer</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->reviewer ?? 'Admin System' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Status Kerusakan</label>
                                <div class="flex items-center">
                                    @php
                                        $tingkatKerusakan = $damagedAsset->tingkat_kerusakan;
                                        $badgeClasses = [
                                            'Ringan' => 'bg-blue-100 text-blue-800',
                                            'Sedang' => 'bg-yellow-100 text-yellow-800',
                                            'Berat' => 'bg-red-100 text-red-800'
                                        ];
                                        $class = $badgeClasses[$tingkatKerusakan] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $class }}">
                                        {{ $tingkatKerusakan ?? '-' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Estimasi Biaya</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    @if($damagedAsset->estimasi_biaya)
                                        Rp {{ number_format($damagedAsset->estimasi_biaya, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <!-- Description and Additional Info -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Deskripsi Kerusakan</label>
                            <p class="text-lg text-gray-900">{{ $damagedAsset->deskripsi_kerusakan ?? 'Tidak ada deskripsi kerusakan.' }}</p>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Estimasi Waktu Perbaikan</label>
                            <p class="text-lg font-semibold text-gray-900">
                                @if($damagedAsset->estimasi_waktu_perbaikan)
                                    @php
                                        $totalHours = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($damagedAsset->estimasi_waktu_perbaikan));
                                        $days = intval($totalHours / 24);
                                        $hours = $totalHours % 24;
                                    @endphp
                                    {{ $days }} hari {{ $hours }} jam
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        
                        @if($damagedAsset->vendor)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Vendor</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $damagedAsset->vendor }}</p>
                        </div>
                        @endif
                        
                        @if($damagedAsset->alasan_penolakan)
                        <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                            <label class="block text-sm font-medium text-red-600 mb-1">Alasan Penolakan</label>
                            <p class="text-lg text-red-800">{{ $damagedAsset->alasan_penolakan }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection