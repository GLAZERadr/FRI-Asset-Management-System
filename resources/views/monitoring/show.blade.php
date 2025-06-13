@extends('layouts.app')
@section('header', 'Detail Laporan Monitoring')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header Section -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $monitoring->id_laporan }}</h1>
            <p class="text-sm text-gray-600">{{ $monitoring->getRoleDisplayName() }} • {{ $monitoring->tanggal_laporan->format('d F Y') }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('pemantauan.monitoring.index') }}" 
               class="px-4 py-2 border bg-green-600 rounded-md text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Report Information Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                    <h3 class="text-lg font-semibold text-blue-900">Informasi Laporan</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600">ID Laporan</label>
                        <p class="text-lg font-mono text-gray-900">{{ $monitoring->id_laporan }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-600">Kode Ruangan</label>
                        <p class="text-sm text-gray-900">{{ $monitoring->kode_ruangan }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-600">Pelapor</label>
                        <p class="text-sm text-gray-900">{{ $monitoring->nama_pelapor }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-600">Jabatan</label>
                        <p class="text-sm text-gray-900">{{ $monitoring->getRoleDisplayName() }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-600">Tanggal Laporan</label>
                        <p class="text-sm text-gray-900">{{ $monitoring->tanggal_laporan->format('d F Y') }}</p>
                    </div>
                    
                    @if($monitoring->reviewer)
                    <div>
                        <label class="text-sm font-medium text-gray-600">Reviewer</label>
                        <p class="text-sm text-gray-900">{{ $monitoring->reviewer }}</p>
                    </div>
                    @endif
                    
                    <div>
                        <label class="text-sm font-medium text-gray-600">Status Validasi</label>
                        <div class="mt-1">
                            @if($monitoring->validated === 'valid')
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    ✓ Tervalidasi
                                </span>
                                @if($monitoring->validated_at)
                                    <p class="text-xs text-gray-500 mt-1">{{ $monitoring->validated_at->format('d/m/Y') }}</p>
                                @endif
                            @else
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    ⏳ Pending
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    @if($monitoring->catatan)
                    <div>
                        <label class="text-sm font-medium text-gray-600">Catatan</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md mt-1">{{ $monitoring->catatan }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="mt-6 bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-green-50 border-b border-green-100">
                    <h3 class="text-lg font-semibold text-green-900">Ringkasan Monitoring</h3>
                </div>
                <div class="p-6">
                    @php
                        $totalAssets = count($monitoring->monitoring_data ?? []);
                        $goodAssets = collect($monitoring->monitoring_data ?? [])->where('status', 'baik')->count();
                        $needMaintenance = $totalAssets - $goodAssets;
                        $goodPercentage = $totalAssets > 0 ? round(($goodAssets / $totalAssets) * 100, 1) : 0;
                    @endphp
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $totalAssets }}</div>
                            <div class="text-sm text-blue-800">Total Aset</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $goodAssets }}</div>
                            <div class="text-sm text-green-800">Kondisi Baik</div>
                        </div>
                        <div class="text-center p-4 bg-red-50 rounded-lg">
                            <div class="text-2xl font-bold text-red-600">{{ $needMaintenance }}</div>
                            <div class="text-sm text-red-800">Perlu Perawatan</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ $goodPercentage }}%</div>
                            <div class="text-sm text-purple-800">Kondisi Baik</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assets Detail -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Aset yang Dimonitor</h3>
                </div>
                
                @if($monitoring->monitoring_data && count($monitoring->monitoring_data) > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($monitoring->monitoring_data as $index => $monitoringItem)
                            @php
                                $asset = $assets->firstWhere('asset_id', $monitoringItem['asset_id']);
                            @endphp
                            
                            @if($asset)
                            <div class="p-6 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="text-lg font-semibold text-gray-900">{{ $asset->nama_asset }}</h4>
                                            @if($monitoringItem['status'] === 'baik')
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    ✓ Baik
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    ⚠ Butuh Perawatan
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm font-mono text-gray-600 mb-3">{{ $asset->asset_id }}</p>
                                    </div>
                                    <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">#{{ $index + 1 }}</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Asset Information -->
                                    <div class="space-y-3">
                                        <h5 class="font-medium text-gray-900 border-b border-gray-200 pb-1">Informasi Aset</h5>
                                        
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <div>
                                                <span class="text-gray-600">Kategori:</span>
                                                <p class="font-medium">{{ $asset->kategori ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Lokasi:</span>
                                                <p class="font-medium">{{ $asset->lokasi ?? '-' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Ruangan:</span>
                                                <p class="font-medium">{{ $asset->kode_ruangan }}</p>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Status Kelayakan:</span>
                                                <p class="font-medium">{{ $asset->status_kelayakan ?? '-' }}</p>
                                            </div>
                                        </div>
                                        
                                        @if($asset->spesifikasi)
                                        <div>
                                            <span class="text-sm text-gray-600">Spesifikasi:</span>
                                            <p class="text-sm bg-gray-50 p-2 rounded mt-1">{{ $asset->spesifikasi }}</p>
                                        </div>
                                        @endif
                                        
                                        @if($asset->nilai_perolehan)
                                        <div>
                                            <span class="text-sm text-gray-600">Nilai Perolehan:</span>
                                            <p class="text-sm font-semibold text-green-600">Rp {{ number_format($asset->nilai_perolehan, 0, ',', '.') }}</p>
                                        </div>
                                        @endif
                                        
                                        @if($asset->tgl_perolehan)
                                        <div>
                                            <span class="text-sm text-gray-600">Tanggal Perolehan:</span>
                                            <p class="text-sm">{{ $asset->tgl_perolehan->format('d F Y') }}</p>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Monitoring Information -->
                                    <div class="space-y-3">
                                        <h5 class="font-medium text-gray-900 border-b border-gray-200 pb-1">Hasil Monitoring</h5>
                                        
                                        @if(isset($monitoringItem['deskripsi']) && $monitoringItem['deskripsi'])
                                        <div>
                                            <span class="text-sm text-gray-600">Deskripsi Kondisi:</span>
                                            <p class="text-sm bg-yellow-50 p-3 rounded mt-1 border-l-4 border-yellow-400">
                                                {{ $monitoringItem['deskripsi'] }}
                                            </p>
                                        </div>
                                        @endif
                                        
                                        <div>
                                            <span class="text-sm text-gray-600">Status Verifikasi:</span>
                                            <p class="text-sm">
                                                @if(isset($monitoringItem['verification']) && $monitoringItem['verification'] === 'verified')
                                                    <span class="text-green-600 font-medium">✓ Terverifikasi</span>
                                                @else
                                                    <span class="text-orange-600 font-medium">⏳ Belum Diverifikasi</span>
                                                @endif
                                            </p>
                                        </div>
                                        
                                        @if(isset($monitoringItem['foto_path']) && $monitoringItem['foto_path'])
                                        <div>
                                            <span class="text-sm text-gray-600 block mb-2">Dokumentasi:</span>
                                            <img src="{{ Storage::url($monitoringItem['foto_path']) }}" 
                                                 alt="Dokumentasi {{ $asset->nama_asset }}" 
                                                 class="w-full h-full object-cover rounded-lg border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                                                 onclick="showImageModal('{{ Storage::url($monitoringItem['foto_path']) }}', '{{ $asset->nama_asset }}')">
                                        </div>
                                        @else
                                        <div>
                                            <span class="text-sm text-gray-600 block mb-2">Dokumentasi:</span>
                                            <div class="w-full h-32 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <p class="text-xs text-gray-500">Tidak ada foto</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="p-6 bg-red-50 border-l-4 border-red-400">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-medium text-red-800">Aset Tidak Ditemukan</h4>
                                        <p class="text-sm text-red-700">Asset ID: {{ $monitoringItem['asset_id'] }}</p>
                                        <p class="text-xs text-red-600 mt-1">Aset mungkin telah dihapus atau dipindahkan dari sistem.</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Data Monitoring</h3>
                        <p class="text-gray-500">Laporan ini belum memiliki data monitoring aset.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 sm:top-8 mx-auto p-4 sm:p-6 border w-11/12 lg:w-3/4 xl:w-1/2 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Dokumentasi Monitoring</h3>
            <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="text-center">
            <img id="modalImage" src="" alt="Dokumentasi Monitoring" class="w-full h-auto rounded max-h-96 object-contain">
        </div>
    </div>
</div>

<script>
function showImageModal(imageSrc, assetName) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalTitle').textContent = 'Dokumentasi - ' + assetName;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Handle escape key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('imageModal').classList.contains('hidden')) {
        closeImageModal();
    }
});

// Print functionality
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        font-size: 12px;
    }
    
    .container {
        max-width: none;
        padding: 0;
    }
    
    .shadow-md {
        box-shadow: none;
        border: 1px solid #e5e7eb;
    }
}
</style>
@endsection