<!-- resources/views/damaged_assets/show.blade.php -->
@extends('layouts.app')

@section('header', 'Detail Kerusakan Aset')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Detail Kerusakan</h2>
                <div class="flex space-x-2">
                    @if(!$damagedAsset->maintenanceAssets()->exists() && Auth::user()->hasRole('staff_logistik') && $damagedAsset->pelapor == Auth::user()->division)
                    <a href="{{ route('perbaikan.aset.edit', $damagedAsset->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit
                    </a>
                    @endif
                    
                    @if(!$damagedAsset->maintenanceAssets()->exists())
                    <a href="{{ route('pengajuan.baru', ['damage_id' => $damagedAsset->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Ajukan Perbaikan
                    </a>
                    @else
                    <a href="{{ route('pengajuan.show', $damagedAsset->maintenanceAssets()->first()->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Lihat Pengajuan
                    </a>
                    @endif
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Kerusakan</h3>
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500 w-1/3">ID Kerusakan</td>
                                <td class="py-3 text-sm text-gray-900">{{ $damagedAsset->damage_id }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Tingkat Kerusakan</td>
                                <td class="py-3 text-sm">
                                    <x-status-badge status="{{ $damagedAsset->tingkat_kerusakan }}">
                                        {{ $damagedAsset->tingkat_kerusakan }}
                                    </x-status-badge>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Deskripsi Kerusakan</td>
                                <td class="py-3 text-sm text-gray-900">{{ $damagedAsset->deskripsi_kerusakan }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Estimasi Biaya</td>
                                <td class="py-3 text-sm text-gray-900">Rp {{ number_format($damagedAsset->estimasi_biaya, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Tanggal Pelaporan</td>
                                <td class="py-3 text-sm text-gray-900">{{ date('d/m/Y', strtotime($damagedAsset->tanggal_pelaporan)) }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Pelapor</td>
                                <td class="py-3 text-sm text-gray-900">{{ $damagedAsset->pelapor }}</td>
                            </tr>
                            @if($damagedAsset->vendor)
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Vendor</td>
                                <td class="py-3 text-sm text-gray-900">{{ $damagedAsset->vendor }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Aset</h3>
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500 w-1/3">ID Aset</td>
                                <td class="py-3 text-sm text-gray-900">{{ $damagedAsset->asset->asset_id }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Nama Aset</td>
                                <td class="py-3 text-sm text-gray-900">{{ $damagedAsset->asset->nama_asset }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Lokasi</td>
                                <td class="py-3 text-sm text-gray-900">{{ $damagedAsset->asset->lokasi }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Kategori</td>
                                <td class="py-3 text-sm text-gray-900">{{ $damagedAsset->asset->kategori ?? 'Tidak ada' }}</td>
                            </tr>
                            @if($damagedAsset->asset->tingkat_kepentingan_asset)
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Tingkat Kepentingan</td>
                                <td class="py-3 text-sm text-gray-900">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $damagedAsset->asset->tingkat_kepentingan_asset >= 8 ? 'bg-red-100 text-red-800' : 
                                           ($damagedAsset->asset->tingkat_kepentingan_asset >= 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $damagedAsset->asset->tingkat_kepentingan_asset }}/10
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        <a href="{{ route('pemantauan.show', $damagedAsset->asset->id) }}" class="text-blue-600 hover:text-blue-900">
                            Lihat detail aset
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <a href="{{ route('perbaikan.aset') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50">
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection