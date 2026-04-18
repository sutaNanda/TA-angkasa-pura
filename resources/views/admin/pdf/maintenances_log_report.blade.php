<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengecekan Rutin Logbook</title>
    <style>
        @page { 
            margin: 35px 30px; 
        }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 10px; 
            color: #334155; 
            line-height: 1.4;
        }
        
        /* HEADER STYLES */
        .header { 
            text-align: center; 
            border-bottom: 2px solid #1e40af; 
            padding-bottom: 15px; 
            margin-bottom: 25px; 
        }
        .header h1 { 
            font-size: 18px; 
            margin: 0 0 8px 0; 
            color: #1e3a8a; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }
        .header p { 
            margin: 0; 
            font-size: 10px; 
            color: #64748b; 
        }
        
        /* TABLE STYLES */
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px; 
        }
        .main-table th { 
            background-color: #f1f5f9; 
            color: #334155; 
            font-weight: bold; 
            text-align: left; 
            padding: 10px 8px; 
            border: 1px solid #cbd5e1; 
            font-size: 9px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .main-table td { 
            padding: 8px; 
            border: 1px solid #e2e8f0; 
            font-size: 9.5px; 
            vertical-align: top; 
        }
        .main-table tr:nth-child(even) { 
            background-color: #f8fafc; 
        }
        
        /* EMPTY STATE */
        .empty-state { 
            text-align: center; 
            font-style: italic; 
            color: #94a3b8; 
            padding: 30px; 
            border: 1px dashed #cbd5e1; 
            background-color: #f8fafc;
            border-radius: 4px;
        }
        
        /* SIGNATURE */
        .signature-container {
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signature { 
            float: right;
            text-align: center; 
            width: 250px; 
        }
        .signature p { 
            margin: 0 0 5px 0; 
            color: #475569;
        }
        .signature-name {
            font-weight: bold; 
            text-decoration: underline;
            color: #1e293b;
            font-size: 11px;
        }
        .signature-role {
            font-size: 9px; 
            color: #64748b; 
            margin-top: 3px;
        }

        /* BADGES */
        .status-badge { 
            display: inline-block; 
            padding: 4px 6px; 
            border-radius: 4px; 
            font-size: 8px; 
            font-weight: bold; 
            color: #fff; 
            text-transform: uppercase; 
            text-align: center;
            min-width: 60px;
        }
        .bg-green { background-color: #10b981; }
        .bg-red { background-color: #ef4444; }
        .bg-gray { background-color: #94a3b8; }
        .bg-blue { background-color: #3b82f6; }
        .bg-yellow { background-color: #f59e0b; }
        .text-red { color: #ef4444; font-weight: bold; }
        .text-muted { color: #94a3b8; font-style: italic; font-size: 9px; }
        
        /* UTILITIES */
        .font-bold { font-weight: bold; color: #1e293b; }
        .text-xs { font-size: 8.5px; color: #64748b; }
        .mb-1 { margin-bottom: 4px; }
        
        /* PHOTOS */
        .photo-container {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;
        }
        .photo-box {
            display: inline-block; 
            margin-right: 8px; 
            text-align: center;
        }
        .photo-label {
            font-size: 7.5px; 
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .photo-label.error { color: #ef4444; }
        .photo-label.success { color: #10b981; }
        .photo-img {
            width: 45px; 
            height: 45px; 
            border: 1px solid #cbd5e1; 
            background: #f8fafc;
            border-radius: 3px;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Aplikasi Monitoring Aset PT. Angkasa Pura | Dicetak pada: {{ $date }}</p>
    </div>

    @if($logs->count() == 0)
        <div class="empty-state">
            Tidak ada data pengecekan rutin pada rentang waktu atau kriteria filter yang dipilih.
        </div>
    @else
        <table class="main-table">
            <thead>
                <tr>
                    <th width="4%" style="text-align: center">No</th>
                    <th width="14%">Waktu & Shift</th>
                    <th width="20%">Aset / Lokasi</th>
                    <th width="14%">Teknisi</th>
                    <th width="12%" style="text-align: center">Hasil Akhir</th>
                    <th width="12%" style="text-align: center">Tindakan Lanjut</th>
                    <th width="24%">Catatan Laporan & Foto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $index => $log)
                    @php
                        // Badge Status Pengecekan
                        $statusBadge = '';
                        if ($log->status == 'normal' || $log->status == 'pass') {
                            $statusBadge = '<span class="status-badge bg-green">Aman</span>';
                        } else {
                            $statusBadge = '<span class="status-badge bg-red">Masalah</span>';
                        }

                        // Badge Status Tindakan Work Order
                        $actionBadge = '';
                        if ($log->status != 'normal' && $log->status != 'pass') {
                            $wo = optional($log->workOrder);
                            if (!$wo->exists) {
                                $actionBadge = '<span class="text-red">Belum Ada Tiket</span>';
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
                            $actionBadge = '<span class="text-muted">- Tidak Perlu -</span>';
                        }
                    @endphp
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td>
                            <div class="font-bold mb-1">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</div>
                            <div class="text-xs">Shift: {{ optional($log->shift)->name ?? '-' }}</div>
                        </td>
                        <td>
                            @if($log->asset_id)
                                <div class="font-bold mb-1">{{ optional($log->asset)->name ?? 'Aset Dihapus' }}</div>
                            @else
                                <div class="font-bold mb-1" style="color: #2563eb;">[Kesatuan Area]</div>
                            @endif
                            <div class="text-xs">Lok: {{ optional($log->location)->name ?? optional(optional($log->asset)->location)->name ?? 'Lokasi Tdk Diketahui' }}</div>
                        </td>
                        <td>{{ optional($log->technician)->name ?? '-' }}</td>
                        <td align="center">{!! $statusBadge !!}</td>
                        <td align="center">{!! $actionBadge !!}</td>
                        <td>
                            <div style="margin-bottom: 5px; line-height: 1.3;">
                                {{ $log->notes ? \Illuminate\Support\Str::limit($log->notes, 120) : '-' }}
                            </div>
                            
                            {{-- Tampilkan Foto Hanya Jika Ada Masalah --}}
                            @if($log->status != 'normal' && $log->status != 'pass')
                                <div class="photo-container">
                                    {{-- Foto Laporan Awal --}}
                                    @if(is_array($log->photos) && count($log->photos) > 0 && file_exists(public_path('storage/' . $log->photos[0])))
                                        <div class="photo-box">
                                            <div class="photo-label error">Kondisi Awal</div>
                                            <img src="{{ public_path('storage/' . $log->photos[0]) }}" class="photo-img">
                                        </div>
                                    @endif

                                    {{-- Foto Hasil Perbaikan (Jika Tiket Sudah Dikerjakan) --}}
                                    @if($log->workOrder)
                                        @php
                                            $afterPath = null;
                                            if (is_array($log->workOrder->photos_after) && count($log->workOrder->photos_after) > 0) {
                                                $afterPath = $log->workOrder->photos_after[0];
                                            } elseif ($log->workOrder->last_progress_photo) {
                                                $afterPath = $log->workOrder->last_progress_photo;
                                            } else {
                                                $history = $log->workOrder->histories()->where('action', 'completed')->latest()->first();
                                                if ($history) {
                                                    $afterPath = is_array($history->photos) && count($history->photos) > 0 ? $history->photos[0] : $history->photo;
                                                }
                                            }
                                        @endphp
                                        
                                        @if($afterPath && file_exists(public_path('storage/' . $afterPath)))
                                            <div class="photo-box">
                                                <div class="photo-label success">Hasil Perbaikan</div>
                                                <img src="{{ public_path('storage/' . $afterPath) }}" class="photo-img">
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signature-container">
        <div class="signature">
            <p style="margin-bottom: 5px;">Disahkan Oleh,</p>
            <div style="margin-top: 10px; margin-bottom: 5px;">
                <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(70)->generate('Laporan Log Pengecekan Sah Sistem AviaTrack. Dicetak: ' . now()->format('d F Y H:i'))) }}" alt="QR Code TTD" />
            </div>
            <div class="signature-name" style="margin-top: 5px;">{{ auth()->user()->name ?? 'Administrator' }}</div>
            <div class="signature-role">Manajer Inventaris Aset</div>
        </div>
    </div>

</body>
</html>
