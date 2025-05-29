<!-- resources/views/damaged_assets/create.blade.php -->
@extends('layouts.app')

@section('header', 'Laporkan Kerusakan Aset')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <form action="{{ route('perbaikan.aset.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="asset_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Aset</label>
                        <select id="asset_id" name="asset_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
                            <option value="">Pilih Aset</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->asset_id }}" {{ request('asset_id') == $asset->asset_id ? 'selected' : '' }}>
                                    {{ $asset->asset_id }} - {{ $asset->nama_asset }} ({{ $asset->lokasi }})
                                </option>
                            @endforeach
                        </select>
                        @error('asset_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="tingkat_kerusakan" class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kerusakan</label>
                        <select id="tingkat_kerusakan" name="tingkat_kerusakan" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
                            <option value="">Pilih Tingkat Kerusakan</option>
                            <option value="Ringan">Ringan</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Berat">Berat</option>
                        </select>
                        @error('tingkat_kerusakan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="col-span-2">
                        <label for="deskripsi_kerusakan" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Kerusakan</label>
                        <textarea id="deskripsi_kerusakan" name="deskripsi_kerusakan" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>{{ old('deskripsi_kerusakan') }}</textarea>
                        @error('deskripsi_kerusakan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="estimasi_biaya" class="block text-sm font-medium text-gray-700 mb-1">Estimasi Biaya</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input 
                                type="number" 
                                id="estimasi_biaya" 
                                name="estimasi_biaya" 
                                value="{{ old('estimasi_biaya', 0) }}" 
                                min="0" 
                                step="1000" 
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" 
                                required
                            >
                        </div>
                        @error('estimasi_biaya')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="vendor" class="block text-sm font-medium text-gray-700 mb-1">Vendor (Opsional)</label>
                        <input type="text" id="vendor" name="vendor" value="{{ old('vendor') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        @error('vendor')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-2">
                    <a href="{{ route('perbaikan.aset') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection