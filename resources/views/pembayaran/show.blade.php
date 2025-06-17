@extends('layouts.app')
@section('header', 'Detail Pembayaran')
@section('content')
<div class="container mx-auto max-w-4xl">
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <!-- Payment Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">No Invoice</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->no_invoice }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jatuh Tempo</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->jatuh_tempo->format('d/m/Y') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vendor</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->vendor }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Tagihan</label>
                        <p class="mt-1 text-sm text-gray-900 font-medium">{{ $payment->getFormattedTotalTagihanAttribute() }}</p>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipe Pembayaran</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->getTipeLabel() }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Pembayaran</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->getJenisLabel() }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <div class="mt-1">
                            @php
                                $statusClasses = [
                                    'belum_dibayar' => 'bg-yellow-100 text-yellow-800',
                                    'sudah_dibayar' => 'bg-green-100 text-green-800',
                                    'terlambat' => 'bg-red-100 text-red-800',
                                    'dibatalkan' => 'bg-gray-100 text-gray-800'
                                ];
                                $class = $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-800';
                                $statusText = $payment->getStatusLabel();
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $class }}">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>

                    @if($payment->tanggal_pembayaran)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Pembayaran</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->tanggal_pembayaran->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">File Invoice</label>
                        @if($payment->file_invoice)
                            <div class="mt-1 flex items-center space-x-2">
                                <a href="{{ route('pembayaran.download-invoice', $payment) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                                    {{ basename($payment->file_invoice) }}
                                </a>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        @else
                            <p class="mt-1 text-sm text-gray-500">Tidak ada file</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Audit Trail -->
            <div class="border-t pt-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Audit</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dibuat Pada</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Diperbarui Pada</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="border-t pt-6 flex justify-between items-center">
                <a href="{{ route('pembayaran.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    Kembali
                </a>

                <div class="flex space-x-2">
                    @if($payment->status === 'belum_dibayar' && Auth::user()->hasAnyRole(['kaur_keuangan_logistik_sdm', 'wakil_dekan_2']))
                        <form action="{{ route('pembayaran.mark-paid', $payment) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50" onclick="return confirm('Tandai pembayaran ini sebagai sudah dibayar?')">
                                Tandai Sudah Dibayar
                            </button>
                        </form>

                        <form action="{{ route('pembayaran.cancel', $payment) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50" onclick="return confirm('Batalkan pembayaran ini?')">
                                Batalkan
                            </button>
                        </form>
                    @endif

                    @can('mark_payment_as_paid')
                        @if($payment->status !== 'sudah_dibayar')
                            <form action="{{ route('pembayaran.mark-paid', $payment) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50" onclick="return confirm('Apakah Anda yakin membayar invoice ini?')">
                                    Bayar
                                </button>
                            </form>
                        @endif
                    @endcan

                    @can('edit_payment')
                        @if($payment->status !== 'sudah_dibayar')
                            <a href="{{ route('pembayaran.edit', $payment) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                Edit
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection