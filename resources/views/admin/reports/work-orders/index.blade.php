@extends('layouts.admin')

@section('title', 'Laporan Work Order')
@section('page-title', 'Laporan Work Order')

@section('content')
<div class="container-fluid px-4 py-6 w-full mx-auto max-w-7xl" x-data="{
    startDate: '{{ request('start_date', '') }}',
    endDate: '{{ request('end_date', '') }}',
    validateForm() {
        if((this.startDate && !this.endDate) || (!this.startDate && this.endDate)) {
            Swal.fire({
                title: 'Rentang Waktu Tidak Lengkap',
                text: 'Mohon isi kedua tanggal (Mulai & Sampai) untuk memfilter berdasarkan waktu.',
                icon: 'warning',
                confirmButtonColor: '#3b82f6',
                customClass: { confirmButton: 'rounded-xl' }
            });
            return false;
        }
        return true;
    }
}">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Laporan Work Order</h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Evaluasi performa penanganan tiket, waktu penyelesaian, dan <i>Mean Time to Repair (MTTR)</i>.</p>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-6 mb-6 w-full">
        <form action="{{ route('admin.reports.work-orders.index') }}" method="GET" @submit="return validateForm()">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 items-end">
                
                {{-- Filter Tanggal --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Mulai Tanggal</label>
                    <input type="date" name="start_date" x-model="startDate" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition-all text-gray-600">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" x-model="endDate" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition-all text-gray-600">
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Status Tiket</label>
                    <div class="relative">
                        <select name="status" class="w-full appearance-none border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 pl-3 pr-10 py-2.5 outline-none shadow-sm transition-all bg-white text-gray-700">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Baru / Open</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Dalam Pengerjaan</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai (Tunggu Verifikasi)</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Selesai & Valid (Verified)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Prioritas --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Prioritas</label>
                    <div class="relative">
                        <select name="priority" class="w-full appearance-none border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 pl-3 pr-10 py-2.5 outline-none shadow-sm transition-all bg-white text-gray-700">
                            <option value="all" {{ request('priority') == 'all' ? 'selected' : '' }}>Semua Prioritas</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High (Tinggi)</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium (Sedang)</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low (Rendah)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-6 flex flex-wrap sm:flex-nowrap gap-3 justify-end border-t border-gray-100 pt-5">
                <a href="{{ route('admin.reports.work-orders.index') }}" class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm text-center focus:outline-none focus:ring-2 focus:ring-gray-200">
                    Reset
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <i class="fa-solid fa-filter"></i> Terapkan Filter
                </button>
                <button type="submit" formaction="{{ route('admin.reports.work-orders.pdf') }}" formtarget="_blank" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors shadow-sm flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                </button>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mb-6 w-full relative">
        <div class="w-full overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-sm text-gray-600 border-collapse whitespace-nowrap md:whitespace-normal">
                <thead class="bg-gray-50/80 text-gray-500 text-[11px] uppercase font-bold tracking-wider border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">No. Tiket</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Waktu Pelaporan</th>
                        <th scope="col" class="px-6 py-4 min-w-[200px] w-1/3">Target Aset & Lokasi</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Teknisi</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">MTTR / Waktu Pengerjaan</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Status Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($workOrders as $wo)
                    <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-1 rounded-md tracking-wider">
                                {{ $wo->ticket_number }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900 text-xs">{{ \Carbon\Carbon::parse($wo->created_at)->format('d M Y') }}</div>
                            <div class="text-[11px] text-gray-500 font-mono mt-0.5 flex items-center gap-1.5"><i class="fa-regular fa-clock text-gray-400"></i>{{ \Carbon\Carbon::parse($wo->created_at)->format('H:i') }}</div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <p class="font-bold text-gray-900 text-sm line-clamp-1" title="{{ $wo->asset ? $wo->asset->name : 'N/A' }}">{{ $wo->asset ? $wo->asset->name : 'N/A' }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-gray-400 shrink-0"></i> <span class="line-clamp-1">{{ $wo->location ? $wo->location->name : 'N/A' }}</span></p>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($wo->technician)
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-slate-800 text-white flex items-center justify-center text-[10px] font-bold shadow-sm shrink-0">
                                        {{ substr($wo->technician->name, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-semibold text-gray-800">{{ $wo->technician->name }}</span>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs text-gray-400 italic bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                    Belum Ditugaskan
                                </span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($wo->mttr_display != '-')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    <i class="fa-solid fa-stopwatch text-emerald-500"></i> {{ $wo->mttr_display }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs italic">- Menunggu -</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @php
                                $statusConfig = match($wo->status) {
                                    'verified' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-600/20'],
                                    'completed' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-600/20'],
                                    'in_progress' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'ring' => 'ring-blue-600/20'],
                                    'handover' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'ring' => 'ring-indigo-600/20'],
                                    'pending_part' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'ring' => 'ring-purple-600/20'],
                                    'open' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-600/20'],
                                    default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'ring' => 'ring-gray-600/20'],
                                };
                            @endphp
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} ring-1 ring-inset {{ $statusConfig['ring'] }}">
                                {{ str_replace('_', ' ', $wo->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-clipboard-list text-2xl text-gray-300"></i>
                                </div>
                                <p class="font-bold text-gray-900 mb-1 text-base">Tidak ada data laporan</p>
                                <p class="text-sm text-gray-500">Ubah kriteria filter tanggal atau status untuk menemukan tiket.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($workOrders->hasPages())
        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200 rounded-b-2xl">
            {{ $workOrders->links() }}
        </div>
        @endif
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>
@endsection