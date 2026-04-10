@extends('layouts.admin')

@section('title', 'Laporan Inventaris Aset')
@section('page-title', 'Laporan Inventaris Aset')

@section('content')
<div class="container-fluid px-4 py-6 w-full mx-auto max-w-7xl">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Laporan Inventaris Aset</h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">Pantau seluruh pendataan aset terintegrasi dengan struktur filter dinamis.</p>
        </div>
    </div>

    {{-- Filter Panel (Alpine.js form handling) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-6 mb-6 w-full" x-data="{
        category: '{{ request('category_id', 'all') }}',
        location: '{{ request('location_id', 'all') }}',
        status: '{{ request('status', 'all') }}'
    }">
        <form action="{{ route('admin.reports.assets.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6 items-end">
                
                {{-- Kategori --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Kategori Aset</label>
                    <div class="relative">
                        <select name="category_id" x-model="category" class="w-full appearance-none border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 pl-3 pr-10 py-2.5 outline-none shadow-sm transition-all bg-white text-gray-700">
                            <option value="all">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Lokasi --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Lokasi Gedung / Ruang</label>
                    <div class="relative">
                        <select name="location_id" x-model="location" class="w-full appearance-none border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 pl-3 pr-10 py-2.5 outline-none shadow-sm transition-all bg-white text-gray-700">
                            <option value="all">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1.5">Status Aset</label>
                    <div class="relative">
                        <select name="status" x-model="status" class="w-full appearance-none border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 pl-3 pr-10 py-2.5 outline-none shadow-sm transition-all bg-white text-gray-700">
                            <option value="all">Semua Status</option>
                            <option value="active">Active (Aktif / Normal)</option>
                            <option value="maintenance">Maintenance (Perbaikan)</option>
                            <option value="broken">Broken (Rusak)</option>
                            <option value="retired">Retired (Pensiun / Dihapus)</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-6 flex flex-wrap sm:flex-nowrap gap-3 justify-end border-t border-gray-100 pt-5">
                <a href="{{ route('admin.reports.assets.index') }}" class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm text-center focus:outline-none focus:ring-2 focus:ring-gray-200">
                    Reset
                </a>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <i class="fa-solid fa-search"></i> Tampilkan
                </button>
                <button type="submit" formaction="{{ route('admin.reports.assets.pdf') }}" formtarget="_blank" class="w-full sm:w-auto px-6 py-2.5 text-sm font-bold text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors shadow-sm flex items-center justify-center gap-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
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
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">ID Aset (UUID)</th>
                        <th scope="col" class="px-6 py-4 min-w-[200px] w-1/4">Aset & Seri</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Kategori</th>
                        <th scope="col" class="px-6 py-4 min-w-[150px] w-1/4">Lokasi / Area</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Tgl. Beli</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($assets as $asset)
                    <tr class="hover:bg-gray-50/80 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-[11px] font-bold text-gray-500 bg-gray-100 border border-gray-200 px-2 py-1 rounded-md tracking-wider">
                                {{ substr($asset->uuid, 0, 8) }}...{{ substr($asset->uuid, -4) }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <p class="font-bold text-gray-900 text-sm line-clamp-1" title="{{ $asset->name }}">{{ $asset->name }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5 font-mono">SN: {{ $asset->serial_number ?? '-' }}</p>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                                {{ $asset->category ? $asset->category->name : 'N/A' }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 text-gray-600 text-xs font-medium">
                                <i class="fa-solid fa-location-dot text-gray-400 shrink-0"></i>
                                <span class="line-flex md:line-clamp-1" title="{{ $asset->location ? $asset->location->name : 'N/A' }}">{{ $asset->location ? $asset->location->name : 'N/A' }}</span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 text-xs font-medium text-center whitespace-nowrap">
                            {{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}
                        </td>
                        
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @php
                                $statusConfig = match($asset->status) {
                                    'active' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-600/20'],
                                    'maintenance' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'ring-amber-600/20'],
                                    'broken' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'ring-rose-600/20'],
                                    'retired' => ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'ring' => 'ring-slate-600/20'],
                                    default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'ring' => 'ring-gray-600/20'],
                                };
                            @endphp
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }} ring-1 ring-inset {{ $statusConfig['ring'] }}">
                                {{ str_replace('_', ' ', $asset->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-box-open text-2xl text-gray-300"></i>
                                </div>
                                <p class="font-bold text-gray-900 mb-1 text-base">Tidak ada aset ditemukan</p>
                                <p class="text-sm text-gray-500">Coba ubah filter pencarian untuk melihat data aset lainnya.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($assets->hasPages())
        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200">
            {{ $assets->links() }}
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
