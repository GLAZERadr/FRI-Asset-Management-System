<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Validasi - {{ $report->validation_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: normal;
            color: #666;
        }
        
        .validation-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        
        .validation-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 35%;
            padding: 8px 10px 8px 0;
            font-weight: bold;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }
        
        .info-value {
            display: table-cell;
            padding: 8px 0;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-ringan {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-sedang {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .status-berat {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .description-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        
        .description-section h4 {
            margin: 0 0 10px 0;
            font-size: 12px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>FAKULTAS REKAYASA INDUSTRI</h1>
        <h2>TELKOM UNIVERSITY</h2>
        <h2>LAPORAN VALIDASI KERUSAKAN ASET</h2>
    </div>
    
    <!-- Validation Info -->
    <div class="validation-info">
        <h3>Informasi Validasi</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">ID Validasi:</div>
                <div class="info-value">{{ $report->validation_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Validasi:</div>
                <div class="info-value">{{ $report->validated_at ? \Carbon\Carbon::parse($report->validated_at)->format('d F Y, H:i') : '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status Validasi:</div>
                <div class="info-value">
                    @if($report->validated === 'Yes')
                        <span class="status-badge status-approved">Disetujui</span>
                    @elseif($report->validated === 'Reject')
                        <span class="status-badge status-rejected">Ditolak</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Asset Information -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">ID Aset:</div>
            <div class="info-value">{{ $report->asset->asset_id }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nama Aset:</div>
            <div class="info-value">{{ $report->asset->nama_asset }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Kategori Aset:</div>
            <div class="info-value">{{ $report->asset->kategori ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Lokasi Aset:</div>
            <div class="info-value">{{ $report->asset->lokasi ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Kode Ruangan:</div>
            <div class="info-value">{{ $report->asset->kode_ruangan ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Pelapor:</div>
            <div class="info-value">{{ $report->reporter_name ?? '-' }} ({{ $report->reporter_role ?? '-' }})</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Pelaporan:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($report->tanggal_pelaporan)->format('d F Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status Kerusakan:</div>
            <div class="info-value">
                @php
                    $statusClass = [
                        'Ringan' => 'status-ringan',
                        'Sedang' => 'status-sedang',
                        'Berat' => 'status-berat'
                    ][$report->tingkat_kerusakan] ?? '';
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $report->tingkat_kerusakan }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Estimasi Biaya:</div>
            <div class="info-value">Rp {{ number_format($report->estimasi_biaya, 0, ',', '.') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Estimasi Waktu Perbaikan:</div>
            <div class="info-value">
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
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Vendor:</div>
            <div class="info-value">{{ $report->vendor ?? '-' }}</div>
        </div>
    </div>
    
    <!-- Description Section -->
    <div class="description-section">
        <h4>Deskripsi Kerusakan:</h4>
        <p>{{ $report->deskripsi_kerusakan ?? 'Tidak ada deskripsi.' }}</p>
    </div>
    
    @if($report->alasan_penolakan)
    <!-- Rejection Reason Section -->
    <div class="description-section" style="background-color: #fef2f2; border-color: #fecaca;">
        <h4>Alasan Penolakan:</h4>
        <p>{{ $report->alasan_penolakan }}</p>
    </div>
    @endif
    
    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Divalidasi Oleh:</div>
            <div class="signature-line">
                <strong>Kepala Unit/Kaur</strong><br>
                Fakultas Rekayasa Industri
            </div>
        </div>
        <div class="signature-box">
            <div>Tanggal: {{ \Carbon\Carbon::parse($report->validated_at)->format('d F Y') }}</div>
            <div class="signature-line">
                <strong>Tanda Tangan & Stempel</strong>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh Sistem Manajemen Aset FRI - Telkom University</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</p>
    </div>
</body>
</html>