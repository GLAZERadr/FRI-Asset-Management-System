@extends('layouts.app')

@section('header', 'Edit Aset')

@section('content')
<div class="container mx-auto max-w-6xl">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 lg:p-6">
            <form action="{{ route('pemantauan.update', $asset->asset_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- File Upload Area -->
                <div class="mb-6">
                    <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-4 lg:p-6 text-center cursor-pointer hover:border-blue-400 transition-colors duration-200">
                        <input type="file" id="foto_asset" name="foto_asset" accept="image/*" class="hidden">
                        
                        @if($asset->foto_asset)
                            <div id="previewContent">
                                <img id="imagePreview" class="mx-auto h-24 lg:h-32 w-24 lg:w-32 object-cover rounded-lg mb-2" src="{{ $asset->foto_asset }}" alt="Current Image">
                                <p id="fileName" class="text-sm text-gray-600">Current Image</p>
                                <button type="button" id="removeImage" class="mt-2 text-red-600 hover:text-red-800 text-sm">Hapus Gambar</button>
                            </div>
                            <div id="uploadContent" class="hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 lg:h-12 w-8 lg:w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Seret atau jatuhkan file Anda di sini untuk menambahkannya.</p>
                                <p class="mt-1 text-xs text-gray-400">Atau klik untuk memilih file gambar (JPG, PNG, GIF, maksimal 2MB)</p>
                            </div>
                        @else
                            <div id="uploadContent">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 lg:h-12 w-8 lg:w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Seret atau jatuhkan file Anda di sini untuk menambahkannya.</p>
                                <p class="mt-1 text-xs text-gray-400">Atau klik untuk memilih file gambar (JPG, PNG, GIF, maksimal 2MB)</p>
                            </div>
                            <div id="previewContent" class="hidden">
                                <img id="imagePreview" class="mx-auto h-24 lg:h-32 w-24 lg:w-32 object-cover rounded-lg mb-2" src="" alt="Preview">
                                <p id="fileName" class="text-sm text-gray-600"></p>
                                <button type="button" id="removeImage" class="mt-2 text-red-600 hover:text-red-800 text-sm">Hapus Gambar</button>
                            </div>
                        @endif
                    </div>
                    @error('foto_asset')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                    <!-- Asset ID (Read only) -->
                    <div class="lg:col-span-1">
                        <label for="asset_id_display" class="block text-sm font-medium text-gray-700 mb-1">ID Aset</label>
                        <input type="text" 
                               id="asset_id_display" 
                               value="{{ $asset->asset_id }}"
                               class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed"
                               disabled>
                    </div>
                    
                    <!-- Nama Aset -->
                    <div class="lg:col-span-1">
                        <label for="nama_asset" class="block text-sm font-medium text-gray-700 mb-1">Nama Aset</label>
                        <input type="text" 
                               id="nama_asset" 
                               name="nama_asset" 
                               value="{{ old('nama_asset', $asset->nama_asset) }}"
                               class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nama_asset') border-red-500 @enderror"
                               placeholder="Nama Aset"
                               required>
                        @error('nama_asset')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tanggal Perolehan -->
                    <div class="lg:col-span-1">
                        <label for="tgl_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Perolehan</label>
                        <input type="date" 
                               id="tgl_perolehan" 
                               name="tgl_perolehan" 
                               value="{{ old('tgl_perolehan', $asset->tgl_perolehan ? $asset->tgl_perolehan->format('Y-m-d') : '') }}"
                               class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tgl_perolehan') border-red-500 @enderror"
                               required>
                        @error('tgl_perolehan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Kategori Aset -->
                    <div class="lg:col-span-1">
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori Aset</label>
                        <select id="kategori" 
                                name="kategori" 
                                class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('kategori') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Kategori Aset</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ old('kategori', $asset->kategori) == $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Masa Pakai Maksimum -->
                    <div class="lg:col-span-1">
                        <label for="masa_pakai_maksimum" class="block text-sm font-medium text-gray-700 mb-1">Masa Pakai Maksimum</label>
                        <div class="flex space-x-2">
                            <input type="number" 
                                   id="masa_pakai_maksimum" 
                                   name="masa_pakai_maksimum" 
                                   value="{{ old('masa_pakai_maksimum', $asset->masa_pakai_duration) }}"
                                   min="1" 
                                   class="flex-1 px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('masa_pakai_maksimum') border-red-500 @enderror"
                                   placeholder="Masa Pakai"
                                   required>
                            <select id="masa_pakai_unit" 
                                    name="masa_pakai_unit" 
                                    class="px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('masa_pakai_unit') border-red-500 @enderror"
                                    required>
                                <option value="hari" {{ old('masa_pakai_unit', $asset->masa_pakai_unit) == 'hari' ? 'selected' : '' }}>Hari</option>
                                <option value="bulan" {{ old('masa_pakai_unit', $asset->masa_pakai_unit) == 'bulan' ? 'selected' : '' }}>Bulan</option>
                                <option value="tahun" {{ old('masa_pakai_unit', $asset->masa_pakai_unit) == 'tahun' ? 'selected' : '' }}>Tahun</option>
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
                    <div class="lg:col-span-1">
                        <label for="spesifikasi" class="block text-sm font-medium text-gray-700 mb-1">Spesifikasi</label>
                        <textarea id="spesifikasi" 
                                  name="spesifikasi" 
                                  rows="3"
                                  class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('spesifikasi') border-red-500 @enderror resize-none"
                                  placeholder="Spesifikasi"
                                  required>{{ old('spesifikasi', $asset->spesifikasi) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Isi dengan merk/tipe/spesifikasi</p>
                        @error('spesifikasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Nilai Perolehan -->
                    <div class="lg:col-span-1">
                        <label for="nilai_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Nilai Perolehan</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                            <input type="number" 
                                   id="nilai_perolehan" 
                                   name="nilai_perolehan" 
                                   value="{{ old('nilai_perolehan', $asset->nilai_perolehan) }}"
                                   min="0"
                                   step="0.01"
                                   class="w-full pl-8 pr-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('nilai_perolehan') border-red-500 @enderror"
                                   placeholder="750000"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Isi dengan angka: 750000</p>
                        @error('nilai_perolehan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Lokasi Aset -->
                    <div class="lg:col-span-1">
                        <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi Aset</label>
                        <select id="lokasi" 
                                name="lokasi" 
                                class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('lokasi') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Lokasi</option>
                            <option value="Logistik" {{ old('lokasi', $asset->lokasi) == 'Logistik' ? 'selected' : '' }}>Logistik</option>
                            <option value="Laboratorium" {{ old('lokasi', $asset->lokasi) == 'Laboratorium' ? 'selected' : '' }}>Laboratorium</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Pilih lokasi aset</p>
                        @error('lokasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Tingkat Kepentingan Asset -->
                    <div class="lg:col-span-1">
                        <label for="tingkat_kepentingan_asset" class="block text-sm font-medium text-gray-700 mb-1">Tingkat Kepentingan Asset</label>
                        <select id="tingkat_kepentingan_asset" 
                                name="tingkat_kepentingan_asset" 
                                class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tingkat_kepentingan_asset') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Tingkat Kepentingan</option>
                            <option value="1" {{ old('tingkat_kepentingan_asset', $asset->tingkat_kepentingan_asset) == '1' ? 'selected' : '' }}>Rendah</option>
                            <option value="2" {{ old('tingkat_kepentingan_asset', $asset->tingkat_kepentingan_asset) == '2' ? 'selected' : '' }}>Sedang</option>
                            <option value="3" {{ old('tingkat_kepentingan_asset', $asset->tingkat_kepentingan_asset) == '3' ? 'selected' : '' }}>Tinggi</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Pilih tingkat kepentingan asset</p>
                        @error('tingkat_kepentingan_asset')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Sumber Perolehan -->
                    <div class="lg:col-span-1">
                        <label for="sumber_perolehan" class="block text-sm font-medium text-gray-700 mb-1">Sumber Perolehan</label>
                        <select id="sumber_perolehan" 
                                name="sumber_perolehan" 
                                class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('sumber_perolehan') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Sumber Perolehan</option>
                            @foreach($sumberPerolehan as $sumber)
                                <option value="{{ $sumber }}" {{ old('sumber_perolehan', $asset->sumber_perolehan) == $sumber ? 'selected' : '' }}>{{ $sumber }}</option>
                            @endforeach
                        </select>
                        @error('sumber_perolehan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Kode Ruangan -->
                    <div class="lg:col-span-1">
                        <label for="kode_ruangan" class="block text-sm font-medium text-gray-700 mb-1">Kode Ruangan</label>
                        <input type="text" 
                               id="kode_ruangan" 
                               name="kode_ruangan" 
                               value="{{ old('kode_ruangan', $asset->kode_ruangan) }}"
                               class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('kode_ruangan') border-red-500 @enderror"
                               placeholder="TULT-0901"
                               required>
                        <p class="mt-1 text-xs text-gray-500">Isi dengan kode Ruangan: TULT-0901</p>
                        @error('kode_ruangan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Status Kelayakan -->
                    <div class="lg:col-span-1">
                        <label for="status_kelayakan" class="block text-sm font-medium text-gray-700 mb-1">Status Kelayakan</label>
                        <select id="status_kelayakan" 
                                name="status_kelayakan" 
                                class="w-full px-3 py-3 lg:py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('status_kelayakan') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Status Kelayakan</option>
                            <option value="Layak" {{ old('status_kelayakan', $asset->status_kelayakan) == 'Layak' ? 'selected' : '' }}>Layak</option>
                            <option value="Tidak Layak" {{ old('status_kelayakan', $asset->status_kelayakan) == 'Tidak Layak' ? 'selected' : '' }}>Tidak Layak</option>
                        </select>
                        @error('status_kelayakan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="mt-6 lg:mt-8 flex flex-col sm:flex-row sm:justify-between gap-3 sm:gap-2">
                    <a href="{{ route('pemantauan.show', $asset->asset_id) }}" 
                       class="w-full sm:w-auto px-6 py-3 lg:py-2 bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 text-center font-medium">
                        Kembali
                    </a>
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-3 lg:py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 font-medium">
                        Simpan Perubahan
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
    
    // Drag and drop functionality - only on desktop
    if (window.innerWidth > 768) {
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
    }
    
    // Remove image
    removeImage.addEventListener('click', function(e) {
        e.stopPropagation();
        fileInput.value = '';
        uploadContent.classList.remove('hidden');
        previewContent.classList.add('hidden');
        dropZone.classList.remove('border-green-400');
        dropZone.classList.add('border-gray-300');
    });
    
    // Format currency input
    const nilaiInput = document.getElementById('nilai_perolehan');
    nilaiInput.addEventListener('input', function(e) {
        // Allow only numbers
        let value = e.target.value.replace(/[^\d]/g, '');
        e.target.value = value;
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
    
    // Auto-resize textarea on mobile
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
    
    // Improve touch targets on mobile
    if (window.innerWidth <= 768) {
        const formElements = document.querySelectorAll('input, select, textarea, button');
        formElements.forEach(element => {
            element.style.minHeight = '48px'; // Recommended touch target size
        });
    }
});
</script>

<style>
/* Additional mobile-specific styles */
@media (max-width: 768px) {
    /* Improve form field spacing */
    .form-field {
        margin-bottom: 1rem;
    }
    
    /* Better focus states for mobile */
    input:focus, select:focus, textarea:focus {
        transform: scale(1.02);
        transition: transform 0.2s ease;
    }
    
    /* Prevent zoom on input focus in iOS */
    input[type="text"],
    input[type="number"],
    input[type="date"],
    select,
    textarea {
        font-size: 16px;
    }
    
    /* Better button styling for mobile */
    button, .button {
        min-height: 48px;
        font-size: 16px;
    }
}

/* Smooth transitions for all interactive elements */
input, select, textarea, button {
    transition: all 0.2s ease;
}

/* Better visual feedback */
input:focus, select:focus, textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
@endsection