@extends('layouts.app')
@section('header', 'Daftar Verifikasi Laporan')
@section('content')
<div class="container mx-auto">
    <!-- Action Buttons -->
    <div class="mb-6 flex justify-end items-center">
        <div class="mr-3">
            <select id="location_filter" onchange="filterByLocation(this.value)" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                <option value="">Pilih Lokasi Aset</option>
                @foreach($locations as $location)
                    <option value="{{ $location }}" {{ request('lokasi') == $location ? 'selected' : '' }}>{{ $location }}</option>
                @endforeach
            </select>
        </div>
        <a href="#" onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            cetak
        </a>
    </div>
    
    <!-- Tab Navigation -->
    <div class="mb-4">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex" id="verification-tabs">
                <a href="#" onclick="switchTab('verification')" id="verification-tab" 
                   class="border-b-2 border-green-500 py-2 px-4 text-sm font-medium text-green-600">
                    Verifikasi
                </a>
                <a href="#" onclick="switchTab('history')" id="history-tab" 
                   class="border-b-2 border-gray-300 py-2 px-4 text-sm font-medium text-gray-600 hover:text-green-600 hover:border-green-300">
                    Histori Verifikasi
                </a>
            </nav>
        </div>
    </div>

    <!-- Verification Tab Content -->
    <div id="verification-content" class="tab-content">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Aset</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Aset</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengecekan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Aset</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumentasi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $rowNumber = 1; @endphp
                        @forelse ($monitoringReports as $report)
                            @foreach($report->monitoring_data as $assetData)
                                @php
                                    $asset = $assets->firstWhere('asset_id', $assetData['asset_id']);
                                @endphp
                                @if($asset && (!isset($assetData['verification']) || $assetData['verification'] !== 'verified'))
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $rowNumber++ }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $asset->asset_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->nama_asset }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->kategori ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->lokasi ?? $report->kode_ruangan }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $report->tanggal_laporan->format('d-m-Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $report->nama_pelapor }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($assetData['status'] === 'baik')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Baik
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Butuh Perawatan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $assetData['deskripsi'] ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if(isset($assetData['foto_path']) && $assetData['foto_path'])
                                            <img src="{{ $assetData['foto_path'] }}" alt="Monitoring Photo" class="h-10 w-10 rounded object-cover cursor-pointer" onclick="showImageModal('{{ $assetData['foto_path'] }}')">
                                        @else
                                            <div class="h-10 w-10 bg-gray-200 rounded flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <a href="{{ route('pemantauan.monitoring.verifying', [$report->id_laporan, $asset->asset_id]) }}" class="text-green-600 hover:text-green-900" title="Proses Laporan">
                                            <div class="relative">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m-4-4h8"/>
                                                </svg>
                                            </div>
                                        </a>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="11" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">Tidak Ada Data Monitoring</h3>
                                    <p class="text-gray-500">Belum ada laporan monitoring yang tersedia untuk diverifikasi.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- History Tab Content -->
    <div id="history-content" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Verifikasi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Ruangan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Aset</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Verifikasi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewer</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Verifikasi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $historyRowNumber = 1; @endphp
                        @forelse ($verifiedMonitoringReports as $report)
                            @foreach($report->monitoring_data as $assetData)
                                @php
                                    $asset = $assets->firstWhere('asset_id', $assetData['asset_id']);
                                @endphp
                                @if($asset && isset($assetData['verification']) && $assetData['verification'] === 'verified')
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $historyRowNumber++ }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $assetData['verification_id'] ?? 'VER-' . $asset->asset_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->nama_asset }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->lokasi ?? $report->kode_ruangan }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $report->nama_pelapor }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($assetData['status'] === 'baik')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Baik
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Butuh Perawatan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ isset($assetData['verification_date']) ? \Carbon\Carbon::parse($assetData['verification_date'])->format('d-m-Y H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $assetData['verifier_name'] ?? auth()->user()->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Terverifikasi
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div class="flex items-center space-x-2">
                                            <!-- Detail Button (Magnifying Glass) -->
                                            <a href="#" 
                                               onclick="showVerificationDetails('{{ $assetData['verification_id'] ?? 'VER-' . $asset->asset_id }}', '{{ $assetData['verification_date'] ?? '' }}', '{{ $assetData['verifier_name'] ?? auth()->user()->name }}', '{{ $asset->asset_id }}', '{{ $asset->nama_asset }}', '{{ $report->nama_pelapor }}', '{{ $assetData['status'] }}', '{{ $assetData['deskripsi'] ?? '' }}')"
                                               class="text-blue-600 hover:text-blue-900" 
                                               title="Detail Verifikasi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </a>
                                            
                                            <!-- Print Button -->
                                            <a href="#" 
                                               onclick="printVerificationData('{{ $assetData['verification_id'] ?? 'VER-' . $asset->asset_id }}', '{{ $asset->asset_id }}', '{{ $asset->nama_asset }}', '{{ $assetData['verification_date'] ?? '' }}', '{{ $assetData['verifier_name'] ?? auth()->user()->name }}')"
                                               class="text-gray-600 hover:text-gray-900" 
                                               title="Cetak Data Verifikasi">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>

                                            <!-- Add this delete button after the Print Button in the history tab actions -->
                                            <form action="{{ route('pemantauan.monitoring.destroy-asset-verification', ['reportId' => $report->id, 'assetId' => $asset->asset_id]) }}" 
                                                method="POST" 
                                                style="display: inline-block;"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus verifikasi untuk asset {{ $asset->nama_asset }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900" 
                                                        title="Hapus Verifikasi">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">Tidak Ada Histori Verifikasi</h3>
                                    <p class="text-gray-500">Belum ada asset yang telah diverifikasi.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination - Show different pagination for different tabs -->
    <div id="verification-pagination" class="pagination-content">
        @if($monitoringReports->hasPages())
        <div class="px-6 py-3 border-t border-gray-200 bg-white rounded-b-lg shadow mt-4">
            {{ $monitoringReports->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    
    <div id="history-pagination" class="pagination-content hidden">
        @if($verifiedMonitoringReports->hasPages())
        <div class="px-6 py-3 border-t border-gray-200 bg-white rounded-b-lg shadow mt-4">
            {{ $verifiedMonitoringReports->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Verification Details Modal -->
<div id="verificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Verifikasi</h3>
                <button onclick="closeVerificationModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">ID Verifikasi</label>
                    <p id="modalVerificationId" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">ID Aset</label>
                    <p id="modalAssetId" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Nama Aset</label>
                    <p id="modalAssetName" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Pelapor</label>
                    <p id="modalReporter" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Status Aset</label>
                    <p id="modalAssetStatus" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Deskripsi</label>
                    <p id="modalDescription" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Verifikasi</label>
                    <p id="modalVerificationDate" class="text-lg font-semibold text-gray-900"></p>
                </div>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Diverifikasi Oleh</label>
                    <p id="modalVerifierName" class="text-lg font-semibold text-gray-900"></p>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="printVerificationModal()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak
                </button>
                <button onclick="closeVerificationModal()" 
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Dokumentasi Monitoring</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <img id="modalImage" src="" alt="Monitoring Documentation" class="w-full h-auto rounded">
        </div>
    </div>
</div>

<script>
// Tab switching function
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Hide all pagination contents
    document.querySelectorAll('.pagination-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Show corresponding pagination
    document.getElementById(tabName + '-pagination').classList.remove('hidden');
    
    // Update tab styles - only target the verification tabs nav
    const verificationTabsNav = document.getElementById('verification-tabs');
    if (verificationTabsNav) {
        verificationTabsNav.querySelectorAll('a').forEach(tab => {
            tab.className = 'border-b-2 border-gray-300 py-2 px-4 text-sm font-medium text-gray-600 hover:text-green-600 hover:border-green-300';
        });
    }
    
    // Set active tab
    if (tabName === 'verification') {
        document.getElementById('verification-tab').className = 'border-b-2 border-green-500 py-2 px-4 text-sm font-medium text-green-600';
    } else {
        document.getElementById('history-tab').className = 'border-b-2 border-green-500 py-2 px-4 text-sm font-medium text-green-600';
    }
}

// Initialize page - show verification tab by default
document.addEventListener('DOMContentLoaded', function() {
    switchTab('verification');
});

// Verification modal functions
function showVerificationDetails(verificationId, verificationDate, verifierName, assetId, assetName, reporter, status, description) {
    document.getElementById('modalVerificationId').textContent = verificationId || '-';
    document.getElementById('modalAssetId').textContent = assetId || '-';
    document.getElementById('modalAssetName').textContent = assetName || '-';
    document.getElementById('modalReporter').textContent = reporter || '-';
    document.getElementById('modalAssetStatus').textContent = status === 'baik' ? 'Baik' : 'Butuh Perawatan';
    document.getElementById('modalDescription').textContent = description || '-';
    document.getElementById('modalVerificationDate').textContent = verificationDate ? 
        new Date(verificationDate).toLocaleString('id-ID', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        }) : '-';
    document.getElementById('modalVerifierName').textContent = verifierName || '-';
    document.getElementById('verificationModal').classList.remove('hidden');
}

function closeVerificationModal() {
    document.getElementById('verificationModal').classList.add('hidden');
}

function printVerificationData(verificationId, assetId, assetName, verificationDate, verifierName) {
    console.log('Print verification data:', {
        verificationId,
        assetId,
        assetName,
        verificationDate,
        verifierName
    });
    alert('Fungsi cetak untuk data verifikasi: ' + verificationId + ' akan segera tersedia.');
}

function printVerificationModal() {
    window.print();
}

// Location filter
function filterByLocation(location) {
    const url = new URL(window.location);
    if (location) {
        url.searchParams.set('lokasi', location);
    } else {
        url.searchParams.delete('lokasi');
    }
    window.location = url;
}

// Image modal functions
function showImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('verificationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVerificationModal();
    }
});

document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});
</script>
@endsection