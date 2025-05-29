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

<script>
function updateStatus(newStatus) {
    if (confirm('Apakah Anda yakin ingin mengubah status menjadi ' + newStatus + '?')) {
        document.getElementById('status-input').value = newStatus;
        document.getElementById('status-update-form').submit();
    }
}
</script>
@endsection