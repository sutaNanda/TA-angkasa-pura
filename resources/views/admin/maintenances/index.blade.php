@extends('layouts.admin')

@section('title', 'Riwayat Pengecekan')
@section('page-title', 'Logbook Pengecekan Rutin')

@section('content')
    {{-- FILTER SECTION (Desain Asli) --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" action="{{ route('admin.maintenances.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-auto">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>

            <div class="w-full md:w-48">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Status Hasil</label>
                <select name="status" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="pass" {{ request('status') == 'pass' ? 'selected' : '' }}>Pass (Normal)</option>
                    <option value="fail" {{ request('status') == 'fail' ? 'selected' : '' }}>Fail (Ada Masalah)</option>
                </select>
            </div>

            <div class="w-full md:w-64">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Cari Aset / Teknisi</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama aset atau teknisi..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
                </div>
            </div>

            <div class="flex gap-2 w-full md:w-auto ml-auto">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.maintenances.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Reset
                </a>
                <a href="{{ route('admin.export.maintenances') }}?{{ http_build_query(request()->all()) }}" target="_blank" class="bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </a>
            </div>
        </form>
    </div>

    {{-- TABLE DATA (Desain Asli dengan perbaikan Status Tindakan) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs">
                <tr>
                    <th class="px-6 py-4 w-12 text-center">No</th>
                    <th class="px-6 py-4">Waktu Pengecekan</th>
                    <th class="px-6 py-4">Target Inspeksi</th>
                    <th class="px-6 py-4">Tipe</th>
                    <th class="px-6 py-4 text-center">Temuan</th>
                    <th class="px-6 py-4 text-center">Status Tindakan</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    @php
                        $findingBadge = '';
                        $rowClass = 'hover:bg-gray-50 border-l-4 border-transparent';

                        // Ambil status dari status utama PatrolLog
                        if ($log->status == 'normal' || $log->status == 'pass') {
                            $findingBadge = '<span class="inline-flex items-center gap-1 text-green-700 font-bold text-xs"><i class="fa-solid fa-check-circle"></i> Normal</span>';
                        } else {
                            $rowClass = 'bg-red-50/30 hover:bg-red-50 border-l-4 border-red-400';
                            $findingBadge = '<span class="inline-flex items-center gap-1 text-red-600 font-bold text-xs"><i class="fa-solid fa-triangle-exclamation"></i> Masalah</span>';
                        }

                        // Logika Badge Work Order (Status Tindakan)
                        $actionBadge = '';
                        if ($log->status != 'normal' && $log->status != 'pass') {
                            // Cek relasi workOrder (belongsTo ke WorkOrder model)
                            $wo = $log->workOrder; 
                            
                            if (!$wo) {
                                $actionBadge = '<span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-[10px] font-bold uppercase border border-gray-200">Belum Ada Tiket</span>';
                            } else {
                                $statusMap = [
                                    'open'        => ['label' => 'Menunggu', 'class' => 'bg-rose-50 text-rose-600 border-rose-100'],
                                    'in_progress' => ['label' => 'Dikerjakan', 'class' => 'bg-blue-50 text-blue-600 border-blue-100'],
                                    'completed'   => ['label' => 'Selesai', 'class' => 'bg-amber-50 text-amber-600 border-amber-100'],
                                    'verified'    => ['label' => 'Terverifikasi', 'class' => 'bg-emerald-50 text-emerald-600 border-emerald-100'],
                                ];

                                $currentStatus = $statusMap[strtolower($wo->status)] ?? ['label' => $wo->status, 'class' => 'bg-slate-50 text-slate-600 border-slate-100'];
                                
                                $actionBadge = '<span class="'.$currentStatus['class'].' px-3 py-1 rounded-full text-[10px] font-bold border uppercase">'.$currentStatus['label'].'</span>';
                            }
                        } else {
                            // Jika normal, otomatis Selesai
                            $actionBadge = '<span class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase"><i class="fa-solid fa-check-double mr-1"></i> Aman</span>';
                        }
                    @endphp

                    <tr class="{{ $rowClass }} transition">
                        <td class="px-6 py-4 text-center font-bold text-gray-400 text-xs">
                            {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900 text-xs">{{ $log->created_at ? $log->created_at->format('d M Y') : '-' }}</div>
                            <div class="text-[10px] text-gray-500">{{ $log->created_at ? $log->created_at->format('H:i') . ' WITA' : '-' }}</div>
                        </td>

                        <td class="px-6 py-4">
                            @if($log->asset)
                                <div class="font-bold text-gray-800 text-xs">{{ $log->asset->name }}</div>
                                <div class="text-[10px] text-gray-500">SN: {{ $log->asset->serial_number ?? '-' }}</div>
                            @elseif($log->location)
                                <div class="font-bold text-blue-700 text-xs"><i class="fa-solid fa-layer-group mr-1"></i> {{ $log->location->name }}</div>
                            @else
                                <div class="font-bold text-red-500 text-xs italic">Aset Tidak Teridentifikasi</div>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            @if(!$log->asset_id)
                                <span class="bg-blue-100 text-blue-700 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider">Kesatuan Area</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider">Aset Tunggal</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 text-center">{!! $findingBadge !!}</td>
                        <td class="px-6 py-4 text-center">{!! $actionBadge !!}</td>

                        <td class="px-6 py-4 text-center">
                            <button onclick="showDetailLog({{ $log->id }})" class="text-blue-600 hover:text-blue-800 font-bold text-[10px] uppercase border border-blue-200 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition">
                                <i class="fa-solid fa-eye mr-1"></i> Detail
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-400 italic">Belum ada riwayat pengecekan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $logs->withQueryString()->links() }}
    </div>

    {{-- MODAL DETAIL (Desain Baru: Bersih, Modern, Minimalis) --}}
    <div id="detailLogModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 transition-opacity" onclick="closeModal()"></div>

            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full max-w-4xl border border-slate-200">
                
                {{-- Header Modal --}}
                <div class="bg-white px-8 py-5 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Detail Laporan Inspeksi</h3>
                        <p class="text-xs font-medium text-slate-400 mt-1 font-mono uppercase" id="modalLogId">ID Log: -</p>
                    </div>
                </div>

                {{-- Body Modal --}}
                <div class="bg-slate-50/50 px-8 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    {{-- Informasi Target & Waktu (Bersih) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <div class="space-y-1.5">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Target Inspeksi</p>
                            <div id="modalAssetName" class="text-slate-800">-</div>
                            <div id="modalAssetLoc" class="text-slate-500 mt-1">-</div>
                        </div>
                        <div class="md:text-right space-y-1.5 border-t border-slate-100 pt-4 md:border-t-0 md:pt-0">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pelaksana & Waktu</p>
                            <p class="text-base font-bold text-slate-800" id="modalTechName">-</p>
                            <p class="text-sm font-medium text-slate-500 flex items-center gap-1.5 md:justify-end">
                                <i class="fa-regular fa-clock text-slate-400"></i> <span id="modalTime">-</span>
                            </p>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-slate-700 flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-list-check text-slate-400"></i> Rincian Hasil Pemeriksaan
                    </h4>
                    
                    {{-- Tabel Checklist Modal (Gaya Bersih, tidak ada gradient gelap) --}}
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold text-xs uppercase tracking-wide">
                                <tr>
                                    <th class="px-5 py-3 w-12 text-center">No</th>
                                    <th class="px-5 py-3">Poin Pemeriksaan</th>
                                    <th class="px-5 py-3 text-center w-32">Status</th>
                                    <th class="px-5 py-3">Hasil / Catatan Teknisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600" id="modalChecklistBody">
                                </tbody>
                        </table>
                    </div>

                    {{-- Timeline Tindakan (Baru) --}}
                    <div id="modalTimelineSection" class="mt-6 hidden">
                        <h4 class="text-sm font-bold text-slate-700 flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-clock-rotate-left text-slate-400"></i> Alur Penanganan Tiket
                        </h4>
                        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                            <div id="modalTimelineBody" class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-slate-200 before:via-slate-200 before:to-transparent">
                                {{-- Items rendered via JS --}}
                            </div>
                        </div>
                    </div>

                    {{-- Kesimpulan / Notes (Desain Rapi) --}}
                    <div id="modalNotesSection" class="mt-6 bg-white p-5 rounded-xl border border-slate-200 shadow-sm hidden">
                        <h5 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="fa-regular fa-comment-dots text-slate-400"></i> Kesimpulan Akhir
                        </h5>
                        <p class="text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-lg border border-slate-100" id="modalNotesText">-</p>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="bg-white px-8 py-4 flex justify-end gap-3 border-t border-slate-100">
                    <button onclick="closeModal()" class="bg-white border border-slate-300 text-slate-700 px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">Tutup</button>
                    <a href="#" id="modalExportBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                    </a>
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
                const response = await fetch(`/admin/maintenances/${id}`);
                const result = await response.json();

                if(result.status === 'success') {
                    const data = result.data;

                    document.getElementById('modalLogId').innerText = 'ID Log: #PTR-' + data.id;
                    
                    const elAssetName = document.getElementById('modalAssetName');
                    const elAssetLoc  = document.getElementById('modalAssetLoc');

                    const isAreaInspection = data.location || !data.asset_id;
                    const locationName = data.location 
                        ? data.location.name 
                        : (data.asset && data.asset.location ? data.asset.location.name : null);

                    // Render Informasi Target yang lebih rapi
                    if (isAreaInspection && locationName) {
                        elAssetName.innerHTML = `
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-slate-800">${locationName}</span>
                                <span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-100 uppercase">Kesatuan Area</span>
                            </div>`;
                        elAssetLoc.innerHTML = `
                            <span class="text-xs text-slate-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-location-dot text-slate-400"></i> Mencakup semua aset di area ini
                            </span>`;
                    } else if (data.asset) {
                        elAssetName.innerHTML = `
                            <div class="flex flex-col">
                                <span class="text-base font-bold text-slate-800">${data.asset.name}</span>
                                ${data.asset.serial_number ? `<span class="text-xs text-slate-400 font-mono mt-0.5">SN: ${data.asset.serial_number}</span>` : ''}
                            </div>`;
                        elAssetLoc.innerHTML = `
                            <span class="text-xs text-slate-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-location-dot text-slate-400"></i> ${data.asset.location ? data.asset.location.name : 'Lokasi tidak diketahui'}
                            </span>`;
                    } else {
                        elAssetName.innerHTML = '<span class="italic text-slate-400 text-sm">Data Tidak Tersedia</span>';
                        elAssetLoc.innerHTML = '';
                    }

                    document.getElementById('modalTechName').innerText = data.technician ? data.technician.name : 'Tidak diketahui';
                    document.getElementById('modalTime').innerText = new Date(data.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
                    document.getElementById('modalExportBtn').href = `/admin/export/maintenances/${data.id}`;

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

                    if (groupedItems.length > 0) {
                        let count = 1;

                        groupedItems.forEach((group) => {
                            // Header Kategori Bersih (Pengganti bg-indigo-900)
                            tbody.innerHTML += `
                                <tr class="bg-slate-50 border-y border-slate-200">
                                    <td colspan="4" class="px-5 py-3">
                                        <span class="text-slate-700 text-xs font-bold uppercase tracking-wider">
                                            Kategori: ${group.template_name}
                                        </span>
                                    </td>
                                </tr>`;

                            group.items.forEach((item) => {
                                // Sub-header (Pengganti bg-gradient)
                                if (item.type === 'header') {
                                    tbody.innerHTML += `
                                        <tr class="bg-white">
                                            <td colspan="4" class="px-5 py-3 border-b border-slate-100">
                                                <span class="text-slate-800 text-sm font-semibold border-l-2 border-blue-500 pl-2">
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

                                // Logika Label Jawaban (Bersih, flat color, tidak norak)
                                if (answerValue === 'pass' || answerValue === 'ya') {
                                    displayAnswer = `<span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-2.5 py-1.5 rounded-md text-xs font-semibold border border-emerald-100/50 w-full justify-center"><i class="fa-solid fa-check text-emerald-500"></i> ${answerValue === 'ya' ? 'Ya' : 'Normal'}</span>`;
                                } else if (answerValue === 'fail' || answerValue === 'tidak') {
                                    displayAnswer = `<span class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 px-2.5 py-1.5 rounded-md text-xs font-semibold border border-rose-100/50 w-full justify-center"><i class="fa-solid fa-xmark text-rose-500"></i> ${answerValue === 'tidak' ? 'Tidak' : 'Masalah'}</span>`;
                                    isIssue = true;
                                } else if (answerValue === 'na') {
                                    displayAnswer = `<span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-500 px-2.5 py-1.5 rounded-md text-xs font-semibold border border-slate-200/50 w-full justify-center">N/A</span>`;
                                } else if (answerValue === '-') {
                                    displayAnswer = `<span class="text-slate-300 italic text-xs w-full text-center block">Kosong</span>`;
                                } else {
                                    displayAnswer = `<span class="text-slate-700 font-medium text-sm">${answerValue} ${item.unit ? `<span class="text-slate-400 text-xs ml-1">${item.unit}</span>` : ''}</span>`;
                                }

                                // Info aset rusak jika ada
                                let assetTag = '';
                                if (isIssue && failedAssets[item.id]) {
                                    assetTag = `
                                        <div class="mt-2 inline-flex items-center gap-1.5 text-xs text-rose-600 bg-rose-50 px-2.5 py-1.5 rounded-lg border border-rose-100">
                                            <i class="fa-solid fa-arrow-turn-down -rotate-90 text-rose-400"></i>
                                            Aset Terkait: <span class="font-semibold">${failedAssets[item.id]}</span>
                                        </div>`;
                                }

                                // Catatan
                                let noteHtml = noteValue ? `<p class="text-sm text-slate-600 mt-1">${noteValue}</p>` : '<span class="text-slate-300">-</span>';
                                
                                // Efek warna baris jika bermasalah
                                const rowBg = isIssue ? 'bg-rose-50/20' : 'bg-white hover:bg-slate-50/50 transition-colors';

                                let row = `
                                    <tr class="${rowBg}">
                                        <td class="px-5 py-4 text-center text-slate-400 text-sm align-top font-medium">${count++}</td>
                                        <td class="px-5 py-4 align-top">
                                            <p class="text-slate-700 text-sm font-medium">${item.question}</p>
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
                                <td colspan="4" class="px-5 py-12 text-center text-slate-400">
                                    <i class="fa-regular fa-folder-open text-3xl mb-3 block text-slate-300"></i>
                                    Tidak ada rincian data inspeksi.
                                </td>
                            </tr>`;
                    }

                    // ── 5. Render Timeline Perbaikan (Baru) ──
                    const timelineSection = document.getElementById('modalTimelineSection');
                    const timelineBody = document.getElementById('modalTimelineBody');
                    
                    // Handle both snake_case and camelCase for relationships
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
                                    <p class="text-xs font-bold text-slate-800">Tiket Dilaporkan (#${wo.ticket_number})</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Dilaporkan Oleh: <span class="font-semibold text-slate-700">${wo.reporter ? wo.reporter.name : 'Teknisi Patroli'}</span></p>
                                    <p class="text-[10px] text-slate-400 mt-1 uppercase font-mono">${new Date(wo.created_at).toLocaleString('id-ID', {dateStyle:'medium', timeStyle:'short'})}</p>
                                </div>
                            </div>`;
                        
                        // 2. Histori Dinamis
                        histories.forEach(h => {
                            let icon = 'fa-arrow-right-arrow-left';
                            let color = 'bg-slate-100 text-slate-500';
                            let actionLabel = h.action;

                            const statusThemes = {
                                'open': { label: 'Dibuka Kembali', icon: 'fa-folder-open', color: 'bg-rose-100 text-rose-600' },
                                'in_progress': { label: 'Mulai Dikerjakan', icon: 'fa-screwdriver-wrench', color: 'bg-blue-100 text-blue-600' },
                                'handover': { label: 'Serah Terima (Handover)', icon: 'fa-hands-holding', color: 'bg-indigo-100 text-indigo-600' },
                                'completed': { label: 'Perbaikan Selesai', icon: 'fa-check-double', color: 'bg-emerald-100 text-emerald-600' },
                                'verified': { label: 'Diverifikasi oleh Admin', icon: 'fa-certificate', color: 'bg-green-100 text-green-600' },
                                'pending_part': { label: 'Menunggu Suku Cadang', icon: 'fa-clock', color: 'bg-amber-100 text-amber-600' }
                            };

                            const theme = statusThemes[h.action] || { label: h.action, icon: 'fa-circle-dot', color: 'bg-slate-100 text-slate-500' };
                            
                            timelineBody.innerHTML += `
                                <div class="relative pl-10">
                                    <span class="absolute left-0 top-1 flex items-center justify-center w-5 h-5 rounded-full ${theme.color} ring-4 ring-white">
                                        <i class="fa-solid ${theme.icon} text-[10px]"></i>
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold text-slate-800">${theme.label}</p>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Oleh: <span class="font-semibold text-slate-700">${h.user ? h.user.name : 'N/A'}</span></p>
                                        ${h.notes ? `<p class="text-[11px] text-slate-600 italic mt-1.5 p-2 bg-slate-50 rounded border border-slate-100">"${h.notes}"</p>` : ''}
                                        <p class="text-[10px] text-slate-400 mt-1 uppercase font-mono">${new Date(h.created_at).toLocaleString('id-ID', {dateStyle:'medium', timeStyle:'short'})}</p>
                                    </div>
                                </div>`;
                        });
                    } else {
                        timelineSection.classList.add('hidden');
                    }

                    // Menampilkan Notes
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
                alert('Gagal memuat detail riwayat: ' + error.message);
            }
        }
    </script>
    
    <style>
        /* Scrollbar yang lebih bersih */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [x-cloak] { display: none !important; }
    </style>
@endsection