@extends('layouts.app')
@section('header', 'Detail Aset')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md overflow-hidden"> 
        <div class="p-6">
            <div class="bg-gray-100 rounded-lg p-4 text-center">
                @if($damagedAsset->damaged_image)
                    <img src="{{ $damagedAsset->damaged_image }}" 
                            alt="{{ $damagedAsset->asset->nama_asset }}" 
                            class="w-40 h-40 object-cover rounded-lg mx-auto">
                @else
                    <div class="w-40 h-40 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
                <p class="text-sm text-gray-500 mt-2">Foto Kerusakan Aset</p>
            </div>
            <form action="{{ route('fix-verification.update', $damagedAsset->damage_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-3 gap-6 mt-4">
                    <!-- Pelapor -->
                    <div>
                        <label for="reporter_role" class="block text-sm font-medium text-gray-700 mb-1">Pelapor</label>
                        <input type="text" 
                            id="reporter_role" 
                            name="reporter_role" 
                            value="{{ old('reporter_role', $damagedAsset->reporter_role) }}"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('reporter_role') border-red-500 @enderror"
                            placeholder="Nama Pelapor"
                            readonly
                            required>
                        @error('reporter_role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lokasi Asset -->
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi Aset</label>
                        <input type="text" 
                            id="lokasi" 
                            name="lokasi" 
                            value="{{ old('lokasi', $damagedAsset->asset->lokasi) }}"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('lokasi') border-red-500 @enderror"
                            placeholder="Lokasi Aset"
                            readonly
                            required>
                        @error('lokasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estimasi Waktu Perbaikan - Date Picker -->
                    <div>
                        <label for="estimasi_waktu_perbaikan" class="block text-sm font-medium text-gray-700 mb-1">Estimasi Waktu Perbaikan</label>
                        <input type="date" 
                               id="estimasi_waktu_perbaikan" 
                               name="estimasi_waktu_perbaikan" 
                               value="{{ old('estimasi_waktu_perbaikan', $damagedAsset->estimasi_waktu_perbaikan ? $damagedAsset->estimasi_waktu_perbaikan->format('Y-m-d') : '') }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('estimasi_waktu_perbaikan') border-red-500 @enderror"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Pilih tanggal estimasi selesai perbaikan</p>
                        @error('estimasi_waktu_perbaikan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ID Verifikasi -->
                    <div>
                        <label for="verification_id" class="block text-sm font-medium text-gray-700 mb-1">ID Verifikasi</label>
                        <input type="text" 
                            id="verification_id" 
                            name="verification_id" 
                            value="{{ old('verification_id', $damagedAsset->verification_id ?? 'VER-' . date('Ymd') . '-' . str_pad($damagedAsset->id, 4, '0', STR_PAD_LEFT)) }}"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('verification_id') border-red-500 @enderror"
                            placeholder="ID Verifikasi"
                            readonly
                            required>
                        @error('verification_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Pelaporan -->
                    <div>
                        <label for="tanggal_pelaporan" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pelaporan</label>
                        <input type="text" 
                            id="tanggal_pelaporan" 
                            name="tanggal_pelaporan" 
                            value="{{ old('tanggal_pelaporan', $damagedAsset->tanggal_pelaporan->format('d-m-Y')) }}"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tanggal_pelaporan') border-red-500 @enderror"
                            placeholder="Tanggal Pelaporan"
                            readonly
                            required>
                        @error('tanggal_pelaporan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estimasi Biaya -->
                    <div>
                        <label for="estimasi_biaya" class="block text-sm font-medium text-gray-700 mb-1">Estimasi Biaya</label>
                        <input type="number" 
                               id="estimasi_biaya" 
                               name="estimasi_biaya" 
                               value="{{ old('estimasi_biaya', $damagedAsset->estimasi_biaya) }}"
                               min="0"
                               step="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('estimasi_biaya') border-red-500 @enderror"
                               placeholder="Estimasi Biaya"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Isi dengan angka : 750000</p>
                        @error('estimasi_biaya')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ID Asset -->
                    <div>
                        <label for="asset_id" class="block text-sm font-medium text-gray-700 mb-1">ID Asset</label>
                        <input type="text" 
                            id="asset_id" 
                            name="asset_id" 
                            value="{{ old('asset_id', $damagedAsset->asset->asset_id) }}"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('asset_id') border-red-500 @enderror"
                            placeholder="ID Asset"
                            readonly
                            required>
                        @error('asset_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi Kerusakan -->
                    <div>
                        <label for="deskripsi_kerusakan" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Kerusakan</label>
                        <input type="text" 
                            id="deskripsi_kerusakan" 
                            name="deskripsi_kerusakan" 
                            value="{{ old('deskripsi_kerusakan', $damagedAsset->deskripsi_kerusakan) }}"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('deskripsi_kerusakan') border-red-500 @enderror"
                            placeholder="Deskripsi Kerusakan"
                            readonly
                            required>
                        @error('deskripsi_kerusakan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tingkat Kerusakan -->
                    <div>
                        <label for="tingkat_kerusakan" class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kerusakan</label>
                        <select id="tingkat_kerusakan" 
                                name="tingkat_kerusakan" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tingkat_kerusakan') border-red-500 @enderror"
                                required>
                            <option value="">Tingkat Kerusakan</option>
                            @foreach($tingkat_kerusakan as $category)
                                <option value="{{ $category }}" {{ (old('tingkat_kerusakan', $damagedAsset->tingkat_kerusakan) == $category) ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Isi sesuai dengan tingkat kerusakan</p>
                        @error('tingkat_kerusakan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Asset -->
                    <div>
                        <label for="nama_asset" class="block text-sm font-medium text-gray-700 mb-1">Nama Aset</label>
                        <input type="text" 
                            id="nama_asset" 
                            name="nama_asset" 
                            value="{{ old('nama_asset', $damagedAsset->asset->nama_asset) }}"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nama_asset') border-red-500 @enderror"
                            placeholder="Nama Aset"
                            readonly
                            required>
                        @error('nama_asset')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Tanggal Verifikasi -->
                    <div>
                        <label for="verified_at" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Verifikasi</label>
                        <input type="date" 
                               id="verified_at" 
                               name="verified_at" 
                               value="{{ old('verified_at', $damagedAsset->verified_at ? $damagedAsset->verified_at->format('Y-m-d') : date('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('verified_at') border-red-500 @enderror"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Tanggal verifikasi laporan</p>
                        @error('verified_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Setujui Verifikasi -->
                    <div>
                        <label for="verified" class="block text-sm font-medium text-gray-700 mb-3">Setujui Verifikasi</label>
                        <div class="flex items-center space-x-4 mt-2">
                            <!-- Approve Button -->
                            <label class="flex items-center cursor-pointer group">
                                <input type="radio" 
                                    name="verified" 
                                    value="Yes" 
                                    {{ old('verified', $damagedAsset->verified) == 'Yes' ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="relative flex items-center justify-center w-full px-6 py-3 bg-white border-2 border-gray-300 rounded-lg transition-all duration-200 peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:shadow-md hover:border-green-300 hover:bg-green-25 group-hover:shadow-sm">
                                    <svg class="w-5 h-5 mr-2 text-gray-400 transition-colors duration-200 peer-checked:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 transition-colors duration-200 peer-checked:text-green-700">Ya, Setujui</span>
                                    <!-- Check indicator -->
                                    <div class="absolute top-2 right-2 w-3 h-3 bg-green-500 rounded-full opacity-0 scale-0 transition-all duration-200 peer-checked:opacity-100 peer-checked:scale-100"></div>
                                </div>
                            </label>
                        </div>
                        @error('verified')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Kategori Asset -->
                    <div>
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori Aset</label>
                        <input type="text" 
                            id="kategori" 
                            name="kategori" 
                            value="{{ old('kategori', $damagedAsset->asset->kategori) }}"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('kategori') border-red-500 @enderror"
                            placeholder="Kategori Aset"
                            readonly
                            required>
                        @error('kategori')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Petugas -->
                    <div>
                        <label for="petugas" class="block text-sm font-medium text-gray-700 mb-1">Petugas</label>
                        <select id="petugas" 
                                name="petugas" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('petugas') border-red-500 @enderror"
                                required>
                            <option value="">Petugas</option>
                            @foreach($petugas as $category)
                                <option value="{{ $category }}" {{ (old('petugas', $damagedAsset->petugas) == $category) ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Isi sesuai dengan petugas yang cocok</p>
                        @error('petugas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-2">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                        Simpan
                    </button>
                    <a href="{{ route('fix-verification.index') }}" class="px-4 py-2 bg-red-600 text-white rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection