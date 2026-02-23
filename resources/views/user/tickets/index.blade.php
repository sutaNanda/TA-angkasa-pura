@extends('layouts.user')

@section('title', 'Riwayat Laporan Saya')

@section('content')
<div class="max-w-7xl mx-auto pb-10">
    
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Riwayat Laporan</h1>
            <p class="text-gray-500 text-sm mt-1">Pantau status perbaikan aset yang Anda laporkan.</p>
        </div>
        {{-- Diperbaiki: Tombol sekarang muncul di HP (Full width) dan menyesuaikan di Laptop --}}
        <a href="{{ route('user.tickets.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg transition gap-2">
            <i class="fa-solid fa-plus"></i> Buat Laporan Baru
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4 mb-6">
        {{-- Total --}}
        <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center md:items-start gap-2 md:gap-3 text-center md:text-left">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-ticket text-sm md:text-base"></i>
            </div>
            <div>
                <p class="text-[10px] md:text-xs text-gray-500 font-bold uppercase tracking-wider">Total Tiket</p>
                <p class="text-base md:text-lg font-bold text-gray-800">{{ $tickets->total() }}</p>
            </div>
        </div>
        {{-- Open (Menunggu) --}}
        <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center md:items-start gap-2 md:gap-3 text-center md:text-left">
            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-yellow-50 text-yellow-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-hourglass-half text-sm md:text-base"></i>
            </div>
            <div>
                <p class="text-[10px] md:text-xs text-gray-500 font-bold uppercase tracking-wider">Menunggu</p>
                {{-- Logic hitung tiket open di halaman saat ini --}}
                <p class="text-base md:text-lg font-bold text-gray-800">{{ $tickets->where('status', 'open')->count() }}</p> 
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
                            @if($ticket->asset->image)
                                <img src="{{ asset('storage/'.$ticket->asset->image) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-cube text-gray-400 text-lg"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-gray-900 text-sm line-clamp-1" title="{{ $ticket->asset->name }}">{{ $ticket->asset->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                                <i class="fa-solid fa-location-dot text-blue-500 mr-1"></i> {{ $ticket->asset->location->name ?? '-' }}
                            </p>
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
                        <div class="text-right">
                            <p class="text-[11px] font-bold text-gray-600">{{ $ticket->created_at->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400">{{ $ticket->created_at->format('H:i') }}</p>
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
</div>  
@endsection