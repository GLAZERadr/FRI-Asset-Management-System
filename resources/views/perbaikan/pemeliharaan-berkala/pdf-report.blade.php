<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemeliharaan - {{ 'LP-' . date('Ymd', strtotime($schedule->tanggal_pemeliharaan)) . '-' . str_pad($schedule->id, 3, '0', STR_PAD_LEFT) }}</title>
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
        
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-scheduled {
            background-color: #fef3c7;
            color: #d97706;
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
        
        .photos-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .photos-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        
        .photo-row {
            display: table-row;
        }
        
        .photo-cell {
            display: table-cell;
            width: 50%;
            padding: 5px;
            text-align: center;
            vertical-align: top;
        }
        
        .photo-placeholder {
            width: 100%;
            height: 150px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9;
            font-size: 10px;
            color: #666;
            overflow: hidden;
        }
        
        .photo-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border: 1px solid #ddd;
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
                <div class="info-label">ID Laporan:</div>
                <div class="info-value">LP-{{ date('Ymd', strtotime($schedule->tanggal_pemeliharaan)) }}-{{ str_pad($schedule->id, 3, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tanggal Laporan:</div>
                <div class="info-value">{{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    @if($schedule->status == 'completed')
                        <span class="status-badge status-completed">Selesai</span>
                    @else
                        <span class="status-badge status-scheduled">Terjadwal</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Maintenance Information -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Lokasi:</div>
            <div class="info-value">{{ $schedule->lokasi }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Pemeliharaan:</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($schedule->tanggal_pemeliharaan)->format('d F Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Dibuat Oleh:</div>
            <div class="info-value">{{ $schedule->created_by ?? 'System' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Dibuat:</div>
            <div class="info-value">{{ $schedule->created_at ? \Carbon\Carbon::parse($schedule->created_at)->format('d F Y, H:i') : '-' }}</div>
        </div>
        @if($schedule->status == 'completed' && $schedule->updated_at)
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
    
    <!-- Summary Statistics -->
    <div class="summary-stats">
        <h4>Ringkasan Pemeliharaan</h4>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-label">Jumlah Dokumentasi:</div>
                <div class="stats-value">{{ $schedule->photos ? count($schedule->photos) : 0 }} foto</div>
            </div>
            <div class="stats-row">
                <div class="stats-label">Status Pemeliharaan:</div>
                <div class="stats-value">{{ $schedule->status == 'completed' ? 'Selesai' : 'Terjadwal' }}</div>
            </div>
            <div class="stats-row">
                <div class="stats-label">Lokasi Pemeliharaan:</div>
                <div class="stats-value">{{ $schedule->lokasi }}</div>
            </div>
        </div>
    </div>
    
    <!-- Documentation Section -->
    <div class="photos-section">
        <h4>Dokumentasi Pemeliharaan</h4>
        @if($schedule->photos && count($schedule->photos) > 0)
            <p style="font-size: 10px; color: #666; margin-bottom: 10px;">
                Total {{ count($schedule->photos) }} foto dokumentasi tersimpan dalam sistem
            </p>
            
            <div class="photos-grid">
                @foreach($schedule->photos as $index => $photo)
                    @if($index % 2 == 0)
                    <div class="photo-row">
                    @endif
                    
                    <div class="photo-cell">
                        @php
                            $imagePath = storage_path($photo);
                            $imageExists = file_exists($imagePath);
                            $base64Image = null;
                            
                            if ($imageExists) {
                                $imageData = file_get_contents($imagePath);
                                $imageInfo = getimagesize($imagePath);
                                $mimeType = $imageInfo['mime'] ?? 'image/jpeg';
                                $base64Image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                            }
                        @endphp
                        
                        @if($base64Image)
                            <img src="{{ $base64Image }}" alt="Foto {{ $index + 1 }}" class="photo-image">
                            <div style="text-align: center; font-size: 8px; color: #666; margin-top: 2px;">
                                Foto {{ $index + 1 }}
                            </div>
                        @else
                            <div class="photo-placeholder">
                                Foto {{ $index + 1 }}<br>
                                <small>{{ basename($photo) }}</small><br>
                                <small style="color: #e74c3c;">File tidak ditemukan</small>
                            </div>
                        @endif
                    </div>
                    
                    @if($index % 2 == 1 || $index == count($schedule->photos) - 1)
                        @if($index == count($schedule->photos) - 1 && $index % 2 == 0)
                            <div class="photo-cell"></div>
                        @endif
                    </div>
                    @endif
                @endforeach
            </div>
        @else
            <p style="font-style: italic; color: #666;">Tidak ada dokumentasi foto tersedia untuk pemeliharaan ini.</p>
        @endif
    </div>
    
    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Penanggung Jawab Pemeliharaan:</div>
            <div class="signature-line">
                <strong>{{ $schedule->created_by ?? 'Staff Pemeliharaan' }}</strong><br>
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