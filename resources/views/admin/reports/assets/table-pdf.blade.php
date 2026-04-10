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
        
        .group-header {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 11px;
            color: #111;
        }

        .text-center { text-align: center; }

        /* Status Colors Vanilla CSS */
        .status-active { color: #166534; font-weight: bold; }
        .status-broken { color: #991b1b; font-weight: bold; }
        .status-maintenance { color: #854d0e; font-weight: bold; }
        .status-retired { color: #475569; font-weight: bold; }

        .footer {
            margin-top: 40px;
            width: 100%;
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
                <th width="20%">Nama Aset & Seri</th>
                <th width="15%">Kategori</th>
                <th width="20%">Lokasi Gedung / Ruang</th>
                <th width="15%">Tanggal Masuk</th>
                <th width="15%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $index => $asset)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $asset->name }}</strong><br>
                        <span style="font-size:10px; color:#666;">SN: {{ $asset->serial_number ?? '-' }}</span>
                    </td>
                    <td class="text-center">{{ $asset->category ? $asset->category->name : '-' }}</td>
                    <td>{{ $asset->location ? $asset->location->name : '-' }}</td>
                    <td class="text-center">{{ $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        @php
                            $statusClass = 'status-retired';
                            if($asset->status == 'active') $statusClass = 'status-active';
                            if($asset->status == 'broken') $statusClass = 'status-broken';
                            if($asset->status == 'maintenance') $statusClass = 'status-maintenance';
                        @endphp
                        <span class="{{ $statusClass }}">
                            {{ strtoupper(str_replace('_', ' ', $asset->status)) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">
                        Tidak ada data inventaris aset yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Area Tanda Tangan Footer -->
    <table class="footer">
        <tr>
            <td width="65%"></td>
            <td width="35%" class="text-center">
                <p>Disahkan Oleh,</p>
                <p style="margin-top: 60px;"><strong>.......................................</strong><br>Manajer Operasional M/E</p>
            </td>
        </tr>
    </table>
</body>
</html>
