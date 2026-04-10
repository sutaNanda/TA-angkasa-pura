@extends('layouts.technician')

@section('title', 'Daftar Lokasi')

@section('content')
<div class="space-y-6">

    {{-- HEADER & PENCARIAN & ACTION --}}
    <div class="bg-white p-4 md:p-6 rounded-2xl border border-gray-100 mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
        <form action="{{ route('technician.locations.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 w-full md:w-3/4">
            <div class="flex-1 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama ruangan atau area..." class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all outline-none">
            </div>
            <div class="flex gap-2 shrink-0">
                <button type="submit" class="flex-1 md:flex-none justify-center bg-gray-800 hover:bg-black text-white px-6 py-3 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                    Cari
                </button>
                @if($search)
                <a href="{{ route('technician.locations.index') }}" class="flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-3 rounded-xl transition-colors">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
                @endif
            </div>
        </form>

        <button type="button" @click="showScanOptions = true" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl text-sm font-bold shadow-md shadow-blue-600/20 transition-all flex items-center justify-center gap-2 w-full md:w-auto shrink-0">
            <i class="fa-solid fa-qrcode text-lg"></i> Scan QR Lokasi
        </button>
    </div>

    {{-- TABEL DAFTAR LOKASI (LISTING) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs">
                    <tr>
                        <th class="px-6 py-4">Kode Lokasi</th>
                        <th class="px-6 py-4">Nama Ruangan / Area</th>
                        <th class="px-6 py-4">Hierarki Lingkungan</th>
                        <th class="px-6 py-4 text-center">Jumlah Aset</th>
                        <th class="px-6 py-4 text-center">Aksi / Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($locations as $loc)
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded text-xs font-bold border border-gray-200 shadow-sm uppercase tracking-widest">{{ $loc->code ?: 'NO-CODE' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold shrink-0 border border-indigo-100 hidden md:flex">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </div>
                                    <p class="font-black text-gray-900 group-hover:text-blue-600 transition-colors {{ $loc->parent_id ? 'text-sm' : 'text-base' }}">{{ $loc->name }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-[200px] md:max-w-md">
                                    <p class="text-[10px] md:text-xs font-bold text-gray-500 break-words leading-tight">
                                        {{ $loc->full_address }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md text-xs font-bold border border-blue-100">
                                    <i class="fa-solid fa-desktop"></i>
                                    {{ $loc->assets_count }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('technician.locations.show', $loc->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors duration-200 shadow-sm" title="Lihat Area dan Aset">
                                    <i class="fa-solid fa-folder-open"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <i class="fa-solid fa-map-location-dot text-3xl text-gray-300"></i>
                                </div>
                                <h3 class="text-base font-bold text-gray-700 mb-1">Lokasi Tidak Ditemukan</h3>
                                <p class="text-gray-500 text-xs">Atau gunakan kata pencarian yang berbeda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    @if($locations->hasPages())
        <div class="mt-6 flex justify-center pb-8">
            {{ $locations->links() }}
        </div>
    @endif


@endsection
