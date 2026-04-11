@extends('layouts.user')

@section('title', 'Riwayat Laporan Saya')

@section('content')
<div class="max-w-7xl mx-auto pb-12 px-4 sm:px-6 lg:px-8 mt-6" x-data="{ showModal: false, selectedTicket: null }">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-5 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Riwayat Laporan Saya</h1>
            <p class="text-gray-500 text-sm mt-1.5 font-medium leading-relaxed max-w-2xl">Pantau status perbaikan aset atau ruangan yang telah Anda laporkan ke tim teknisi secara real-time.</p>
        </div>
        <div class="w-full md:w-auto shrink-0">
            <a href="{{ route('user.tickets.create') }}" class="w-full md:w-auto inline-flex justify-center items-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 transition-all duration-200 gap-2.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fa-solid fa-plus text-sm"></i> Buat Laporan Baru
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-10">
        {{-- Total --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex flex-col items-start gap-3 hover:shadow-md transition-shadow duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 border border-blue-100">
                <i class="fa-solid fa-ticket-simple text-xl"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Total Laporan</p>
                <p class="text-2xl font-black text-gray-900 leading-none">{{ $tickets->total() }}</p>
            </div>
        </div>
        {{-- Open (Menunggu) --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex flex-col items-start gap-3 hover:shadow-md transition-shadow duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 border border-amber-100">
                <i class="fa-solid fa-clock-rotate-left text-xl"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Menunggu</p>
                <p class="text-2xl font-black text-gray-900 leading-none">{{ $tickets->where('status', 'open')->count() }}</p> 
            </div>
        </div>
        {{-- In Progress (Diproses) --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex flex-col items-start gap-3 hover:shadow-md transition-shadow duration-200 group" title="Sedang dikerjakan oleh teknisi">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 border border-indigo-100">
                <i class="fa-solid fa-person-digging text-xl"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Diproses</p>
                <p class="text-2xl font-black text-gray-900 leading-none">{{ $tickets->whereIn('status', ['in_progress', 'pending_part', 'handover'])->count() }}</p> 
            </div>
        </div>
        {{-- Completed (Selesai) --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex flex-col items-start gap-3 hover:shadow-md transition-shadow duration-200 group">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300 border border-emerald-100">
                <i class="fa-solid fa-check-double text-xl"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 font-bold uppercase tracking-wider mb-1">Selesai</p>
                <p class="text-2xl font-black text-gray-900 leading-none">{{ $tickets->whereIn('status', ['completed', 'verified'])->count() }}</p> 
            </div>
        </div>
    </div>

    {{-- Ticket List (Cards Layout for User Dashboard) --}}
    @if($tickets->count() > 0)
        <div class="flex flex-col gap-4">
            @foreach($tickets as $ticket)
            <div class="bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-gray-200 hover:shadow-md transition-all duration-200 flex flex-col lg:flex-row gap-6 relative overflow-hidden group">
                
                {{-- Decorative Side Border --}}
                <div class="absolute left-0 top-0 bottom-0 w-1.5 
                    {{ $ticket->status == 'open' ? 'bg-amber-400' : 
                      (in_array($ticket->status, ['in_progress', 'pending_part', 'handover']) ? 'bg-indigo-400' : 'bg-emerald-400') }}">
                </div>

                {{-- KIRI: Info Aset & Tiket --}}
                <div class="flex items-start gap-4 lg:w-1/3 shrink-0">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-gray-50 border border-gray-200 flex-shrink-0 overflow-hidden flex items-center justify-center shadow-sm">
                        @if($ticket->asset && $ticket->asset->image)
                            <img src="{{ asset('storage/'.$ticket->asset->image) }}" class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid {{ $ticket->asset ? 'fa-box' : 'fa-layer-group' }} text-gray-300 text-2xl"></i>
                        @endif
                    </div>
                    <div class="flex flex-col min-w-0 pt-0.5">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-[10px] font-bold font-mono text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md border border-gray-200 tracking-wide">{{ $ticket->ticket_number }}</span>
                            <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md border
                                {{ $ticket->priority == 'high' ? 'bg-rose-50 text-rose-600 border-rose-200' : 
                                  ($ticket->priority == 'medium' ? 'bg-amber-50 text-amber-600 border-amber-200' : 
                                  'bg-emerald-50 text-emerald-600 border-emerald-200') }}">
                                {{ $ticket->priority }}
                            </span>
                        </div>
                        @if($ticket->asset)
                            <p class="font-bold text-gray-900 text-sm sm:text-base line-clamp-1" title="{{ $ticket->asset->name }}">{{ $ticket->asset->name }}</p>
                            <p class="text-[11px] text-gray-500 mt-1 line-clamp-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-location-dot text-gray-400"></i> {{ $ticket->asset->location->name ?? '-' }}
                            </p>
                        @else
                            <p class="font-bold text-gray-900 text-sm sm:text-base line-clamp-1">{{ $ticket->location->name ?? 'Lokasi tidak diketahui' }}</p>
                            <p class="text-[11px] text-amber-600 mt-1 line-clamp-1 flex items-center gap-1.5 font-medium bg-amber-50 px-2 py-0.5 rounded w-fit border border-amber-100">
                                <i class="fa-solid fa-triangle-exclamation"></i> Aset spesifik tidak dipilih
                            </p>
                        @endif
                    </div>
                </div>

                {{-- TENGAH: Deskripsi Masalah --}}
                <div class="lg:w-2/5 flex flex-col justify-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5"><i class="fa-solid fa-comment-dots mr-1"></i> Laporan Anda</p>
                    <p class="font-medium text-gray-700 text-sm line-clamp-2 md:line-clamp-3 leading-relaxed" title="{{ $ticket->issue_description }}">
                        "{{ $ticket->issue_description }}"
                    </p>
                </div>

                {{-- KANAN: Status, Teknisi, Aksi --}}
                <div class="lg:w-1/4 flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-center gap-4 lg:gap-3 border-t lg:border-t-0 lg:border-l border-gray-100 pt-4 lg:pt-0 lg:pl-6 w-full shrink-0">
                    
                    <div class="flex flex-col lg:items-end gap-2">
                        {{-- Badge Status --}}
                        @if($ticket->status == 'open')
                            <span class="bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg text-xs font-bold ring-1 ring-inset ring-amber-600/20 w-fit flex items-center gap-1.5 shadow-sm">
                                <i class="fa-solid fa-hourglass-half"></i> Menunggu Teknisi
                            </span>
                        @elseif(in_array($ticket->status, ['in_progress', 'pending_part', 'handover']))
                            <span class="bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-bold ring-1 ring-inset ring-indigo-600/20 w-fit flex items-center gap-1.5 shadow-sm">
                                <i class="fa-solid fa-person-digging fa-fade"></i> Sedang Diproses
                            </span>
                        @elseif(in_array($ticket->status, ['completed', 'verified']))
                            <span class="bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-lg text-xs font-bold ring-1 ring-inset ring-emerald-600/20 w-fit flex items-center gap-1.5 shadow-sm">
                                <i class="fa-solid fa-check-double"></i> Selesai
                            </span>
                        @else
                            <span class="bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-gray-200 w-fit flex items-center gap-1.5">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        @endif

                        {{-- Waktu --}}
                        <div class="text-left lg:text-right">
                            <p class="text-[11px] font-bold text-gray-600">{{ $ticket->created_at->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5"><i class="fa-regular fa-clock"></i> {{ $ticket->created_at->format('H:i') }} WITA</p>
                        </div>
                    </div>
                    
                    @php
                        // Data tracking untuk Alpine Modal
                        $completedHistory = $ticket->histories()->where('action', 'completed')->latest()->first();
                        $photoBeforeUrls = $ticket->photos_before_urls;
                        $photoAfterUrls = $ticket->photos_after_urls;
                        
                        $ticketData = [
                            'ticket_number' => $ticket->ticket_number,
                            'asset_name' => $ticket->asset ? $ticket->asset->name : ($ticket->location ? $ticket->location->name : 'Tidak diketahui'),
                            'location_name' => $ticket->asset ? ($ticket->asset->location->name ?? '-') : ($ticket->location ? $ticket->location->name : '-'),
                            'status' => $ticket->status,
                            'priority' => $ticket->priority,
                            'issue_description' => $ticket->issue_description,
                            'created_at' => $ticket->created_at->format('d M Y, H:i'),
                            'completed_date' => in_array($ticket->status, ['completed', 'verified']) ? $ticket->updated_at->format('d M Y, H:i') : null,
                            'photos_before_urls' => $photoBeforeUrls,
                            'photos_after_urls' => $photoAfterUrls,
                            'technician_name' => $ticket->technician ? $ticket->technician->name : 'Tim Teknisi',
                            'completed_note' => $completedHistory ? $completedHistory->description : 'Perbaikan telah diselesaikan oleh teknisi.',
                        ];
                    @endphp
                    
                    <button @click="selectedTicket = {{ json_encode($ticketData) }}; showModal = true" class="shrink-0 w-10 h-10 lg:w-auto lg:h-auto lg:px-4 lg:py-2 rounded-xl lg:rounded-lg bg-gray-50 text-gray-600 flex items-center justify-center lg:justify-between gap-2 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fa-solid fa-expand text-sm"></i>
                        <span class="hidden lg:inline text-xs font-bold">Lihat Detail</span>
                    </button>

                </div>

            </div>
            @endforeach
        </div>
        
        {{-- Pagination --}}
        <div class="mt-8">
            {{ $tickets->links() }}
        </div>

    @else
        {{-- Empty State (No Tickets) --}}
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 text-center py-20 px-5 mt-6 py-10">
            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-dashed border-gray-200 text-gray-300">
                <i class="fa-solid fa-clipboard-check text-4xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Laporan Aktif</h3>
            <p class="text-gray-500 mb-8 max-w-md mx-auto text-xs leading-relaxed">Anda belum pernah membuat laporan kerusakan aset. Jika menemukan kendala fasilitas atau peralatan, segera laporkan agar tim teknisi kami dapat menanganinya.</p>
        </div>
    @endif

    {{-- MODAL DETAIL TIKET (ALPINE JS) --}}
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        
        {{-- Backdrop Blur --}}
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showModal = false"></div>
        
        {{-- Modal Content --}}
        <div class="bg-white w-full max-w-lg sm:max-w-xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] sm:max-h-[85vh] relative z-10 transform transition-all"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95">
            
            {{-- Header Berwarna Gelap --}}
            <div class="relative bg-slate-900 text-white p-6 sm:p-8">
                <button @click="showModal = false" class="absolute top-4 right-4 sm:top-6 sm:right-6 w-8 h-8 bg-white/10 text-white/70 rounded-full flex items-center justify-center hover:bg-white/20 hover:text-white transition focus:outline-none">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="flex items-center gap-3">
                    <span class="bg-blue-500/30 border border-blue-400/30 text-blue-100 px-2.5 py-1 rounded font-mono text-[10px] font-bold shadow-sm tracking-widest" x-text="selectedTicket?.ticket_number"></span>
                    <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-1 rounded border" 
                          :class="{'bg-rose-500/20 text-rose-300 border-rose-500/30': selectedTicket?.priority === 'high', 'bg-amber-500/20 text-amber-300 border-amber-500/30': selectedTicket?.priority === 'medium', 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30': selectedTicket?.priority === 'low'}"
                          x-text="'PRIORITAS ' + selectedTicket?.priority"></span>
                </div>
                <h4 class="font-bold text-xl sm:text-2xl mt-4 leading-tight" x-text="selectedTicket?.asset_name"></h4>
                <div class="flex items-center gap-2 text-xs text-slate-300 mt-2.5">
                    <i class="fa-solid fa-location-dot text-rose-400"></i>
                    <span x-text="selectedTicket?.location_name"></span>
                </div>
            </div>

            {{-- Scrollable Body --}}
            <div class="p-6 sm:p-8 overflow-y-auto bg-gray-50 flex-1 custom-scrollbar">
                
                {{-- Laporan Awal --}}
                <div class="mb-6 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-400 rounded-l-2xl"></div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2.5 flex items-center gap-1.5"><i class="fa-solid fa-comment-dots text-gray-400"></i> Keluhan / Laporan Awal</p>
                    <p class="text-sm text-gray-800 font-medium leading-relaxed mb-3" x-text="`&quot;${selectedTicket?.issue_description}&quot;`"></p>
                    <div class="text-[10px] text-gray-400 flex items-center gap-1.5 font-medium bg-gray-50 px-2.5 py-1.5 rounded-lg w-fit border border-gray-100">
                        <i class="fa-regular fa-clock"></i> Dilaporkan: <span x-text="selectedTicket?.created_at"></span>
                    </div>

                    {{-- Foto Sebelum --}}
                    <template x-if="selectedTicket?.photos_before_urls?.length > 0">
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2.5">Lampiran Bukti Laporan</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(url, index) in selectedTicket?.photos_before_urls" :key="index">
                                    <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                                        <img :src="url" class="h-20 w-20 sm:h-24 sm:w-24 object-cover hover:opacity-90 hover:scale-105 transition-all cursor-pointer duration-300" @click="window.open(url, '_blank')">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Hasil Perbaikan (Hanya Muncul Jika Status Completed/Verified) --}}
                <template x-if="selectedTicket?.status === 'completed' || selectedTicket?.status === 'verified'">
                    <div class="bg-emerald-50 p-5 sm:p-6 rounded-2xl border border-emerald-200 shadow-sm relative overflow-hidden mt-6">
                        {{-- Decorative background icon --}}
                        <div class="absolute -right-4 -top-4 text-emerald-600/10 pointer-events-none"><i class="fa-solid fa-clipboard-check text-8xl"></i></div>
                        
                        <div class="relative z-10">
                            <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <i class="fa-solid fa-wrench"></i> Hasil Tindakan Teknisi
                            </p>
                            <div class="bg-white/60 p-4 rounded-xl border border-emerald-100/50 mb-4">
                                <p class="text-sm text-emerald-900 font-medium leading-relaxed italic" x-html="`&quot;${selectedTicket?.completed_note}&quot;`"></p>
                            </div>
                            
                            <div class="flex items-center gap-2 mb-4">
                                <div class="bg-white text-emerald-700 text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-sm border border-emerald-100 flex items-center gap-1.5">
                                    <i class="fa-regular fa-circle-check"></i> Selesai pada: <span x-text="selectedTicket?.completed_date"></span>
                                </div>
                            </div>

                            {{-- Foto Setelah Perbaikan --}}
                            <template x-if="selectedTicket?.photos_after_urls?.length > 0">
                                <div class="mt-4 pt-4 border-t border-emerald-200/50">
                                    <p class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider mb-2.5">Foto Bukti Perbaikan</p>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(url, index) in selectedTicket?.photos_after_urls" :key="index">
                                            <div class="rounded-xl overflow-hidden border border-emerald-200 shadow-sm bg-white">
                                                <img :src="url" class="h-20 w-20 sm:h-24 sm:w-24 object-cover hover:opacity-90 hover:scale-105 transition-all cursor-pointer duration-300" @click="window.open(url, '_blank')">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            
                            {{-- Jika tidak ada foto perbaikan --}}
                            <template x-if="!selectedTicket?.photos_after_urls || selectedTicket?.photos_after_urls?.length === 0">
                                <div class="mt-4 w-full py-4 bg-emerald-100/50 rounded-xl flex flex-col items-center justify-center text-emerald-600/60 border border-emerald-200/60 border-dashed">
                                    <i class="fa-solid fa-camera-slash text-xl mb-1.5 opacity-50"></i>
                                    <span class="text-[10px] font-bold tracking-wide">Teknisi tidak melampirkan foto hasil akhir</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                
                {{-- Info Proses (Jika Belum Selesai) --}}
                <template x-if="selectedTicket?.status !== 'completed' && selectedTicket?.status !== 'verified'">
                    <div class="mt-4 p-5 rounded-2xl border border-dashed border-gray-300 bg-transparent text-center flex flex-col items-center justify-center text-gray-500">
                        <i class="fa-solid fa-spinner fa-spin-pulse text-2xl text-blue-400 mb-2"></i>
                        <p class="text-sm font-bold text-gray-700">Dalam Proses Penanganan</p>
                        <p class="text-[11px] mt-1">Laporan Anda telah masuk dan sedang ditindaklanjuti oleh tim <span x-text="selectedTicket?.technician_name"></span>.</p>
                    </div>
                </template>

            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        [x-cloak] { display: none !important; }
    </style>
</div>
@endsection
