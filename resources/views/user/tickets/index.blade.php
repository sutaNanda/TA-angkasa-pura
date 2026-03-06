@extends('layouts.user')

@section('title', 'Riwayat Laporan Saya')

@section('content')
<div class="max-w-7xl mx-auto pb-10" x-data="{ showModal: false, selectedTicket: null }">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 tracking-tight">Riwayat Laporan</h1>
            <p class="text-gray-500 text-sm mt-1 font-medium">Pantau status perbaikan aset yang Anda laporkan secara real-time.</p>
        </div>
        {{-- Diperbaiki: Tombol sekarang muncul di HP (Full width) dan menyesuaikan di Laptop --}}
        <a href="{{ route('user.tickets.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg transition gap-2">
            <i class="fa-solid fa-plus"></i> Buat Laporan Baru
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        {{-- Total --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center md:items-start gap-3 text-center md:text-left hover:shadow-md transition-shadow duration-200">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-ticket text-base"></i>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Total Tiket</p>
                <p class="text-xl font-black text-gray-900">{{ $tickets->total() }}</p>
            </div>
        </div>
        {{-- Open (Menunggu) --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center md:items-start gap-3 text-center md:text-left hover:shadow-md transition-shadow duration-200">
            <div class="w-10 h-10 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-clock-rotate-left text-base"></i>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Menunggu</p>
                <p class="text-xl font-black text-gray-900">{{ $tickets->where('status', 'open')->count() }}</p> 
            </div>
        </div>
        {{-- In Progress (Diproses) --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center md:items-start gap-3 text-center md:text-left hover:shadow-md transition-shadow duration-200" title="Termasuk status in_progress">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-person-digging text-base"></i>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Diproses</p>
                <p class="text-xl font-black text-gray-900">{{ $tickets->where('status', 'in_progress')->count() }}</p> 
            </div>
        </div>
        {{-- Completed (Selesai) --}}
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center md:items-start gap-3 text-center md:text-left hover:shadow-md transition-shadow duration-200">
            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-check-double text-base"></i>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Selesai</p>
                <p class="text-xl font-black text-gray-900">{{ $tickets->where('status', 'completed')->count() }}</p> 
            </div>
        </div>
    </div>

    {{-- Ticket List (Diubah menjadi Responsive Flex-List pengganti Table) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        @if($tickets->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($tickets as $ticket)
                <div class="p-4 md:p-5 hover:bg-blue-50/30 transition grid grid-cols-1 md:grid-cols-12 gap-4 items-start md:items-center">
                    
                    {{-- KIRI: Info Aset (Cols 1-4) --}}
                    <div class="md:col-span-4 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                            @if($ticket->asset && $ticket->asset->image)
                                <img src="{{ asset('storage/'.$ticket->asset->image) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid {{ $ticket->asset ? 'fa-cube' : 'fa-location-dot' }} text-gray-400 text-lg"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            @if($ticket->asset)
                                <p class="font-bold text-gray-900 text-sm line-clamp-1" title="{{ $ticket->asset->name }}">{{ $ticket->asset->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                                    <i class="fa-solid fa-location-dot text-blue-500 mr-1"></i> {{ $ticket->asset->location->name ?? '-' }}
                                </p>
                            @else
                                <p class="font-bold text-gray-900 text-sm line-clamp-1">{{ $ticket->location->name ?? 'Lokasi tidak diketahui' }}</p>
                                <p class="text-xs text-orange-500 mt-0.5 line-clamp-1">
                                    <i class="fa-solid fa-cube mr-1"></i> Aset belum diidentifikasi
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- TENGAH: Info Laporan (Cols 5-9) --}}
                    <div class="md:col-span-5">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-mono text-gray-500 bg-gray-100 px-2 py-0.5 rounded border">{{ $ticket->ticket_number }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $ticket->priority == 'high' ? 'text-red-500' : ($ticket->priority == 'medium' ? 'text-orange-500' : 'text-green-500') }}">
                                • {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                        <p class="font-medium text-gray-800 text-sm line-clamp-2 md:line-clamp-1" title="{{ $ticket->issue_description }}">{{ $ticket->issue_description }}</p>
                    </div>

                    {{-- KANAN: Status & Tanggal (Cols 10-12) --}}
                    <div class="md:col-span-3 flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-2 border-t md:border-t-0 border-gray-100 pt-3 md:pt-0 w-full md:w-auto">
                        
                        {{-- Badge Status --}}
                        @if($ticket->status == 'open')
                            <span class="bg-yellow-50 text-yellow-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-yellow-200 w-fit">
                                <i class="fa-solid fa-hourglass-half mr-1"></i> Menunggu
                            </span>
                        @elseif($ticket->status == 'in_progress')
                            <span class="bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-blue-200 w-fit">
                                <i class="fa-solid fa-person-digging mr-1"></i> Diproses
                            </span>
                        @elseif($ticket->status == 'completed')
                            <span class="bg-green-50 text-green-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-green-200 w-fit">
                                <i class="fa-solid fa-check-circle mr-1"></i> Selesai
                            </span>
                        @else
                            <span class="bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-gray-200 w-fit">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        @endif

                        {{-- Waktu --}}
                        <div class="text-right flex items-center gap-3">
                            <div>
                                <p class="text-[11px] font-bold text-gray-600">{{ $ticket->created_at->format('d M Y') }}</p>
                                <p class="text-[10px] text-gray-400">{{ $ticket->created_at->format('H:i') }}</p>
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
                                    'completed_date' => $ticket->status == 'completed' ? $ticket->updated_at->format('d M Y, H:i') : null,
                                    'photos_after_urls' => $photoAfterUrls,
                                    'technician_name' => $ticket->technician ? $ticket->technician->name : 'Teknisi',
                                    'completed_note' => $completedHistory ? $completedHistory->description : 'Perbaikan telah diselesaikan oleh teknisi.',
                                ];
                            @endphp
                            <button @click="selectedTicket = {{ json_encode($ticketData) }}; showModal = true" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition shadow-sm border border-blue-100">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
            
            {{-- Pagination --}}
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $tickets->links() }}
            </div>

        @else
            {{-- Empty State --}}
            <div class="text-center py-16 px-4">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 border border-gray-100 shadow-inner">
                    <i class="fa-regular fa-clipboard text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800">Belum Ada Laporan</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto text-sm mt-2">Anda belum pernah membuat laporan kerusakan aset. Jika menemukan masalah, segera laporkan agar tim Teknisi dapat menanganinya.</p>
                <a href="{{ route('user.tickets.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-md">
                    <i class="fa-solid fa-plus"></i> Buat Laporan Sekarang
                </a>
            </div>
        @endif

    </div>

    {{-- MODAL DETAIL TIKET --}}
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
        
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        
        <div class="bg-white w-full max-w-md sm:max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] relative z-10 transform transition-all">
            
            {{-- Header Berwarna Gelap --}}
            <div class="relative bg-gray-900 text-white p-5">
                <button @click="showModal = false" class="absolute top-4 right-4 w-8 h-8 bg-white/10 text-white rounded-full flex items-center justify-center hover:bg-white/20 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <div class="flex items-center gap-3">
                    <span class="bg-white/20 text-white px-2.5 py-1 rounded font-mono text-xs font-bold shadow-sm" x-text="selectedTicket?.ticket_number"></span>
                    <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded" 
                          :class="{'bg-red-500/20 text-red-300': selectedTicket?.priority === 'high', 'bg-orange-500/20 text-orange-300': selectedTicket?.priority === 'medium', 'bg-green-500/20 text-green-300': selectedTicket?.priority === 'low'}"
                          x-text="selectedTicket?.priority"></span>
                </div>
                <h4 class="font-bold text-xl mt-3 leading-tight" x-text="selectedTicket?.asset_name"></h4>
                <div class="flex items-center gap-2 text-xs text-gray-300 mt-2">
                    <i class="fa-solid fa-location-dot text-red-400"></i>
                    <span x-text="selectedTicket?.location_name"></span>
                </div>
            </div>

            <div class="p-5 overflow-y-auto bg-gray-50 flex-1 custom-scrollbar">
                
                {{-- Laporan Awal --}}
                <div class="mb-5 bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5"><i class="fa-solid fa-clipboard-question text-blue-500"></i> Laporan Awal Anda</p>
                    <p class="text-sm text-gray-800 font-medium leading-relaxed mb-3" x-text="selectedTicket?.issue_description"></p>
                    <div class="text-[10px] text-gray-400 flex items-center gap-1.5">
                        <i class="fa-regular fa-clock"></i> Dilaporkan: <span x-text="selectedTicket?.created_at"></span>
                    </div>

                    {{-- Foto Sebelum --}}
                    <template x-if="selectedTicket?.photos_before_urls?.length > 0">
                        <div class="mt-3">
                            <p class="text-[10px] font-bold text-gray-500 mb-2">Lampiran Foto Anda</p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(url, index) in selectedTicket?.photos_before_urls" :key="index">
                                    <div class="rounded-lg overflow-hidden border border-gray-100">
                                        <img :src="url" class="h-20 w-20 object-cover hover:opacity-90 transition cursor-pointer" @click="window.open(url, '_blank')">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Hasil Perbaikan (Jika Selesai) --}}
                <template x-if="selectedTicket?.status === 'completed'">
                    <div class="bg-green-50 p-4 rounded-xl border border-green-200 shadow-sm relative overflow-hidden">
                        <div class="absolute -right-2 -top-2 text-green-600/10"><i class="fa-solid fa-check-circle text-6xl"></i></div>
                        
                        <p class="text-[10px] font-bold text-green-600 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-wrench text-green-500"></i> Hasil Perbaikan <span x-text="selectedTicket?.technician_name"></span>
                        </p>
                        <p class="text-sm text-green-900 font-medium leading-relaxed drop-shadow-sm relative z-10 mb-3" x-html="selectedTicket?.completed_note"></p>
                        
                        <div class="flex items-center gap-2 mb-3 z-10 relative">
                            <span class="bg-white text-green-700 text-[10px] font-bold px-2 py-0.5 rounded shadow-sm border border-green-100">Selesai: <span x-text="selectedTicket?.completed_date"></span></span>
                        </div>

                        {{-- Foto Setelah Perbaikan --}}
                        <template x-if="selectedTicket?.photos_after_urls?.length > 0">
                            <div class="mt-3 relative z-10">
                                <div class="bg-green-100 text-[10px] text-center py-1 font-bold text-green-700 mb-2 rounded">Foto Bukti Perbaikan dari Teknisi</div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="(url, index) in selectedTicket?.photos_after_urls" :key="index">
                                        <div class="rounded-lg overflow-hidden border border-green-200 shadow-sm">
                                            <img :src="url" class="h-24 w-24 object-cover hover:opacity-90 transition cursor-pointer" @click="window.open(url, '_blank')">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedTicket?.photos_after_urls || selectedTicket?.photos_after_urls?.length === 0">
                            <div class="mt-2 w-full h-24 bg-green-100/50 rounded-lg flex flex-col items-center justify-center text-green-600/50 border border-green-200 border-dashed relative z-10">
                                <i class="fa-solid fa-image-slash text-xl mb-1"></i>
                                <span class="text-[10px] font-bold">Teknisi tidak melampirkan foto</span>
                            </div>
                        </template>
                    </div>
                </template>

            </div>
        </div>
    </div>

</div>  
@endsection