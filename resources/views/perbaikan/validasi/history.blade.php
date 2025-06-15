@extends('layouts.app')
@section('header', 'Histori Validasi')
@section('content')
<div class="container mx-auto">
    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('perbaikan.validation.history') }}" method="GET" class="grid grid-cols-6 gap-4 items-end">
            <div>
                <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Pilih Lokasi</label>
                <select id="lokasi" name="lokasi" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location }}" {{ request('lokasi') == $location ? 'selected' : '' }}>{{ $location }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select id="bulan" name="bulan" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Bulan</option>
                    @foreach($months as $value => $name)
                        <option value="{{ $value }}" {{ request('bulan') == $value ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <select id="tahun" name="tahun" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Tahun</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $value => $name)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('perbaikan.validation.history') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- History Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Aset</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Aset</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Aset</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Kerusakan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Proposal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Validasi</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Validasi</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($reports as $index => $asset)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $reports->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $asset->asset->asset_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->asset->nama_asset }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->asset->kategori }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $asset->asset->lokasi }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @php
                                $tingkatKerusakan = $asset->tingkat_kerusakan;
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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($asset->estimasi_biaya, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($asset->validated === 'Yes')
                                <span class="text-green-600">Disetujui</span>
                            @elseif($asset->validated === 'Reject')
                                <span class="text-red-600">Ditolak</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            @if($asset->validated === 'Yes')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @elseif($asset->validated === 'Reject')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $asset->validated_at ? \Carbon\Carbon::parse($asset->validated_at)->format('d-m-Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-2">
                                <!-- View Detail Icon (magnifying glass) -->
                                <a href="{{ route('perbaikan.validation.show', $asset->validation_id) }}" class="text-gray-600 hover:text-gray-900" title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </a>
                                
                                <!-- External Link Icon -->
                                <a href="{{ route('perbaikan.validation.download-pdf', $asset->validation_id) }}" class="text-gray-600 hover:text-gray-900" title="Download PDF">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900">Tidak Ada Histori</h3>
                                <p class="text-gray-500">Belum ada histori validasi yang tersedia.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($reports->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $reports->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Rejection Reason Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-black bg-opacity-30 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Alasan Penolakan</h3>
            <p id="rejectionReasonText" class="text-gray-600 mb-6"></p>
            <div class="flex justify-end">
                <button onclick="closeRejectionModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showRejectionReason(reason) {
    document.getElementById('rejectionReasonText').textContent = reason;
    document.getElementById('rejectionModal').classList.remove('hidden');
}

function closeRejectionModal() {
    document.getElementById('rejectionModal').classList.add('hidden');
}
</script>

@endsection