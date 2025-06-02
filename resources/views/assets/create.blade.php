@extends('layouts.app')

@section('header', 'Tambah Aset Baru')

@section('content')
<div class="container mx-auto">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <!-- File Upload Area -->
            <div class="mb-6">
                <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition-colors duration-200">
                    <input type="file" id="foto_asset" name="foto_asset" accept="image/*" class="hidden">
                    <div id="uploadContent">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Seret atau jatuhkan file Anda di sini untuk menambahkannya.</p>
                        <p class="mt-1 text-xs text-gray-400">Atau klik untuk memilih file gambar (JPG, PNG, GIF, maksimal 2MB)</p>
                    </div>
                    <div id="previewContent" class="hidden">
                        <img id="imagePreview" class="mx-auto h-32 w-32 object-cover rounded-lg mb-2" src="" alt="Preview">
                        <p id="fileName" class="text-sm text-gray-600"></p>
                        <button type="button" id="removeImage" class="mt-2 text-red-600 hover:text-red-800 text-sm">Hapus Gambar</button>
                    </div>
                </div>
                @error('foto_asset')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <form action="{{ route('pemantauan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-2 gap-6">
                    <!-- Nama Aset -->
                    <div>
                        <label for="nama_asset" class="block text-sm font-medium text-gray-700 mb-1">Nama Aset</label>
                        <input type="text" 
                               id="nama_asset" 
                               name="nama_asset" 
                               value="{{ old('nama_asset') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nama_asset') border-red-500 @enderror"
                               placeholder="Nama Aset"
                               required>
                        @error('nama_asset')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tanggal Perolehan -->
                    <div>
                        <label for="tgl_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perolehan</label>
                        <input type="date" 
                               id="tgl_perolehan" 
                               name="tgl_perolehan" 
                               value="{{ old('tgl_perolehan') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tgl_perolehan') border-red-500 @enderror"
                               required>
                        @error('tgl_perolehan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Kategori Aset -->
                    <div>
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori Aset</label>
                        <select id="kategori" 
                                name="kategori" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('kategori') border-red-500 @enderror"
                                required>
                            <option value="">Kategori Aset</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ old('kategori') == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Masa Pakai Maksimum -->
                    <div>
                        <label for="masa_pakai_maksimum" class="block text-sm font-medium text-gray-700 mb-1">Masa Pakai Maksimum</label>
                        <div class="flex space-x-2">
                            <input type="number" 
                                   id="masa_pakai_maksimum" 
                                   name="masa_pakai_maksimum" 
                                   value="{{ old('masa_pakai_maksimum') }}"
                                   min="1" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('masa_pakai_maksimum') border-red-500 @enderror"
                                   placeholder="Masa Pakai"
                                   required>
                            <select id="masa_pakai_unit" 
                                    name="masa_pakai_unit" 
                                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('masa_pakai_unit') border-red-500 @enderror"
                                    required>
                                <option value="hari" {{ old('masa_pakai_unit') == 'hari' ? 'selected' : '' }}>Hari</option>
                                <option value="bulan" {{ old('masa_pakai_unit', 'bulan') == 'bulan' ? 'selected' : '' }}>Bulan</option>
                                <option value="tahun" {{ old('masa_pakai_unit') == 'tahun' ? 'selected' : '' }}>Tahun</option>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Contoh: 14 bulan, 2 tahun, 365 hari</p>
                        @error('masa_pakai_maksimum')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('masa_pakai_unit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Spesifikasi -->
                    <div>
                        <label for="spesifikasi" class="block text-sm font-medium text-gray-700 mb-1">Spesifikasi</label>
                        <textarea id="spesifikasi" 
                                  name="spesifikasi" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('spesifikasi') border-red-500 @enderror"
                                  placeholder="Spesifikasi"
                                  required>{{ old('spesifikasi') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Isi dengan merk/tipe/spesifikasi</p>
                        @error('spesifikasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nilai Perolehan -->
                    <div>
                        <label for="nilai_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Nilai Perolehan</label>
                        <input type="number" 
                               id="nilai_perolehan" 
                               name="nilai_perolehan" 
                               value="{{ old('nilai_perolehan') }}"
                               min="0"
                               step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nilai_perolehan') border-red-500 @enderror"
                               placeholder="Nilai Perolehan"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Isi dengan angka : 750000</p>
                        @error('nilai_perolehan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lokasi Aset -->
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi Aset</label>
                        <input type="text" 
                               id="lokasi" 
                               name="lokasi" 
                               value="{{ old('lokasi') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('lokasi') border-red-500 @enderror"
                               placeholder="Lokasi Aset"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Isi dengan kode gedung: TULT/GACUK</p>
                        @error('lokasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sumber Perolehan -->
                    <div>
                        <label for="sumber_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Sumber Perolehan</label>
                        <select id="sumber_perolehan" 
                                name="sumber_perolehan" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('sumber_perolehan') border-red-500 @enderror"
                                required>
                            <option value="">Sumber Perolehan</option>
                            @foreach($sumberPerolehan as $sumber)
                                <option value="{{ $sumber }}" {{ old('sumber_perolehan') == $sumber ? 'selected' : '' }}>{{ $sumber }}</option>
                            @endforeach
                        </select>
                        @error('sumber_perolehan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kode Ruangan -->
                    <div>
                        <label for="kode_ruangan" class="block text-sm font-medium text-gray-700 mb-1">Kode Ruangan</label>
                        <input type="text" 
                               id="kode_ruangan" 
                               name="kode_ruangan" 
                               value="{{ old('kode_ruangan') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('kode_ruangan') border-red-500 @enderror"
                               placeholder="Kode Ruangan"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Isi dengan kode Ruangan: TULT-0901</p>
                        @error('kode_ruangan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Kelayakan -->
                    <div>
                        <label for="status_kelayakan" class="block text-sm font-medium text-gray-700 mb-1">Status Kelayakan</label>
                        <select id="status_kelayakan" 
                                name="status_kelayakan" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('status_kelayakan') border-red-500 @enderror"
                                required>
                            <option value="">Status Kelayakan</option>
                            <option value="Layak" {{ old('status_kelayakan') == 'Layak' ? 'selected' : '' }}>Layak</option>
                            <option value="Tidak Layak" {{ old('status_kelayakan') == 'Tidak Layak' ? 'selected' : '' }}>Tidak Layak</option>
                        </select>
                        @error('status_kelayakan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-2">
                    <a href="{{ route('pemantauan.index') }}" class="px-4 py-2 bg-red-600 text-white rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                        Hapus
                    </a>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('foto_asset');
    const uploadContent = document.getElementById('uploadContent');
    const previewContent = document.getElementById('previewContent');
    const imagePreview = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');
    const removeImage = document.getElementById('removeImage');

    // Click to select file
    dropZone.addEventListener('click', function(e) {
        if (e.target !== removeImage) {
            fileInput.click();
        }
    });

    // Handle file selection
    fileInput.addEventListener('change', function(e) {
        handleFile(e.target.files[0]);
    });

    // Drag and drop functionality
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // Remove image
    removeImage.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        uploadContent.classList.remove('hidden');
        previewContent.classList.add('hidden');
        dropZone.classList.remove('border-green-400');
        dropZone.classList.add('border-gray-300');
    });

    function handleFile(file) {
        if (file && file.type.startsWith('image/')) {
            // Check file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                alert('File terlalu besar. Maksimal 2MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                fileName.textContent = file.name;
                uploadContent.classList.add('hidden');
                previewContent.classList.remove('hidden');
                dropZone.classList.remove('border-gray-300');
                dropZone.classList.add('border-green-400');
            };
            reader.readAsDataURL(file);

            // Set the file to the input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
        } else {
            alert('Silakan pilih file gambar (JPG, PNG, GIF).');
        }
    }
});
</script>
@endsection