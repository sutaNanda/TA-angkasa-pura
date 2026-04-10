@extends('layouts.admin')

@section('title', 'Laporan Riwayat Patroli')
@section('page-title', 'Laporan Riwayat Patroli')

@section('content')
<div class="container-fluid px-4 py-6 w-full mx-auto max-w-7xl" x-data="{
    startDate: '{{ request('start_date', '') }}',
    endDate: '{{ request('end_date', '') }}',
    status: '{{ request('status', 'all') }}',
    validateForm() {
        if((this.startDate && !this.endDate) || (!this.startDate && this.endDate)) {
            Swal.fire({
                title: 'Rentang Waktu Tidak Lengkap',
                text: 'Mohon isi kedua tanggal (Mulai & Sampai) untuk melakukan pencarian spesifik.',
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
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Laporan Logbook Patroli</h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Rekapitulasi riwayat pemeriksaan teknisi lapangan beserta status temuan harian.</p>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-6 mb-6 w-full">
        <form action="{{ route('admin.reports.patrol-logs.index') }}" method="GET" @submit="return validateForm()">
            <div class="grid grid-cols-3 sm:grid-cols-3 gap-4 lg:gap-6 items-end">
                
                {{-- Dari Tanggal --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" x-model="startDate" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition-all text-gray-600">
                </div>
                
                {{-- Sampai Tanggal --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" x-model="endDate" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition-all text-gray-600">
                </div>

                {{-- Status Inspeksi --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Status Inspeksi</label>
                    <div class="relative">
                        <select name="status" x-model="status" class="w-full appearance-none border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 pl-3 pr-10 py-2.5 outline-none shadow-sm transition-all bg-white text-gray-700">
                            <option value="all">Semua Status</option>
                            <option value="normal">Normal / Aman</option>
                            <option value="issue_found">Issue Found (Ada Masalah)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-6 flex flex-wrap sm:flex-nowrap gap-3 justify-end border-t border-gray-100 pt-5">
                <a href="{{ route('admin.reports.patrol-logs.index') }}" class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm text-center focus:outline-none focus:ring-2 focus:ring-gray-200">
                    Reset
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <i class="fa-solid fa-search"></i> Cari Data
                </button>
                <button type="submit" formaction="{{ route('admin.reports.patrol-logs.pdf') }}" formtarget="_blank" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors shadow-sm flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                    <i class="fa-solid fa-file-pdf"></i> Ekspor PDF
                </button>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col overflow-hidden mb-6 w-full max-w-full">
        <div class="w-full overflow-x-auto relative custom-scrollbar">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-max w-full text-left text-sm text-gray-600 border-collapse">
                    <thead class="bg-gray-50/80 text-gray-500 text-[11px] uppercase font-bold tracking-wider border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-6 py-4 whitespace-nowrap">Tgl & Waktu</th>
                            <th scope="col" class="px-6 py-4 whitespace-nowrap">Teknisi (Pelaksana)</th>
                            <th scope="col" class="px-6 py-4 whitespace-nowrap min-w-[200px]">Target Lokasi / Aset</th>
                            <th scope="col" class="px-6 py-4 whitespace-nowrap">Jenis Pekerjaan</th>
                            <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Status Temuan</th>
                            <th scope="col" class="px-6 py-4 whitespace-nowrap">Catatan Singkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                            
                            {{-- Tanggal & Waktu --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900 text-xs">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-gray-500 font-mono mt-0.5 flex items-center gap-1.5"><i class="fa-regular fa-clock text-gray-400"></i>{{ $log->created_at->format('H:i') }} WITA</div>
                            </td>

                            {{-- Teknisi --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->technician)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-slate-800 text-white flex items-center justify-center text-[10px] font-bold shadow-sm shrink-0">
                                            {{ substr($log->technician->name, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-semibold text-gray-800">{{ $log->technician->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-xs">Sistem / N/A</span>
                                @endif
                            </td>

                            {{-- Lokasi / Aset --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    @if($log->asset)
                                        <p class="font-bold text-gray-900 text-sm line-clamp-1" title="{{ $log->asset->name }}">{{ $log->asset->name }}</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5"><i class="fa-solid fa-location-dot text-gray-400 shrink-0"></i> <span class="line-clamp-1">{{ $log->location ? $log->location->name : '-' }}</span></p>
                                    @else
                                        <p class="font-bold text-blue-800 text-sm flex items-center gap-1.5 line-clamp-1"><i class="fa-solid fa-layer-group text-blue-500"></i> {{ $log->location ? $log->location->name : 'N/A' }}</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">Inspeksi Kesatuan Area</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Pekerjaan --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-md text-[11px] font-bold border border-gray-200">
                                    {{ $log->checklistTemplate ? $log->checklistTemplate->name : 'Inspeksi Reguler' }}
                                </span>
                            </td>

                            {{-- Status Temuan --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($log->status === 'normal' || $log->status === 'pass')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 uppercase tracking-wider">
                                        <i class="fa-solid fa-check text-emerald-500"></i> Aman / Normal
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20 uppercase tracking-wider">
                                        <i class="fa-solid fa-xmark text-rose-500"></i> Masalah (Issue)
                                    </span>
                                @endif
                            </td>

                            {{-- Catatan --}}
                            <td class="px-6 py-4 text-xs text-gray-600">
                                <div class="truncate max-w-[200px]" title="{{ $log->notes }}">
                                    {{ $log->notes ? $log->notes : '-' }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="fa-solid fa-clipboard-check text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="font-bold text-gray-900 mb-1 text-base">Tidak ada riwayat patroli</p>
                                    <p class="text-sm text-gray-500">Ubah rentang tanggal atau status filter untuk menemukan data.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200 rounded-b-2xl">
            {{ $logs->links() }}
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
