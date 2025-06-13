<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ isset($validationData) ? 'Laporan Validasi' : 'Laporan Monitoring' }} - {{ date('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
        }
        
        .info-section {
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            vertical-align: top;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .no-wrap {
            white-space: nowrap;
        }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            font-weight: bold;
        }
        
        .row-odd {
            background-color: #f9f9f9;
        }
        
        .row-even {
            background-color: #ffffff;
        }
        
        .status-baik {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-rusak {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }
        
        /* Column widths for monitoring data */
        .col-no { width: 3%; }
        .col-id { width: 12%; }
        .col-ruangan { width: 12%; }
        .col-kode { width: 10%; }
        .col-jumlah { width: 8%; }
        .col-periode { width: 10%; }
        .col-status { width: 10%; }
        .col-aksi { width: 35%; }
        
        /* Column widths for validation data */
        .col-reviewer { width: 12%; }
        .col-aksi-validasi { width: 15%; }
        
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ isset($validationData) ? 'Laporan Validasi' : 'Laporan Monitoring' }}</h1>
        <p>Fakultas Rekayasa Industri - Telkom University</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</p>
        @if(request('lokasi'))
            <p>Lokasi: {{ request('lokasi') }}</p>
        @endif
        @if(request('year'))
            <p>Tahun: {{ request('year') }}</p>
        @endif
    </div>

    @if(isset($validationData))
        {{-- Validation Data Table --}}
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-id">ID Laporan</th>
                    <th class="col-ruangan">Nama Ruangan</th>
                    <th class="col-kode">Kode Ruangan</th>
                    <th class="col-jumlah">Jumlah Unit Aset</th>
                    <th class="col-periode">Periode Monitoring</th>
                    <th class="col-reviewer">Reviewer</th>
                    <th class="col-aksi-validasi">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $processedReports = collect($validationData)->groupBy('id_laporan');
                @endphp
                
                @forelse ($processedReports as $reportId => $reportGroup)
                    @php
                        $firstReport = $reportGroup->first();
                    @endphp
                    <tr class="{{ $loop->index % 2 == 0 ? 'row-even' : 'row-odd' }}">
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="no-wrap">{{ $firstReport['id_laporan'] }}</td>
                        <td>{{ $firstReport['nama_ruangan'] ?? '-' }}</td>
                        <td class="text-center">{{ $firstReport['kode_ruangan'] }}</td>
                        <td class="text-center">{{ $firstReport['jumlah_unit_aset'] }}</td>
                        <td class="text-center">{{ $firstReport['periode_monitoring'] }}</td>
                        <td>{{ $firstReport['reviewer'] ?? '-' }}</td>
                        <td class="text-center">
                            <span class="status-rusak">Perlu Validasi</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data validasi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>Total Laporan Perlu Validasi: {{ count($processedReports) }} laporan</p>
            @php
                $totalAssets = 0;
                if(!empty($validationData)) {
                    foreach($validationData as $data) {
                        // Extract number from string like "5 unit"
                        $unitCount = (int) filter_var($data['jumlah_unit_aset'], FILTER_SANITIZE_NUMBER_INT);
                        $totalAssets += $unitCount;
                    }
                }
            @endphp
            <p>Total Aset Perlu Divalidasi: {{ $totalAssets }} unit</p>
        </div>

    @elseif(isset($monitoringReports))
        {{-- Regular Monitoring Data Table --}}
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-id">ID Laporan</th>
                    <th class="col-ruangan">Nama Ruangan</th>
                    <th class="col-kode">Kode Ruangan</th>
                    <th class="col-jumlah">Jumlah Unit Aset</th>
                    <th class="col-periode">Periode Monitoring</th>
                    <th class="col-status">Status Validasi</th>
                    <th class="col-aksi">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monitoringReports as $index => $report)
                    <tr class="{{ $index % 2 == 0 ? 'row-even' : 'row-odd' }}">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="no-wrap">{{ $report->id_laporan }}</td>
                        <td>{{ $report->nama_ruangan ?? '-' }}</td>
                        <td class="text-center">{{ $report->kode_ruangan }}</td>
                        <td class="text-center">
                            @if($report->monitoring_data)
                                {{ count($report->monitoring_data) }} unit
                            @else
                                0 unit
                            @endif
                        </td>
                        <td class="text-center">{{ $report->tanggal_laporan->format('M-Y') }}</td>
                        <td class="text-center">
                            @if($report->status_validasi == 'validated')
                                <span class="status-baik">✓</span>
                            @else
                                <span class="status-rusak">-</span>
                            @endif
                        </td>
                        <td>
                            @if($report->monitoring_data)
                                @php
                                    $statusCounts = ['baik' => 0, 'rusak' => 0];
                                    foreach($report->monitoring_data as $assetData) {
                                        if(isset($assetData['status'])) {
                                            if($assetData['status'] === 'baik') {
                                                $statusCounts['baik']++;
                                            } else {
                                                $statusCounts['rusak']++;
                                            }
                                        }
                                    }
                                @endphp
                                Aset Baik: {{ $statusCounts['baik'] }} | Perlu Perawatan: {{ $statusCounts['rusak'] }}
                                @if($statusCounts['rusak'] > 0)
                                    <br><small>Detail: 
                                    @foreach($report->monitoring_data as $assetData)
                                        @if(isset($assetData['status']) && $assetData['status'] !== 'baik')
                                            @php
                                                $asset = isset($assets) ? $assets->firstWhere('asset_id', $assetData['asset_id']) : null;
                                            @endphp
                                            {{ $asset ? $asset->nama_asset : $assetData['asset_id'] }}{{ !$loop->last ? ', ' : '' }}
                                        @endif
                                    @endforeach
                                    </small>
                                @endif
                            @else
                                Belum ada data monitoring
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data monitoring</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>Total Laporan: {{ count($monitoringReports) }} laporan</p>
            @php
                $totalAssets = 0;
                $totalBaik = 0;
                $totalRusak = 0;
                
                foreach($monitoringReports as $report) {
                    if($report->monitoring_data) {
                        $totalAssets += count($report->monitoring_data);
                        foreach($report->monitoring_data as $assetData) {
                            if(isset($assetData['status'])) {
                                if($assetData['status'] === 'baik') {
                                    $totalBaik++;
                                } else {
                                    $totalRusak++;
                                }
                            }
                        }
                    }
                }
            @endphp
            <p>Total Aset Dimonitor: {{ $totalAssets }} unit</p>
            <p>Kondisi Baik: {{ $totalBaik }} | Perlu Perawatan: {{ $totalRusak }}</p>
        </div>
    @else
        {{-- Fallback when no data is provided --}}
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-id">ID Laporan</th>
                    <th class="col-ruangan">Nama Ruangan</th>
                    <th class="col-kode">Kode Ruangan</th>
                    <th class="col-jumlah">Jumlah Unit Aset</th>
                    <th class="col-periode">Periode Monitoring</th>
                    <th class="col-status">Status</th>
                    <th class="col-aksi">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data tersedia</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Total Data: 0</p>
        </div>
    @endif
</body>
</html>