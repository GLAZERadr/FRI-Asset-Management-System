<!-- resources/views/assets/index.blade.php -->
@extends('layouts.app')

@section('header', 'Pemantauan Aset')

@section('content')
<div class="container mx-auto">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('pemantauan') }}" method="GET" class="grid grid-cols-3 gap-6">
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
                <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kategori</label>
                <select id="kategori" name="kategori" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('kategori') == $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 mr-2">
                    Filter
                </button>
                <a href="{{ route('pemantauan') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Hapus Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between">
        <div class="flex">
            <x-search-input placeholder="Search assets..." name="search" value="{{ request('search') }}" form="search-form" />
            <form id="search-form" action="{{ route('pemantauan') }}" method="GET" class="hidden">
                <input type="hidden" name="lokasi" value="{{ request('lokasi') }}">
                <input type="hidden" name="kategori" value="{{ request('kategori') }}">
            </form>
        </div>
        <a href="{{ route('pemantauan.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Tambah Aset
        </a>
    </div>

    <!-- Assets Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <x-table>
            <x-slot name="thead">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Aset
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama Aset
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Lokasi
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kategori
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </x-slot>
            
            @forelse ($assets as $asset)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $asset->asset_id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $asset->nama_asset }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $asset->lokasi }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $asset->kategori ?? 'Tidak ada' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('pemantauan.show', $asset->id) }}" class="text-blue-600 hover:text-blue-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        <a href="{{ route('pemantauan.edit', $asset->id) }}" class="text-yellow-600 hover:text-yellow-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </a>
                        <a href="{{ route('perbaikan.aset.create', ['asset_id' => $asset->asset_id]) }}" class="text-green-600 hover:text-green-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center">
                    <x-empty-state 
                        title="Tidak Ada Aset" 
                        description="Belum ada aset yang ditambahkan ke sistem." 
                        action="true"
                        actionUrl="{{ route('pemantauan.create') }}"
                        actionText="Tambah Aset Baru"
                    />
                </td>
            </tr>
            @endforelse
        </x-table>

        <!-- Pagination -->
        <div class="px-6 py-3 border-t">
            {{ $assets->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection