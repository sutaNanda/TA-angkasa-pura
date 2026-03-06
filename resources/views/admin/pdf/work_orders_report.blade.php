<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tiket Perbaikan</title>
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
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; color: #fff; }
        .bg-green { background-color: #16a34a; }
        .bg-blue { background-color: #2563eb; }
        .bg-yellow { background-color: #ca8a04; }
        .bg-red { background-color: #dc2626; }
        .bg-gray { background-color: #64748b; }
        .signature { margin-top: 40px; text-align: right; width: 100%; }
        .signature p { margin-bottom: 50px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Aplikasi Monitoring Aset PT. Angkasa Pura | Diekspor pada: {{ $date }}</p>
    </div>

    @if($filter_start || $filter_status)
    <div class="filter-info">
        <strong>Filter Laporan:</strong> 
        @if($filter_start) Periode: {{ $filter_start }} s/d {{ $filter_end ?: $filter_start }} | @endif
        @if($filter_status) Status: {{ strtoupper($filter_status) }} @endif
    </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%" class="text-center">No</th>
                <th width="12%">No. Tiket</th>
                <th width="15%">Waktu Dibuat</th>
                <th width="15%">Aset & Lokasi</th>
                <th width="25%">Deskripsi Masalah</th>
                <th width="15%">Teknisi</th>
                <th width="15%">Status Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $index => $ticket)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $ticket->ticket_number }}</strong></td>
                    <td>{{ $ticket->created_at->format('d M Y, H:i') }}</td>
                    <td>
                        <strong>{{ $ticket->asset->name ?? 'Aset Terhapus' }}</strong><br>
                        <span style="font-size: 9px; color: #64748b;">{{ $ticket->asset->location->name ?? '-' }}</span>
                    </td>
                    <td>{{ $ticket->issue_description }}</td>
                    <td>{{ $ticket->technician->name ?? '-' }}</td>
                    <td>
                        @php
                            $statusClass = match($ticket->status) {
                                'verified' => 'bg-green',
                                'completed' => 'bg-yellow',
                                'in_progress' => 'bg-blue',
                                'open' => 'bg-red',
                                default => 'bg-gray'
                            };
                            
                            $statusLabel = match($ticket->status) {
                                'verified' => 'Selesai & Valid',
                                'completed' => 'Tunggu Verifikasi',
                                'in_progress' => 'Sedang Dikerjakan',
                                'open' => 'Belum Tertangani',
                                'handover' => 'Handover',
                                'pending_part' => 'Tunggu Sparepart',
                                default => 'Unknown'
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ strtoupper($statusLabel) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data tiket pada periode/filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature">
        <p>Mengetahui,</p>
        <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->name ?? 'Administrator' }}</div>
        <div style="font-size: 9px; color: #64748b;">Manajer Operasional / Admin</div>
    </div>

</body>
</html>
