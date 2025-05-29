<!-- resources/views/damaged_assets/index.blade.php -->
@extends('layouts.app')

@section('header', 'Daftar Kerusakan Aset')

@section('content')
<div class="container mx-auto">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('perbaikan.aset') }}" method="GET" class="grid grid-cols-3 gap-6">
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
                <label for="tingkat_kerusakan" class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kerusakan</label>
                <select id="tingkat_kerusakan" name="tingkat_kerusakan" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Tingkat</option>
                    <option value="Ringan" {{ request('tingkat_kerusakan') == 'Ringan' ? 'selected' : '' }}>Ringan</option>
                    <option value="Sedang" {{ request('tingkat_kerusakan') == 'Sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="Berat" {{ request('tingkat_kerusakan') == 'Berat' ? 'selected' : '' }}>Berat</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 mr-2">
                    Filter
                </button>
                <a href="{{ route('perbaikan.aset') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Hapus Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex justify-end">
        <a href="{{ route('perbaikan.aset.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Laporkan Kerusakan
        </a>
    </div>

    <!-- Damaged Assets Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <x-table>
            <x-slot name="thead">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Kerusakan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aset
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Lokasi
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tingkat
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Biaya
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Pelaporan
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </x-slot>
            
            @forelse ($damagedAssets as $damage)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $damage->damage_id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $damage->asset->nama_asset }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $damage->asset->lokasi }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <x-status-badge status="{{ $damage->tingkat_kerusakan }}">
                        {{ $damage->tingkat_kerusakan }}
                    </x-status-badge>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    Rp {{ number_format($damage->estimasi_biaya, 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ date('d/m/Y', strtotime($damage->tanggal_pelaporan)) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('perbaikan.aset.show', $damage->id) }}" class="text-blue-600 hover:text-blue-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        @if(!$damage->maintenanceAssets()->exists())
                        <a href="{{ route('pengajuan.baru', ['damage_id' => $damage->id]) }}" class="text-green-600 hover:text-green-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>
                        @if(Auth::user()->hasRole('staff_logistik') && $damage->pelapor == Auth::user()->division)
                        <a href="{{ route('perbaikan.aset.edit', $damage->id) }}" class="text-yellow-600 hover:text-yellow-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                        @endif
                        @else
                        <a href="{{ route('pengajuan.show', $damage->maintenanceAssets()->first()->id) }}" class="text-indigo-600 hover:text-indigo-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-10 text-center">
                    <x-empty-state 
                        title="Tidak Ada Laporan Kerusakan" 
                        description="Belum ada kerusakan aset yang dilaporkan." 
                        action="true"
                        actionUrl="{{ route('perbaikan.aset.create') }}"
                        actionText="Laporkan Kerusakan"
                    />
                </td>
            </tr>
            @endforelse
        </x-table>

        <!-- Pagination -->
        <div class="px-6 py-3 border-t">
            {{ $damagedAssets->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection