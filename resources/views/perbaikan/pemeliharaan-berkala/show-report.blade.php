@extends('layouts.app')
@section('header', 'Detail Laporan Pemeliharaan')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Detail Laporan Pemeliharaan</h2>
                <div class="flex space-x-2">
                    @if($schedule->status == 'Selesai')
                        <a href="{{ route('perbaikan.pemeliharaan-berkala.download-pdf', $schedule->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download PDF
                        </a>
                    @endif
                    <a href="{{ route('perbaikan.pemeliharaan-berkala.report') }}" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
            
            <!-- Report Information Section -->
            <div class="w-full">
                    <div class="space-y-4">
                        <!-- Status Info -->
                        <div class="bg-gray-50 p-4 rounded-lg border-l-4 
                            @if($schedule->status == 'Selesai') border-green-500 
                            @elseif($schedule->status == 'Dibatalkan') border-red-500
                            @else border-blue-500 @endif">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($schedule->status == 'Selesai')
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @elseif($schedule->status == 'Dibatalkan')
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium 
                                        @if($schedule->status == 'Selesai') text-green-800 
                                        @elseif($schedule->status == 'Dibatalkan') text-red-800
                                        @else text-blue-800 @endif">
                                        Status Pemeliharaan: {{ $schedule->status }}
                                        @if($schedule->auto_generated)
                                            <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Auto Generated</span>
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

                        <!-- Asset Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">ID Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->asset_id }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Nama Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->asset->nama_asset ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Lokasi Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->asset->kode_ruangan ?? '-' }}</p>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Pemeliharaan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->tanggal_pemeliharaan->format('d F Y') }}</p>
                            </div>
                        </div>

                        <!-- Maintenance Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Jenis Pemeliharaan</label>
                                @php
                                    $jenisClass = [
                                        'Rutin' => 'bg-green-100 text-green-800',
                                        'Tambahan' => 'bg-yellow-100 text-yellow-800',
                                        'Khusus' => 'bg-purple-100 text-purple-800'
                                    ];
                                @endphp
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $jenisClass[$schedule->jenis_pemeliharaan] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $schedule->jenis_pemeliharaan }}
                                </span>
                            </div>
                            
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Penanggung Jawab</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $schedule->penanggung_jawab ?? '-' }}</p>
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

                        <!-- Additional Information -->
                        @if($schedule->alasan_penjadwalan)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Alasan Penjadwalan</label>
                            <p class="text-lg text-gray-900">{{ $schedule->alasan_penjadwalan }}</p>
                        </div>
                        @endif
                        
                        @if($schedule->catatan_tambahan)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Catatan Tambahan</label>
                            <p class="text-lg text-gray-900">{{ $schedule->catatan_tambahan }}</p>
                        </div>
                        @endif

                        <!-- Asset Category Information -->
                        @if($schedule->asset && $schedule->asset->kategori)
                        <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                            <h4 class="text-sm font-medium text-blue-800 mb-2">Informasi Aset</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <p><span class="font-medium text-blue-700">Kategori:</span> {{ $schedule->asset->kategori }}</p>
                                @if($schedule->asset->merk)
                                <p><span class="font-medium text-blue-700">Merk:</span> {{ $schedule->asset->merk }}</p>
                                @endif
                                @if($schedule->asset->model)
                                <p><span class="font-medium text-blue-700">Model:</span> {{ $schedule->asset->model }}</p>
                                @endif
                                @if($schedule->asset->tahun_pembuatan)
                                <p><span class="font-medium text-blue-700">Tahun:</span> {{ $schedule->asset->tahun_pembuatan }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Timeline Info -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-600 mb-2">Timeline</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p><span class="font-medium">Dibuat:</span> {{ $schedule->created_at->format('d F Y, H:i') }}</p>
                                @if($schedule->updated_at != $schedule->created_at)
                                <p><span class="font-medium">Diperbarui:</span> {{ $schedule->updated_at->format('d F Y, H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection