@extends('layouts.admin')

@section('title', 'Tiket Perbaikan')
@section('page-title', 'Manajemen Tiket Perbaikan')

@section('content')
    {{-- TABS FILTER (Statistik Cepat) --}}
    <div class="flex border-b border-gray-200 mb-6 overflow-x-auto">
        <a href="{{ route('admin.work-orders.index') }}" 
           class="px-6 py-3 font-medium text-sm whitespace-nowrap border-b-2 transition {{ !request('tab') ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">
            Semua Tiket 
            <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ !request('tab') ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' }}">
                {{ $counts['all'] }}
            </span>
        </a>
        <a href="{{ route('admin.work-orders.index', ['tab' => 'open']) }}" 
           class="px-6 py-3 font-medium text-sm whitespace-nowrap border-b-2 transition {{ request('tab') == 'open' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">
            Perlu Respon (Open)
            <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ request('tab') == 'open' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                {{ $counts['open'] }}
            </span>
        </a>
        <a href="{{ route('admin.work-orders.index', ['tab' => 'verify']) }}" 
           class="px-6 py-3 font-medium text-sm whitespace-nowrap border-b-2 transition {{ request('tab') == 'verify' ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">
            Butuh Verifikasi
            <span class="ml-2 py-0.5 px-2 rounded-full text-xs {{ request('tab') == 'verify' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $counts['verify'] }}
            </span>
        </a>
    </div>

    {{-- TOOLBAR (Search & Create) --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <form method="GET" class="relative w-full md:w-80">
            @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No Tiket, Aset, atau Teknisi..." class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-blue-500 w-full text-sm shadow-sm transition">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400"></i>
        </form>

        <div class="flex gap-2">
            @if(!auth()->user()->isManajer())
            <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition shadow-md hover:shadow-lg flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fa-solid fa-plus"></i> Buat Tiket Manual
            </button>
            @endif

            <a href="{{ route('admin.work-orders.export', request()->all()) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold transition shadow-md hover:shadow-lg flex items-center gap-2 w-full md:w-auto justify-center">
                <i class="fa-solid fa-file-pdf"></i> Export PDF
            </a>

            @if(request('tab') == 'verify')
                @if(!auth()->user()->isManajer())
                <form action="{{ route('admin.work-orders.verify-all') }}" method="POST" onsubmit="return confirmVerifyAll(event)">
                    @csrf
                    <button type="submit" 
                            {{ $counts['verify'] == 0 ? 'disabled' : '' }}
                            class="px-5 py-2.5 rounded-lg text-sm font-bold transition shadow-md flex items-center gap-2 w-full md:w-auto justify-center
                                {{ $counts['verify'] > 0 ? 'bg-green-600 hover:bg-green-700 text-white hover:shadow-lg animate-pulse' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}">
                        <i class="fa-solid fa-check-double"></i> Verifikasi Semua ({{ $counts['verify'] }})
                    </button>
                </form>
                @endif
            @endif


        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 w-12 text-center">No</th>
                        <th class="px-6 py-4">Tiket & Aset</th>
                        <th class="px-6 py-4">Masalah</th>
                        <th class="px-6 py-4">Prioritas</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Teknisi</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tickets as $ticket)
                        @php
                            // Styling Prioritas
                            $prioBadge = match($ticket->priority) {
                                'high' => 'bg-red-50 text-red-600 border-red-100',
                                'medium' => 'bg-orange-50 text-orange-600 border-orange-100',
                                default => 'bg-green-50 text-green-600 border-green-100'
                            };

                            // Styling Status & Icon
                            $statusConfig = match($ticket->status) {
                                'open' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => 'fa-envelope-open', 'label' => 'Baru / Open'],
                                'in_progress' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fa-spinner fa-spin', 'label' => 'Dikerjakan'],
                                'pending_part' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'icon' => 'fa-box-open', 'label' => 'Tunggu Sparepart'],
                                'handover' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-700', 'icon' => 'fa-handshake', 'label' => 'Operan Shift'],
                                'completed' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-clipboard-check', 'label' => 'Selesai (Verifikasi)'],
                                'verified' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => 'fa-check-double', 'label' => 'Verified'],
                            };

                            // Highlight baris yang butuh perhatian (Completed butuh verif, Open butuh assign)
                            $rowHighlight = ($ticket->status == 'completed' || $ticket->status == 'open') ? 'bg-yellow-50/50' : '';
                        @endphp

                        <tr class="hover:bg-gray-50 transition {{ $rowHighlight }}">
                            {{-- KOLOM NO --}}
                            <td class="px-6 py-4 text-center font-bold text-gray-400 text-xs">
                                {{ ($tickets->currentPage() - 1) * $tickets->perPage() + $loop->iteration }}
                            </td>

                            {{-- TIKET & ASET --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono font-bold text-blue-600 text-xs bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                        {{ $ticket->ticket_number }}
                                    </span>
                                </div>
                                @if($ticket->asset)
                                    <div class="font-bold text-gray-800">{{ $ticket->asset->name }}</div>
                                    <div class="text-xs text-gray-500 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot"></i> {{ $ticket->asset->location->name ?? '-' }}
                                    </div>
                                @else
                                    <div class="font-bold text-gray-800">{{ $ticket->location->name ?? 'Lokasi tidak diketahui' }}</div>
                                    <div class="text-xs text-orange-500 flex items-center gap-1">
                                        <i class="fa-solid fa-cube"></i> Aset belum diidentifikasi
                                    </div>
                                @endif
                            </td>

                            {{-- MASALAH --}}
                            <td class="px-6 py-4 max-w-xs">
                                <div class="text-gray-800 font-medium text-sm line-clamp-2" title="{{ $ticket->issue_description }}">
                                    {{ $ticket->issue_description }}
                                </div>
                                <div class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                    @if($ticket->maintenance_id) 
                                        <i class="fa-solid fa-robot"></i> Dari Patroli Rutin
                                    @else 
                                        <i class="fa-solid fa-user-pen"></i> Laporan Manual
                                    @endif
                                    &bull; {{ $ticket->created_at->diffForHumans() }}
                                </div>
                            </td>

                            {{-- PRIORITAS --}}
                            <td class="px-6 py-4">
                                <span class="{{ $prioBadge }} px-2.5 py-1 rounded text-[10px] font-bold uppercase border tracking-wider">
                                    {{ $ticket->priority }}
                                </span>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} px-3 py-1 rounded-full text-xs font-bold border border-opacity-20">
                                    <i class="fa-solid {{ $statusConfig['icon'] }}"></i> {{ $statusConfig['label'] }}
                                </span>
                            </td>

                            {{-- TEKNISI --}}
                            <td class="px-6 py-4">
                                @if($ticket->technician)
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs shadow-sm">
                                            {{ substr($ticket->technician->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-gray-700">{{ $ticket->technician->name }}</div>
                                            <div class="text-[10px] text-gray-400">Teknisi</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-xs bg-gray-50 px-2 py-1 rounded border border-gray-200">Belum Ditugaskan</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-6 py-4 text-center">
                                @if(!auth()->user()->isManajer() && ($ticket->status == 'open' || $ticket->status == 'handover'))
                                    <button onclick="openAssignModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}', {{ $ticket->technician_id ?? 'null' }}, '{{ $ticket->priority }}')" class="bg-blue-600 text-white hover:bg-blue-700 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm w-full">
                                        Tugaskan
                                    </button>
                                @elseif(!auth()->user()->isManajer() && $ticket->status == 'completed')
                                    <button onclick="openVerifyModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}')" class="bg-green-600 text-white hover:bg-green-700 px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm w-full flex items-center justify-center gap-1 animate-pulse">
                                        <i class="fa-solid fa-check-double"></i> Verifikasi
                                    </button>
                                @else
                                    <button onclick="showDetailModal({{ $ticket->id }})" class="text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-200 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition w-full">
                                        Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fa-solid fa-ticket text-3xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-gray-900 font-bold">Tidak ada tiket ditemukan</h3>
                                    <p class="text-sm">Coba ubah filter atau buat tiket baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $tickets->withQueryString()->links() }}
        </div>
    </div>

    {{-- ======================== MODALS ======================== --}}

    {{-- 1. CREATE MANUAL --}}
    <div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('createModal')"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                <div class="bg-white px-6 py-5 border-b flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-pen-to-square text-blue-600 mr-2"></i> Buat Tiket Manual</h3>
                </div>
                <form action="{{ route('admin.work-orders.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Aset Bermasalah</label>
                        <select name="asset_id" class="w-full border-2 border-gray-300 rounded-lg text-sm focus:ring-blue-500 pl-2 py-2" required>
                            <option value="">Pilih Aset</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }} - {{ $asset->location->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi Masalah</label>
                        <textarea name="issue_description" rows="3" class="w-full border-2 border-gray-300 rounded-lg text-sm focus:ring-blue-500 pl-2 py-2" required placeholder="Jelaskan kerusakan secara detail..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Prioritas</label>
                        <div class="flex gap-4">
                            <label class="flex items-center"><input type="radio" name="priority" value="low" class="mr-2 text-blue-600"> Low</label>
                            <label class="flex items-center"><input type="radio" name="priority" value="medium" checked class="mr-2 text-orange-600"> Medium</label>
                            <label class="flex items-center"><input type="radio" name="priority" value="high" class="mr-2 text-red-600"> High</label>
                        </div>
                    </div>
                    <div class="pt-4 flex justify-end gap-2">
                        <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 border rounded-lg text-sm font-medium hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md">Simpan Tiket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. ASSIGN TEKNISI --}}
    <div id="assignModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/40 transition-opacity" onclick="closeModal('assignModal')"></div>
            <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-200">

                {{-- Header --}}
                <div class="bg-slate-800 px-6 py-5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-user-gear text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white">Tugaskan Teknisi</h3>
                        <p class="text-xs text-blue-100">Tiket: <span id="assignTicketNo" class="font-mono font-bold text-white"></span></p>
                    </div>
                </div>

                <form id="assignForm" method="POST" class="p-6 space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Pilih Teknisi --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-user-tie text-blue-500 mr-1"></i> Pilih Teknisi
                        </label>
                        <div class="relative">
                            <select name="technician_id"
                                class="w-full appearance-none border border-gray-300 rounded-xl pl-4 pr-10 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm"
                                required>
                                <option value="">Pilih Teknisi</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        @if($technicians->isEmpty())
                            <p class="text-xs text-red-500 mt-1"><i class="fa-solid fa-circle-exclamation mr-1"></i>Belum ada teknisi terdaftar di sistem.</p>
                        @else
                            <p class="text-xs text-gray-400 mt-1">{{ $technicians->count() }} teknisi tersedia</p>
                        @endif
                    </div>

                    {{-- Prioritas --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fa-solid fa-flag text-orange-500 mr-1"></i> Prioritas Penanganan
                        </label>
                        <div class="grid grid-cols-3 gap-2" id="prioritySelector">
                            <label class="cursor-pointer">
                                <input type="radio" name="priority" value="low" class="sr-only peer">
                                <div class="peer-checked:bg-green-100 peer-checked:border-green-500 peer-checked:text-green-700 border-2 border-gray-200 rounded-xl p-2.5 text-center text-xs font-bold text-gray-500 hover:border-green-300 transition">
                                    <i class="fa-solid fa-circle-down block mb-1 text-base"></i> Low
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="priority" value="medium" class="sr-only peer" checked>
                                <div class="peer-checked:bg-orange-100 peer-checked:border-orange-500 peer-checked:text-orange-700 border-2 border-gray-200 rounded-xl p-2.5 text-center text-xs font-bold text-gray-500 hover:border-orange-300 transition">
                                    <i class="fa-solid fa-circle-minus block mb-1 text-base"></i> Medium
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="priority" value="high" class="sr-only peer">
                                <div class="peer-checked:bg-red-100 peer-checked:border-red-500 peer-checked:text-red-700 border-2 border-gray-200 rounded-xl p-2.5 text-center text-xs font-bold text-gray-500 hover:border-red-300 transition">
                                    <i class="fa-solid fa-circle-up block mb-1 text-base"></i> High
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                        <button type="button" onclick="closeModal('assignModal')"
                            class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md shadow-blue-600/30 flex items-center gap-2 transition">
                            <i class="fa-solid fa-paper-plane"></i> Tugaskan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- 3. VERIFY & DETAIL (Digabung biar simple) --}}
    <div id="verifyModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('verifyModal')"></div>
            <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all border border-gray-200">
                <div class="bg-green-600 px-6 py-4 flex justify-between items-center text-white">
                    <div>
                        <h3 class="font-bold text-lg" id="modalVerifyTitle">Detail Tiket Perbaikan</h3>
                        <p class="text-xs text-green-100 opacity-90">Review hasil pekerjaan teknisi sebelum menutup tiket.</p>
                    </div>
                </div>
                
                <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar" id="verifyContent">
                    <p class="text-center text-gray-500 py-10"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data...</p>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-100" id="verifyFooter">
                    </div>
            </div>
        </div>
    </div>

    <script>
        function confirmVerifyAll(event) {
            event.preventDefault();
            const form = event.target;
            
            Swal.fire({
                title: 'Verifikasi Semua Tiket?',
                text: "Semua tiket yang statusnya 'Selesai' akan langsung diverifikasi dan ditutup.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#16a34a', // green-600
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'Ya, Verifikasi Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function openCreateModal() { document.getElementById('createModal').classList.remove('hidden'); }
        
        function openAssignModal(id, ticketNo, currentTechId = null, currentPriority = 'medium') {
            document.getElementById('assignTicketNo').innerText = ticketNo;
            document.getElementById('assignForm').action = `/admin/work-orders/${id}/assign`;

            // Pre-select technician jika sudah ada
            const techSelect = document.querySelector('#assignForm select[name="technician_id"]');
            if (techSelect && currentTechId) {
                techSelect.value = currentTechId;
            } else if (techSelect) {
                techSelect.value = '';
            }

            // Pre-select priority (radio button)
            const prioValue = currentPriority || 'medium';
            const prioRadio = document.querySelector(`#assignForm input[name="priority"][value="${prioValue}"]`);
            if (prioRadio) prioRadio.checked = true;

            document.getElementById('assignModal').classList.remove('hidden');
        }

        async function openVerifyModal(id, ticketNo) {
            // Setup Modal untuk Mode Verifikasi
            setupModal(id, true);
        }

        async function showDetailModal(id) {
            // Setup Modal untuk Mode Read-Only (Detail)
            setupModal(id, false);
        }

        async function setupModal(id, isVerifyMode) {
            const modal = document.getElementById('verifyModal');
            const content = document.getElementById('verifyContent');
            const footer = document.getElementById('verifyFooter');
            const title = document.getElementById('modalVerifyTitle');
            const formAction = `/admin/work-orders/${id}/verify`;

            modal.classList.remove('hidden');
            content.innerHTML = '<p class="text-center text-gray-500 py-10"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data...</p>';
            
            // Ubah Header Warna
            const header = modal.querySelector('.bg-green-600');
            if(isVerifyMode) {
                header.classList.remove('bg-gray-800'); header.classList.add('bg-green-600');
                title.innerText = "Verifikasi Perbaikan";
            } else {
                header.classList.remove('bg-green-600'); header.classList.add('bg-gray-800');
                title.innerText = "Detail Tiket";
            }

            try {
                const res = await fetch(`/admin/work-orders/${id}`);
                const json = await res.json();
                const data = json.data;

                // Build photo HTML outside template literal to avoid nested backtick issues
                function renderPhotoGrid(urls) {
                    if (!urls || urls.length === 0) {
                        return '<div class="aspect-video bg-gray-100 rounded-lg flex items-center justify-center text-gray-300 border border-dashed border-gray-200"><i class="fa-solid fa-image text-2xl"></i></div>';
                    }
                    var html = '<div class="flex flex-wrap gap-2">';
                    urls.forEach(function(url) {
                        html += '<a href="' + url + '" target="_blank"><img src="' + url + '" class="h-20 w-20 object-cover rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:scale-105 transition cursor-pointer"></a>';
                    });
                    html += '</div>';
                    return html;
                }

                var completedEntry = (data.histories || []).find(function(h) { return h.action === 'completed'; });
                var laporanText = completedEntry && completedEntry.description ? completedEntry.description : 'Belum ada laporan dari teknisi.';
                var photosBeforeHtml = renderPhotoGrid(data.photos_before_urls);
                var photosAfterHtml = renderPhotoGrid(data.photos_after_urls);

                // Calculate duration
                function formatDuration(ms) {
                    if (ms <= 0) return '-';
                    var seconds = Math.floor(ms / 1000);
                    var minutes = Math.floor(seconds / 60);
                    var hours = Math.floor(minutes / 60);
                    var days = Math.floor(hours / 24);
                    hours = hours % 24;
                    minutes = minutes % 60;
                    var parts = [];
                    if (days > 0) parts.push(days + ' hari');
                    if (hours > 0) parts.push(hours + ' jam');
                    if (minutes > 0) parts.push(minutes + ' menit');
                    return parts.length > 0 ? parts.join(' ') : '< 1 menit';
                }

                function formatDate(dateStr) {
                    if (!dateStr) return '-';
                    var d = new Date(dateStr);
                    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                }

                var createdAt = new Date(data.created_at);
                var verifiedEntry = (data.histories || []).find(function(h) { return h.action === 'verified'; });
                var endDate = verifiedEntry ? new Date(verifiedEntry.created_at) : (completedEntry ? new Date(completedEntry.created_at) : null);
                var durationMs = endDate ? (endDate - createdAt) : (new Date() - createdAt);
                var durationText = formatDuration(durationMs);
                var durationLabel = verifiedEntry ? 'Terverifikasi' : (completedEntry ? 'Selesai dikerjakan' : 'Masih berjalan');
                var durationColor = verifiedEntry ? 'text-green-600' : (completedEntry ? 'text-blue-600' : 'text-orange-600');
                var durationIcon = verifiedEntry ? 'fa-check-double' : (completedEntry ? 'fa-clipboard-check' : 'fa-hourglass-half');

                // Build timeline HTML
                var timelineHtml = '<div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 mb-6 border border-blue-100">'
                    + '<div class="flex items-center gap-2 mb-3">'
                    + '<i class="fa-solid fa-clock-rotate-left text-blue-600"></i>'
                    + '<h4 class="text-sm font-bold text-gray-700">Durasi Penanganan</h4>'
                    + '</div>'
                    + '<div class="flex items-center justify-between">'
                    + '<div class="text-center flex-1">'
                    + '<p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Dibuat</p>'
                    + '<p class="text-xs font-semibold text-gray-700">' + formatDate(data.created_at) + '</p>'
                    + '</div>'
                    + '<div class="flex flex-col items-center flex-1">'
                    + '<div class="w-full flex items-center gap-1">'
                    + '<div class="flex-1 h-0.5 bg-blue-200"></div>'
                    + '<i class="fa-solid fa-arrow-right text-blue-300 text-[10px]"></i>'
                    + '<div class="flex-1 h-0.5 bg-blue-200"></div>'
                    + '</div>'
                    + '<div class="mt-1 px-3 py-1 bg-white rounded-full shadow-sm border border-blue-100">'
                    + '<span class="text-xs font-bold ' + durationColor + '"><i class="fa-solid fa-stopwatch mr-1"></i>' + durationText + '</span>'
                    + '</div>'
                    + '</div>'
                    + '<div class="text-center flex-1">'
                    + '<p class="text-[10px] uppercase font-bold text-gray-400 mb-1">' + durationLabel + '</p>'
                    + '<p class="text-xs font-semibold text-gray-700">' + (endDate ? formatDate(endDate) : '<span class=\'text-orange-500 italic\'>Belum selesai</span>') + '</p>'
                    + '</div>'
                    + '</div>'
                    + '</div>';

                var assetName = data.asset ? data.asset.name : (data.location ? data.location.name : 'Tidak diketahui');
                var assetLocation = data.asset ? (data.asset.location ? data.asset.location.name : '-') : (data.asset ? '-' : '<span class=\'text-orange-500 text-xs italic\'>Aset belum diidentifikasi</span>');

                content.innerHTML = '<div class="grid grid-cols-2 gap-6 mb-6">'
                    + '<div class="bg-gray-50 p-4 rounded-xl border border-gray-100">'
                    + '<p class="text-xs text-gray-500 uppercase font-bold mb-1">' + (data.asset ? 'Aset & Lokasi' : 'Lokasi Laporan') + '</p>'
                    + '<p class="font-bold text-gray-800">' + assetName + '</p>'
                    + '<p class="text-sm text-gray-600">' + assetLocation + '</p>'
                    + '</div>'
                    + '<div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-right">'
                    + '<p class="text-xs text-gray-500 uppercase font-bold mb-1">Teknisi Penanggung Jawab</p>'
                    + '<p class="font-bold text-gray-800">' + (data.technician ? data.technician.name : 'Belum Ada') + '</p>'
                    + '<p class="text-sm text-gray-600">' + data.ticket_number + '</p>'
                    + '</div>'
                    + '</div>'

                    + timelineHtml

                    + '<div class="mb-6">'
                    + '<h4 class="text-sm font-bold text-gray-700 mb-2">Masalah Dilaporkan</h4>'
                    + '<div class="bg-red-50 p-4 rounded-xl border border-red-100 text-red-800 text-sm">"' + data.issue_description + '"</div>'
                    + '</div>'

                    + '<div class="mb-6">'
                    + '<h4 class="text-sm font-bold text-gray-700 mb-2">Laporan Pengerjaan</h4>'
                    + '<div class="bg-white border border-gray-200 p-4 rounded-xl shadow-sm">'
                    + '<p class="text-sm text-gray-700 mb-3 italic">"' + laporanText + '"</p>'
                    + '<div class="grid grid-cols-2 gap-4 mt-4">'
                    + '<div>'
                    + '<p class="text-xs text-center mb-2 font-bold text-gray-400 uppercase">Foto Sebelum</p>'
                    + photosBeforeHtml
                    + '</div>'
                    + '<div>'
                    + '<p class="text-xs text-center mb-2 font-bold text-gray-400 uppercase">Foto Sesudah</p>'
                    + photosAfterHtml
                    + '</div>'
                    + '</div>'
                    + '</div>'
                    + '</div>';

                // Footer Logic
                if (isVerifyMode) {
                    var reopenAction = '/admin/work-orders/' + id + '/reopen';
                    footer.innerHTML = '<div class="w-full">'
                        + '<div id="reopenSection" style="display:none;" class="mb-3 w-full">'
                        + '<form action="' + reopenAction + '" method="POST" class="w-full">'
                        + '@csrf'
                        + '<label class="block text-xs font-bold text-red-600 mb-1"><i class="fa-solid fa-exclamation-triangle mr-1"></i>Alasan Penolakan / Re-open</label>'
                        + '<textarea name="rejection_note" rows="2" class="w-full border-2 border-red-300 rounded-lg text-sm px-3 py-2 focus:ring-red-500 focus:border-red-500 mb-2" placeholder="Jelaskan kenapa tiket ini perlu dikerjakan ulang..." required></textarea>'
                        + '<button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-bold shadow-md flex items-center justify-center gap-2">'
                        + '<i class="fa-solid fa-rotate-left"></i> Konfirmasi Re-open'
                        + '</button>'
                        + '</form>'
                        + '</div>'
                        + '<div class="flex items-center justify-between gap-3">'
                        + '<button type="button" onclick="document.getElementById(\'reopenSection\').style.display = document.getElementById(\'reopenSection\').style.display === \'none\' ? \'block\' : \'none\'" class="text-red-500 hover:text-red-700 text-sm font-bold flex items-center gap-1 border border-red-200 hover:bg-red-50 px-4 py-2.5 rounded-lg transition">'
                        + '<i class="fa-solid fa-rotate-left"></i> Tolak & Re-open'
                        + '</button>'
                        + '<div class="flex items-center gap-2">'
                        + '<button onclick="closeModal(\'verifyModal\')" class="text-gray-500 hover:text-gray-700 text-sm font-bold px-4 py-2.5">Batal</button>'
                        + '<form action="' + formAction + '" method="POST">'
                        + '@csrf'
                        + '<button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-lg shadow-green-600/30 flex items-center gap-2">'
                        + '<i class="fa-solid fa-check-double"></i> Setujui & Tutup'
                        + '</button>'
                        + '</form>'
                        + '</div>'
                        + '</div>'
                        + '</div>';
                } else {
                    footer.innerHTML = '<button onclick="closeModal(\'verifyModal\')" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm font-bold hover:bg-gray-50 w-full">Tutup</button>';
                }

            } catch(e) {
                content.innerHTML = '<p class="text-red-500 text-center py-4">Gagal memuat data.</p>';
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