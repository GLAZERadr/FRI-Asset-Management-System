@extends('layouts.app')
@section('header', 'Pemeliharaan Berkala')
@section('content')
<div class="container mx-auto">
    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('perbaikan.pemeliharaan-berkala.index') }}" method="GET" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Pilih Lokasi Aset</label>
                <select id="lokasi" name="lokasi" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $location)
                        <option value="{{ $location }}" {{ request('lokasi') == $location ? 'selected' : '' }}>{{ $location }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </button>
                <a href="{{ route('perbaikan.pemeliharaan-berkala.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md shadow-sm hover:bg-gray-300">
                    Reset
                </a>
                <button type="button" onclick="openBasicModal()" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Jadwal
                </button>
            </div>
        </form>
    </div>
    
    <!-- Maintenance Schedule Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pemeliharaan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($schedules as $index => $schedule)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $schedules->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $schedule->lokasi }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $schedule->tanggal_pemeliharaan->format('d-m-Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            @if($schedule->status !== 'completed')
                                <div class="flex justify-center space-x-2">
                                    <!-- Add Details Icon (checkmark) -->
                                    <button onclick="openDetailsModal({{ $schedule->id }})" 
                                        class="text-green-600 hover:text-green-900" title="Tambah Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Delete Icon -->
                                    <button onclick="confirmDelete({{ $schedule->id }})" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m3 0V6a2 2 0 00-2-2H7a2 2 0 00-2 2v1m3 0h6m-6 0V9a1 1 0 001 1h4a1 1 0 001-1V7" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900">Tidak Ada Jadwal</h3>
                                <p class="text-gray-500">Belum ada jadwal pemeliharaan yang ditambahkan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($schedules->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $schedules->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Basic Schedule Modal (Add Schedule) -->
<div id="basicScheduleModal" class="fixed inset-0 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full">
            <form action="{{ route('perbaikan.pemeliharaan-berkala.store-basic') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Tambah Jadwal Pemeliharaan</h3>
                        <button type="button" onclick="closeBasicModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 mb-6">
                        <div>
                            <label for="basic_lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                            <input type="text" id="basic_lokasi" name="lokasi" required 
                                placeholder="Masukkan lokasi aset"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="basic_tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pemeliharaan</label>
                            <input type="date" id="basic_tanggal" name="tanggal_pemeliharaan" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeBasicModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Hapus
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal (Edit/Add Details) -->
<div id="detailsModal" class="fixed inset-0 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
            <form id="detailsForm" enctype="multipart/form-data" method="POST">
                @csrf
                @method('PUT')
                
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Detail Pemeliharaan</h3>
                        <button type="button" onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- File Upload Area -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <p class="text-sm text-gray-600 mb-2">Seret atau jatuhkan file Anda di sini untuk menambahkannya.</p>
                            <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="hidden" onchange="handleFileSelect(event)">
                            <button type="button" onclick="document.getElementById('photos').click()" class="text-green-600 hover:text-green-700 font-medium">
                                Pilih File
                            </button>
                        </div>
                        <div id="selectedFiles" class="mt-2"></div>
                    </div>

                    <div class="mb-4">
                        <label for="detail_deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Pemeliharaan</label>
                        <textarea id="detail_deskripsi" name="deskripsi_pemeliharaan" rows="3" 
                            placeholder="Isi detail pengerjaan perbaikan"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>

                    <div class="mb-6">
                        <label for="detail_catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan/Tindak Lanjut</label>
                        <textarea id="detail_catatan" name="catatan_tindak_lanjut" rows="3" 
                            placeholder="Isi dengan saran perbaikan aset"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDetailsModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Hapus
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Apakah yakin dihapus ?</h3>
            <div class="flex justify-end space-x-3">
                <button onclick="deleteSchedule()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;

// Basic Modal Functions
function openBasicModal() {
    document.getElementById('basicScheduleModal').classList.remove('hidden');
}

function closeBasicModal() {
    document.getElementById('basicScheduleModal').classList.add('hidden');
}

// Details Modal Functions
function openDetailsModal(id) {
    document.getElementById('detailsForm').action = `/perbaikan/pemeliharaan-berkala/${id}/details`;
    document.getElementById('detailsModal').classList.remove('hidden');
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
    // Reset form
    document.getElementById('detailsForm').reset();
    document.getElementById('selectedFiles').innerHTML = '';
}

// Delete Functions
function confirmDelete(id) {
    deleteId = id;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
    deleteId = null;
}

function deleteSchedule() {
    if (deleteId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/perbaikan/pemeliharaan-berkala/${deleteId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// File Upload Functions
function handleFileSelect(event) {
    const files = event.target.files;
    const selectedFilesDiv = document.getElementById('selectedFiles');
    selectedFilesDiv.innerHTML = '';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileDiv = document.createElement('div');
        fileDiv.className = 'flex items-center justify-between bg-gray-100 p-2 rounded mt-2';
        fileDiv.innerHTML = `
            <span class="text-sm text-gray-700">${file.name}</span>
            <button type="button" onclick="removeFile(${i})" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        selectedFilesDiv.appendChild(fileDiv);
    }
}

function removeFile(index) {
    const input = document.getElementById('photos');
    const dt = new DataTransfer();
    const files = input.files;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }
    
    input.files = dt.files;
    handleFileSelect({ target: input });
}
</script>

@endsection