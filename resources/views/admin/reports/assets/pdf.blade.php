<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris Aset</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #2b2b2b;
        }
        .header {
            width: 100%;
            border-bottom: 3px solid #1a4f8d;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header td { border: none; }
        .logo-ap {
            font-size: 26px;
            font-weight: 900;
            color: #1a4f8d;
            letter-spacing: 1px;
        }
        .title-area { text-align: center; }
        h2 { margin: 0 0 5px 0; padding: 0; font-size: 18px; text-transform: uppercase; color: #111;}
        p.subtitle { margin: 2px 0; font-size: 11px; color: #555; }
        
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        table.summary-table { width: 100%; }
        table.summary-table td { font-size: 11px; border: none; padding: 3px; }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 7px;
            text-align: left;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #1a4f8d;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        table.data-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-center { text-align: center; }
        
        /* Status Colors */
        .status-badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-broken { background-color: #fee2e2; color: #991b1b; }
        .status-maintenance { background-color: #fef9c3; color: #854d0e; }
        .status-retired { background-color: #e2e8f0; color: #475569; }

        .footer {
            margin-top: 40px;
            width: 100%;
        }
        .page-break { page-break-after: always; }
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
                <h2>Laporan Inventaris Aset M/E Airport</h2>
                <p class="subtitle">Sistem Informasi Manajemen Aset & Monitoring Riwayat Pemeliharaan</p>
                <p class="subtitle">Dicetak pada: {{ now()->format('d F Y, H:i') }} | Rev. 1.0</p>
            </td>
        </tr>
    </table>

    <!-- Parameter Filter Info -->
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td width="15%"><strong>Filter Kategori</strong></td>
                <td width="35%">: {{ $request->category_id && $request->category_id != 'all' ? \App\Models\Category::find($request->category_id)->name ?? '-' : 'Semua Kategori' }}</td>
                <td width="15%"><strong>Total Aset</strong></td>
                <td width="35%">: {{ count($assets) }} Unit</td>
            </tr>
            <tr>
                <td><strong>Filter Lokasi</strong></td>
                <td>: {{ $request->location_id && $request->location_id != 'all' ? \App\Models\Location::find($request->location_id)->name ?? '-' : 'Semua Lokasi' }}</td>
                <td><strong>Status Kondisi</strong></td>
                <td>: {{ strtoupper($request->status && $request->status != 'all' ? $request->status : 'SEMUA') }}</td>
            </tr>
        </table>
    </div>

    <!-- Tabel Data Utama -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">QR (UUID)</th>
                <th width="20%">Nama Aset</th>
                <th width="13%">No. Seri</th>
                <th width="15%">Kategori</th>
                <th width="16%">Lokasi Gedung</th>
                <th width="10%">Tgl Beli</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $index => $asset)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-size: 10px; color:#555">{{ substr($asset->uuid, 0, 8) }}</td>
                    <td><strong>{{ $asset->name }}</strong></td>
                    <td>{{ $asset->serial_number ?? '-' }}</td>
                    <td>{{ $asset->category ? $asset->category->name : '-' }}</td>
                    <td>{{ $asset->location ? $asset->location->name : '-' }}</td>
                    <td class="text-center">{{ $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        @php
                            $badgeClass = 'status-retired';
                            if($asset->status == 'active') $badgeClass = 'status-active';
                            if($asset->status == 'broken') $badgeClass = 'status-broken';
                            if($asset->status == 'maintenance') $badgeClass = 'status-maintenance';
                        @endphp
                        <span class="status-badge {{ $badgeClass }}">
                            {{ strtoupper($asset->status) }}
                        </span>
                    </td>
                </tr>
            @endforeach

            @if(count($assets) == 0)
                <tr>
                    <td colspan="8" class="text-center" style="padding: 25px;">Tidak ada data aset untuk kriteria filter tersebut.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Area Tanda Tangan -->
    <table class="footer">
        <tr>
            <td width="65%">
                <p style="font-size: 10px; color: #666;">
                    * Dokumen Laporan Inventaris Aset sah dikeluarkan oleh Sistem Informasi Manajemen Aset M/E Airport.<br>
                    * Harap validasi ke sistem jika terdapat perbedaan fisik alat di lapangan.
                </p>
            </td>
            <td width="35%" class="text-center">
                <p>Disahkan Oleh,</p>
                <div style="margin-top: 15px; margin-bottom: 5px;">
                    <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(80)->generate('Dokumen Inventaris Aset Sah AviaTrack. Dicetak: ' . now()->format('d F Y H:i'))) }}" alt="QR Code" />
                </div>
                <p><strong>{{ strtoupper(auth()->check() ? auth()->user()->name : 'MANAJER ASET') }}</strong><br>Manajer Aset / General Affairs</p>
            </td>
        </tr>
    </table>
</body>
</html>
