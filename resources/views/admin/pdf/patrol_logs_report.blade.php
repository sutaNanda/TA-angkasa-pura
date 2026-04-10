<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Patroli</title>
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
        p.subtitle { margin: 3px 0; font-size: 12px; color: #444; font-weight: bold; }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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

        /* Murni Vanilla styling untuk status */
        .status-badge { font-weight: bold; }
        .status-normal { color: #166534; }
        .status-issue { color: #991b1b; }

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
                <h2>Laporan Logbook Riwayat Patroli</h2>
                <p class="subtitle">
                    Periode: 
                    @if($request->start_date && $request->end_date)
                        {{ \Carbon\Carbon::parse($request->start_date)->format('d F Y') }} - {{ \Carbon\Carbon::parse($request->end_date)->format('d F Y') }}
                    @else
                        Keseluruhan Waktu (Sampai {{ now()->format('d F Y') }})
                    @endif
                </p>
                <div style="font-size: 10px; margin-top:2px;">
                    Filter Status: {{ strtoupper(str_replace('_', ' ', $request->status && $request->status != 'all' ? $request->status : 'SEMUA STATUS')) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Utama -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">Tgl & Waktu</th>
                <th width="14%">Teknisi</th>
                <th width="20%">Lokasi / Aset</th>
                <th width="16%">Jenis Inspeksi</th>
                <th width="10%">Status</th>
                <th width="24%">Catatan / Temuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $log->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $log->technician ? $log->technician->name : '-' }}</td>
                    <td>
                        @if($log->asset)
                            <strong>{{ $log->asset->name }}</strong><br>
                        @endif
                        <span style="font-size:10px; color:#555;">{{ $log->location ? $log->location->name : '-' }}</span>
                    </td>
                    <td>{{ $log->checklistTemplate ? $log->checklistTemplate->name : 'Inspeksi Reguler' }}</td>
                    <td class="text-center">
                        @if($log->status == 'normal')
                            <span class="status-badge status-normal">NORMAL</span>
                        @else
                            <span class="status-badge status-issue">ISSUE</span>
                        @endif
                    </td>
                    <td>{{ $log->notes ?: '-' }}</td>
                </tr>
            @endforeach

            @if(count($logs) == 0)
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">
                        Tidak ada catatan patroli pada periode pencarian ini.
                    </td>
                </tr>
            @endif
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
