@extends('layouts.admin')

@section('title', 'Riwayat Pengecekan')
@section('page-title', 'Logbook Pengecekan Rutin')

@section('content')
    {{-- FILTER SECTION --}}
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
            </div>
        </form>
    </div>

    {{-- TABLE DATA --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs">
                <tr>
                    <th class="px-6 py-4 w-12 text-center">No</th> {{-- KOLOM BARU --}}
                    <th class="px-6 py-4">Waktu Pengecekan</th>
                    <th class="px-6 py-4">Aset</th>
                    <th class="px-6 py-4">Teknisi</th>
                    <th class="px-6 py-4">Hasil Cek</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    @php
                        // Logika Warna Status & Badge Dinamis
                        $statusBadge = '';
                        $rowClass = 'hover:bg-gray-50 border-l-4 border-transparent';

                        if ($log->status == 'normal') {
                            $statusBadge = '<span class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold border border-green-200"><i class="fa-solid fa-check-circle"></i> Normal</span>';
                        } else {
                            // Default: Issue Found (Red)
                            $rowClass = 'bg-red-50 hover:bg-red-100 border-l-4 border-red-500';
                            $statusBadge = '<span class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold border border-red-200"><i class="fa-solid fa-triangle-exclamation"></i> Menunggu Perbaikan</span>';

                            // Cek Work Order
                            $wo = optional($log->workOrder);
                            
                            if ($wo->status == 'completed') {
                                $rowClass = 'bg-white hover:bg-gray-50 border-l-4 border-blue-500'; // Jadi biru/bersih jika selesai
                                $statusBadge = '<span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold border border-blue-200"><i class="fa-solid fa-check-double"></i> Selesai Diperbaiki</span>';
                            } elseif ($wo->status == 'handover') {
                                $rowClass = 'bg-yellow-50 hover:bg-yellow-100 border-l-4 border-yellow-500';
                                $statusBadge = '<span class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold border border-yellow-200"><i class="fa-solid fa-arrow-right-arrow-left"></i> Status: Handover</span>';
                            } elseif ($wo->status == 'in_progress') {
                                $rowClass = 'bg-blue-50 hover:bg-blue-100 border-l-4 border-blue-400';
                                $techName = $wo->technician ? explode(' ', $wo->technician->name)[0] : 'Teknisi';
                                $statusBadge = '<span class="inline-flex items-center gap-1 bg-cyan-100 text-cyan-800 px-3 py-1 rounded-full text-xs font-semibold border border-cyan-200"><i class="fa-solid fa-person-digging"></i> Dikerjakan: '.$techName.'</span>';
                            }
                        }
                    @endphp

                    <tr class="{{ $rowClass }} transition">
                        {{-- NOMOR URUT DINAMIS (PAGINATION SUPPORT) --}}
                        <td class="px-6 py-4 text-center font-bold text-gray-400 text-xs">
                            {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $log->created_at ? $log->created_at->format('d M Y') : '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at ? $log->created_at->format('H:i') . ' WITA' : '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800">{{ $log->asset->name ?? 'Aset Terhapus' }}</div>
                            <div class="text-xs text-gray-500">SN: {{ $log->asset->serial_number ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->technician)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <span>{{ $log->technician->name }}</span>
                                </div>
                            @else
                                <span class="text-gray-400 italic text-xs">Belum dikerjakan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {!! $statusBadge !!}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="showDetailLog({{ $log->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-200 hover:bg-blue-50 px-3 py-1 rounded transition">
                                Lihat Detail
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-gray-400">
                            <i class="fa-regular fa-folder-open text-3xl mb-2"></i>
                            <p>Belum ada riwayat pengecekan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $logs->withQueryString()->links() }}
    </div>

    {{-- MODAL DETAIL (AJAX) --}}
    <div id="detailLogModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal()"></div>

            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">
                
                {{-- Header Modal --}}
                <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Detail Riwayat Patroli</h3>
                        <p class="text-xs text-gray-500" id="modalLogId">ID Log: -</p>
                    </div>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>

                {{-- Body Modal --}}
                <div class="bg-white px-6 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    {{-- Info Aset & Teknisi --}}
                    <div class="grid grid-cols-2 gap-4 mb-6 bg-blue-50 p-4 rounded-lg border border-blue-100">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Aset</p>
                            <p class="text-sm font-semibold text-gray-800" id="modalAssetName">-</p>
                            <p class="text-xs text-gray-600" id="modalAssetLoc">-</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 uppercase font-bold">Teknisi</p>
                            <p class="text-sm font-semibold text-gray-800" id="modalTechName">-</p>
                            <p class="text-xs text-gray-600" id="modalTime">-</p>
                        </div>
                    </div>

                    {{-- [NEW] Vertical Activity Timeline (Riwayat Aktivitas & Handover) --}}
                    <div id="timelineSection" class="mb-4 hidden">
                        <h4 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2 flex items-center">
                            <i class="fa-solid fa-timeline mr-2 text-blue-600"></i> Kronologi Pengerjaan
                        </h4>
                        
                        <div class="relative border-l-2 border-gray-200 ml-3 space-y-6" id="modalTimelineBody">
                            {{-- Timeline Items akan di-inject via JS --}}
                        </div>
                    </div>

                    {{-- Tabel Checklist --}}
                    <h4 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">Hasil Checklist</h4>
                    <table class="w-full text-sm text-left border rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 text-gray-700 font-bold text-xs uppercase">
                            <tr>
                                <th class="px-4 py-2 w-10 text-center">#</th>
                                <th class="px-4 py-2">Pertanyaan</th>
                                <th class="px-4 py-2">Jawaban</th>
                                <th class="px-4 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="modalChecklistBody">
                            {{-- Data via JS --}}
                        </tbody>
                    </table>

                    {{-- Global Notes --}}
                    <div id="modalNotesSection" class="mt-4 bg-yellow-50 p-4 rounded-lg border border-yellow-100 hidden">
                        <h5 class="text-xs font-bold text-yellow-800 uppercase mb-1">Catatan Patroli</h5>
                        <p class="text-sm text-gray-700" id="modalNotesText">-</p>
                    </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button onclick="closeModal()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        function closeModal() {
            document.getElementById('detailLogModal').classList.add('hidden');
        }

        async function showDetailLog(id) {
            try {
                // Fetch data dari API Controller
                const response = await fetch(`/admin/maintenances/${id}`);
                const result = await response.json();

                if(result.status === 'success') {
                    const data = result.data;

                    // Isi Header
                    document.getElementById('modalLogId').innerText = 'ID Log: #PTR-' + data.id;
                    document.getElementById('modalAssetName').innerText = data.asset ? data.asset.name : 'Aset Terhapus';
                    document.getElementById('modalAssetLoc').innerText = data.asset.location ? data.asset.location.name : '-';
                    document.getElementById('modalTechName').innerText = data.technician ? data.technician.name : '-';
                    document.getElementById('modalTime').innerText = new Date(data.created_at).toLocaleString('id-ID');

                    // [NEW] Logic Timeline
                    const timelineBody = document.getElementById('modalTimelineBody');
                    const timelineSection = document.getElementById('timelineSection');
                    timelineBody.innerHTML = '';
                    
                    if(data.work_order && data.work_order.histories && data.work_order.histories.length > 0) {
                        timelineSection.classList.remove('hidden');
                        
                        // 1. Initial State (Patroli)
                        timelineBody.innerHTML += `
                            <div class="ml-6">
                                <span class="absolute -left-2.5 flex items-center justify-center w-5 h-5 bg-blue-100 rounded-full ring-4 ring-white">
                                    <i class="fa-solid fa-clipboard-check text-blue-600 text-[10px]"></i>
                                </span>
                                <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900">Patroli Selesai (Isu Ditemukan)</h3>
                                <time class="block mb-2 text-xs font-normal leading-none text-gray-400">
                                    ${new Date(data.created_at).toLocaleString('id-ID')} - Oleh ${data.technician ? data.technician.name : 'Sistem'}
                                </time>
                                <p class="mb-4 text-xs text-gray-500">
                                    Patroli rutin menemukan ketidaknormalan pada aset. Tiket otomatis dibuat.
                                </p>
                            </div>
                        `;

                        // 2. Loop History
                        data.work_order.histories.forEach(history => {
                            let icon = 'fa-circle-check';
                            let color = 'bg-gray-200';
                            let iconColor = 'text-gray-500';
                            let title = history.action;

                            if(history.action === 'created') {
                                icon = 'fa-ticket'; color = 'bg-purple-100'; iconColor = 'text-purple-600';
                                title = 'Tiket Perbaikan Dibuat';
                            } else if(history.action === 'in_progress') {
                                icon = 'fa-person-digging'; color = 'bg-blue-100'; iconColor = 'text-blue-600';
                                title = 'Sedang Dikerjakan';
                            } else if(history.action === 'handover') {
                                icon = 'fa-arrow-right-arrow-left'; color = 'bg-yellow-100'; iconColor = 'text-yellow-600';
                                title = 'Handover (Alih Tugas)';
                            } else if(history.action === 'completed') {
                                icon = 'fa-check'; color = 'bg-green-100'; iconColor = 'text-green-600';
                                title = 'Pekerjaan Selesai';
                            }

                            timelineBody.innerHTML += `
                                <div class="ml-6">
                                    <span class="absolute -left-2.5 flex items-center justify-center w-5 h-5 ${color} rounded-full ring-4 ring-white">
                                        <i class="fa-solid ${icon} ${iconColor} text-[10px]"></i>
                                    </span>
                                    <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900">
                                        ${title} 
                                        ${history.action === 'handover' ? '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded ml-2">Penting</span>' : ''}
                                    </h3>
                                    <time class="block mb-2 text-xs font-normal leading-none text-gray-400">
                                        ${new Date(history.created_at).toLocaleString('id-ID')} - Oleh ${history.user ? history.user.name : '-'}
                                    </time>
                                    <p class="mb-2 text-sm text-gray-600 bg-gray-50 p-2 rounded border border-gray-100 italic">
                                        "${history.description || '-'}"
                                    </p>
                                </div>
                            `;
                        });

                    } else if(data.status === 'issue_found') {
                        // Kasus Issue Found tapi belum ada History (Baru dibuat)
                        timelineSection.classList.remove('hidden');
                        timelineBody.innerHTML = `
                            <div class="ml-6">
                                <span class="absolute -left-2.5 flex items-center justify-center w-5 h-5 bg-red-100 rounded-full ring-4 ring-white">
                                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-[10px]"></i>
                                </span>
                                <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900">Menunggu Tindakan</h3>
                                <p class="mb-4 text-xs text-gray-500">Tiket perbaikan belum diproses oleh teknisi.</p>
                            </div>
                        `;
                    } else {
                        // Normal, sembunyikan timeline
                        timelineSection.classList.add('hidden');
                    }

                    // Isi Tabel Checklist
                    const tbody = document.getElementById('modalChecklistBody');
                    tbody.innerHTML = '';

                    const templateItems = data.checklist_template ? data.checklist_template.items : [];
                    const answers = data.inspection_data || {};

                    if(templateItems.length > 0) {
                        templateItems.forEach((item, index) => {
                            // Ambil jawaban dari JSON (key = item.id)
                            let answerValue = answers[item.id] || '-';
                            let isIssue = false;

                            // Logika Tampilan Jawaban
                            if (item.type === 'pass_fail') {
                                if (answerValue === 'pass') {
                                    answerValue = '<span class="text-green-600 font-bold">Normal</span>';
                                } else if (answerValue === 'fail') {
                                    answerValue = '<span class="text-red-600 font-bold">Masalah</span>';
                                    isIssue = true;
                                }
                            } else if (item.type === 'numeric' && item.unit) {
                                answerValue += ' ' + item.unit;
                            }

                            // Icon Status
                            let statusIcon = isIssue 
                                ? '<i class="fa-solid fa-triangle-exclamation text-red-500 text-lg"></i>' 
                                : '<i class="fa-solid fa-check-circle text-green-500 text-lg"></i>';
                            
                            let row = `
                                <tr>
                                    <td class="px-4 py-2 text-center text-gray-400 text-xs">${index + 1}</td>
                                    <td class="px-4 py-2 text-gray-700 font-medium">
                                        ${item.question}
                                    </td>
                                    <td class="px-4 py-2">${answerValue}</td>
                                    <td class="px-4 py-2 text-center">${statusIcon}</td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                    } else if (Object.keys(answers).length > 0) {
                        // Fallback jika tidak ada template (Legacy Data / Template Terhapus)
                        // Coba iterate answers langsung (hanya key/value)
                        Object.keys(answers).forEach((key, index) => {
                             let row = `
                                <tr>
                                    <td class="px-4 py-2 text-center text-gray-400 text-xs">${index + 1}</td>
                                    <td class="px-4 py-2 text-gray-700 italic">Item #${key} (Template Hilang)</td>
                                    <td class="px-4 py-2">${answers[key]}</td>
                                    <td class="px-4 py-2 text-center">-</td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-4 text-center text-gray-400">Tidak ada detail checklist.</td></tr>';
                    }

                    // Update Notes Section
                    const notesSection = document.getElementById('modalNotesSection');
                    const notesText = document.getElementById('modalNotesText');
                    
                    if(data.notes && data.notes !== '-') {
                        notesSection.classList.remove('hidden');
                        notesText.innerText = data.notes;
                    } else {
                        notesSection.classList.add('hidden');
                        notesText.innerText = '-';
                    }

                    document.getElementById('detailLogModal').classList.remove('hidden');
                }
            } catch (error) {
                Swal.fire('Gagal!', 'Gagal mengambil data detail.', 'error');
                console.error(error);
            }
        }
    </script>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
@endsection