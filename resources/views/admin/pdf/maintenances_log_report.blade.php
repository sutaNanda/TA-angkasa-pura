<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengecekan Rutin Logbook</title>
    <style>
        @page { margin: 30px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0 0 5px 0; color: #1e3a8a; text-transform: uppercase; }
        .header p { margin: 0; font-size: 10px; color: #64748b; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .main-table th { background-color: #f8fafc; color: #475569; font-weight: bold; text-align: left; padding: 8px 10px; border: 1px solid #cbd5e1; font-size: 10px; text-transform: uppercase; }
        .main-table td { padding: 8px 10px; border: 1px solid #e2e8f0; font-size: 10px; vertical-align: top; }
        .main-table tr:nth-child(even) { background-color: #fcfcfc; }
        
        .empty-state { text-align: center; font-style: italic; color: #94a3b8; padding: 20px; border: 1px dashed #cbd5e1; }
        
        .signature { margin-top: 40px; text-align: right; width: 100%; page-break-inside: avoid; }
        .signature p { margin-bottom: 60px; }

        .status-badge { display: inline-block; padding: 3px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; color: #fff; text-transform: uppercase; }
        .bg-green { background-color: #16a34a; }
        .bg-red { background-color: #dc2626; }
        .bg-gray { background-color: #64748b; }
        .bg-blue { background-color: #2563eb; }
        .bg-yellow { background-color: #eab308; }
        
        .text-xs { font-size: 9px; color: #64748b; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Aplikasi Monitoring Aset PT. Angkasa Pura | Diekspor pada: {{ $date }}</p>
    </div>

    @if($logs->count() == 0)
        <div class="empty-state">
            Tidak ada data pengecekan rutin pada rentang waktu atau kriteria filter yang terdaftar.
        </div>
    @else
        <table class="main-table">
            <thead>
                <tr>
                    <th width="3%" style="text-align: center">No</th>
                    <th width="15%">Waktu & Shift</th>
                    <th width="20%">Aset / Lokasi</th>
                    <th width="15%">Teknisi</th>
                    <th width="15%" style="text-align: center">Hasil Akhir</th>
                    <th width="12%" style="text-align: center">Tindakan Lanjut</th>
                    <th width="20%">Catatan Teknisi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $index => $log)
                    @php
                        // Badge Status Pengecekan
                        $statusBadge = '';
                        if ($log->status == 'normal' || $log->status == 'pass') {
                            $statusBadge = '<span class="status-badge bg-green">Aman / Normal</span>';
                        } else {
                            $statusBadge = '<span class="status-badge bg-red">Ada Masalah</span>';
                        }

                        // Badge Status Tindakan Work Order
                        $actionBadge = '';
                        if ($log->status != 'normal' && $log->status != 'pass') {
                            $wo = optional($log->workOrder);
                            if (!$wo->exists) {
                                $actionBadge = '<span class="text-xs font-bold" style="color: #dc2626;">Belum Ada Tiket</span>';
                            } elseif ($wo->status == 'verified') {
                                $actionBadge = '<span class="status-badge bg-green">Selesai</span>';
                            } elseif ($wo->status == 'completed') {
                                $actionBadge = '<span class="status-badge bg-yellow">Verifikasi</span>';
                            } elseif ($wo->status == 'in_progress') {
                                $actionBadge = '<span class="status-badge bg-blue">Dikerjakan</span>';
                            } else {
                                $actionBadge = '<span class="status-badge bg-gray">Open</span>';
                            }
                        } else {
                            $actionBadge = '<span class="text-xs" style="color:#94a3b8">- Tdk Perlu -</span>';
                        }
                    @endphp
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td>
                            <span class="font-bold">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</span><br>
                            <span class="text-xs">Shift: {{ $log->shift ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="font-bold">{{ optional($log->asset)->name ?? 'Aset Dihapus' }}</span><br>
                            <span class="text-xs">{{ optional(optional($log->asset)->location)->name ?? 'Lokasi Tdk Diketahui' }}</span>
                        </td>
                        <td>{{ optional($log->technician)->name ?? '-' }}</td>
                        <td align="center">{!! $statusBadge !!}</td>
                        <td align="center">{!! $actionBadge !!}</td>
                        <td>
                            <span class="text-xs">{{ $log->notes ? \Illuminate\Support\Str::limit($log->notes, 100) : '-' }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signature">
        <p>Disahkan Oleh,</p>
        <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->name ?? 'Administrator' }}</div>
        <div style="font-size: 9px; color: #64748b;">Manajer Inventaris Aset</div>
    </div>

</body>
</html>
