@extends('layouts.app')
@section('header', 'Validasi Aset - ' . $report->id_laporan)
@section('content')
<div class="container mx-auto px-4">
    <!-- Header Information -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">ID Laporan</label>
                <p class="mt-1 text-sm text-gray-900">{{ $report->id_laporan }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kode Ruangan</label>
                <p class="mt-1 text-sm text-gray-900">{{ $report->kode_ruangan }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Laporan</label>
                <p class="mt-1 text-sm text-gray-900">{{ $report->tanggal_laporan->format('d-m-Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="mb-4">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <a href="#" onclick="showTab('status')" id="status-tab" 
                   class="border-b-2 border-black py-2 px-4 text-sm font-medium text-black">
                    Status Kelayakan
                </a>
                <a href="#" onclick="showTab('validasi')" id="validasi-tab" 
                   class="border-transparent text-gray-400 cursor-not-allowed border-b-2 py-2 px-4 text-sm font-medium">
                    Validasi
                </a>
            </nav>
        </div>
    </div>

    <!-- Status Kelayakan Tab Content -->
    <div id="status-content" class="tab-content">
        <!-- Filter -->
        <div class="mb-4 flex justify-end">
            <select id="statusFilter" onchange="filterByStatus(this.value)" 
                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                <option value="">Filter</option>
                <option value="Layak">Layak</option>
                <option value="Tidak Layak">Tidak Layak</option>
            </select>
        </div>

        <!-- Assets Table -->
        <form id="statusForm" onsubmit="return false;">
            @csrf
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Aset</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spesifikasi</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Aset</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Perolehan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumentasi</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status kelayakan
                                    <button type="button" onclick="toggleSort()" class="ml-1">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                        </svg>
                                    </button>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur Aset (Tahun)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Umur (Tahun)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="assetsTableBody">
                            @forelse ($assetsData as $index => $assetInfo)
                                @php
                                    $asset = $assetInfo['asset'];
                                    $condition = $assetInfo['condition'];
                                    $notes = $assetInfo['notes'];
                                    $statusKelayakan = $assetInfo['status_kelayakan'];
                                @endphp
                                <tr class="asset-row" data-status="{{ $statusKelayakan }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $asset->asset_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->nama_asset }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->kategori ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->spesifikasi ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->lokasi ?? $asset->kode_ruangan }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $asset->tgl_perolehan ? $asset->tgl_perolehan->format('d-m-Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $notes ?: '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($asset->foto_asset)
                                            <button type="button" onclick="openImageModal('{{ $asset->foto_asset }}')" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                                                </svg>
                                            </button>
                                        @else
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                                            </svg>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <select name="assets[{{ $asset->asset_id }}][status_kelayakan]" 
                                                    class="status-select border border-gray-300 rounded px-2 py-1 text-xs mr-2" 
                                                    onchange="updateRowStatus(this)">
                                                <option value="Tidak Layak" {{ $statusKelayakan === 'Tidak Layak' ? 'selected' : '' }}>Tidak Layak</option>
                                                <option value="Layak" {{ $statusKelayakan === 'Layak' ? 'selected' : '' }}>Layak</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Hidden inputs for condition and notes -->
                                        <input type="hidden" name="assets[{{ $asset->asset_id }}][condition]" value="{{ $condition }}">
                                        <input type="hidden" name="assets[{{ $asset->asset_id }}][notes]" value="{{ $notes }}">
                                    </td>
                                        <!-- NEW: Umur Aset Field -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <input type="text" 
                                            name="assets[{{ $asset->asset_id }}][umur_aset]" 
                                            value=""
                                            class="w-24 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
                                            placeholder="5 tahun">
                                    </td>
                                    <!-- NEW: Max Umur Field -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <input type="text" 
                                            name="assets[{{ $asset->asset_id }}][max_umur]" 
                                            value=""
                                            class="w-24 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500"
                                            placeholder="10 tahun">
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900">Tidak Ada Aset</h3>
                                        <p class="text-gray-500">Tidak ada aset yang ditemukan untuk ruangan ini.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

        <!-- Action Buttons for Status Tab -->
        <div class="flex justify-end space-x-3 mb-6">
            <button type="button" onclick="deleteValidation()" 
                    class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                Hapus
            </button>
            <button type="button" onclick="saveAndContinueToValidation()" 
                    class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Simpan
            </button>
        </div>
    </div>

    <!-- Validasi Tab Content (Initially Hidden) -->
    <div id="validasi-content" class="tab-content hidden">
        <!-- Summary Table with Asset Groups -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="validationSummaryTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Unit</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Unit Baik</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Unit Tidak Layak</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Aset Tidak Layak</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Unit Rusak</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Aset Rusak</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi Kerusakan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumentasi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="validationSummaryBody">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Validation Form -->
        <form method="POST" action="{{ route('fix-validation.approve', $report->id_laporan) }}" id="validationForm">
            @csrf
            <!-- Validator Notes -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea id="catatan" name="catatan" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                          placeholder="Isi dengan catatan laporan anda...">{{ old('catatan') }}</textarea>
            </div>

            <!-- Validation Checkbox -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex items-center">
                    <input type="checkbox" id="validasi_checkbox" name="validasi" value="1" required
                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <label for="validasi_checkbox" class="ml-2 block text-sm text-gray-900">
                        Validasi
                    </label>
                </div>
            </div>

            <!-- Final Action Buttons -->
            <div class="flex justify-end space-x-3 mb-6">
                <button type="button" onclick="deleteValidation()" 
                        class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Hapus
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Dokumentasi Aset</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <img id="modalImage" src="" alt="Asset Documentation" class="w-full h-auto rounded">
        </div>
    </div>
</div>

<!-- Success/Error Notifications -->
<div id="notification" class="fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 hidden"></div>

<script>
let isFormSaved = false;
let assetsData = @json($assetsData);

function showTab(tabName) {
    if (tabName === 'validasi' && !isFormSaved) {
        showNotification('Silakan simpan status kelayakan terlebih dahulu sebelum melanjutkan ke validasi.', 'warning');
        return;
    }
    
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Update tab styles - ONLY target the specific nav tabs, not all nav elements
    const tabNav = document.querySelector('.border-b.border-gray-200 nav');
    if (tabNav) {
        tabNav.querySelectorAll('a').forEach(tab => {
            tab.className = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-4 text-sm font-medium';
        });
    }
    
    if (tabName === 'status') {
        document.getElementById('status-tab').className = 'border-b-2 border-black py-2 px-4 text-sm font-medium text-black';
    } else {
        document.getElementById('validasi-tab').className = 'border-b-2 border-black py-2 px-4 text-sm font-medium text-black';
    }
}

function saveAndContinueToValidation() {
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Menyimpan...';
    button.disabled = true;
    
    // Collect form data
    const formData = new FormData(document.getElementById('statusForm'));
    
    // Send AJAX request to save status
    fetch(`{{ route('fix-validation.store', $report->id_laporan) }}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Mark form as saved
            isFormSaved = true;
            
            // Enable validasi tab
            const validasiTab = document.getElementById('validasi-tab');
            validasiTab.className = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-4 text-sm font-medium cursor-pointer';
            validasiTab.onclick = function() { showTab('validasi'); };
            
            // Generate validation summary table
            generateValidationSummary();
            
            // Switch to validasi tab
            showTab('validasi');
            
            // Show success message
            showNotification('Status kelayakan berhasil disimpan. Silakan lanjutkan ke validasi.', 'success');
        } else {
            throw new Error(data.message || 'Terjadi kesalahan saat menyimpan data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(error.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
    })
    .finally(() => {
        // Restore button state
        button.textContent = originalText;
        button.disabled = false;
    });
}

function generateValidationSummary() {
    const statusSelects = document.querySelectorAll('.status-select');
    const assetGroups = {};
    
    // Group assets by name and calculate statistics
    statusSelects.forEach((select, index) => {
        const row = select.closest('tr');
        const assetName = row.cells[2].textContent.trim();
        const assetId = row.cells[1].textContent.trim();
        const status = select.value;
        
        if (!assetGroups[assetName]) {
            assetGroups[assetName] = {
                total: 0,
                baik: 0,
                tidakLayak: 0,
                rusak: 0,
                tidakLayakIds: [],
                rusakIds: [],
                deskripsi: []
            };
        }
        
        assetGroups[assetName].total++;
        if (status === 'Layak') {
            assetGroups[assetName].baik++;
        } else {
            assetGroups[assetName].tidakLayak++;
            assetGroups[assetName].tidakLayakIds.push(assetId);
            
            // Check if it's damaged based on condition from the original data
            const assetInfo = assetsData.find(a => a.asset.asset_id === assetId);
            if (assetInfo && ['Rusak', 'Tidak Berfungsi', 'Buruk'].includes(assetInfo.condition)) {
                assetGroups[assetName].rusak++;
                assetGroups[assetName].rusakIds.push(assetId);
                if (assetInfo.notes) {
                    assetGroups[assetName].deskripsi.push(assetInfo.notes);
                }
            }
        }
    });
    
    // Generate table HTML
    let tableHTML = '';
    Object.entries(assetGroups).forEach(([name, data]) => {
        tableHTML += `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${name}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${data.total}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${data.baik}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${data.tidakLayak}</td>
                <td class="px-6 py-4 text-sm text-gray-500">${data.tidakLayakIds.join(', ') || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${data.rusak}</td>
                <td class="px-6 py-4 text-sm text-gray-500">${data.rusakIds.join(', ') || '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-500">${data.deskripsi.join('; ') || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                    </svg>
                </td>
            </tr>
        `;
    });
    
    if (tableHTML === '') {
        tableHTML = `
            <tr>
                <td colspan="9" class="px-6 py-10 text-center text-sm text-gray-500">
                    Tidak ada data untuk ditampilkan
                </td>
            </tr>
        `;
    }
    
    document.getElementById('validationSummaryBody').innerHTML = tableHTML;
}

function deleteValidation() {
    if (confirm('Apakah Anda yakin ingin menghapus validasi ini?')) {
        window.location.href = "{{ route('fix-validation.index') }}";
    }
}

function showNotification(message, type) {
    const notification = document.getElementById('notification');
    notification.className = `fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 
        type === 'warning' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' :
        'bg-red-100 text-red-800 border border-red-200'
    }`;
    notification.textContent = message;
    notification.classList.remove('hidden');
    
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 5000);
}

function filterByStatus(status) {
    const rows = document.querySelectorAll('.asset-row');
    rows.forEach(row => {
        if (status === '' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function updateRowStatus(selectElement) {
    const row = selectElement.closest('.asset-row');
    row.dataset.status = selectElement.value;
}

function editStatus(button) {
    // This function can be used to add additional editing functionality if needed
    const select = button.parentElement.querySelector('.status-select');
    select.focus();
}

function openImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

function toggleSort() {
    const tbody = document.getElementById('assetsTableBody');
    const rows = Array.from(tbody.querySelectorAll('.asset-row'));
    
    rows.sort((a, b) => {
        const statusA = a.dataset.status;
        const statusB = b.dataset.status;
        return statusA.localeCompare(statusB);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Close modals when clicking outside
window.onclick = function(event) {
    const imageModal = document.getElementById('imageModal');
    
    if (event.target === imageModal) {
        closeImageModal();
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Show status tab by default
    showTab('status');
});
</script>
@endsection