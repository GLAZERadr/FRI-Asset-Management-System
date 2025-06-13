@extends('layouts.app')
@section('header', 'Verifikasi Laporan Monitoring')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('pemantauan.monitoring.updateVerification', [$report->id_laporan, $assetMonitoringData['asset_id']]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Asset Photo -->
                    <div class="flex justify-center">
                        <div class="bg-gray-100 rounded-lg p-4 w-full max-w-md">
                            @if(isset($assetMonitoringData['foto_path']) && $assetMonitoringData['foto_path'])
                                <img src="{{ Storage::url($assetMonitoringData['foto_path']) }}" 
                                     alt="Monitoring Photo" 
                                     class="w-full h-full object-cover rounded-lg">
                                <p class="text-sm text-gray-500 mt-2 text-center">Foto Monitoring</p>
                            @elseif($asset && $asset->foto_asset)
                                <img src="{{ Storage::url($asset->foto_asset) }}" 
                                     alt="{{ $asset->nama_asset }}" 
                                     class="w-full h-64 object-cover rounded-lg">
                                <p class="text-sm text-gray-500 mt-2 text-center">Foto Aset</p>
                            @else
                                <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <div class="text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-500 mt-2">Tidak ada foto</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Asset Information -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">ID Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $assetMonitoringData['asset_id'] }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Nama Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset ? $asset->nama_asset : 'Asset not found' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kategori Aset</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset ? ($asset->kategori ?? '-') : '-' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kode Ruangan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $asset ? ($asset->kode_ruangan ?? '-') : '-' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Pelapor</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $report->nama_pelapor }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Kondisi Aset</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    @if($assetMonitoringData['status'] === 'baik')
                                        <span class="text-green-600">Baik</span>
                                    @else
                                        <span class="text-red-600">Butuh Perawatan</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Pengecekan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($report->tanggal_laporan)->format('d-m-Y') }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Deskripsi</label>
                                <p class="text-lg font-semibold text-gray-900">{{ $assetMonitoringData['deskripsi'] ?? '-' }}</p>
                            </div>
                            
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-600 mb-1">Status Verifikasi</label>
                                <p class="text-lg font-semibold">
                                    @if($assetMonitoringData['verification'] === 'verified')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Not Verified
                                        </span>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-600 mb-3">Setujui verifikasi</label>
                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" 
                                               name="verification_status" 
                                               value="verified" 
                                               {{ $assetMonitoringData['verification'] === 'verified' ? 'checked' : '' }}
                                               class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300">
                                        <span class="ml-2 text-lg font-medium text-gray-900">Verifikasi</span>
                                    </label>
                                </div>
                            </div>
                            
                            @if($assetMonitoringData['status'] === 'butuh_perawatan')
                            <div class="col-span-2">
                                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                    <span class="text-red-700 font-medium">Membutuhkan Perawatan</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="submit" 
                            name="verification_status" 
                            value="verified"
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        Simpan
                    </button>
                    <a href="{{ route('pemantauan.monitoring.verify') }}" 
                       class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection