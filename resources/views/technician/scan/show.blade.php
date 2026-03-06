@extends('layouts.technician')

@section('header_title', 'Detail Lokasi')

@section('content')
<div class="container mx-auto px-4 md:px-8 pb-24 max-w-6xl">
    
    {{-- 1. HEADER LOKASI (Clean Design) --}}
    <div class="bg-white rounded-3xl p-6 md:p-8 mb-8 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        {{-- Background Ornament --}}
        <div class="absolute -right-10 -top-10 text-gray-50 opacity-50 rotate-12 pointer-events-none">
            <i class="fa-solid fa-map-location-dot text-[150px]"></i>
        </div>

        <div class="flex items-start gap-5 relative z-10">
            <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-600 flex items-center justify-center text-3xl flex-shrink-0 shadow-inner border border-blue-100/50">
                <i class="fa-solid fa-map-pin"></i>
            </div>
            <div>
                <h2 class="font-black text-gray-800 text-2xl md:text-3xl tracking-tight mb-2">{{ $location->name }}</h2>
                <p class="text-sm text-gray-500 flex items-center gap-2 font-medium">
                    <i class="fa-solid fa-building text-gray-400"></i> {{ $location->description ?? 'Tidak ada deskripsi lokasi' }}
                </p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-lg border border-gray-200">
                        <i class="fa-solid fa-cube mr-1 text-gray-400"></i> {{ $stats['total_assets'] }} Aset Terdaftar
                    </span>
                </div>
            </div>
        </div>

        {{-- Statistik Tugas --}}
        <div class="flex gap-4 md:gap-6 relative z-10">
            <div class="text-center bg-gray-50 rounded-2xl p-4 min-w-[100px] border border-gray-100">
                <div class="text-3xl font-black text-gray-800 mb-1">{{ $stats['total_tasks'] }}</div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tugas<br>Hari Ini</div>
            </div>
        </div>
    </div>

    {{-- 2. TUGAS MAINTENANCE HARI INI (Hanya muncul jika ada) --}}
    @if($maintenanceTasks->isNotEmpty())
        <div class="mb-10">
            <h3 class="font-black text-gray-400 text-xs uppercase tracking-widest mb-4 flex items-center gap-2 ml-2">
                <i class="fa-solid fa-clipboard-list text-blue-500"></i> Tugas Perawatan Area Ini
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($maintenanceTasks as $task)
                    <div class="bg-white rounded-2xl p-5 shadow-sm border-l-4 border-l-blue-500 border-y border-r border-gray-100 hover:shadow-md transition-shadow group">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-bold text-gray-800 text-base mb-1">{{ $task->checklistTemplate->name ?? $task->maintenancePlan->name }}</h4>
                                <p class="text-xs text-gray-500 font-medium flex items-center gap-1.5">
                                    <i class="fa-regular fa-clock text-gray-400"></i> 
                                    Estimasi: {{ $task->checklistTemplate->estimated_duration ?? $task->maintenancePlan->estimated_duration ?? 15 }} Menit
                                </p>
                            </div>
                            <span class="bg-blue-50 text-blue-600 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-wider border border-blue-100">
                                {{ $task->checklistTemplate->frequency ?? $task->maintenancePlan->frequency ?? 'Rutin' }}
                            </span>
                        </div>
                        
                        <a href="{{ route('technician.locations.maintenance.inspect', $task->id) }}" 
                           class="w-full block text-center bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white font-bold py-3 rounded-xl transition-colors text-sm">
                            <i class="fa-solid fa-play mr-2"></i> Mulai Kerjakan Tugas
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 3. DAFTAR ASET (Tampil Bersih & Rapi) --}}
    <div>
        <div class="flex items-center justify-between mb-4 ml-2">
            <h3 class="font-black text-gray-400 text-xs uppercase tracking-widest flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-gray-400"></i> Daftar Inventaris Aset
            </h3>
            
            {{-- Tombol Patroli Bebas HANYA BISA DIKLIK JIKA TIDAK ADA JADWAL --}}
            @if($assets->isNotEmpty() && $maintenanceTasks->isEmpty())
                 <a href="{{ route('technician.locations.inspect', $location->id) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved"></i> Patroli Area Bebas
                </a>
            @endif
        </div>

        @if($assets->isNotEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                
                {{-- TAMPILAN DESKTOP (TABLE) --}}
                <div class="desktop-asset-list w-full overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50/80 text-gray-500 text-xs uppercase tracking-widest border-b border-gray-200">
                                <th class="p-4 font-bold w-16 text-center">No</th>
                                <th class="p-4 font-bold">Informasi Aset</th>
                                <th class="p-4 font-bold w-48">Kategori</th>
                                <th class="p-4 font-bold w-64">Serial Number</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach($assets as $index => $asset)
                                <tr class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="p-4 text-center text-gray-400 font-bold">{{ $index + 1 }}</td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center border border-gray-200 group-hover:bg-blue-50 group-hover:text-blue-500 transition-colors">
                                                <i class="{{ $asset->category->icon ?? 'fa-solid fa-desktop' }}"></i>
                                            </div>
                                            <span class="font-bold text-gray-800">{{ $asset->name }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold">{{ $asset->category->name }}</span>
                                    </td>
                                    <td class="p-4 text-gray-500 font-mono text-xs">
                                        <i class="fa-solid fa-barcode mr-2 opacity-50"></i>{{ $asset->serial_number ?? 'Belum ada SN' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- TAMPILAN MOBILE (LIST VIEW) --}}
                <div class="mobile-asset-list divide-y divide-gray-100">
                    @foreach($assets as $index => $asset)
                        <div class="p-4 flex gap-4 items-start bg-white">
                            <div class="w-12 h-12 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center flex-shrink-0 border border-gray-100 mt-1">
                                <i class="{{ $asset->category->icon ?? 'fa-solid fa-desktop' }} text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-800 text-base leading-tight truncate mb-1.5">{{ $asset->name }}</h4>
                                <div class="flex flex-wrap gap-2 items-center">
                                    <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">{{ $asset->category->name }}</span>
                                </div>
                                <p class="text-xs text-gray-400 font-mono mt-2 flex items-center gap-1.5">
                                    <i class="fa-solid fa-barcode"></i> {{ $asset->serial_number ?? 'No SN' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 p-10 text-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-gray-100">
                    <i class="fa-solid fa-box-open text-gray-300 text-3xl"></i>
                </div>
                <h4 class="font-black text-gray-500 mb-1">Ruangan Kosong</h4>
                <p class="text-xs text-gray-400 font-medium">Belum ada aset yang didaftarkan di lokasi ini.</p>
            </div>
        @endif
    </div>

</div>

{{-- CSS MURNI UNTUK MENJAMIN RESPONSIVITAS --}}
<style>
    /* Bypass Tailwind Cache: Mengatur Kapan Tabel Muncul & Kapan List HP Muncul */
    @media (min-width: 768px) {
        .desktop-asset-list { display: block !important; }
        .mobile-asset-list { display: none !important; }
    }
    @media (max-width: 767px) {
        .desktop-asset-list { display: none !important; }
        .mobile-asset-list { display: block !important; }
    }
</style>
@endsection