<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hirarki Lokasi & Aset</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0 0 5px 0; color: #1e3a8a; text-transform: uppercase; }
        .header p { margin: 0; font-size: 10px; color: #64748b; }
        
        /* Lokasi Container */
        .location-box { margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 4px; overflow: hidden; page-break-inside: avoid; }
        .loc-header { background-color: #f1f5f9; padding: 8px 10px; font-weight: bold; border-bottom: 1px solid #cbd5e1; font-size: 12px; color: #1e293b; }
        .loc-desc { font-size: 9px; color: #64748b; font-weight: normal; margin-left: 10px; }
        
        /* Aset Tabel dalam Lokasi */
        .asset-table { width: 100%; border-collapse: collapse; }
        .asset-table th { background-color: #fff; color: #475569; font-weight: bold; text-align: left; padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 9px; text-transform: uppercase; }
        .asset-table td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; vertical-align: middle; }
        .asset-table tr:last-child td { border-bottom: none; }
        
        .empty-assets { padding: 10px; text-align: center; font-style: italic; color: #94a3b8; font-size: 10px; }
        
        /* Anak Lokasi (Indentasi/Margin) */
        .child-loc { margin: 10px; border: 1px dashed #94a3b8; }
        .child-header { background-color: #f8fafc; border-bottom: 1px dashed #94a3b8; }
        
        /* Tanda Tangan */
        .signature { margin-top: 40px; text-align: right; width: 100%; page-break-inside: avoid; }
        .signature p { margin-bottom: 50px; }

        .status-badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; color: #fff; text-transform: uppercase; }
        .bg-green { background-color: #16a34a; }
        .bg-red { background-color: #dc2626; }
        .bg-gray { background-color: #64748b; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Aplikasi Monitoring Aset PT. Angkasa Pura | Diekspor pada: {{ $date }}</p>
    </div>

    <!-- MACRO / BLADE COMPONENT-LIKE UNTUK RECURSIVE (MANUAL) -->
    @php
        if (!function_exists('renderAssetsTable')) {
            function renderAssetsTable($assets) {
                if ($assets->count() == 0) {
                    echo '<div class="empty-assets">Tidak ada aset terdaftar di lokasi ini.</div>';
                    return;
                }

                echo '<table class="asset-table">';
                echo '<thead><tr><th width="5%">No</th><th width="30%">Nama Aset / Serial</th><th width="25%">Kategori</th><th width="20%">Kondisi</th><th width="20%">Status</th></tr></thead>';
                echo '<tbody>';
                foreach ($assets as $index => $asset) {
                    $statusClass = match($asset->status) {
                        'active' => 'bg-green', 'inactive' => 'bg-red', default => 'bg-gray'
                    };
                    $statusLabel = $asset->status == 'active' ? 'Aktif' : ($asset->status == 'inactive' ? 'Rusak/Mati' : 'Maintenance');
                    $badge = "<span class=\"status-badge {$statusClass}\">{$statusLabel}</span>";

                    $kondisi = $asset->condition ?? 'Normal';
                    $kategori = $asset->category->name ?? '-';
                    $serial = $asset->serial_number ? "SN: {$asset->serial_number}" : "Kode: {$asset->asset_code}";
                    $no = $index + 1;

                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td><strong>{$asset->name}</strong><br><span style='font-size:8px;color:#64748b'>{$serial}</span></td>";
                    echo "<td>{$kategori}</td>";
                    echo "<td>{$kondisi}</td>";
                    echo "<td>{$badge}</td>";
                    echo "</tr>";
                }
                echo '</tbody></table>';
            }
        }
    @endphp

    <!-- RENDER LOKASI UTAMA -->
    @foreach($locations as $parentLoc)
        <div class="location-box">
            <div class="loc-header">
                {{ $parentLoc->name }} 
                <span class="loc-desc">Lantai {{ $parentLoc->floor ?? '-' }} | Kode: {{ $parentLoc->code ?? '-' }}</span>
            </div>
            
            <!-- Aset di Parent -->
            {{ renderAssetsTable($parentLoc->assets) }}

            <!-- RENDER ANAK LOKASI (Level 2) -->
            @foreach($parentLoc->children as $childLoc)
                <div class="location-box child-loc">
                    <div class="loc-header child-header">
                        &rdsh; SUB: {{ $childLoc->name }}
                        <span class="loc-desc">{{ $childLoc->code ?? '-' }}</span>
                    </div>

                    {{ renderAssetsTable($childLoc->assets) }}

                    <!-- RENDER CUCU LOKASI (Level 3 - Opsional) -->
                    @foreach($childLoc->children as $grandchild)
                        <div class="location-box child-loc" style="margin-left: 20px;">
                            <div class="loc-header child-header" style="background-color: #fff">
                                &rdsh; RGN: {{ $grandchild->name }}
                            </div>
                            {{ renderAssetsTable($grandchild->assets) }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach

    <!-- ASET TANPA LOKASI (UNASSIGNED) -->
    @if($unassignedAssets->count() > 0)
        <div class="location-box" style="border-color: #dc2626;">
            <div class="loc-header" style="background-color: #fef2f2; color: #991b1b;">
                <i class="fa-solid fa-triangle-exclamation"></i> Aset Tanpa Lokasi (Unassigned)
            </div>
            {{ renderAssetsTable($unassignedAssets) }}
        </div>
    @endif

    <div class="signature">
        <p>Disahkan Oleh,</p>
        <div style="font-weight: bold; text-decoration: underline;">{{ auth()->user()->name ?? 'Administrator' }}</div>
        <div style="font-size: 9px; color: #64748b;">Manajer Inventaris Aset</div>
    </div>

</body>
</html>
