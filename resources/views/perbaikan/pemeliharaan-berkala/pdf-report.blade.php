<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemeliharaan - {{ $schedule->asset_id }}</title>
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
        
        .report-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .report-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
            font-weight: bold;
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
        
        .status-selesai {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-dijadwalkan {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-dibatalkan {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .jenis-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .jenis-rutin {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .jenis-tambahan {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .jenis-khusus {
            background-color: #e9d5ff;
            color: #7c3aed;
        }
        
        .description-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
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
        
        .summary-stats {
            background-color: #e3f2fd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            border: 1px solid #bbdefb;
        }
        
        .summary-stats h4 {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: #1565c0;
            font-weight: bold;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stats-label {
            display: table-cell;
            width: 50%;
            padding: 3px 10px 3px 0;
            font-weight: bold;
            color: #1976d2;
        }
        
        .stats-value {
            display: table-cell;
            padding: 3px 0;
            color: #1976d2;
        }
        
        .auto-badge {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>FAKULTAS REKAYASA INDUSTRI</h1>
        <h2>TELKOM UNIVERSITY</h2>
        <h2>LAPORAN PEMELIHARAAN BERKALA</h2>
    </div>
    
    <!-- Report Info -->
    <div class="report-info">
        <h3>Informasi Laporan</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">ID Aset:</div>
                <div class="info-value">
                    {{ $schedule->asset_id }}
                    @if($schedule->auto_generated)
                        <span class="auto-badge">AUTO GENERATED</span>
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Laporan:</div>
                <div class="info-value">{{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    @if($schedule->status == 'Selesai')
                        <span class="status-badge status-selesai">Selesai</span>
                    @elseif($schedule->status == 'Dibatalkan')
                        <span class="status-badge status-dibatalkan">Dibatalkan</span>
                    @else
                        <span class="status-badge status-dijadwalkan">Dijadwalkan</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Asset Information -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Nama Aset:</div>
            <div class="info-value">{{ $schedule->asset->nama_asset ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Lokasi Aset:</div>
            <div class="info-value">{{ $schedule->asset->kode_ruangan ?? '-' }}</div>
        </div>
        @if($schedule->asset && $schedule->asset->kategori)
        <div class="info-row">
            <div class="info-label">Kategori Aset:</div>
            <div class="info-value">{{ $schedule->asset->kategori }}</div>
        </div>
        @endif
        @if($schedule->asset && $schedule->asset->merk)
        <div class="info-row">
            <div class="info-label">Merk:</div>
            <div class="info-value">{{ $schedule->asset->merk }}</div>
        </div>
        @endif
    </div>
    
    <!-- Maintenance Information -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Tanggal Pemeliharaan:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($schedule->tanggal_pemeliharaan)->format('d F Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Jenis Pemeliharaan:</div>
            <div class="info-value">
                @if($schedule->jenis_pemeliharaan == 'Rutin')
                    <span class="jenis-badge jenis-rutin">Rutin</span>
                @elseif($schedule->jenis_pemeliharaan == 'Tambahan')
                    <span class="jenis-badge jenis-tambahan">Tambahan</span>
                @elseif($schedule->jenis_pemeliharaan == 'Khusus')
                    <span class="jenis-badge jenis-khusus">Khusus</span>
                @else
                    {{ $schedule->jenis_pemeliharaan ?? '-' }}
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Penanggung Jawab:</div>
            <div class="info-value">{{ $schedule->penanggung_jawab ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Dibuat:</div>
            <div class="info-value">{{ $schedule->created_at ? \Carbon\Carbon::parse($schedule->created_at)->format('d F Y, H:i') : '-' }}</div>
        </div>
        @if($schedule->status == 'Selesai' && $schedule->updated_at)
        <div class="info-row">
            <div class="info-label">Tanggal Selesai:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($schedule->updated_at)->format('d F Y, H:i') }}</div>
        </div>
        @endif
    </div>
    
    <!-- Description Section -->
    @if($schedule->deskripsi_pemeliharaan)
    <div class="description-section">
        <h4>Deskripsi Pemeliharaan:</h4>
        <p>{{ $schedule->deskripsi_pemeliharaan }}</p>
    </div>
    @endif
    
    <!-- Notes Section -->
    @if($schedule->catatan_tindak_lanjut)
    <div class="description-section">
        <h4>Catatan/Tindak Lanjut:</h4>
        <p>{{ $schedule->catatan_tindak_lanjut }}</p>
    </div>
    @endif
    
    <!-- Additional Information -->
    @if($schedule->alasan_penjadwalan)
    <div class="description-section">
        <h4>Alasan Penjadwalan:</h4>
        <p>{{ $schedule->alasan_penjadwalan }}</p>
    </div>
    @endif
    
    @if($schedule->catatan_tambahan)
    <div class="description-section">
        <h4>Catatan Tambahan:</h4>
        <p>{{ $schedule->catatan_tambahan }}</p>
    </div>
    @endif
    
    <!-- Summary Statistics -->
    <div class="summary-stats">
        <h4>Ringkasan Pemeliharaan</h4>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-label">ID Aset:</div>
                <div class="stats-value">{{ $schedule->asset_id }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-label">Jenis Pemeliharaan:</div>
                <div class="stats-value">{{ $schedule->jenis_pemeliharaan ?? '-' }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-label">Status Pemeliharaan:</div>
                <div class="stats-value">{{ $schedule->status }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-label">Lokasi Aset:</div>
                <div class="stats-value">{{ $schedule->asset->kode_ruangan ?? '-' }}</div>
            </div>
            @if($schedule->auto_generated)
            <div class="stats-row">
                <div class="stats-label">Dibuat Otomatis:</div>
                <div class="stats-value">Ya (Sistem)</div>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Penanggung Jawab Pemeliharaan:</div>
            <div class="signature-line">
                <strong>{{ $schedule->penanggung_jawab ?? 'Staff Pemeliharaan' }}</strong><br>
                Fakultas Rekayasa Industri
            </div>
        </div>
        <div class="signature-box">
            <div>Tanggal: {{ \Carbon\Carbon::now()->format('d F Y') }}</div>
            <div class="signature-line">
                <strong>Tanda Tangan & Stempel</strong>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>Laporan Pemeliharaan Berkala - Fakultas Rekayasa Industri, Telkom University</strong></p>
        <p>Dokumen ini dibuat secara otomatis oleh Sistem Manajemen Aset</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</p>
    </div>
</body>
</html>