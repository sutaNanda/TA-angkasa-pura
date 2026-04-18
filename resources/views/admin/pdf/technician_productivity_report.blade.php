<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Produktivitas Teknisi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #003366;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #003366;
            font-size: 18px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }

        .meta-info {
            margin-bottom: 20px;
            width: 100%;
        }

        .meta-info td {
            font-size: 12px;
            padding: 3px 0;
        }

        /* --- STYLING TABEL DOMPDF --- */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }

        table.data-table th {
            background-color: #003366;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            text-align: center;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.data-table td {
            font-size: 11px;
            vertical-align: middle;
        }

        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }

        .footer-ttd {
            width: 100%;
            margin-top: 40px;
        }

        .footer-ttd td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .footer-ttd .signature-space {
            height: 80px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN PRODUKTIVITAS DAN BEBAN KERJA TEKNISI</h1>
        <p>PT Angkasa Pura Indonesia - AviaTrack</p>
    </div>

    <table class="meta-info">
        @if(isset($startDate) && isset($endDate))
        <tr>
            <td width="15%"><strong>Periode Laporan</strong></td>
            <td width="3%">:</td>
            <td>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</td>
        </tr>
        @else
        <tr>
            <td width="15%"><strong>Periode Laporan</strong></td>
            <td width="3%">:</td>
            <td>Semua Waktu</td>
        </tr>
        @endif
        <tr>
            <td width="15%"><strong>Dicetak Pada</strong></td>
            <td width="3%">:</td>
            <td>{{ now()->format('d F Y H:i') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama Teknisi</th>
                <th width="25%">Email</th>
                <th width="15%">Sif</th>
                <th width="15%">Total Tiket Diselesaikan</th>
                <th width="15%">Total Inspeksi (Patroli)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td class="text-center">{{ optional($user->shift)->name ?? 'Tidak Ada' }}</td>
                    <td class="text-center"><strong>{{ $user->completed_work_orders_count }}</strong></td>
                    <td class="text-center"><strong>{{ $user->total_patrols_count }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px;">Belum ada data teknisi yang tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="footer-ttd">
        <tr>
            <td>
                <!-- Ruang kosong kiri -->
            </td>
            <td>
                <p>Mengetahui,</p>
                <div style="margin-top: 15px; margin-bottom: 5px;">
                    <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(70)->generate('Laporan Produktivitas Teknisi Sah Sistem AviaTrack. Dicetak: ' . now()->format('d F Y H:i'))) }}" alt="QR Code TTD" />
                </div>
                <p style="margin-bottom: 2px;"><strong>{{ strtoupper(auth()->check() ? auth()->user()->name : 'MANAJER INVENTARIS ASET') }}</strong><br>Manajer Inventaris Aset</p>
                <hr style="width: 60%; border: 0.5px solid #000;">
            </td>
        </tr>
    </table>

</body>
</html>
