<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Aset - {{ date('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
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
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
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
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
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
        }
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN DATA ASET</h1>
        <p>Fakultas Rekayasa Industri - Telkom University</p>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%">No</th>
                <th style="width: 12%">ID Aset</th>
                <th style="width: 15%">Nama Aset</th>
                <th style="width: 8%">Kategori</th>
                <th style="width: 15%">Spesifikasi</th>
                <th style="width: 10%">Kode Ruangan</th>
                <th style="width: 10%">Tgl Perolehan</th>
                <th style="width: 12%">Nilai Perolehan</th>
                <th style="width: 15%">Sumber Perolehan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $index => $asset)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="no-wrap">{{ $asset->asset_id }}</td>
                <td>{{ $asset->nama_asset }}</td>
                <td class="text-center">{{ $asset->kategori }}</td>
                <td>{{ $asset->spesifikasi }}</td>
                <td class="text-center">{{ $asset->kode_ruangan }}</td>
                <td class="text-center no-wrap">{{ \Carbon\Carbon::parse($asset->tgl_perolehan)->format('d/m/Y') }}</td>
                <td class="text-right">Rp {{ number_format($asset->nilai_perolehan, 0, ',', '.') }}</td>
                <td>{{ $asset->sumber_perolehan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data aset</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Aset: {{ $assets->count() }} item</p>
        <p>Total Nilai: Rp {{ number_format($assets->sum('nilai_perolehan'), 0, ',', '.') }}</p>
    </div>
</body>
</html>