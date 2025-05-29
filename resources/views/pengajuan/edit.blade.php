<!-- resources/views/pengajuan/edit.blade.php -->
@extends('layouts.app')

@section('header', 'Edit Pengajuan Perbaikan')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Edit Kerusakan Aset</h2>
            
            <form action="{{ route('pengajuan.update', $maintenanceRequest->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID Aset</label>
                        <input type="text" value="{{ $maintenanceRequest->asset->asset_id }} - {{ $maintenanceRequest->asset->nama_asset }}" class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm" disabled>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Pengajuan</label>
                        <input type="text" value="{{ $maintenanceRequest->status }}" class="w-full rounded-md border-gray-300 bg-gray-100 shadow-sm" disabled>
                    </div>
                    
                    <div class="col-span-2">
                        <label for="deskripsi_kerusakan" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Kerusakan</label>
                        <textarea id="deskripsi_kerusakan" name="deskripsi_kerusakan" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50" required>{{ old('deskripsi_kerusakan', $maintenanceRequest->damagedAsset->deskripsi_kerusakan) }}</textarea>
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
                            <input type="number" id="estimasi_biaya" name="estimasi_biaya" value="{{ old('estimasi_biaya', $maintenanceRequest->damagedAsset->estimasi_biaya) }}" min="0" step="1000" class="w-full pl-12 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50" required>
                        </div>
                        @error('estimasi_biaya')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-2">
                    <a href="{{ route('pengajuan.show', $maintenanceRequest->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-400 focus:ring ring-gray-200 disabled:opacity-25 transition ease-in-out duration-150">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection