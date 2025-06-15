@extends('layouts.app')
@section('header', 'Isi Proposal')
@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <!-- Header with Back Button -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Isi Proposal</h2>
                <a href="{{ route('perbaikan.validation.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Asset Photo Section -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-100 rounded-lg p-4 text-center">
                        @if($report->damaged_image)
                            <img src="{{ Storage::url($report->damaged_image) }}" 
                                 alt="{{ $report->asset->nama_asset }}" 
                                 class="w-full h-64 object-cover rounded-lg mx-auto">
                        @else
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Asset Information Section -->
                <div class="lg:col-span-2">
                    <form action="{{ route('perbaikan.validation.update', $report->validation_id) }}" method="POST" id="validationForm">
                        @csrf
                        <div class="space-y-4">
                            <!-- Read-only Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">ID Aset</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $report->asset->asset_id }}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Nama Aset</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $report->asset->nama_asset }}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Lokasi Aset</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $report->asset->lokasi ?? '-' }}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Kategori Aset</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $report->asset->kategori ?? '-' }}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Pelapor</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $report->reporter_role ?? '-' }}</p>
                                </div>
                                
                                <!-- Editable Status Kerusakan -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Status Kerusakan</label>
                                    <select name="tingkat_kerusakan" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" required>
                                        <option value="Ringan" {{ $report->tingkat_kerusakan === 'Ringan' ? 'selected' : '' }}>Ringan</option>
                                        <option value="Sedang" {{ $report->tingkat_kerusakan === 'Sedang' ? 'selected' : '' }}>Sedang</option>
                                        <option value="Berat" {{ $report->tingkat_kerusakan === 'Berat' ? 'selected' : '' }}>Berat</option>
                                    </select>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Deskripsi</label>
                                    <p class="text-lg font-semibold text-gray-900">{{ $report->deskripsi_kerusakan ?? '-' }}</p>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Estimasi Biaya</label>
                                    <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($report->estimasi_biaya, 0, ',', '.') }}</p>
                                </div>
                                
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Estimasi Waktu Perbaikan</label>
                                    <p class="text-lg font-semibold text-gray-900">
                                        @if($report->estimasi_waktu_perbaikan)
                                            @php
                                                $totalHours = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($report->estimasi_waktu_perbaikan));
                                                $days = intval($totalHours / 24);
                                                $hours = $totalHours % 24;
                                            @endphp
                                            {{ $days }} hari {{ $hours }} jam
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>

                                <!-- Alasan Penolakan -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Alasan Penolakan</label>
                                    <textarea name="alasan_penolakan" rows="3" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                                        placeholder="Isi alasan jika ditolak...">{{ $report->alasan_penolakan }}</textarea>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex justify-center space-x-4 pt-6">
                                <button type="button" onclick="submitForm('Yes')" 
                                    class="px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Divalidasi
                                </button>
                                
                                <button type="button" onclick="confirmReject()" 
                                    class="px-6 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Tolak & Kembalikan ke Staf
                                </button>
                            </div>
                        </div>
                        
                        <!-- Hidden input for validation status -->
                        <input type="hidden" name="validated" id="validatedInput">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="rejectModal" class="fixed inset-0 bg-opacity-30 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white bg-opacity-90 backdrop-blur-sm rounded-lg p-6 max-w-md w-full mx-4 shadow-2xl border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Penolakan</h3>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menolak proposal ini? Pastikan Anda telah mengisi alasan penolakan.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Batal
                </button>
                <button onclick="submitForm('Reject')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Ya, Tolak
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function submitForm(status) {
    document.getElementById('validatedInput').value = status;
    document.getElementById('validationForm').submit();
}

function confirmReject() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>

@endsection