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
                                 class="w-full h-64 object-cover rounded-lg mx-auto">
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
                        
                        <!-- Status Information -->
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h3 class="text-lg font-semibold text-blue-900 mb-2">Status Aset</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-blue-700 mb-1">Status Aktif</label>
                                    <p class="text-blue-900">
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            {{ $asset->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $asset->is_active ? 'Masih Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-blue-700 mb-1">Terakhir Diupdate</label>
                                    <p class="text-blue-900">{{ $asset->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Maintenance History Section -->
                        @if($asset->damagedAssets->count() > 0)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Kerusakan</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tingkat</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($asset->damagedAssets as $damage)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $damage->tanggal_pelaporan ? $damage->tanggal_pelaporan->format('d/m/Y') : '-' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ Str::limit($damage->deskripsi_kerusakan, 50) }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $damage->tingkat_kerusakan === 'Ringan' ? 'bg-green-100 text-green-800' : 
                                                       ($damage->tingkat_kerusakan === 'Sedang' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ $damage->tingkat_kerusakan }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $damage->pelapor ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Maintenance Requests History -->
                        @if($asset->maintenanceAssets->count() > 0)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Maintenance</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Request</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimasi Biaya</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($asset->maintenanceAssets as $maintenance)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $maintenance->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $maintenance->vendor ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $maintenance->estimasi_biaya ? 'Rp ' . number_format($maintenance->estimasi_biaya, 0, ',', '.') : '-' }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Maintenance
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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