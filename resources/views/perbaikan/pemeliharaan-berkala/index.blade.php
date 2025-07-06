@extends('layouts.app')
@section('header', 'Pemeliharaan Berkala')
@section('content')
<div class="container mx-auto">
    <!-- Auto-generation Status Alert -->
    @if(session('auto_generated_count'))
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
        <div class="flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span><strong>{{ session('auto_generated_count') }}</strong> jadwal pemeliharaan otomatis telah dibuat untuk aset yang sering mengalami kerusakan.</span>
        </div>
    </div>
    @endif

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between space-y-4 lg:space-y-0">
            <form action="{{ route('perbaikan.pemeliharaan-berkala.index') }}" method="GET" class="flex flex-col lg:flex-row items-end space-y-4 lg:space-y-0 lg:space-x-4 flex-1">
                <div class="w-full lg:w-64">
                    <label for="asset_search" class="block text-sm font-medium text-gray-700 mb-1">Cari Aset</label>
                    <input type="text" id="asset_search" name="asset_search" value="{{ request('asset_search') }}"
                           placeholder="ID Aset atau Nama Aset"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                
                <div class="w-full lg:w-48">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Status</option>
                        <option value="Dijadwalkan" {{ request('status') == 'Dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Dibatalkan" {{ request('status') == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
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
                </div>
            </form>
            
            <div class="flex space-x-2">
                <button type="button" onclick="autoGenerateSchedules()" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow-sm hover:bg-blue-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Auto Generate
                </button>
                <button type="button" onclick="openScheduleModal()" class="px-4 py-2 bg-green-600 text-white rounded-md shadow-sm hover:bg-green-700 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Jadwal
                </button>
            </div>
        </div>
    </div>
    
    <!-- Maintenance Schedule Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pemeliharaan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penanggung Jawab</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($schedules as $index => $schedule)
                    <tr class="hover:bg-gray-50 {{ $schedule->auto_generated ? 'bg-blue-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $schedules->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $schedule->asset_id }}
                            @if($schedule->auto_generated)
                                <span class="ml-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Auto</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $schedule->asset->nama_asset ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $schedule->asset->kode_ruangan ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $schedule->tanggal_pemeliharaan->format('d-m-Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @php
                                $jenisClass = [
                                    'Rutin' => 'bg-green-100 text-green-800',
                                    'Tambahan' => 'bg-yellow-100 text-yellow-800',
                                    'Khusus' => 'bg-purple-100 text-purple-800'
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $jenisClass[$schedule->jenis_pemeliharaan] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $schedule->jenis_pemeliharaan }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = [
                                    'Dijadwalkan' => 'bg-blue-100 text-blue-800',
                                    'Selesai' => 'bg-green-100 text-green-800',
                                    'Dibatalkan' => 'bg-red-100 text-red-800'
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass[$schedule->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $schedule->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $schedule->penanggung_jawab ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-2">
                                <!-- View Details -->
                                <button onclick="viewScheduleDetails({{ $schedule->id }})" 
                                    class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                
                                @if($schedule->status !== 'Selesai')
                                    <!-- Edit Icon -->
                                    <button onclick="editSchedule({{ $schedule->id }})" 
                                        class="text-green-600 hover:text-green-900" title="Edit Jadwal">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Delete Icon -->
                                    <button onclick="confirmDelete({{ $schedule->id }})" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-10 text-center">
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

<!-- Schedule Modal (Add/Edit Schedule) -->
<div id="scheduleModal" class="fixed inset-0 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-screen overflow-y-auto">
            <form id="scheduleForm" method="POST">
                @csrf
                <div id="methodField"></div>
                
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Jadwal Pemeliharaan</h3>
                        <button type="button" onclick="closeScheduleModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="md:col-span-2">
                            <label for="asset_id" class="block text-sm font-medium text-gray-700 mb-1">ID Aset *</label>
                            <input type="text" id="asset_id" name="asset_id" required 
                                placeholder="Masukkan ID Aset (misal: A-12345)"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                            <p class="text-xs text-gray-500 mt-1">Pastikan ID aset sudah terdaftar dalam sistem</p>
                        </div>
                        
                        <div>
                            <label for="tanggal_pemeliharaan" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pemeliharaan *</label>
                            <input type="date" id="tanggal_pemeliharaan" name="tanggal_pemeliharaan" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="jenis_pemeliharaan" class="block text-sm font-medium text-gray-700 mb-1">Jenis Pemeliharaan *</label>
                            <select id="jenis_pemeliharaan" name="jenis_pemeliharaan" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                                <option value="">Pilih Jenis</option>
                                <option value="Rutin">Rutin</option>
                                <option value="Tambahan">Tambahan</option>
                                <option value="Khusus">Khusus</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="alasan_penjadwalan" class="block text-sm font-medium text-gray-700 mb-1">Alasan Penjadwalan</label>
                            <textarea id="alasan_penjadwalan" name="alasan_penjadwalan" rows="2" 
                                placeholder="Contoh: Laporan kerusakan berulang, Jadwal 6 bulan"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        
                        <div>
                            <label for="penanggung_jawab" class="block text-sm font-medium text-gray-700 mb-1">Penanggung Jawab</label>
                            <input type="text" id="penanggung_jawab" name="penanggung_jawab" 
                                placeholder="Nama staff/logistik yang bertanggung jawab"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status Jadwal</label>
                            <select id="status" name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500">
                                <option value="Dijadwalkan">Dijadwalkan</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="catatan_tambahan" class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
                            <textarea id="catatan_tambahan" name="catatan_tambahan" rows="3" 
                                placeholder="Opsional, untuk keterangan kondisi atau kebutuhan khusus"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Batal
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

<!-- View Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-3xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Detail Jadwal Pemeliharaan</h3>
                    <button type="button" onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="detailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-500 mb-6">Apakah Anda yakin ingin menghapus jadwal pemeliharaan ini?</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Batal
                </button>
                <button onclick="deleteSchedule()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let deleteId = null;
let editingId = null;

// Modal Functions
function openScheduleModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Jadwal Pemeliharaan';
    document.getElementById('scheduleForm').action = '{{ route("perbaikan.pemeliharaan-berkala.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('scheduleForm').reset();
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').classList.add('hidden');
    document.getElementById('scheduleForm').reset();
}

function editSchedule(id) {
    editingId = id;
    document.getElementById('modalTitle').textContent = 'Edit Jadwal Pemeliharaan';
    document.getElementById('scheduleForm').action = `/perbaikan/pemeliharaan-berkala/${id}`;
    document.getElementById('methodField').innerHTML = '@method("PUT")';
    
    // Fetch schedule data and populate form
    fetch(`/perbaikan/pemeliharaan-berkala/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('asset_id').value = data.asset_id || '';
            document.getElementById('tanggal_pemeliharaan').value = data.tanggal_pemeliharaan || '';
            document.getElementById('jenis_pemeliharaan').value = data.jenis_pemeliharaan || '';
            document.getElementById('alasan_penjadwalan').value = data.alasan_penjadwalan || '';
            document.getElementById('penanggung_jawab').value = data.penanggung_jawab || '';
            document.getElementById('status').value = data.status || '';
            document.getElementById('catatan_tambahan').value = data.catatan_tambahan || '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data jadwal');
        });
    
    document.getElementById('scheduleModal').classList.remove('hidden');
}

function viewScheduleDetails(id) {
    fetch(`/perbaikan/pemeliharaan-berkala/${id}/details`)
        .then(response => response.json())
        .then(data => {
            const content = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Informasi Aset</h4>
                        <p><strong>ID Aset:</strong> ${data.asset_id}</p>
                        <p><strong>Nama Aset:</strong> ${data.asset?.nama_asset || '-'}</p>
                        <p><strong>Lokasi:</strong> ${data.asset?.kode_ruangan || '-'}</p>
                        <p><strong>Kategori:</strong> ${data.asset?.kategori || '-'}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Informasi Jadwal</h4>
                        <p><strong>Tanggal:</strong> ${data.tanggal_pemeliharaan_formatted}</p>
                        <p><strong>Jenis:</strong> ${data.jenis_pemeliharaan}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                        <p><strong>Penanggung Jawab:</strong> ${data.penanggung_jawab || '-'}</p>
                    </div>
                    <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Alasan Penjadwalan</h4>
                        <p class="text-gray-700">${data.alasan_penjadwalan || '-'}</p>
                    </div>
                    <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Catatan Tambahan</h4>
                        <p class="text-gray-700">${data.catatan_tambahan || '-'}</p>
                    </div>
                    ${data.previous_notes && data.previous_notes.length > 0 ? `
                    <div class="md:col-span-2 bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2">Catatan Pemeliharaan Sebelumnya</h4>
                        ${data.previous_notes.map(note => `<p class="text-blue-700 mb-1">• ${note}</p>`).join('')}
                    </div>
                    ` : ''}
                </div>
            `;
            document.getElementById('detailsContent').innerHTML = content;
            document.getElementById('detailsModal').classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat detail jadwal');
        });
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
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

// Auto Generate Function
function autoGenerateSchedules() {
    if (confirm('Sistem akan menganalisis aset yang sering mengalami kerusakan dan membuat jadwal pemeliharaan otomatis. Lanjutkan?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("perbaikan.pemeliharaan-berkala.auto-generate") }}';
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

// Set minimum date to today
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('tanggal_pemeliharaan');
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
});
</script>

@endsection