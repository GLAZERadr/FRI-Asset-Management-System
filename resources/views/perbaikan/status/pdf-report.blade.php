<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perbaikan Aset - {{ $maintenanceAsset->maintenance_id }}</title>
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
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #7f8c8d;
            font-weight: normal;
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
            width: 25%;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }
        
        .info-value {
            display: table-cell;
            width: 25%;
            padding: 5px 15px 5px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #bdc3c7;
        }
        
        .description-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
            white-space: pre-wrap;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            color: white;
        }
        
        .status-selesai { background-color: #27ae60; }
        .status-sukses { background-color: #27ae60; }
        .status-perlu-tindak-lanjut { background-color: #f39c12; }
        .status-ringan { background-color: #3498db; }
        .status-sedang { background-color: #f39c12; }
        .status-berat { background-color: #e74c3c; }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 15px;
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
            font-weight: bold;
        }
        
        .two-column {
            display: table;
            width: 100%;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
            vertical-align: top;
        }
        
        .column:last-child {
            padding-right: 0;
            padding-left: 15px;
        }
        
        @media print {
            body { margin: 0; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN PERBAIKAN ASET</h1>
        <h2>Fakultas Rekayasa Industri - Universitas Telkom</h2>
    </div>

    <!-- Basic Information Section -->
    <div class="section">
        <div class="section-title">INFORMASI UMUM</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">ID Maintenance:</div>
                <div class="info-value">{{ $maintenanceAsset->maintenance_id }}</div>
                <div class="info-label">ID Damage:</div>
                <div class="info-value">{{ $maintenanceAsset->damage_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">ID Aset:</div>
                <div class="info-value">{{ $maintenanceAsset->damagedAsset->asset_id ?? '-' }}</div>
                <div class="info-label">Nama Aset:</div>
                <div class="info-value">{{ $maintenanceAsset->asset->nama_asset ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Kategori:</div>
                <div class="info-value">{{ $maintenanceAsset->asset->kategori ?? '-' }}</div>
                <div class="info-label">Lokasi:</div>
                <div class="info-value">{{ $maintenanceAsset->asset->lokasi ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Kode Ruangan:</div>
                <div class="info-value">{{ $maintenanceAsset->asset->kode_ruangan ?? '-' }}</div>
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="status-badge status-selesai">{{ $maintenanceAsset->status }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Damage Information -->
    <div class="section">
        <div class="section-title">INFORMASI KERUSAKAN</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Tingkat Kerusakan:</div>
                <div class="info-value">
                    @if($maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->tingkat_kerusakan)
                        <span class="status-badge status-{{ strtolower($maintenanceAsset->damagedAsset->tingkat_kerusakan) }}">
                            {{ $maintenanceAsset->damagedAsset->tingkat_kerusakan }}
                        </span>
                    @else
                        -
                    @endif
                </div>
                <div class="info-label">Pelapor:</div>
                <div class="info-value">{{ $maintenanceAsset->damagedAsset->reporter_role ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Pelaporan:</div>
                <div class="info-value">
                    {{ $maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->tanggal_pelaporan ? $maintenanceAsset->damagedAsset->tanggal_pelaporan->format('d/m/Y') : '-' }}
                </div>
                <div class="info-label">Estimasi Biaya:</div>
                <div class="info-value">
                    {{ $maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->estimasi_biaya ? 'Rp ' . number_format($maintenanceAsset->damagedAsset->estimasi_biaya, 0, ',', '.') : '-' }}
                </div>
            </div>
        </div>
        
        @if($maintenanceAsset->damagedAsset && $maintenanceAsset->damagedAsset->deskripsi_kerusakan)
        <div style="margin-top: 15px;">
            <strong>Deskripsi Kerusakan:</strong>
            <div class="description-box">{{ $maintenanceAsset->damagedAsset->deskripsi_kerusakan }}</div>
        </div>
        @endif
    </div>

    <!-- Maintenance Information -->
    <div class="section">
        <div class="section-title">INFORMASI PERBAIKAN</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Tanggal Pengajuan:</div>
                <div class="info-value">{{ $maintenanceAsset->tanggal_pengajuan ? $maintenanceAsset->tanggal_pengajuan->format('d/m/Y') : '-' }}</div>
                <div class="info-label">Tanggal Perbaikan:</div>
                <div class="info-value">{{ $maintenanceAsset->tanggal_perbaikan ? $maintenanceAsset->tanggal_perbaikan->format('d/m/Y') : '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Selesai:</div>
                <div class="info-value">{{ $maintenanceAsset->tanggal_selesai ? $maintenanceAsset->tanggal_selesai->format('d/m/Y') : '-' }}</div>
                <div class="info-label">Teknisi:</div>
                <div class="info-value">{{ $maintenanceAsset->teknisi ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Hasil Perbaikan:</div>
                <div class="info-value">
                    @if($maintenanceAsset->hasil_perbaikan)
                        <span class="status-badge status-{{ $maintenanceAsset->hasil_perbaikan === 'Sukses' ? 'sukses' : 'perlu-tindak-lanjut' }}">
                            {{ $maintenanceAsset->hasil_perbaikan }}
                        </span>
                    @else
                        -
                    @endif
                </div>
                <div class="info-label">Jumlah Perbaikan:</div>
                <div class="info-value">{{ $maintenanceCount }} kali</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Biaya Perbaikan:</div>
                <div class="info-value">{{ $totalCost ? 'Rp ' . number_format($totalCost, 0, ',', '.') : 'Rp 0' }}</div>
                <div class="info-label"></div>
                <div class="info-value"></div>
            </div>
        </div>
    </div>

    <!-- Technical Details -->
    <div class="section">
        <div class="section-title">DETAIL TEKNIS</div>
        
        @if($maintenanceAsset->penyebab_kerusakan)
        <div style="margin-bottom: 15px;">
            <strong>Penyebab Kerusakan:</strong>
            <div class="description-box">{{ $maintenanceAsset->penyebab_kerusakan }}</div>
        </div>
        @endif
        
        @if($maintenanceAsset->deskripsi_perbaikan)
        <div style="margin-bottom: 15px;">
            <strong>Deskripsi Perbaikan:</strong>
            <div class="description-box">{{ $maintenanceAsset->deskripsi_perbaikan }}</div>
        </div>
        @endif
        
        @if($maintenanceAsset->rekomendasi)
        <div style="margin-bottom: 15px;">
            <strong>Rekomendasi:</strong>
            <div class="description-box">{{ $maintenanceAsset->rekomendasi }}</div>
        </div>
        @endif

        @if($maintenanceAsset->catatan)
        <div style="margin-bottom: 15px;">
            <strong>Catatan:</strong>
            <div class="description-box">{{ $maintenanceAsset->catatan }}</div>
        </div>
        @endif
    </div>

    <!-- Asset Specifications -->
    @if($maintenanceAsset->asset && $maintenanceAsset->asset->spesifikasi)
    <div class="section">
        <div class="section-title">SPESIFIKASI ASET</div>
        <div class="description-box">{{ $maintenanceAsset->asset->spesifikasi }}</div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis pada {{ now()->format('d F Y H:i:s') }}</p>
        <p>Fakultas Rekayasa Industri - Universitas Telkom</p>
    </div>
</body>
</html>