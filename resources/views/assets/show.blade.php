<!-- resources/views/assets/show.blade.php -->
@extends('layouts.app')

@section('header', 'Detail Aset')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Informasi Aset</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('pemantauan.edit', $asset->id) }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('perbaikan.aset.create', ['asset_id' => $asset->asset_id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Laporkan Kerusakan
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500 w-1/3">ID Aset</td>
                                <td class="py-3 text-sm text-gray-900">{{ $asset->asset_id }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Nama Aset</td>
                                <td class="py-3 text-sm text-gray-900">{{ $asset->nama_asset }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Lokasi</td>
                                <td class="py-3 text-sm text-gray-900">{{ $asset->lokasi }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Kategori</td>
                                <td class="py-3 text-sm text-gray-900">{{ $asset->kategori ?? 'Tidak ada' }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Tingkat Kepentingan</td>
                                <td class="py-3 text-sm text-gray-900">
                                    @if($asset->tingkat_kepentingan_asset)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $asset->tingkat_kepentingan_asset >= 8 ? 'bg-red-100 text-red-800' : 
                                               ($asset->tingkat_kepentingan_asset >= 5 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $asset->tingkat_kepentingan_asset }}/10
                                        </span>
                                    @else
                                        Tidak ada
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Tanggal Dibuat</td>
                                <td class="py-3 text-sm text-gray-900">{{ $asset->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-500">Terakhir Diupdate</td>
                                <td class="py-3 text-sm text-gray-900">{{ $asset->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Kerusakan</h3>
                    
                    @if($asset->damagedAssets->count() > 0)
                        <div class="overflow-auto max-h-80">
                            <table class="min-w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kerusakan</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($asset->damagedAssets as $damage)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $damage->tanggal_pelaporan->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ Str::limit($damage->deskripsi_kerusakan, 30) }}</td>
                                        <td class="px-4 py-2">
                                            <x-status-badge status="{{ $damage->tingkat_kerusakan }}">
                                                {{ $damage->tingkat_kerusakan }}
                                            </x-status-badge>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>