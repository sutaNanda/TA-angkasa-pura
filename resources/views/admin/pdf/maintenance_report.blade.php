<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perawatan Aset - {{ $maintenance->ticket_number ?? 'MNT-' . $maintenance->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        
        /* Header Laporan */
        .report-header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .report-header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
            color: #1e3a8a; /* Biru Tua */
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-header p {
            margin: 0;
            font-size: 10px;
            color: #64748b;
        }

        /* Seksi Informasi Dasar */
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 140px;
            color: #475569;
        }
        .info-value {
            font-weight: normal;
        }
        .separator {
            width: 10px;
            text-align: center;
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #fff;
        }
        .bg-green { background-color: #16a34a; }
        .bg-blue { background-color: #2563eb; }
        .bg-gray { background-color: #64748b; }
        .bg-yellow { background-color: #ca8a04; }

        /* Judul Seksi */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
            background-color: #f1f5f9;
            padding: 5px 10px;
            margin: 20px 0 10px 0;
            border-left: 3px solid #2563eb;
        }

        /* Tabel Checklist/Hasil */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .text-center { text-align: center; }

        /* Foto/Bukti Kerusakan */
        .photo-container {
            width: 100%;
            margin-top: 10px;
        }
        .photo-box {
            display: inline-block;
            width: 48%; /* Layout 2 kolom foto jika banyak, atau 1 ukuran besar */
            margin-right: 1%;
            margin-bottom: 10px;
            text-align: center;
        }
        .photo-box img {
            max-width: 100%;
            max-height: 200px;
            border: 1px solid #cbd5e1;
            padding: 2px;
            background-color: #fff;
        }
        .photo-caption {
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
            font-style: italic;
        }

        /* Tanda Tangan */
        .signature-section {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 30%;
            display: inline-block;
            text-align: center;
        }
        .sig-box.right {
            float: right;
        }
        .sig-name {
            margin-top: 50px;
            font-weight: bold;
            text-decoration: underline;
        }
        .sig-title {
            color: #64748b;
            font-size: 9px;
        }

        /* Utility */
        .mt-2 { margin-top: 10px; }
        .text-danger { color: #dc2626; font-weight: bold; }
        .text-success { color: #16a34a; font-weight: bold; }

    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="report-header">
        <h1>LAPORAN PERAWATAN ASET RUTIN (PREVENTIVE)</h1>
        <p>No. Dokumen: {{ $maintenance->ticket_number ?? 'MNT-' . str_pad($maintenance->id, 5, '0', STR_PAD_LEFT) }} | Diekspor pada: {{ $date }}</p>
    </div>

    <!-- INFORMASI DASAR -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-label">Nama Aset</td>
                <td class="separator">:</td>
                <td class="info-value"><strong>{{ $maintenance->asset->name }}</strong></td>
                
                <td class="info-label">Status Pekerjaan</td>
                <td class="separator">:</td>
                <td class="info-value">
                    @php
                        $statusClass = match($maintenance->status) {
                            'completed' => 'bg-green',
                            'in_progress' => 'bg-blue',
                            default => 'bg-gray'
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">
                        {{ $maintenance->status == 'completed' ? 'SELESAI' : strtoupper($maintenance->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="info-label">Kode / Barcode</td>
                <td class="separator">:</td>
                <td class="info-value">{{ $maintenance->asset->asset_code ?? '-' }}</td>
                
                <td class="info-label">Kategori Laporan</td>
                <td class="separator">:</td>
                <td class="info-value">Preventive Maintenance</td>
            </tr>
            <tr>
                <td class="info-label">Lokasi Aset</td>
                <td class="separator">:</td>
                <td class="info-value">{{ $maintenance->asset->location->name ?? '-' }}</td>
                
                <td class="info-label">Waktu Mulai (Scan)</td>
                <td class="separator">:</td>
                <td class="info-value">{{ $maintenance->started_at ? \Carbon\Carbon::parse($maintenance->started_at)->format('d M Y, H:i') : '-' }} WIB</td>
            </tr>
            <tr>
                <td class="info-label">Kategori Aset</td>
                <td class="separator">:</td>
                <td class="info-value">{{ $maintenance->asset->category->name ?? '-' }}</td>
                
                <td class="info-label">Waktu Selesai</td>
                <td class="separator">:</td>
                <td class="info-value">{{ $maintenance->completed_at ? \Carbon\Carbon::parse($maintenance->completed_at)->format('d M Y, H:i') : '-' }} WIB</td>
            </tr>
            <tr>
                <td class="info-label">Template / SOP</td>
                <td class="separator">:</td>
                <td class="info-value">{{ $maintenance->checklistTemplate->name ?? '-' }}</td>

                <td class="info-label">Teknisi Bertugas</td>
                <td class="separator">:</td>
                <td class="info-value"><strong>{{ $maintenance->technician->name ?? 'Sistem' }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- HASIL PENGECEKAN TUGAS -->
    <div class="section-title">A. RINCIAN HASIL PENGECEKAN (CHECKLIST)</div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="40%">Item Pemeriksaan / Standar</th>
                <th width="20%">Tipe Data</th>
                <th width="35%">Hasil Kelayakan / Nilai</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($maintenance->results) && $maintenance->results->count() > 0)
                @foreach($maintenance->results as $index => $result)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $result->item->question ?? '-' }}</td>
                        <td>
                            @if($result->item->type == 'pass_fail') Pilihan (OK/Rusak)
                            @elseif($result->item->type == 'number') Numerik ({{ $result->item->unit ?? '-' }})
                            @elseif($result->item->type == 'checkbox') Centang (Ya/Tidak)
                            @else Teks Bebas @endif
                        </td>
                        <td>
                            @if($result->item->type == 'pass_fail')
                                @if(strtolower($result->value) == 'ok' || strtolower($result->value) == 'pass')
                                    <span class="text-success">✔ AMAN (OK)</span>
                                @else
                                    <span class="text-danger">✖ KONDISI BURUK ({{ $result->value }})</span>
                                @endif
                            @elseif($result->item->type == 'checkbox')
                                {{ $result->value == '1' || strtolower($result->value) == 'true' ? '✔ YA' : '✖ TIDAK' }}
                            @elseif($result->item->type == 'number')
                                <strong>{{ $result->value }}</strong> {{ $result->item->unit ?? '' }}
                            @else
                                {{ $result->value ?: '-' }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center">Data hasil pengecekan belum diinput oleh teknisi.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- CATATAN & KESIMPULAN -->
    <div class="section-title">B. CATATAN & TINDAK LANJUT TEKNISI</div>
    <div style="border: 1px solid #cbd5e1; padding: 10px; background-color: #fff; min-height: 50px;">
        {!! nl2br(e($maintenance->notes ?: 'Tidak ada catatan tambahan dari teknisi.')) !!}
    </div>

    <!-- FOTO BUKTI (BASE64) -->
    <div class="section-title">C. DOKUMENTASI & FOTO KONDISI ASET</div>
    @if($maintenance->photo_proof && file_exists(public_path('storage/' . $maintenance->photo_proof)))
        <div class="photo-container">
            <div class="photo-box">
                @php
                    // Konversi ke base64 agar DOMPDF bisa merender gambar local dgn baik
                    $path = public_path('storage/' . $maintenance->photo_proof);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                @endphp
                <img src="{{ $base64 }}" alt="Foto Bukti Perawatan">
                <div class="photo-caption">Dilampirkan oleh Teknisi pada saat penyelesaian tugas.</div>
            </div>
        </div>
    @else
        <div style="padding: 10px; border: 1px dashed #cbd5e1; text-align: center; color: #94a3b8;">
            Tidak ada foto/bukti visual yang dilampirkan.
        </div>
    @endif

    <!-- TANDA TANGAN LEGALITAS -->
    <div class="signature-section">
        <div class="sig-box">
            <p>Dikerjakan Oleh,</p>
            <div class="sig-name">{{ $maintenance->technician->name ?? 'Teknisi' }}</div>
            <div class="sig-title">Pelaksana Penugasan</div>
        </div>
        
        <div class="sig-box right">
            <p>Mengetahui / Verifikator,</p>
            <div class="sig-name">{{ auth()->user()->name ?? 'Administrator' }}</div>
            <div class="sig-title">Manajer Operasional / Admin</div>
        </div>
    </div>

</body>
</html>
