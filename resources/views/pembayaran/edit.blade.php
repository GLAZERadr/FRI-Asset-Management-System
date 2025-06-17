@extends('layouts.app')
@section('header', 'Edit Pembayaran')
@section('content')
<div class="container mx-auto max-w-4xl">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            @if(session('error'))
                <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('pembayaran.update', $payment) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <x-form-input 
                                name="no_invoice" 
                                label="No Invoice" 
                                required 
                                value="{{ old('no_invoice', $payment->no_invoice) }}"
                                placeholder="Masukkan nomor invoice"
                            />
                        </div>
                        
                        <div>
                            <x-form-input 
                                name="jatuh_tempo" 
                                label="Jatuh Tempo" 
                                type="date" 
                                required 
                                value="{{ old('jatuh_tempo', $payment->jatuh_tempo->format('Y-m-d')) }}"
                            />
                        </div>
                        
                        <div>
                            <x-form-input 
                                name="vendor" 
                                label="Vendor" 
                                required 
                                value="{{ old('vendor', $payment->vendor) }}"
                                placeholder="Nama vendor/perusahaan"
                            />
                        </div>
                        
                        <div>
                            <x-form-input 
                                name="total_tagihan" 
                                label="Total Tagihan" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                value="{{ old('total_tagihan', $payment->total_tagihan) }}"
                                placeholder="0.00"
                            />
                            <p class="mt-1 text-xs text-gray-500">Masukkan dalam format angka (contoh: 1500000)</p>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label for="tipe_pembayaran" class="block text-sm font-medium text-gray-700">
                                Tipe Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select name="tipe_pembayaran" id="tipe_pembayaran" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 @error('tipe_pembayaran') border-red-300 @enderror" 
                                required>
                                <option value="">Pilih tipe pembayaran</option>
                                @foreach($paymentTypes as $key => $label)
                                    <option value="{{ $key }}" {{ (old('tipe_pembayaran', $payment->tipe_pembayaran) == $key) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipe_pembayaran')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jenis_pembayaran" class="block text-sm font-medium text-gray-700">
                                Jenis Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_pembayaran" id="jenis_pembayaran" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 @error('jenis_pembayaran') border-red-300 @enderror" 
                                required>
                                <option value="">Pilih jenis pembayaran</option>
                                @foreach(\App\Models\Payment::JENIS_PEMBAYARAN as $key => $label)
                                    <option value="{{ $key }}" {{ old('jenis_pembayaran') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_pembayaran')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 @error('status') border-red-300 @enderror" 
                                required>
                                <option value="">Pilih status</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" {{ (old('status', $payment->status) == $key) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="file_invoice" class="block text-sm font-medium text-gray-700">
                                File Invoice
                            </label>
                            @if($payment->file_invoice)
                                <div class="mt-1 mb-2 text-sm text-gray-600">
                                    File saat ini: 
                                    <a href="{{ route('pembayaran.download-invoice', $payment) }}" class="text-blue-600 hover:text-blue-900">
                                        {{ basename($payment->file_invoice) }}
                                    </a>
                                </div>
                            @endif
                            <input type="file" 
                                name="file_invoice" 
                                id="file_invoice" 
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('file_invoice') border-red-300 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Format yang didukung: PDF, JPG, PNG, DOC, DOCX (Max: 10MB). Kosongkan jika tidak ingin mengubah file.</p>
                            @error('file_invoice')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Conditional field for payment date -->
                        <div id="tanggal_pembayaran_field" style="{{ ($payment->status === 'sudah_dibayar') ? 'display: block;' : 'display: none;' }}">
                            <x-form-input 
                                name="tanggal_pembayaran" 
                                label="Tanggal Pembayaran" 
                                type="datetime-local"
                                value="{{ old('tanggal_pembayaran', $payment->tanggal_pembayaran ? $payment->tanggal_pembayaran->format('Y-m-d\TH:i') : '') }}"
                            />
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-between items-center border-t pt-6">
                    <a href="{{ route('pembayaran.show', $payment) }}" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        Batal
                    </a>
                    
                    <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        Update Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const tanggalPembayaranField = document.getElementById('tanggal_pembayaran_field');
    
    function toggleTanggalPembayaran() {
        if (statusSelect.value === 'sudah_dibayar') {
            tanggalPembayaranField.style.display = 'block';
        } else {
            tanggalPembayaranField.style.display = 'none';
        }
    }
    
    // Initial check
    toggleTanggalPembayaran();
    
    // Listen for changes
    statusSelect.addEventListener('change', toggleTanggalPembayaran);
});
</script>
@endsection