@extends('layouts.admin')

@section('title', 'Tiket Perbaikan')
@section('page-title', 'Manajemen Tiket Perbaikan')

@section('content')
<div class="container-fluid px-4 py-6 w-full mx-auto">
    
    {{-- Header Title (Mobile Visible, on Desktop usually handled by layout but good for context) --}}
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Tiket Perbaikan</h1>
        <p class="text-sm text-gray-500 mt-1">Pantau, tugaskan, dan verifikasi masalah aset atau ruangan di satu tempat.</p>
    </div>

    {{-- TABS FILTER (Statistik Cepat) --}}
    <div class="flex space-x-1 border-b border-gray-200 mb-6 overflow-x-auto custom-scrollbar pb-px">
        <a href="{{ route('admin.work-orders.index') }}" 
           class="flex items-center gap-2 px-5 py-3 font-semibold text-sm whitespace-nowrap border-b-2 transition-all duration-200 {{ !request('tab') ? 'text-blue-600 border-blue-600' : 'text-gray-500 border-transparent hover:text-gray-800 hover:border-gray-300' }}">
            <i class="fa-solid fa-layer-group {{ !request('tab') ? 'text-blue-500' : 'text-gray-400' }}"></i>
            Semua Tiket 
            <span class="py-0.5 px-2.5 rounded-full text-[11px] font-bold {{ !request('tab') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $counts['all'] }}
            </span>
        </a>
        <a href="{{ route('admin.work-orders.index', ['tab' => 'open']) }}" 
           class="flex items-center gap-2 px-5 py-3 font-semibold text-sm whitespace-nowrap border-b-2 transition-all duration-200 {{ request('tab') == 'open' ? 'text-red-600 border-red-600' : 'text-gray-500 border-transparent hover:text-gray-800 hover:border-gray-300' }}">
            <i class="fa-solid fa-envelope-open-text {{ request('tab') == 'open' ? 'text-red-500' : 'text-gray-400' }}"></i>
            Perlu Respon (Open)
            <span class="py-0.5 px-2.5 rounded-full text-[11px] font-bold {{ request('tab') == 'open' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $counts['open'] }}
            </span>
        </a>
        <a href="{{ route('admin.work-orders.index', ['tab' => 'verify']) }}" 
           class="flex items-center gap-2 px-5 py-3 font-semibold text-sm whitespace-nowrap border-b-2 transition-all duration-200 {{ request('tab') == 'verify' ? 'text-yellow-600 border-yellow-500' : 'text-gray-500 border-transparent hover:text-gray-800 hover:border-gray-300' }}">
            <i class="fa-solid fa-clipboard-check {{ request('tab') == 'verify' ? 'text-yellow-500' : 'text-gray-400' }}"></i>
            Butuh Verifikasi
            <span class="py-0.5 px-2.5 rounded-full text-[11px] font-bold {{ request('tab') == 'verify' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $counts['verify'] }}
            </span>
        </a>
    </div>

    {{-- TOOLBAR (Search & Create & Date Filter) --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        
        {{-- Form Pencarian & Filter --}}
        <form method="GET" class="flex flex-col md:flex-row items-start md:items-center gap-3 w-full xl:w-auto flex-1">
            @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif
            
            <div class="relative w-1/2 md:max-w-xs shrink-0">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Tiket/Aset..." class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-full text-sm shadow-sm transition-all outline-none">
            </div>
            
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full md:w-auto">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full sm:w-auto py-2.5 px-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm shadow-sm transition-all outline-none text-gray-600" title="Dari Tanggal">
                <span class="text-gray-400 font-medium hidden sm:block">-</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full sm:w-auto py-2.5 px-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm shadow-sm transition-all outline-none text-gray-600" title="Sampai Tanggal">
                
                <button type="submit" class="w-full sm:w-auto bg-white hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all border border-gray-300 shadow-sm flex items-center justify-center focus:ring-2 focus:ring-gray-200">
                    <i class="fa-solid fa-filter sm:hidden mr-2"></i> 
                    <span>Filter</span>
                </button>
            </div>
        </form>

        {{-- Tombol Aksi --}}
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 w-full xl:w-auto shrink-0">
            @if(auth()->user()->role === 'manajer')
            <a href="{{ route('admin.work-orders.export', request()->all()) }}" target="_blank" class="w-full sm:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2 focus:ring-2 focus:ring-gray-200">
                <i class="fa-regular fa-file-pdf text-red-500"></i> <span class="whitespace-nowrap">Export PDF</span>
            </a>
            @endif

            @if(!auth()->user()->isManajer())
                <button onclick="openCreateModal()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all shadow-sm hover:shadow flex items-center justify-center gap-2 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <i class="fa-solid fa-plus"></i> <span class="whitespace-nowrap">Buat Tiket</span>
                </button>
            @endif

            @if(request('tab') == 'verify' && !auth()->user()->isManajer())
                <form action="{{ route('admin.work-orders.verify-all') }}" method="POST" onsubmit="return confirmVerifyAll(event)" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" 
                            {{ $counts['verify'] == 0 ? 'disabled' : '' }}
                            class="w-full px-5 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center justify-center gap-2 focus:ring-2 focus:ring-green-500 focus:ring-offset-1 whitespace-nowrap
                                {{ $counts['verify'] > 0 ? 'bg-green-600 hover:bg-green-700 text-white shadow-sm hover:shadow animate-pulse' : 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed' }}">
                        <i class="fa-solid fa-check-double"></i> Verifikasi Semua ({{ $counts['verify'] }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- TABLE SECTION (FIXED SCROLL BUG) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 relative overflow-hidden">
        <div class="overflow-x-auto rounded-t-2xl custom-scrollbar">
            <table class="w-full text-sm text-left text-gray-600 min-w-[1000px]">
                <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] font-bold border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-12 text-center whitespace-nowrap">No</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Tiket & Aset</th>
                        <th scope="col" class="px-6 py-4 min-w-[250px] w-[30%]">Detail Masalah</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Prioritas</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Status</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Teknisi</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($tickets as $ticket)
                        @php
                            // Styling Prioritas
                            $prioBadge = match($ticket->priority) {
                                'high' => 'bg-red-50 text-red-700 ring-red-600/20',
                                'medium' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                default => 'bg-green-50 text-green-700 ring-green-600/20'
                            };

                            // Styling Status & Icon
                            $statusConfig = match($ticket->status) {
                                'open' => ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'ring' => 'ring-gray-500/20', 'icon' => 'fa-envelope-open', 'label' => 'Baru / Open'],
                                'in_progress' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'ring' => 'ring-blue-600/20', 'icon' => 'fa-spinner fa-spin', 'label' => 'Dikerjakan'],
                                'pending_part' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'ring' => 'ring-purple-600/20', 'icon' => 'fa-box-open', 'label' => 'Tunggu Part'],
                                'handover' => ['bg' => 'bg-pink-50', 'text' => 'text-pink-700', 'ring' => 'ring-pink-600/20', 'icon' => 'fa-handshake', 'label' => 'Operan Shift'],
                                'completed' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'ring' => 'ring-yellow-600/20', 'icon' => 'fa-clipboard-check', 'label' => 'Butuh Verifikasi'],
                                'verified' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-600/20', 'icon' => 'fa-check-double', 'label' => 'Selesai'],
                            };

                            $rowHighlight = ($ticket->status == 'completed' || $ticket->status == 'open') ? 'bg-yellow-50/30' : 'hover:bg-gray-50/80';
                        @endphp

                        <tr class="transition-colors duration-150 {{ $rowHighlight }}">
                            <td class="px-6 py-4 whitespace-nowrap text-center font-medium text-gray-400 text-xs">
                                {{ ($tickets->currentPage() - 1) * $tickets->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1.5">
                                    <div>
                                        <span class="font-mono font-bold text-blue-700 text-xs bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                                            {{ $ticket->ticket_number }}
                                        </span>
                                    </div>
                                    <div>
                                        @if($ticket->asset)
                                            <div class="font-bold text-gray-900 text-sm truncate max-w-[200px]" title="{{ $ticket->asset->name }}">{{ $ticket->asset->name }}</div>
                                            <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5">
                                                <i class="fa-solid fa-location-dot text-gray-400"></i> <span class="truncate max-w-[180px]">{{ $ticket->asset->location->name ?? '-' }}</span>
                                            </div>
                                        @else
                                            <div class="font-bold text-gray-900 text-sm truncate max-w-[200px]">{{ $ticket->location->name ?? 'Lokasi tidak diketahui' }}</div>
                                            <div class="text-[11px] text-orange-500 mt-0.5 flex items-center gap-1.5">
                                                <i class="fa-solid fa-cube"></i> Aset belum diidentifikasi
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-gray-800 font-medium text-sm line-clamp-2 leading-relaxed" title="{{ $ticket->issue_description }}">
                                    {{ $ticket->issue_description }}
                                </div>
                                <div class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1.5">
                                    @if($ticket->maintenance_id) 
                                        <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-semibold whitespace-nowrap"><i class="fa-solid fa-robot mr-1"></i> Patroli Rutin</span>
                                    @else 
                                        <span class="bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-semibold whitespace-nowrap"><i class="fa-solid fa-user-pen mr-1"></i> Laporan Manual</span>
                                    @endif
                                    <span class="whitespace-nowrap">&bull; {{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset {{ $prioBadge }}">
                                    {{ $ticket->priority }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold ring-1 ring-inset {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} {{ $statusConfig['ring'] }}">
                                    <i class="fa-solid {{ $statusConfig['icon'] }}"></i> {{ $statusConfig['label'] }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($ticket->technician)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs font-bold shadow-sm shrink-0">
                                            {{ substr($ticket->technician->name, 0, 1) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-900 truncate max-w-[120px]">{{ $ticket->technician->name }}</span>
                                            <span class="text-[10px] text-gray-500">Teknisi</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-[11px] bg-gray-50 px-2 py-1 rounded border border-gray-200">Belum Ditugaskan</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if(!auth()->user()->isManajer() && ($ticket->status == 'open' || $ticket->status == 'handover'))
                                    <button onclick="openAssignModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}', {{ $ticket->technician_id ?? 'null' }}, '{{ $ticket->priority }}')" class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-1.5 rounded-lg text-xs font-bold transition shadow-sm w-full focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:outline-none">
                                        Tugaskan
                                    </button>
                                @elseif(!auth()->user()->isManajer() && $ticket->status == 'completed')
                                    <button onclick="openVerifyModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}')" class="bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-1.5 rounded-lg text-xs font-bold transition shadow-sm w-full flex items-center justify-center gap-1.5 animate-pulse focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 focus:outline-none">
                                        <i class="fa-solid fa-check-double"></i> Verifikasi
                                    </button>
                                @else
                                    <button onclick="showDetailModal({{ $ticket->id }})" class="text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 hover:text-blue-600 font-semibold text-xs px-4 py-1.5 rounded-lg transition w-full shadow-sm focus:ring-2 focus:ring-gray-200 focus:outline-none">
                                        Lihat Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-ticket text-2xl text-gray-300"></i>
                                    </div>
                                    <h3 class="text-gray-900 font-bold text-base mb-1">Tidak ada tiket ditemukan</h3>
                                    <p class="text-sm">Coba ubah filter pencarian atau buat tiket perbaikan baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($tickets->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
            {{ $tickets->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- ======================== MODALS ======================== --}}

    {{-- 1. CREATE MANUAL --}}
    <div id="createModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('createModal')"></div>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fa-solid fa-pen-to-square"></i></span>
                        Buat Tiket Manual
                    </h3>
                    <button type="button" onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600 transition focus:outline-none">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                
                <form action="{{ route('admin.work-orders.store') }}" method="POST" class="px-6 py-5 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Aset Bermasalah <span class="text-red-500">*</span></label>
                        <select name="asset_id" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition" required>
                            <option value="">— Pilih Aset —</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }} - {{ $asset->location->name ?? 'Tanpa Lokasi' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi Masalah <span class="text-red-500">*</span></label>
                        <textarea name="issue_description" rows="3" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition resize-none" required placeholder="Jelaskan kerusakan secara detail..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prioritas Laporan</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer group">
                                <input type="radio" name="priority" value="low" class="peer sr-only">
                                <div class="p-2.5 border border-gray-200 rounded-xl text-center peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:ring-1 peer-checked:ring-green-500 transition hover:bg-gray-50">
                                    <span class="text-xs font-bold text-gray-600 peer-checked:text-green-700">Low</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="priority" value="medium" checked class="peer sr-only">
                                <div class="p-2.5 border border-gray-200 rounded-xl text-center peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:ring-1 peer-checked:ring-orange-500 transition hover:bg-gray-50">
                                    <span class="text-xs font-bold text-gray-600 peer-checked:text-orange-700">Medium</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="priority" value="high" class="peer sr-only">
                                <div class="p-2.5 border border-gray-200 rounded-xl text-center peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:ring-1 peer-checked:ring-red-500 transition hover:bg-gray-50">
                                    <span class="text-xs font-bold text-gray-600 peer-checked:text-red-700">High</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="pt-4 mt-2 flex justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeModal('createModal')" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition focus:outline-none">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i> Simpan Tiket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. ASSIGN TEKNISI --}}
    <div id="assignModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('assignModal')"></div>
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                
                {{-- Header --}}
                <div class="bg-slate-800 px-6 py-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/10">
                            <i class="fa-solid fa-user-gear text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight">Tugaskan Teknisi</h3>
                            <p class="text-xs text-blue-200 mt-0.5">ID Tiket: <span id="assignTicketNo" class="font-mono font-bold text-white bg-white/20 px-1.5 py-0.5 rounded ml-1"></span></p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal('assignModal')" class="text-gray-400 hover:text-white transition focus:outline-none">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <form id="assignForm" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Pilih Teknisi --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-user-tie text-gray-400"></i> Pilih Teknisi
                        </label>
                        <div class="relative">
                            <select name="technician_id" class="w-full appearance-none border border-gray-300 rounded-xl pl-4 pr-10 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm outline-none" required>
                                <option value="">— Pilih Teknisi —</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                        @if($technicians->isEmpty())
                            <p class="text-xs text-red-500 mt-1.5 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i>Belum ada teknisi terdaftar di sistem.</p>
                        @else
                            <p class="text-[11px] text-gray-500 mt-1.5">{{ $technicians->count() }} teknisi tersedia</p>
                        @endif
                    </div>

                    {{-- Prioritas --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-flag text-gray-400"></i> Prioritas Penanganan
                        </label>
                        <div class="grid grid-cols-3 gap-3" id="prioritySelector">
                            <label class="cursor-pointer group">
                                <input type="radio" name="priority" value="low" class="sr-only peer">
                                <div class="peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:ring-1 peer-checked:ring-green-500 border border-gray-200 rounded-xl p-2.5 text-center transition hover:bg-gray-50 shadow-sm">
                                    <i class="fa-solid fa-circle-down block mb-1 text-base text-gray-400 peer-checked:text-green-600 group-hover:text-green-500 transition-colors"></i> 
                                    <span class="text-xs font-bold text-gray-600 peer-checked:text-green-700">Low</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="priority" value="medium" class="sr-only peer" checked>
                                <div class="peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:ring-1 peer-checked:ring-orange-500 border border-gray-200 rounded-xl p-2.5 text-center transition hover:bg-gray-50 shadow-sm">
                                    <i class="fa-solid fa-circle-minus block mb-1 text-base text-gray-400 peer-checked:text-orange-600 group-hover:text-orange-500 transition-colors"></i> 
                                    <span class="text-xs font-bold text-gray-600 peer-checked:text-orange-700">Medium</span>
                                </div>
                            </label>
                            <label class="cursor-pointer group">
                                <input type="radio" name="priority" value="high" class="sr-only peer">
                                <div class="peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:ring-1 peer-checked:ring-red-500 border border-gray-200 rounded-xl p-2.5 text-center transition hover:bg-gray-50 shadow-sm">
                                    <i class="fa-solid fa-circle-up block mb-1 text-base text-gray-400 peer-checked:text-red-600 group-hover:text-red-500 transition-colors"></i> 
                                    <span class="text-xs font-bold text-gray-600 peer-checked:text-red-700">High</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" onclick="closeModal('assignModal')" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition focus:outline-none">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-sm flex items-center gap-2 transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:outline-none">
                            <i class="fa-solid fa-paper-plane"></i> Tugaskan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- 3. VERIFY & DETAIL (Digabung) --}}
    <div id="verifyModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('verifyModal')"></div>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-200">
                <div class="bg-emerald-600 px-6 py-4 flex justify-between items-center text-white" id="modalVerifyHeader">
                    <div>
                        <h3 class="font-bold text-lg flex items-center gap-2" id="modalVerifyTitle">
                            <i class="fa-solid fa-clipboard-check"></i> Detail Tiket Perbaikan
                        </h3>
                        <p class="text-xs text-white/80 mt-0.5" id="modalVerifySubtitle">Review hasil pekerjaan teknisi sebelum menutup tiket.</p>
                    </div>
                    <button type="button" onclick="closeModal('verifyModal')" class="text-white/70 hover:text-white transition focus:outline-none">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                
                <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar" id="verifyContent">
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl mb-3 text-blue-500"></i>
                        <p class="text-sm font-medium">Memuat detail tiket...</p>
                    </div>
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
                text: "Semua tiket yang statusnya 'Selesai' akan langsung diverifikasi dan ditutup permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#059669', // emerald-600
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Verifikasi Semua!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'rounded-xl',
                    cancelButton: 'rounded-xl'
                }
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

            // Pre-select technician
            const techSelect = document.querySelector('#assignForm select[name="technician_id"]');
            if (techSelect && currentTechId) {
                techSelect.value = currentTechId;
            } else if (techSelect) {
                techSelect.value = '';
            }

            // Pre-select priority
            const prioValue = currentPriority || 'medium';
            const prioRadio = document.querySelector(`#assignForm input[name="priority"][value="${prioValue}"]`);
            if (prioRadio) prioRadio.checked = true;

            document.getElementById('assignModal').classList.remove('hidden');
        }

        async function openVerifyModal(id, ticketNo) {
            setupModal(id, true);
        }

        async function showDetailModal(id) {
            setupModal(id, false);
        }

        async function setupModal(id, isVerifyMode) {
            const modal = document.getElementById('verifyModal');
            const content = document.getElementById('verifyContent');
            const footer = document.getElementById('verifyFooter');
            const title = document.getElementById('modalVerifyTitle');
            const subtitle = document.getElementById('modalVerifySubtitle');
            const header = document.getElementById('modalVerifyHeader');
            const formAction = `/admin/work-orders/${id}/verify`;

            modal.classList.remove('hidden');
            content.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-gray-400"><i class="fa-solid fa-circle-notch fa-spin text-3xl mb-3 text-blue-500"></i><p class="text-sm font-medium">Memuat detail tiket...</p></div>';
            
            // Atur Warna Header berdasarkan mode
            if(isVerifyMode) {
                header.className = 'bg-emerald-600 px-6 py-5 flex justify-between items-start sm:items-center text-white';
                title.innerHTML = '<i class="fa-solid fa-clipboard-check"></i> Verifikasi Perbaikan';
                subtitle.innerText = 'Review laporan dan bukti foto sebelum tiket ditutup secara permanen.';
            } else {
                header.className = 'bg-slate-800 px-6 py-5 flex justify-between items-start sm:items-center text-white';
                title.innerHTML = '<i class="fa-solid fa-file-lines"></i> Detail Tiket Laporan';
                subtitle.innerText = 'Informasi lengkap terkait tiket kerusakan aset atau lokasi.';
            }

            try {
                const res = await fetch(`/admin/work-orders/${id}`);
                const json = await res.json();
                const data = json.data;

                // Helper: Bikin Grid Foto yang elegan
                function renderPhotoGrid(urls, placeholderText) {
                    if (!urls || urls.length === 0) {
                        return '<div class="h-24 w-full bg-gray-50 rounded-xl flex flex-col items-center justify-center text-gray-400 border border-dashed border-gray-300"><i class="fa-solid fa-camera-slash text-xl mb-1.5 opacity-50"></i><span class="text-[10px] font-medium text-center leading-tight px-2">' + placeholderText + '</span></div>';
                    }
                    var html = '<div class="grid grid-cols-2 sm:grid-cols-3 gap-3">';
                    urls.forEach(function(url) {
                        html += '<a href="' + url + '" target="_blank" class="block aspect-square overflow-hidden rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-blue-300 transition-all group">';
                        html += '<img src="' + url + '" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">';
                        html += '</a>';
                    });
                    html += '</div>';
                    return html;
                }

                var completedEntry = (data.histories || []).find(function(h) { return h.action === 'completed'; });
                var laporanText = completedEntry && completedEntry.description ? completedEntry.description : 'Belum ada catatan atau laporan akhir dari teknisi.';
                
                var initialPhotoHtml = renderPhotoGrid(data.initial_photo_url ? [data.initial_photo_url] : [], 'Pelapor tidak melampirkan foto bukti kerusakan');
                var photosAfterHtml = renderPhotoGrid(data.photos_after_urls, 'Teknisi belum melampirkan foto hasil perbaikan');

                // Helper: Hitung Durasi
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
                    if (minutes > 0 && days === 0) parts.push(minutes + ' mnt'); // hide minutes if > 1 day to keep clean
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
                var durationLabel = verifiedEntry ? 'Selesai & Diverifikasi' : (completedEntry ? 'Selesai Dikerjakan' : 'Masih Berjalan (Open)');
                var durationColor = verifiedEntry ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : (completedEntry ? 'text-blue-600 bg-blue-50 border-blue-200' : 'text-orange-600 bg-orange-50 border-orange-200');
                var durationIcon = verifiedEntry ? 'fa-check-double' : (completedEntry ? 'fa-clipboard-check' : 'fa-spinner fa-spin');

                // Build Timeline Tracker HTML (Modern look)
                var timelineHtml = 
                      '<div class="mb-8">'
                    +   '<h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fa-solid fa-timeline text-gray-400"></i> Linimasa Penanganan</h4>'
                    +   '<div class="relative flex items-center justify-between w-full">'
                    +     '<div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-200 rounded-full z-0"></div>'
                    +     '<div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-blue-500 rounded-full z-0 transition-all" style="width: ' + (verifiedEntry ? '100%' : (completedEntry ? '50%' : '0%')) + '"></div>'
                    
                    +     '<div class="relative z-10 flex flex-col items-center gap-2">'
                    +       '<div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-md ring-4 ring-white"><i class="fa-solid fa-flag text-xs"></i></div>'
                    +       '<div class="text-center"><p class="text-[10px] font-bold text-gray-800 uppercase tracking-wide">Dibuat</p><p class="text-[10px] text-gray-500 mt-0.5">' + formatDate(data.created_at) + '</p></div>'
                    +     '</div>'

                    +     '<div class="relative z-10 flex flex-col items-center gap-2 -mt-8">' // lifted up slightly for duration badge
                    +       '<div class="px-3 py-1 rounded-full border text-[11px] font-bold shadow-sm whitespace-nowrap ' + durationColor + '"><i class="fa-solid fa-stopwatch mr-1.5"></i>' + durationText + '</div>'
                    +     '</div>'

                    +     '<div class="relative z-10 flex flex-col items-center gap-2">'
                    +       '<div class="w-8 h-8 rounded-full flex items-center justify-center shadow-md ring-4 ring-white ' + (endDate ? (verifiedEntry ? 'bg-emerald-500 text-white' : 'bg-blue-500 text-white') : 'bg-gray-200 text-gray-400') + '"><i class="fa-solid ' + durationIcon + ' text-xs"></i></div>'
                    +       '<div class="text-center"><p class="text-[10px] font-bold text-gray-800 uppercase tracking-wide">' + durationLabel + '</p><p class="text-[10px] text-gray-500 mt-0.5">' + (endDate ? formatDate(endDate) : 'Menunggu...') + '</p></div>'
                    +     '</div>'
                    +   '</div>'
                    + '</div>';

                var assetName = data.asset ? data.asset.name : (data.location ? data.location.name : 'Tidak diketahui');
                var assetLocation = data.asset ? (data.asset.location ? data.asset.location.name : '-') : (data.asset ? '-' : '<span class="text-orange-500 text-[11px] font-medium bg-orange-50 px-1.5 py-0.5 rounded border border-orange-100"><i class="fa-solid fa-cube mr-1"></i> Aset tidak spesifik</span>');

                // Content Assembly
                content.innerHTML = 
                      '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">'
                    +   '<div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200">'
                    +     '<p class="text-[10px] text-gray-500 uppercase tracking-wider font-bold mb-1.5 flex items-center gap-1.5"><i class="fa-solid fa-box text-gray-400"></i> ' + (data.asset ? 'Aset & Lokasi' : 'Lokasi Laporan') + '</p>'
                    +     '<p class="font-bold text-gray-900 text-sm mb-0.5">' + assetName + '</p>'
                    +     '<p class="text-xs text-gray-600">' + assetLocation + '</p>'
                    +   '</div>'
                    +   '<div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">'
                    +     '<p class="text-[10px] text-blue-600 uppercase tracking-wider font-bold mb-1.5 flex items-center gap-1.5"><i class="fa-solid fa-user-gear text-blue-400"></i> Teknisi Penanggung Jawab</p>'
                    +     '<p class="font-bold text-gray-900 text-sm mb-0.5">' + (data.technician ? data.technician.name : '<span class="text-gray-500 italic">Belum Ditugaskan</span>') + '</p>'
                    +     '<p class="text-xs font-mono font-bold text-blue-700 bg-white inline-block px-1.5 py-0.5 rounded border border-blue-200 mt-1">' + data.ticket_number + '</p>'
                    +   '</div>'
                    + '</div>'

                    + timelineHtml

                    + '<div class="mb-8">'
                    +   '<h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-red-500"></i> Masalah Dilaporkan</h4>'
                    +   '<div class="bg-red-50/50 p-4 rounded-xl border border-red-100 text-red-900 text-sm leading-relaxed">' + data.issue_description + '</div>'
                    + '</div>'

                    + '<div>'
                    +   '<h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fa-solid fa-clipboard-list text-emerald-500"></i> Laporan Pengerjaan & Bukti</h4>'
                    +   '<div class="bg-white border border-gray-200 p-5 rounded-xl shadow-sm">'
                    +     '<div class="mb-5">'
                    +       '<p class="text-[10px] text-gray-500 uppercase tracking-wider font-bold mb-2">Catatan Teknisi:</p>'
                    +       '<div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-sm text-gray-700 italic border-l-4 border-l-blue-400">"' + laporanText + '"</div>'
                    +     '</div>'
                    +     '<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-5 border-t border-gray-100">'
                    +       '<div>'
                    +         '<p class="text-[10px] text-center mb-2.5 font-bold text-gray-500 uppercase tracking-wider">Kondisi Awal (Bukti Laporan)</p>'
                    +         initialPhotoHtml
                    +       '</div>'
                    +       '<div>'
                    +         '<p class="text-[10px] text-center mb-2.5 font-bold text-gray-500 uppercase tracking-wider">Kondisi Akhir (Selesai Diperbaiki)</p>'
                    +         photosAfterHtml
                    +       '</div>'
                    +     '</div>'
                    +   '</div>'
                    + '</div>';

                // Footer Logic
                if (isVerifyMode) {
                    var reopenAction = '/admin/work-orders/' + id + '/reopen';
                    footer.innerHTML = 
                          '<div class="w-full">'
                        +   '<div id="reopenSection" style="display:none;" class="mb-4 w-full bg-red-50 p-4 rounded-xl border border-red-200">'
                        +     '<form action="' + reopenAction + '" method="POST" class="w-full">'
                        +       '@csrf'
                        +       '<label class="block text-xs font-bold text-red-700 mb-1.5"><i class="fa-solid fa-circle-exclamation mr-1"></i> Alasan Penolakan / Re-open</label>'
                        +       '<textarea name="rejection_note" rows="2" class="w-full border border-red-300 rounded-xl text-sm px-3 py-2 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 mb-3 outline-none shadow-sm resize-none" placeholder="Jelaskan instruksi kenapa tiket ini perlu dikerjakan ulang..." required></textarea>'
                        +       '<div class="flex justify-end gap-2">'
                        +         '<button type="button" onclick="document.getElementById(\'reopenSection\').style.display = \'none\'" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-600 hover:bg-gray-50 transition">Batal Penolakan</button>'
                        +         '<button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center justify-center gap-2 transition">'
                        +           '<i class="fa-solid fa-rotate-left"></i> Konfirmasi Re-open'
                        +         '</button>'
                        +       '</div>'
                        +     '</form>'
                        +   '</div>'
                        
                        +   '<div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3">'
                        +     '<button type="button" onclick="document.getElementById(\'reopenSection\').style.display = document.getElementById(\'reopenSection\').style.display === \'none\' ? \'block\' : \'none\'" class="w-full sm:w-auto text-red-600 hover:text-red-800 text-sm font-bold flex items-center justify-center gap-1.5 border border-red-200 bg-white hover:bg-red-50 px-5 py-2.5 rounded-xl transition shadow-sm">'
                        +       '<i class="fa-solid fa-arrow-rotate-left"></i> Tolak & Kembalikan'
                        +     '</button>'
                        +     '<div class="flex items-center gap-3 w-full sm:w-auto">'
                        +       '<button type="button" onclick="closeModal(\'verifyModal\')" class="w-full sm:w-auto text-gray-500 bg-white border border-gray-300 hover:bg-gray-50 text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-sm">Batal</button>'
                        +       '<form action="' + formAction + '" method="POST" class="w-full sm:w-auto">'
                        +         '@csrf'
                        +         '<button type="submit" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm shadow-emerald-600/20 flex items-center justify-center gap-2 transition focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">'
                        +           '<i class="fa-solid fa-check-double"></i> Setujui & Tutup Tiket'
                        +         '</button>'
                        +       '</form>'
                        +     '</div>'
                        +   '</div>'
                        + '</div>';
                } else {
                    footer.innerHTML = '<div class="w-full flex justify-end"><button onclick="closeModal(\'verifyModal\')" class="bg-white border border-gray-300 text-gray-700 px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-gray-50 transition shadow-sm focus:outline-none">Tutup Detail</button></div>';
                }

            } catch(e) {
                content.innerHTML = '<div class="py-10 text-center"><div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100 text-red-500 mb-3"><i class="fa-solid fa-triangle-exclamation text-xl"></i></div><p class="text-red-600 font-medium">Gagal memuat data tiket. Silakan coba lagi.</p></div>';
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>
@endsection