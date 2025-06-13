<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring - {{ date('Y-m-d') }}</title>
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
        
        /* Specific column widths for monitoring data */
        .col-no { width: 3%; }
        .col-id { width: 12%; }
        .col-ruangan { width: 12%; }
        .col-kode { width: 10%; }
        .col-jumlah { width: 8%; }
        .col-periode { width: 10%; }
        .col-status { width: 10%; }
        .col-aksi { width: 35%; }
        
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
        <h1>Laporan Monitoring</h1>
        <p>Fakultas Rekayasa Industri - Telkom University</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</p>
        @if(request('lokasi'))
            <p>Lokasi: {{ request('lokasi') }}</p>
        @endif
        @if(request('year'))
            <p>Tahun: {{ request('year') }}</p>
        @endif
    </div>

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
                        @if($report->validated == 'valid')
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
                                            $asset = $assets->firstWhere('asset_id', $assetData['asset_id']);
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
</body>
</html>