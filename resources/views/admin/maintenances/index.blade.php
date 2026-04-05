@extends('layouts.admin')

@section('title', 'Riwayat Pengecekan')
@section('page-title', 'Logbook Pengecekan Rutin')

@section('content')
<div class="container-fluid px-4 py-6 w-full mx-auto max-w-full">
    
    {{-- Header Title --}}
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Riwayat Pengecekan</h1>
            <p class="text-sm text-gray-500 mt-1">Laporan historis hasil inspeksi rutin dan patroli teknisi.</p>
        </div>
        @if(auth()->user()->role === 'manajer')
        <div class="shrink-0 w-full md:w-auto">
            <a href="{{ route('admin.export.maintenances') }}?{{ http_build_query(request()->all()) }}" target="_blank" class="w-full md:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2 focus:ring-2 focus:ring-gray-200">
                <i class="fa-regular fa-file-pdf text-red-500"></i> Export Laporan (PDF)
            </a>
        </div>
        @endif
    </div>

    {{-- FILTER SECTION --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 mb-6 w-full">
        <form method="GET" action="{{ route('admin.maintenances.index') }}" class="flex flex-col xl:flex-row gap-4 items-end">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 w-full">
                {{-- Dari Tanggal --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition-all text-gray-600">
                </div>
                
                {{-- Sampai Tanggal --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition-all text-gray-600">
                </div>

                {{-- Status Hasil --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Status Hasil</label>
                    <div class="relative">
                        <select name="status" class="w-full appearance-none border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 pl-3 pr-10 py-2.5 outline-none shadow-sm transition-all bg-white text-gray-700">
                            <option value="">Semua Status</option>
                            <option value="pass" {{ request('status') == 'pass' ? 'selected' : '' }}>Pass (Normal)</option>
                            <option value="fail" {{ request('status') == 'fail' ? 'selected' : '' }}>Fail (Ada Masalah)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Pencarian --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Cari Target</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Aset / Teknisi / Tiket..." class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none shadow-sm transition-all text-gray-700">
                    </div>
                </div>
            </div>

            <div class="flex gap-2 w-full xl:w-auto shrink-0 pt-2 xl:pt-0">
                <a href="{{ route('admin.maintenances.index') }}" class="flex-1 xl:flex-none text-center bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm focus:ring-2 focus:ring-gray-200">
                    Reset
                </a>
                <button type="submit" class="flex-1 xl:flex-none flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition-all focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>

    {{-- TABLE DATA (STRUKTUR ANTI BUG OVERFLOW) --}}
    <div class="w-full max-w-full bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col overflow-hidden mb-6">
        <div class="w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[1100px] text-sm text-left text-gray-600 border-collapse">
                <thead class="bg-gray-50/80 text-gray-500 uppercase tracking-wider text-[11px] font-bold border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-12 text-center whitespace-nowrap">No</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Waktu Pemeriksaan</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap min-w-[200px]">Target Inspeksi</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Tipe</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Shift</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Status Temuan</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Tiket/Tindakan</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($logs as $log)
                        @php
                            $findingBadge = '';
                            $rowClass = 'hover:bg-gray-50/80 border-l-[3px] border-transparent';

                            // Status Patroli (Temuan)
                            if ($log->status == 'normal' || $log->status == 'pass') {
                                $findingBadge = '<span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-md text-[11px] font-bold ring-1 ring-inset ring-emerald-600/20"><i class="fa-solid fa-check text-emerald-500"></i> Aman</span>';
                            } else {
                                $rowClass = 'bg-rose-50/30 hover:bg-rose-50/60 border-l-[3px] border-rose-400';
                                $findingBadge = '<span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 px-2.5 py-1 rounded-md text-[11px] font-bold ring-1 ring-inset ring-rose-600/20"><i class="fa-solid fa-xmark text-rose-500"></i> Masalah</span>';
                            }

                            // Status Tiket / Tindakan
                            $actionBadge = '';
                            if ($log->status != 'normal' && $log->status != 'pass') {
                                $wo = $log->workOrder; 
                                
                                if (!$wo) {
                                    $actionBadge = '<span class="bg-gray-50 text-gray-500 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border border-gray-200">Belum Ada Tiket</span>';
                                } else {
                                    $statusMap = [
                                        'open'        => ['label' => 'Menunggu', 'class' => 'bg-rose-50 text-rose-700 ring-rose-600/20', 'icon' => 'fa-envelope-open'],
                                        'in_progress' => ['label' => 'Dikerjakan', 'class' => 'bg-blue-50 text-blue-700 ring-blue-600/20', 'icon' => 'fa-spinner fa-spin'],
                                        'completed'   => ['label' => 'Selesai', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'icon' => 'fa-clipboard-check'],
                                        'verified'    => ['label' => 'Terverifikasi', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'icon' => 'fa-check-double'],
                                    ];
                                    $currentStatus = $statusMap[strtolower($wo->status)] ?? ['label' => $wo->status, 'class' => 'bg-slate-50 text-slate-600 ring-slate-500/20', 'icon' => 'fa-ticket'];
                                    $actionBadge = '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset '.$currentStatus['class'].'"><i class="fa-solid '.$currentStatus['icon'].'"></i> '.$currentStatus['label'].'</span>';
                                }
                            } else {
                                $actionBadge = '<span class="text-gray-400 italic text-xs">—</span>';
                            }
                        @endphp

                        <tr class="{{ $rowClass }} transition-colors duration-150">
                            <td class="px-6 py-4 text-center font-medium text-gray-400 text-xs whitespace-nowrap">
                                {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 text-sm">{{ $log->created_at ? $log->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5"><i class="fa-regular fa-clock text-gray-400"></i> {{ $log->created_at ? $log->created_at->format('H:i') . ' WITA' : '-' }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->asset)
                                    <div class="font-bold text-gray-900 text-sm truncate max-w-[250px]" title="{{ $log->asset->name }}">{{ $log->asset->name }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5 font-mono">SN: {{ $log->asset->serial_number ?? '-' }}</div>
                                @elseif($log->location)
                                    <div class="font-bold text-blue-800 text-sm flex items-center gap-1.5 truncate max-w-[250px]"><i class="fa-solid fa-layer-group text-blue-500"></i> {{ $log->location->name }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Area / Kesatuan Lokasi</div>
                                @else
                                    <div class="font-bold text-rose-500 text-xs italic bg-rose-50 px-2 py-0.5 inline-block rounded border border-rose-100">Aset Tidak Teridentifikasi</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!$log->asset_id)
                                    <span class="bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">Kesatuan Area</span>
                                @else
                                    <span class="bg-slate-100 border border-slate-200 text-slate-600 text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">Aset Tunggal</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->shift)
                                    <span class="{{ $log->shift->badge_class }} px-2.5 py-1 rounded-full text-[10px] font-bold border inline-flex items-center gap-1.5">
                                        <i class="{{ $log->shift->icon_class }}"></i> {{ $log->shift->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs italic">—</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-4 text-center whitespace-nowrap">{!! $findingBadge !!}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">{!! $actionBadge !!}</td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button onclick="showDetailLog({{ $log->id }})" class="text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 hover:text-blue-600 font-semibold text-xs px-4 py-1.5 rounded-lg transition w-full shadow-sm focus:ring-2 focus:ring-gray-200 focus:outline-none flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-list-check text-2xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-gray-900 font-bold text-base mb-1">Belum ada riwayat pengecekan</h3>
                                    <p class="text-sm">Riwayat inspeksi rutin akan muncul di sini setelah teknisi melakukan tugas.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination (Disatukan ke dalam Container Tabel) --}}
        @if($logs->hasPages())
        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200">
            {{ $logs->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- MODAL DETAIL --}}
    <div id="detailLogModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal()"></div>

            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full max-w-4xl border border-gray-200">
                
                {{-- Header Modal --}}
                <div class="bg-slate-800 px-6 py-5 flex justify-between items-center text-white">
                    <div>
                        <h3 class="text-lg font-bold flex items-center gap-2">
                            <i class="fa-solid fa-file-signature text-blue-400"></i> Detail Laporan Inspeksi
                        </h3>
                        <p class="text-xs font-medium text-slate-300 mt-1 font-mono tracking-wider" id="modalLogId">ID LOG: -</p>
                    </div>
                    <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-white transition focus:outline-none">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="bg-slate-50/50 px-6 py-6 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    
                    {{-- Informasi Target & Waktu --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-crosshairs"></i> Target Inspeksi</p>
                            <div id="modalAssetName" class="text-gray-900">-</div>
                            <div id="modalAssetLoc" class="text-gray-500 mt-1">-</div>
                        </div>
                        <div class="border-t border-gray-100 pt-4 md:border-t-0 md:pt-0 md:border-l md:border-gray-100 md:pl-5">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-user-check"></i> Pelaksana & Waktu</p>
                            <p class="text-sm font-bold text-gray-900 mb-1" id="modalTechName">-</p>
                            <p class="text-xs font-medium text-gray-500 flex items-center gap-1.5 bg-gray-50 inline-block px-2 py-1 rounded border border-gray-100">
                                <i class="fa-regular fa-clock text-gray-400"></i> <span id="modalTime">-</span>
                            </p>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-list-check text-blue-500"></i> Rincian Hasil Pemeriksaan
                    </h4>
                    
                    {{-- Tabel Checklist Modal --}}
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm mb-6">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left min-w-[600px]">
                                <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 font-bold text-[11px] uppercase tracking-wide">
                                    <tr>
                                        <th class="px-5 py-3.5 w-12 text-center whitespace-nowrap">No</th>
                                        <th class="px-5 py-3.5 whitespace-nowrap">Poin Pemeriksaan</th>
                                        <th class="px-5 py-3.5 text-center w-36 whitespace-nowrap">Status</th>
                                        <th class="px-5 py-3.5 min-w-[200px]">Hasil / Catatan Teknisi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-gray-600 bg-white" id="modalChecklistBody">
                                    </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Timeline Tindakan --}}
                    <div id="modalTimelineSection" class="hidden mb-6">
                        <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-timeline text-indigo-500"></i> Alur Penanganan Tiket Terkait
                        </h4>
                        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                            <div id="modalTimelineBody" class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-gray-200 before:via-gray-200 before:to-transparent">
                                {{-- Items rendered via JS --}}
                            </div>
                        </div>
                    </div>

                    {{-- Kesimpulan / Notes --}}
                    <div id="modalNotesSection" class="hidden bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                        <h5 class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="fa-regular fa-comment-dots text-gray-400"></i> Kesimpulan Akhir / Catatan Teknisi
                        </h5>
                        <p class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-lg border border-gray-100 border-l-4 border-l-blue-400 italic" id="modalNotesText">-</p>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="bg-white px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100 rounded-b-2xl">
                    <button onclick="closeModal()" class="w-full sm:w-auto bg-white border border-gray-300 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-colors focus:ring-2 focus:ring-gray-200 focus:outline-none">Tutup</button>
                    @if(auth()->user()->role === 'manajer')
                    <a href="#" id="modalExportBtn" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm flex items-center justify-center gap-2 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:outline-none">
                        <i class="fa-solid fa-file-pdf"></i> Unduh PDF Laporan
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('detailLogModal').classList.add('hidden');
        }

        async function showDetailLog(id) {
            try {
                // Tampilkan loading state jika perlu (bisa ditambahkan)
                const response = await fetch(`/admin/maintenances/${id}`);
                const result = await response.json();

                if(result.status === 'success') {
                    const data = result.data;

                    document.getElementById('modalLogId').innerText = 'ID LOG: #PTR-' + data.id;
                    
                    const elAssetName = document.getElementById('modalAssetName');
                    const elAssetLoc  = document.getElementById('modalAssetLoc');

                    const isAreaInspection = data.location || !data.asset_id;
                    const locationName = data.location 
                        ? data.location.name 
                        : (data.asset && data.asset.location ? data.asset.location.name : null);

                    // Render Informasi Target
                    if (isAreaInspection && locationName) {
                        elAssetName.innerHTML = `
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-900">${locationName}</span>
                                <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-100 uppercase tracking-wide">Kesatuan Area</span>
                            </div>`;
                        elAssetLoc.innerHTML = `
                            <span class="text-xs text-gray-500 flex items-center gap-1.5 mt-1">
                                <i class="fa-solid fa-location-dot text-gray-400"></i> Pemeriksaan seluruh aset dalam area
                            </span>`;
                    } else if (data.asset) {
                        elAssetName.innerHTML = `
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">${data.asset.name}</span>
                                ${data.asset.serial_number ? `<span class="text-[11px] text-gray-500 font-mono mt-0.5">SN: ${data.asset.serial_number}</span>` : ''}
                            </div>`;
                        elAssetLoc.innerHTML = `
                            <span class="text-xs text-gray-500 flex items-center gap-1.5 mt-1">
                                <i class="fa-solid fa-location-dot text-gray-400"></i> ${data.asset.location ? data.asset.location.name : 'Lokasi tidak diketahui'}
                            </span>`;
                    } else {
                        elAssetName.innerHTML = '<span class="italic text-rose-500 bg-rose-50 px-2 py-1 rounded text-xs border border-rose-100">Data Aset Tidak Tersedia</span>';
                        elAssetLoc.innerHTML = '';
                    }

                    document.getElementById('modalTechName').innerText = data.technician ? data.technician.name : 'Tidak diketahui';
                    document.getElementById('modalTime').innerText = new Date(data.created_at).toLocaleString('id-ID', { dateStyle: 'long', timeStyle: 'short' }) + ' WITA';
                    const exportBtn = document.getElementById('modalExportBtn');
                    if (exportBtn) {
                        exportBtn.href = `/admin/export/maintenances/${data.id}`;
                    }

                    // Parser JSON Checklist
                    const tbody = document.getElementById('modalChecklistBody');
                    tbody.innerHTML = '';

                    const groupedItems = data.grouped_items || [];
                    let parsedData = data.inspection_data || {};
                    if (typeof parsedData === 'string') {
                        try { parsedData = JSON.parse(parsedData); } catch(e) { parsedData = {}; }
                    }

                    const isNewFormat = parsedData.hasOwnProperty('answers');
                    let answers      = isNewFormat ? (parsedData.answers || {}) : parsedData;
                    let itemNotes    = isNewFormat ? (parsedData.notes || {}) : {};
                    let failedAssets = isNewFormat ? (parsedData.failed_assets || {}) : {};
                    let assetNamesMap = data.asset_names_map || {};

                    if (groupedItems.length > 0) {
                        let count = 1;

                        groupedItems.forEach((group) => {
                            // Header Kategori
                            tbody.innerHTML += `
                                <tr class="bg-gray-50/80 border-y border-gray-200">
                                    <td colspan="4" class="px-5 py-3 text-gray-700 text-xs font-bold uppercase tracking-wider">
                                        <i class="fa-solid fa-folder-tree text-blue-500 mr-1.5"></i> ${group.template_name}
                                    </td>
                                </tr>`;

                            group.items.forEach((item) => {
                                // Sub-header 
                                if (item.type === 'header') {
                                    tbody.innerHTML += `
                                        <tr class="bg-white">
                                            <td colspan="4" class="px-5 py-3.5 border-b border-gray-100">
                                                <span class="text-gray-800 text-sm font-bold border-l-4 border-blue-500 pl-3 block">
                                                    ${item.question}
                                                </span>
                                            </td>
                                        </tr>`;
                                    return;
                                }

                                const answerValue = answers[item.id] !== undefined ? answers[item.id] : '-';
                                const noteValue   = itemNotes[item.id] || '';
                                let isIssue       = false;
                                let displayAnswer = '';

                                // Logika Label Jawaban
                                if (answerValue === 'pass' || answerValue === 'ya') {
                                    displayAnswer = `<span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold ring-1 ring-inset ring-emerald-600/20 w-full justify-center shadow-sm"><i class="fa-solid fa-check text-emerald-500"></i> ${answerValue === 'ya' ? 'Ya' : 'Normal'}</span>`;
                                } else if (answerValue === 'fail' || answerValue === 'tidak') {
                                    displayAnswer = `<span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 px-3 py-1.5 rounded-lg text-xs font-bold ring-1 ring-inset ring-rose-600/20 w-full justify-center shadow-sm"><i class="fa-solid fa-xmark text-rose-500"></i> ${answerValue === 'tidak' ? 'Tidak' : 'Masalah'}</span>`;
                                    isIssue = true;
                                } else if (answerValue === 'na') {
                                    displayAnswer = `<span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg text-xs font-bold ring-1 ring-inset ring-gray-500/20 w-full justify-center shadow-sm">N/A</span>`;
                                } else if (answerValue === '-') {
                                    displayAnswer = `<span class="text-gray-300 italic text-xs w-full text-center block">Kosong</span>`;
                                } else {
                                    displayAnswer = `<span class="text-gray-800 font-bold text-sm block text-center">${answerValue} ${item.unit ? `<span class="text-gray-400 text-xs ml-0.5 font-medium">${item.unit}</span>` : ''}</span>`;
                                }

                                // Info aset rusak jika ada
                                let assetTag = '';
                                if (isIssue && failedAssets[item.id]) {
                                    let ids = failedAssets[item.id];
                                    if (!Array.isArray(ids)) ids = [ids];
                                    let names = ids.map(id => assetNamesMap[id] || id).join(', ');
                                    assetTag = `
                                        <div class="mt-2.5 inline-flex items-start gap-2 text-[11px] text-rose-700 bg-rose-50/50 px-3 py-2 rounded-lg border border-rose-100 w-full sm:w-auto">
                                            <i class="fa-solid fa-arrow-turn-down -rotate-90 text-rose-400 mt-0.5 shrink-0"></i>
                                            <span><strong>Aset Terindikasi:</strong> ${names}</span>
                                        </div>`;
                                }

                                // Catatan
                                let noteHtml = noteValue ? `<div class="text-sm text-gray-600 mt-1 leading-relaxed">${noteValue}</div>` : '<span class="text-gray-300 italic text-xs">-</span>';
                                
                                // Efek warna baris jika bermasalah
                                const rowBg = isIssue ? 'bg-rose-50/20' : 'bg-white hover:bg-gray-50/50 transition-colors';

                                let row = `
                                    <tr class="${rowBg}">
                                        <td class="px-5 py-4 text-center text-gray-400 text-sm align-top font-medium">${count++}</td>
                                        <td class="px-5 py-4 align-top">
                                            <p class="text-gray-800 text-sm font-medium leading-snug">${item.question}</p>
                                            ${assetTag}
                                        </td>
                                        <td class="px-5 py-4 text-center align-top">
                                            ${displayAnswer}
                                        </td>
                                        <td class="px-5 py-4 align-top">
                                            ${noteHtml}
                                        </td>
                                    </tr>
                                `;
                                tbody.innerHTML += row;
                            });
                        });
                    } else {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="px-5 py-16 text-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fa-regular fa-folder-open text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="font-medium text-sm text-gray-500">Tidak ada rincian data inspeksi.</p>
                                </td>
                            </tr>`;
                    }

                    // ── Render Timeline Perbaikan ──
                    const timelineSection = document.getElementById('modalTimelineSection');
                    const timelineBody = document.getElementById('modalTimelineBody');
                    
                    const wo = data.work_order || data.workOrder;
                    
                    if (wo) {
                        timelineSection.classList.remove('hidden');
                        timelineBody.innerHTML = '';
                        
                        const histories = wo.histories || [];
                        
                        // 1. Paporan (Dibuat)
                        timelineBody.innerHTML += `
                            <div class="relative pl-10">
                                <span class="absolute left-0 top-1 flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 ring-4 ring-white">
                                    <i class="fa-solid fa-flag text-blue-600 text-[10px]"></i>
                                </span>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Tiket Otomatis Dibuat (#${wo.ticket_number})</p>
                                    <p class="text-[11px] text-gray-500 mt-0.5">Dibuat dari hasil laporan inspeksi ini.</p>
                                    <p class="text-[10px] text-gray-400 mt-1 uppercase font-mono tracking-wide">${new Date(wo.created_at).toLocaleString('id-ID', {dateStyle:'medium', timeStyle:'short'})}</p>
                                </div>
                            </div>`;
                        
                        // 2. Histori Dinamis
                        histories.forEach(h => {
                            const statusThemes = {
                                'open': { label: 'Dibuka Kembali', icon: 'fa-folder-open', color: 'bg-rose-100 text-rose-600' },
                                'in_progress': { label: 'Mulai Dikerjakan', icon: 'fa-screwdriver-wrench', color: 'bg-blue-100 text-blue-600' },
                                'handover': { label: 'Serah Terima (Handover)', icon: 'fa-hands-holding', color: 'bg-indigo-100 text-indigo-600' },
                                'completed': { label: 'Perbaikan Selesai', icon: 'fa-check-double', color: 'bg-emerald-100 text-emerald-600' },
                                'verified': { label: 'Diverifikasi oleh Admin', icon: 'fa-certificate', color: 'bg-green-100 text-green-600' },
                                'pending_part': { label: 'Menunggu Suku Cadang', icon: 'fa-clock', color: 'bg-amber-100 text-amber-600' }
                            };

                            const theme = statusThemes[h.action] || { label: h.action, icon: 'fa-circle-dot', color: 'bg-gray-100 text-gray-500' };
                            
                            timelineBody.innerHTML += `
                                <div class="relative pl-10">
                                    <span class="absolute left-0 top-1 flex items-center justify-center w-5 h-5 rounded-full ${theme.color} ring-4 ring-white">
                                        <i class="fa-solid ${theme.icon} text-[10px]"></i>
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold text-gray-800">${theme.label}</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Oleh: <span class="font-bold text-gray-700">${h.user ? h.user.name : 'Sistem'}</span></p>
                                        ${h.notes ? `<div class="text-[11px] text-gray-600 italic mt-2 p-3 bg-gray-50 rounded-lg border border-gray-100 leading-relaxed border-l-2 border-l-gray-300">"${h.notes}"</div>` : ''}
                                        <p class="text-[10px] text-gray-400 mt-1.5 uppercase font-mono tracking-wide">${new Date(h.created_at).toLocaleString('id-ID', {dateStyle:'medium', timeStyle:'short'})}</p>
                                    </div>
                                </div>`;
                        });
                    } else {
                        timelineSection.classList.add('hidden');
                    }

                    // Menampilkan Notes Akhir
                    const notesSection = document.getElementById('modalNotesSection');
                    const notesText = document.getElementById('modalNotesText');
                    if (data.notes && data.notes.trim() !== '' && data.notes !== '-') {
                        notesSection.classList.remove('hidden');
                        notesText.innerText = data.notes;
                    } else {
                        notesSection.classList.add('hidden');
                    }

                    document.getElementById('detailLogModal').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Gagal memuat detail riwayat: ' + error.message, 'error');
            }
        }
    </script>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [x-cloak] { display: none !important; }
    </style>
</div>
@endsection