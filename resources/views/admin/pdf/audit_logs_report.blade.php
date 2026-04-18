<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Log Aktivitas Sistem</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0 0 5px 0; color: #1e3a8a; text-transform: uppercase; }
        .header p { margin: 0; font-size: 10px; color: #64748b; }
        .filter-info { margin-bottom: 15px; font-size: 10px; color: #475569; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th { background-color: #f8fafc; color: #475569; font-weight: bold; text-align: left; padding: 8px; border: 1px solid #cbd5e1; font-size: 10px; text-transform: uppercase; }
        .data-table td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: top; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; color: #fff; background-color: #64748b; }
        .signature { margin-top: 40px; text-align: right; width: 100%; }
        .signature p { margin-bottom: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Aplikasi Monitoring Aset PT. Angkasa Pura | Diekspor pada: {{ $date }}</p>
    </div>

    @if($filter_start || $filter_module)
    <div class="filter-info">
        <strong>Filter Laporan:</strong> 
        @if($filter_start) Periode: {{ $filter_start }} s/d {{ $filter_end ?: $filter_start }} | @endif
        @if($filter_module) Modul Area: {{ strtoupper($filter_module) }} @endif
    </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="15%">Waktu Aktivitas</th>
                <th width="12%">Tipe Tindakan</th>
                <th width="15%">Aktor / Pengguna</th>
                <th width="15%">Area Modul</th>
                <th width="40%">Rincian Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                    <td><span class="badge">{{ strtoupper($log->action) }}</span></td>
                    <td>{{ $log->user->name ?? 'System' }}<br><span style="font-size:9px;color:#64748b">{{ $log->ip_address }}</span></td>
                    <td>{{ $log->module ?: '-' }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada catatan aktivitas (Log) pada periode/filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <p style="margin-bottom: 5px;">Mengetahui,</p>
        <div style="margin-top: 10px; margin-bottom: 5px;">
            <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(70)->generate('Laporan Log Aktivitas Sistem Sah AviaTrack. Dicetak: ' . now()->format('d F Y H:i'))) }}" alt="QR Code TTD" />
        </div>
        <div style="font-weight: bold; text-decoration: underline; margin-top: 5px;">{{ auth()->user()->name ?? 'Administrator' }}</div>
        <div style="font-size: 9px; color: #64748b;">Security / IT Admin</div>
    </div>

</body>
</html>
