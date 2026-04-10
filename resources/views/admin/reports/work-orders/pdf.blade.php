<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Work Order & Perbaikan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            width: 100%;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header td { border: none; }
        .logo-ap {
            font-size: 24px;
            font-weight: bold;
            color: #1a4f8d;
        }
        .title-area { text-align: center; }
        h2 { margin: 0; padding: 0; font-size: 16px; text-transform: uppercase;}
        p.subtitle { margin: 2px 0; font-size: 11px; color: #555; }
        
        .summary {
            margin-bottom: 15px;
            width: 100%;
        }
        .summary td { font-size: 11px; border: none; padding: 2px; }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #999;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-center { text-align: center; }
        .mttr-badge { font-weight: bold; }
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
            <td width="20%">
               <!-- Ganti dengan path logo API asli jika mau -->
               <div class="logo-ap">ANGKASA PURA</div>
            </td>
            <td width="80%" class="title-area">
                <h2>Laporan Kegiatan Work Order & Pemeliharaan</h2>
                <p class="subtitle">Sistem Informasi Manajemen Aset M/E Airport</p>
                <p class="subtitle">Dicetak pada: {{ now()->format('d F Y, H:i') }}</p>
            </td>
        </tr>
    </table>

    <!-- Parameter Filter Info -->
    <table class="summary">
        <tr>
            <td width="15%"><strong>Periode Filter</strong></td>
            <td width="35%">: {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d M Y') : 'Awal' }} - {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d M Y') : 'Sekarang' }}</td>
            <td width="15%"><strong>Status</strong></td>
            <td width="35%">: {{ strtoupper($request->status && $request->status != 'all' ? str_replace('_', ' ', $request->status) : 'SEMUA') }}</td>
        </tr>
        <tr>
            <td><strong>Prioritas</strong></td>
            <td>: {{ strtoupper($request->priority && $request->priority != 'all' ? $request->priority : 'SEMUA') }}</td>
            <td><strong>Total Tiket</strong></td>
            <td>: {{ count($workOrders) }} Laporan</td>
        </tr>
    </table>

    <!-- Tabel Data Utama -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="12%">No. Tiket</th>
                <th width="12%">Tgl Dibuat</th>
                <th width="18%">Aset & Lokasi</th>
                <th width="20%">Kendala (Isu)</th>
                <th width="12%">Pelapor & Teknisi</th>
                <th width="13%">Waktu Selesai / MTTR</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($workOrders as $index => $wo)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $wo->ticket_number }}</strong></td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($wo->created_at)->format('d/m/Y H:i') }}</td>
                    <td>
                        <strong>{{ $wo->asset ? $wo->asset->name : 'N/A' }}</strong><br>
                        <span style="font-size:9px; color:#555;">Lokasi: {{ $wo->location ? $wo->location->name : 'N/A' }}</span>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($wo->issue_description, 60) }}</td>
                    <td>
                        <span style="color:#666; font-size:9px;">Pelapor:</span><br>
                        <strong>{{ $wo->actual_reporter_name }}</strong><br>
                        <span style="color:#666; font-size:9px;">Teknisi Ditunjuk:</span><br>
                        <strong>{{ $wo->technician ? $wo->technician->name : 'Belum Ditugaskan' }}</strong>
                    </td>
                    <td class="text-center mttr-badge">
                        @if($wo->status == 'completed' || $wo->status == 'verified')
                            <span style="color:#16a34a">{{ $wo->mttr_display }}</span><br>
                            <span style="font-size: 8px; font-weight:normal;">Akhir: {{ \Carbon\Carbon::parse($wo->completed_at)->format('d/m H:i') }}</span>
                        @else
                            <span style="color:#999">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ strtoupper(str_replace('_', ' ', $wo->status)) }}
                    </td>
                </tr>
            @endforeach

            @if(count($workOrders) == 0)
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Tidak ada perbaikan yang dilaporkan pada rentang filter ini.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Area Tanda Tangan -->
    <table class="footer">
        <tr>
            <td width="70%"></td>
            <td width="30%" class="text-center">
                <p>Mengetahui,</p>
                <div style="margin-top: 15px; margin-bottom: 5px;">
                    <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(80)->generate('Dokumen Laporan Perbaikan Sah AviaTrack. Dicetak: ' . now()->format('d F Y H:i'))) }}" alt="QR Code" />
                </div>
                <p><strong>{{ strtoupper(auth()->check() ? auth()->user()->name : 'MANAJER OPERASIONAL') }}</strong><br>Manajer Operasional M/E</p>
            </td>
        </tr>
    </table>
</body>
</html>
