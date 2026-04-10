<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tiket Perbaikan</title>
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
            margin-bottom: 15px; 
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

        /* FILTER INFO */
        .filter-info { 
            margin-bottom: 20px; 
            font-size: 9.5px; 
            color: #475569; 
            background-color: #f8fafc;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            display: inline-block;
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
            margin: 0 0 70px 0; 
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
            min-width: 70px;
        }
        .bg-emerald { background-color: #10b981; }
        .bg-rose { background-color: #e11d48; }
        .bg-blue { background-color: #3b82f6; }
        .bg-amber { background-color: #f59e0b; }
        .bg-indigo { background-color: #6366f1; }
        .bg-purple { background-color: #a855f7; }
        .bg-slate { background-color: #64748b; }
        
        /* UTILITIES */
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; color: #1e293b; }
        .text-xs { font-size: 8.5px; color: #64748b; }
        .mb-1 { margin-bottom: 4px; }
        .text-blue { color: #2563eb; }
        
        /* PHOTOS */
        .photo-container {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;
        }
        .photo-box {
            display: inline-block; 
            margin-right: 10px; 
            text-align: center;
        }
        .photo-label {
            font-size: 7.5px; 
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .photo-label.error { color: #e11d48; }
        .photo-label.success { color: #10b981; }
        .photo-img {
            width: 50px; 
            height: 50px; 
            border: 1px solid #cbd5e1; 
            background: #f8fafc;
            border-radius: 4px;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Aplikasi Monitoring Aset PT. Angkasa Pura | Dicetak pada: {{ $date }}</p>
    </div>

    @if($filter_start || $filter_status)
    <div class="filter-info">
        <strong>FILTER LAPORAN</strong> &nbsp;|&nbsp; 
        @if($filter_start) Periode: {{ \Carbon\Carbon::parse($filter_start)->format('d M Y') }} s/d {{ $filter_end ? \Carbon\Carbon::parse($filter_end)->format('d M Y') : \Carbon\Carbon::parse($filter_start)->format('d M Y') }} &nbsp;|&nbsp; @endif
        @if($filter_status) Status Tiket: <strong>{{ strtoupper($filter_status) }}</strong> @endif
    </div>
    @endif

    @if($tickets->count() == 0)
        <div class="empty-state">
            Tidak ada data tiket perbaikan pada periode atau kriteria filter yang dipilih.
        </div>
    @else
        <table class="main-table">
            <thead>
                <tr>
                    <th width="4%" class="text-center">No</th>
                    <th width="16%">No. Tiket & Waktu</th>
                    <th width="20%">Aset & Lokasi</th>
                    <th width="32%">Laporan Masalah & Bukti Foto</th>
                    <th width="14%">Teknisi</th>
                    <th width="14%" class="text-center">Status Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tickets as $index => $ticket)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        
                        {{-- Tiket & Waktu --}}
                        <td>
                            <div class="font-bold text-blue mb-1">{{ $ticket->ticket_number }}</div>
                            <div class="text-xs">{{ $ticket->created_at->format('d M Y') }}</div>
                            <div class="text-xs">{{ $ticket->created_at->format('H:i') }} WITA</div>
                        </td>
                        
                        {{-- Aset & Lokasi --}}
                        <td>
                            <div class="font-bold mb-1">{{ $ticket->asset->name ?? 'Aset Terhapus' }}</div>
                            <div class="text-xs">Lok: {{ $ticket->asset->location->name ?? '-' }}</div>
                        </td>
                        
                        {{-- Deskripsi Masalah & Foto --}}
                        <td>
                            <div style="margin-bottom: 5px; line-height: 1.3;">
                                {{ $ticket->issue_description }}
                            </div>
                            
                            {{-- Container Foto --}}
                            <div class="photo-container">
                                @php
                                    // Logic Foto Laporan (Sebelum)
                                    $beforePath = null;
                                    if ($ticket->initial_photo) {
                                        $beforePath = $ticket->initial_photo;
                                    } elseif (is_array($ticket->photos_before) && count($ticket->photos_before) > 0) {
                                        $beforePath = $ticket->photos_before[0];
                                    }
                                @endphp
                                @if($beforePath && file_exists(public_path('storage/' . $beforePath)))
                                    <div class="photo-box">
                                        <div class="photo-label error">Kondisi Rusak</div>
                                        <img src="{{ public_path('storage/' . $beforePath) }}" class="photo-img">
                                    </div>
                                @endif

                                @php
                                    // Logic Foto Selesai (Sesudah)
                                    $afterPath = null;
                                    if (is_array($ticket->photos_after) && count($ticket->photos_after) > 0) {
                                        $afterPath = $ticket->photos_after[0];
                                    } elseif ($ticket->last_progress_photo) {
                                        $afterPath = $ticket->last_progress_photo;
                                    } else {
                                        $history = $ticket->histories()->where('action', 'completed')->latest()->first();
                                        if ($history) {
                                            $afterPath = is_array($history->photos) && count($history->photos) > 0 ? $history->photos[0] : $history->photo;
                                        }
                                    }
                                @endphp
                                @if($afterPath && file_exists(public_path('storage/' . $afterPath)))
                                    <div class="photo-box">
                                        <div class="photo-label success">Selesai Diperbaiki</div>
                                        <img src="{{ public_path('storage/' . $afterPath) }}" class="photo-img">
                                    </div>
                                @endif
                            </div>
                        </td>
                        
                        {{-- Teknisi --}}
                        <td>
                            <div class="font-bold">{{ $ticket->technician->name ?? '-' }}</div>
                            <div class="text-xs mt-1">
                                Prio: 
                                <span style="text-transform: uppercase; font-weight:bold; color: {{ $ticket->priority == 'high' ? '#e11d48' : ($ticket->priority == 'medium' ? '#f59e0b' : '#10b981') }}">
                                    {{ $ticket->priority }}
                                </span>
                            </div>
                        </td>
                        
                        {{-- Status Akhir --}}
                        <td class="text-center">
                            @php
                                $statusClass = match(strtolower($ticket->status)) {
                                    'verified' => 'bg-emerald',
                                    'completed' => 'bg-amber',
                                    'in_progress' => 'bg-blue',
                                    'handover' => 'bg-indigo',
                                    'pending_part' => 'bg-purple',
                                    'open' => 'bg-rose',
                                    default => 'bg-slate'
                                };
                                
                                $statusLabel = match(strtolower($ticket->status)) {
                                    'verified' => 'Selesai & Valid',
                                    'completed' => 'Tunggu Verifikasi',
                                    'in_progress' => 'Sedang Dikerjakan',
                                    'open' => 'Belum Tertangani',
                                    'handover' => 'Operan Shift',
                                    'pending_part' => 'Tunggu Sparepart',
                                    default => 'Unknown'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="signature-container">
        <div class="signature">
            <p>Mengetahui,</p>
            <div class="signature-name">{{ auth()->user()->name ?? 'Administrator' }}</div>
            <div class="signature-role">Manajer Operasional / Admin</div>
        </div>
    </div>

</body>
</html>
