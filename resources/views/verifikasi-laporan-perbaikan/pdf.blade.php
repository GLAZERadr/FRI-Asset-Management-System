<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pemantauan Aset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .header h2 {
            margin: 3px 0 0 0;
            font-size: 12px;
            font-weight: normal;
            color: #666;
        }
        
        .filter-info {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .filter-info h3 {
            margin: 0 0 8px 0;
            font-size: 11px;
            color: #333;
            font-weight: bold;
        }
        
        .filter-details {
            display: table;
            width: 100%;
        }
        
        .filter-row {
            display: table-row;
        }
        
        .filter-label {
            display: table-cell;
            width: 25%;
            padding: 2px 5px 2px 0;
            font-weight: bold;
        }
        
        .filter-value {
            display: table-cell;
            padding: 2px 0;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }
        
        .data-table th {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }
        
        .data-table td {
            border: 1px solid #ddd;
            padding: 5px 4px;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
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
        
        .verification-verified {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .verification-rejected {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .verification-pending {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .summary-stats {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .stat-box {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            margin-right: 5px;
        }
        
        .stat-number {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-label {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-small {
            font-size: 8px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>FAKULTAS REKAYASA INDUSTRI</h1>
        <h2>TELKOM UNIVERSITY</h2>
        <h2>LAPORAN PEMANTAUAN ASET RUSAK</h2>
    </div>
    
    <!-- Filter Information -->
    <div class="filter-info">
        <h3>Informasi Filter & Statistik</h3>
        <div class="filter-details">
            <div class="filter-row">
                <div class="filter-label">Lokasi:</div>
                <div class="filter-value">{{ $filterInfo['lokasi'] }}</div>
            </div>
            <div class="filter-row">
                <div class="filter-label">Total Data:</div>
                <div class="filter-value">{{ $filterInfo['total'] }} item</div>
            </div>
            <div class="filter-row">
                <div class="filter-label">Tanggal Generate:</div>
                <div class="filter-value">{{ $filterInfo['generated_at'] }} WIB</div>
            </div>
        </div>
    </div>
    
    <!-- Summary Statistics -->
    <div class="summary-stats">
        @php
            $totalVerified = $damagedAssets->where('verified', 'Yes')->count();
            $totalRejected = $damagedAssets->where('verified', 'No')->count();
            $totalPending = $damagedAssets->whereNull('verified')->count();
            $totalRingan = $damagedAssets->where('tingkat_kerusakan', 'Ringan')->count();
            $totalSedang = $damagedAssets->where('tingkat_kerusakan', 'Sedang')->count();
            $totalBerat = $damagedAssets->where('tingkat_kerusakan', 'Berat')->count();
        @endphp
        
        <div class="stat-box">
            <div class="stat-number">{{ $totalVerified }}</div>
            <div class="stat-label">Terverifikasi</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $totalRejected }}</div>
            <div class="stat-label">Ditolak</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $totalPending }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-box">
            <div class="stat-number">{{ $totalBerat }}</div>
            <div class="stat-label">Kerusakan Berat</div>
        </div>
    </div>
    
    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">ID Verifikasi</th>
                <th style="width: 15%;">Nama Aset</th>
                <th style="width: 12%;">Lokasi</th>
                <th style="width: 10%;">Tingkat Kerusakan</th>
                <th style="width: 10%;">Pelapor</th>
                <th style="width: 10%;">Tgl Laporan</th>
                <th style="width: 10%;">Reviewer</th>
                <th style="width: 8%;">Tgl Verifikasi</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($damagedAssets as $index => $asset)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $asset->damage_id ?? 'VER-' . str_pad($asset->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $asset->asset->nama_asset ?? '-' }}</td>
                <td>{{ $asset->asset->lokasi ?? '-' }}</td>
                <td class="text-center">
                    @php
                        $statusClass = [
                            'Ringan' => 'status-ringan',
                            'Sedang' => 'status-sedang',
                            'Berat' => 'status-berat'
                        ][$asset->tingkat_kerusakan] ?? '';
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $asset->tingkat_kerusakan ?? '-' }}</span>
                </td>
                <td>{{ $asset->reporter_role ?? '-' }}</td>
                <td class="text-center">{{ $asset->tanggal_pelaporan ? \Carbon\Carbon::parse($asset->tanggal_pelaporan)->format('d-m-Y') : '-' }}</td>
                <td>{{ $asset->reviewer ?? 'Admin System' }}</td>
                <td class="text-center">{{ $asset->verified_at ? \Carbon\Carbon::parse($asset->verified_at)->format('d-m-Y') : '-' }}</td>
                <td class="text-center">
                    @if($asset->verified == 'Yes')
                        <span class="status-badge verification-verified">✓ Verified</span>
                    @elseif($asset->verified == 'No')
                        <span class="status-badge verification-rejected">✗ Rejected</span>
                    @else
                        <span class="status-badge verification-pending">⏳ Pending</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data yang tersedia</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>Laporan Pemantauan Aset - Fakultas Rekayasa Industri, Telkom University</strong></p>
        <p>Dokumen ini dibuat secara otomatis oleh Sistem Manajemen Aset</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</p>
        @if($filterInfo['total'] > 0)
        <p>Menampilkan {{ $filterInfo['total'] }} dari total data pemantauan aset</p>
        @endif
    </div>
</body>
</html>