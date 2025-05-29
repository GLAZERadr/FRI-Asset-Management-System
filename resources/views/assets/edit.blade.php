<!-- resources/views/assets/edit.blade.php -->
@extends('layouts.app')

@section('header', 'Edit Aset')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <form action="{{ route('pemantauan.update', $asset->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <x-form-input name="asset_id" label="ID Aset" value="{{ $asset->asset_id }}" disabled/>
                    </div>
                    
                    <div>
                        <x-form-input name="nama_asset" label="Nama Aset" value="{{ $asset->nama_asset }}" required/>
                    </div>
                    
                    <div>
                        <x-form-input name="lokasi" label="Lokasi" value="{{ $asset->lokasi }}" required/>
                    </div>
                    
                    <div>
                        <x-form-input name="kategori" label="Kategori" value="{{ $asset->kategori }}" />
                    </div>
                    
                    <div>
                        <x-form-input name="tingkat_kepentingan_asset" label="Tingkat Kepentingan (1-10)" type="number" min="1" max="10" value="{{ $asset->tingkat_kepentingan_asset }}" />
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-2">
                    <a href="{{ route('pemantauan.show', $asset->id) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-50">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection