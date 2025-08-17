@extends('layouts.app')
@section('header')
    @if(auth()->user()->hasRole(['wakil_dekan_2', 'kaur_laboratorium', 'kaur_keuangan_logistik_sdm']))
        Laporan Monitoring
    @else
        Data Aset
    @endif
@endsection

@section('content')
<div class="container mx-auto px-2 sm:px-4 lg:px-6 xl:px-8 max-w-full">
    <!-- Filters and Action Buttons -->
    <div class="mb-4 lg:mb-6 flex flex-col space-y-3 lg:space-y-0 lg:flex-row lg:justify-between lg:items-center">
        <!-- Filter Controls -->
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 flex-1">
            @if(!auth()->user()->hasRole('wakil_dekan_2'))
                <!-- Location Filter -->
                <div class="w-full sm:w-auto min-w-0 sm:min-w-[200px]">
                    <select id="location_filter" onchange="filterByLocation(this.value)" 
                            class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Lokasi Aset</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" {{ request('lokasi') == $location ? 'selected' : '' }}>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <!-- Year Filter for Wakil Dekan 2 -->
                <div class="w-full sm:w-auto min-w-0 sm:min-w-[200px]">
                    <select id="year_filter" onchange="filterByYear(this.value)" 
                            class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Tahun Laporan</option>
                        @foreach($availableYears ?? [] as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Validation Status Filter -->
            @if(auth()->user()->hasRole(['wakil_dekan_2', 'kaur_laboratorium', 'kaur_keuangan_logistik_sdm']))
                <div class="w-full sm:w-auto min-w-0 sm:min-w-[200px]">
                    <select id="validation_filter" onchange="filterByValidation(this.value)" 
                            class="w-full px-2 sm:px-3 py-2 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                        <option value="">Semua Status Validasi</option>
                        <option value="valid" {{ request('validation_status') == 'valid' ? 'selected' : '' }}>Tervalidasi</option>
                        <option value="not_validated" {{ request('validation_status') == 'not_validated' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
            @endif

            <button onclick="resetAllFilters()" class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm bg-gray-500 text-white rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reset
            </button>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 lg:ml-4">
            <a href="{{ route('pemantauan.monitoring.printLaporan') }}" target="_blank" 
               class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm bg-gray-600 text-white rounded-md shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 sm:h-5 w-4 sm:w-5 mr-1 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak
            </a>
        </div>
    </div>
    
    <!-- Tab Navigation - Only for Wakil Dekan 2 -->
    @if(auth()->user()->hasRole('wakil_dekan_2'))
        <div class="mb-3 sm:mb-4">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex overflow-x-auto" id="main-tabs">
                    <a href="#" onclick="showTabContent('logistik')" id="logistik-tab" 
                       class="border-b-2 border-green-500 py-2 px-3 sm:px-4 text-sm font-medium text-green-600 whitespace-nowrap">
                        Laporan Monitoring Logistik
                    </a>
                    <a href="#" onclick="showTabContent('laboratorium')" id="laboratorium-tab" 
                       class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-3 sm:px-4 text-sm font-medium whitespace-nowrap">
                        Laporan Monitoring Lab
                    </a>
                </nav>
            </div>
        </div>
    @else
        <!-- Single Tab for Other Roles -->
        <div class="mb-3 sm:mb-4">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex">
                    <a href="#" class="border-b-2 border-green-500 py-2 px-3 sm:px-4 text-sm font-medium text-green-600">
                        Laporan Pemantauan
                    </a>
                </nav>
            </div>
        </div>
    @endif

    @if(auth()->user()->hasRole('wakil_dekan_2'))
        <!-- Logistik Tab Content -->
        <div id="logistik-content" class="tab-content">
            @include('monitoring.partials.logistik-table', [
                'monitoringReports' => $logistikReports ?? $monitoringReports,
                'assets' => $assets,
                'tableType' => 'logistik'
            ])
        </div>
        <!-- Laboratorium Tab Content -->
        <div id="laboratorium-content" class="tab-content hidden">
            @include('monitoring.partials.laboratorium-table', [
                'monitoringReports' => $laboratoriumReports ?? $monitoringReports,
                'assets' => $assets,
                'tableType' => 'laboratorium'
            ])
        </div>
    @elseif(auth()->user()->hasRole(['kaur_keuangan_logistik_sdm', 'staff_logistik']))
        <div id="logistik-content" class="tab-content">
            @include('monitoring.partials.logistik-table', [
                'monitoringReports' => $logistikReports ?? $monitoringReports,
                'assets' => $assets,
                'tableType' => 'logistik'
            ])
        </div>   
    @elseif(auth()->user()->hasRole(['kaur_laboratorium', 'staff_laboratorium']))
        <div id="laboratorium-content" class="tab-content">
            @include('monitoring.partials.laboratorium-table', [
                'monitoringReports' => $laboratoriumReports ?? $monitoringReports,
                'assets' => $assets,
                'tableType' => 'laboratorium'
            ])
        </div>
    @else
        <!-- Responsive Table for Other Roles -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Desktop Table View -->
            <div class="hidden xl:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Laporan</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumentasi</th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php $rowNumber = 1; @endphp
                            @forelse ($monitoringReports as $report)
                                @if($report->monitoring_data)
                                    @foreach($report->monitoring_data as $assetData)
                                        @php
                                            $asset = $assets->firstWhere('asset_id', $assetData['asset_id']);
                                        @endphp
                                        @if($asset)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">{{ $rowNumber++ }}</td>
                                            <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $report->id_laporan }}</td>
                                            <td class="px-3 py-4 text-sm text-gray-500 max-w-xs">
                                                <div class="truncate" title="{{ $asset->nama_asset }}">{{ $asset->nama_asset }}</div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->kategori ?? '-' }}</td>
                                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $asset->lokasi ?? $report->kode_ruangan }}</td>
                                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->tanggal_laporan->format('d-m-Y') }}</td>
                                            <td class="px-3 py-4 text-sm text-gray-500 max-w-xs">
                                                <div class="truncate" title="{{ $report->nama_pelapor }}">{{ $report->nama_pelapor }}</div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-sm">
                                                @if(isset($assetData['status']) && $assetData['status'] === 'baik')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Baik</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Butuh Perawatan</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-sm">
                                                @if(isset($assetData['foto_path']) && $assetData['foto_path'])
                                                    <img src="{{ $assetData['foto_path'] }}" alt="Monitoring Photo" class="h-8 w-8 rounded object-cover cursor-pointer" onclick="showImageModal('{{ $assetData['foto_path'] }}')">
                                                @else
                                                    <div class="h-8 w-8 bg-gray-200 rounded flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap text-center">
                                                <button onclick="showReportModal('{{ $report->id_laporan }}', '{{ $asset->asset_id }}')" 
                                                        class="text-gray-600 hover:text-gray-900 p-1 rounded transition-colors" title="Lihat Detail">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                @endif
                            @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900">Tidak Ada Data Monitoring</h3>
                                        <p class="text-gray-500">Belum ada laporan monitoring yang tersedia.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Compact Table View (Large screens) -->
            <div class="hidden lg:block xl:hidden overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Laporan</th>
                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aset & Lokasi</th>
                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th scope="col" class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th scope="col" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $rowNumber = 1; @endphp
                        @forelse ($monitoringReports as $report)
                            @if($report->monitoring_data)
                                @foreach($report->monitoring_data as $assetData)
                                    @php
                                        $asset = $assets->firstWhere('asset_id', $assetData['asset_id']);
                                    @endphp
                                    @if($asset)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-2 py-3 text-sm text-gray-900">{{ $rowNumber++ }}</td>
                                        <td class="px-2 py-3 text-sm font-medium text-gray-900">{{ $report->id_laporan }}</td>
                                        <td class="px-2 py-3 text-sm text-gray-500">
                                            <div class="font-medium truncate" title="{{ $asset->nama_asset }}">{{ $asset->nama_asset }}</div>
                                            <div class="text-xs text-gray-400">{{ $asset->lokasi ?? $report->kode_ruangan }}</div>
                                        </td>
                                        <td class="px-2 py-3 text-sm">
                                            @if(isset($assetData['status']) && $assetData['status'] === 'baik')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Baik</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Butuh Perawatan</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-3 text-sm text-gray-500">{{ $report->tanggal_laporan->format('d-m-Y') }}</td>
                                        <td class="px-2 py-3 text-center">
                                            <button onclick="showReportModal('{{ $report->id_laporan }}', '{{ $asset->asset_id }}')" 
                                                    class="text-gray-600 hover:text-gray-900 p-1 rounded" title="Detail">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            @endif
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900">Tidak Ada Data Monitoring</h3>
                                    <p class="text-gray-500">Belum ada laporan monitoring yang tersedia.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden space-y-3 p-3">
                @php $rowNumber = 1; @endphp
                @forelse ($monitoringReports as $report)
                    @if($report->monitoring_data)
                        @foreach($report->monitoring_data as $assetData)
                            @php
                                $asset = $assets->firstWhere('asset_id', $assetData['asset_id']);
                            @endphp
                            @if($asset)
                            <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                            #{{ $rowNumber++ }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-900">{{ $report->id_laporan }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if(isset($assetData['status']) && $assetData['status'] === 'baik')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Baik</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Butuh Perawatan</span>
                                        @endif
                                        <button onclick="showReportModal('{{ $report->id_laporan }}', '{{ $asset->asset_id }}')" 
                                                class="text-blue-600 hover:text-blue-900 p-1" title="Lihat Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-2">
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aset</dt>
                                        <dd class="mt-1 text-sm text-gray-900 font-medium line-clamp-2">{{ $asset->nama_asset }}</dd>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $asset->kategori ?? '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $asset->lokasi ?? $report->kode_ruangan }}</dd>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $report->tanggal_laporan->format('d-m-Y') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ $report->nama_pelapor }}</dd>
                                        </div>
                                    </div>

                                    @if(isset($assetData['deskripsi']) && $assetData['deskripsi'] && $assetData['deskripsi'] !== '-')
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $assetData['deskripsi'] }}</dd>
                                    </div>
                                    @endif

                                    @if(isset($assetData['foto_path']) && $assetData['foto_path'])
                                    <div>
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Dokumentasi</dt>
                                        <dd class="mt-1">
                                            <img src="{{ $assetData['foto_path'] }}" alt="Monitoring Photo" class="h-16 w-16 rounded-lg object-cover cursor-pointer border border-gray-200" onclick="showImageModal('{{ $assetData['foto_path'] }}')">
                                        </dd>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        @endforeach
                    @endif
                @empty
                <div class="p-8 text-center">
                    <div class="flex flex-col items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900">Tidak Ada Data Monitoring</h3>
                        <p class="text-gray-500 text-center">Belum ada laporan monitoring yang tersedia.</p>
                    </div>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($monitoringReports->hasPages())
            <div class="px-3 sm:px-4 lg:px-6 py-3 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $monitoringReports->firstItem() ?? 0 }} sampai {{ $monitoringReports->lastItem() ?? 0 }} 
                        dari {{ $monitoringReports->total() }} hasil
                    </div>
                    <div class="hidden sm:block">
                        {{ $monitoringReports->appends(request()->query())->links() }}
                    </div>
                    <div class="sm:hidden">
                        {{ $monitoringReports->appends(request()->query())->simplePaginate() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    @endif
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 sm:top-20 mx-auto p-4 sm:p-5 border w-11/12 sm:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="mt-3 text-center">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Dokumentasi Monitoring</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <img id="modalImage" src="" alt="Monitoring Documentation" class="w-full h-auto rounded max-h-96 object-contain">
        </div>
    </div>
</div>

<!-- Report Detail Modal -->
<div id="reportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-4 sm:top-8 mx-auto p-4 sm:p-6 border w-11/12 lg:w-3/4 xl:w-2/3 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Detail Laporan Monitoring</h3>
            <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="reportContent" class="space-y-6">
            <!-- Content will be loaded here -->
            <div class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        </div>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
@if(auth()->user()->hasRole('wakil_dekan_2'))
// Tab switching functionality for Wakil Dekan 2
function showTabContent(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Update tab styles - only target the main tabs nav
    const mainTabsNav = document.getElementById('main-tabs');
    if (mainTabsNav) {
        mainTabsNav.querySelectorAll('a').forEach(tab => {
            tab.className = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 py-2 px-3 sm:px-4 text-sm font-medium whitespace-nowrap';
        });
    }
    
    // Set active tab
    document.getElementById(tabName + '-tab').className = 'border-b-2 border-green-500 py-2 px-3 sm:px-4 text-sm font-medium text-green-600 whitespace-nowrap';
}

function filterByYear(year) {
    const url = new URL(window.location);
    if (year) {
        url.searchParams.set('year', year);
    } else {
        url.searchParams.delete('year');
    }
    window.location = url;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    showTabContent('logistik'); // Default to logistik tab
});
@else
// Filter function for other roles
function filterByLocation(location) {
    const url = new URL(window.location);
    if (location) {
        url.searchParams.set('lokasi', location);
    } else {
        url.searchParams.delete('lokasi');
    }
    window.location = url;
}
@endif

// Validation Status Filter function
function filterByValidation(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('validation_status', status);
    } else {
        url.searchParams.delete('validation_status');
    }
    window.location = url;
}

// Image Modal functions
function showImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Report Detail Modal functions
function showReportModal(reportId, assetId) {
    document.getElementById('reportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loadReportDetails(reportId, assetId);
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function loadReportDetails(reportId, assetId) {
    const reportContent = document.getElementById('reportContent');
    
    // Find the report and asset data from the current page data
    const reports = @json($monitoringReports ?? []);
    const assets = @json($assets ?? []);
    
    let reportData = null;
    let assetData = null;
    let monitoringData = null;
    
    // Find the specific report
    if (Array.isArray(reports.data)) {
        reportData = reports.data.find(report => report.id_laporan === reportId);
    } else if (Array.isArray(reports)) {
        reportData = reports.find(report => report.id_laporan === reportId);
    }
    
    // Find the specific asset
    assetData = assets.find(asset => asset.asset_id === assetId);
    
    // Find the monitoring data for this specific asset
    if (reportData && reportData.monitoring_data) {
        monitoringData = reportData.monitoring_data.find(data => data.asset_id === assetId);
    }
    
    if (!reportData || !assetData) {
        reportContent.innerHTML = `
            <div class="text-center py-8">
                <div class="text-red-500 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <p class="text-gray-600">Data tidak ditemukan</p>
            </div>
        `;
        return;
    }
    
    const statusBadge = monitoringData && monitoringData.status === 'baik' 
        ? '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Baik</span>'
        : '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Butuh Perawatan</span>';
    
    const photoSection = monitoringData && monitoringData.foto_path 
        ? `
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Dokumentasi</h4>
                <img src="${monitoringData.foto_path.startsWith('http') ? monitoringData.foto_path : '/storage/' + monitoringData.foto_path}" 
                     alt="Dokumentasi Monitoring" 
                     class="w-full max-w-md h-64 object-cover rounded-lg border border-gray-200 cursor-pointer"
                     onclick="showImageModal('${monitoringData.foto_path.startsWith('http') ? monitoringData.foto_path : '/storage/' + monitoringData.foto_path}')">
            </div>
        `
        : `
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Dokumentasi</h4>
                <div class="w-full max-w-md h-64 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                    <div class="text-center">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500 text-sm">Tidak ada foto</p>
                    </div>
                </div>
            </div>
        `;
    
    reportContent.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Report Information -->
            <div class="space-y-4">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Laporan</h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">ID Laporan:</span>
                            <span class="text-sm text-gray-900 font-mono">${reportData.id_laporan}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Tanggal Laporan:</span>
                            <span class="text-sm text-gray-900">${new Date(reportData.tanggal_laporan).toLocaleDateString('id-ID')}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Pelapor:</span>
                            <span class="text-sm text-gray-900">${reportData.nama_pelapor}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Kode Ruangan:</span>
                            <span class="text-sm text-gray-900">${reportData.kode_ruangan}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Status Aset:</span>
                            ${statusBadge}
                        </div>
                    </div>
                </div>
                
                <!-- Asset Information -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Informasi Aset</h4>
                    <div class="bg-blue-50 rounded-lg p-4 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">ID Aset:</span>
                            <span class="text-sm text-gray-900 font-mono">${assetData.asset_id}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Nama Aset:</span>
                            <span class="text-sm text-gray-900 font-semibold">${assetData.nama_asset}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Kategori:</span>
                            <span class="text-sm text-gray-900">${assetData.kategori || '-'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Lokasi:</span>
                            <span class="text-sm text-gray-900">${assetData.lokasi || reportData.kode_ruangan}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Spesifikasi:</span>
                            <span class="text-sm text-gray-900">${assetData.spesifikasi || '-'}</span>
                        </div>
                        ${assetData.nilai_perolehan ? `
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-600">Nilai Perolehan:</span>
                            <span class="text-sm text-gray-900">Rp ${new Intl.NumberFormat('id-ID').format(assetData.nilai_perolehan)}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <!-- Monitoring Details and Photo -->
            <div class="space-y-4">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Detail Monitoring</h4>
                    <div class="bg-yellow-50 rounded-lg p-4 space-y-3">
                        ${monitoringData && monitoringData.deskripsi && monitoringData.deskripsi !== '-' ? `
                        <div>
                            <span class="text-sm font-medium text-gray-600">Deskripsi Kondisi:</span>
                            <p class="text-sm text-gray-900 mt-1 p-3 bg-white rounded border">${monitoringData.deskripsi}</p>
                        </div>
                        ` : `
                        <div>
                            <span class="text-sm font-medium text-gray-600">Deskripsi Kondisi:</span>
                            <p class="text-sm text-gray-500 mt-1 italic">Tidak ada deskripsi khusus</p>
                        </div>
                        `}
                        
                        ${reportData.catatan ? `
                        <div>
                            <span class="text-sm font-medium text-gray-600">Catatan Tambahan:</span>
                            <p class="text-sm text-gray-900 mt-1 p-3 bg-white rounded border">${reportData.catatan}</p>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                ${photoSection}
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
            <button onclick="closeReportModal()" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Tutup
            </button>
            <button onclick="window.print()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Cetak Detail
            </button>
        </div>
    `;
}

// Close modal when clicking outside
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

document.getElementById('reportModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReportModal();
    }
});

// Handle escape key to close modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('imageModal').classList.contains('hidden')) {
            closeImageModal();
        }
        if (!document.getElementById('reportModal').classList.contains('hidden')) {
            closeReportModal();
        }
    }
});

// Reset all filters function
function resetAllFilters() {
    // Reset all select elements
    const locationFilter = document.getElementById('location_filter');
    const yearFilter = document.getElementById('year_filter');
    const validationFilter = document.getElementById('validation_filter');
    
    if (locationFilter) locationFilter.value = '';
    if (yearFilter) yearFilter.value = '';
    if (validationFilter) validationFilter.value = '';
    
    // Clear all URL parameters and reload
    const url = new URL(window.location);
    url.search = '';
    window.location = url;
}
</script>

@endsection