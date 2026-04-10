<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris Aset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #003366;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header td { border: none; }
        .logo-ap {
            font-size: 24px;
            font-weight: bold;
            color: #003366;
        }
        .title-area { text-align: center; }
        h2 { margin: 0; padding: 0; font-size: 16px; text-transform: uppercase; color: #003366; }
        p.subtitle { margin: 2px 0; font-size: 11px; color: #555; }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #003366;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .text-center { text-align: center; }

        /* Status Colors Vanilla CSS */
        .status-badge { font-weight: bold; }
        .status-active { color: #166534; }
        .status-broken { color: #991b1b; }
        .status-maintenance { color: #854d0e; }
        .status-retired { color: #475569; }

        .footer {
            margin-top: 40px;
            width: 100%;
        }
        .ttd-box {
            float: right;
            width: 35%;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- KOP Laporan -->
    <table class="header">
        <tr>
            <td width="25%">
               <div class="logo-ap">ANGKASA PURA</div>
            </td>
            <td width="75%" class="title-area">
                <h2>Laporan Inventaris Aset</h2>
                <p class="subtitle">Sistem Informasi Manajemen Aset M/E Airport</p>
                <p class="subtitle">Dicetak pada: {{ now()->format('d F Y') }}</p>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Utama -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Nama Aset</th>
                <th width="15%">Serial Number / Kode Aset</th>
                <th width="20%">Lokasi</th>
                <th width="15%">Kategori</th>
                <th width="10%">Tanggal Pembelian</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $index => $asset)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $asset->name }}</strong></td>
                    <td class="text-center">{{ $asset->serial_number ?? substr($asset->uuid, 0, 8) }}</td>
                    <td>{{ $asset->location ? $asset->location->name : '-' }}</td>
                    <td class="text-center">{{ $asset->category ? $asset->category->name : '-' }}</td>
                    <td class="text-center">{{ $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        @php
                            $statusClass = 'status-retired';
                            if($asset->status == 'active') $statusClass = 'status-active';
                            if($asset->status == 'broken') $statusClass = 'status-broken';
                            if($asset->status == 'maintenance') $statusClass = 'status-maintenance';
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ strtoupper(str_replace('_', ' ', $asset->status)) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">
                        Tidak ada data inventaris aset yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Area Tanda Tangan Footer -->
    <div class="footer">
        <div class="ttd-box">
            <p>Disahkan Oleh,</p>
            <p style="margin-top: 60px;"><strong>.......................................</strong><br>Manajer Inventaris Aset</p>
        </div>
    </div>
</body>
</html>
