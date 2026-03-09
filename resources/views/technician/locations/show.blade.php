@extends('layouts.technician')

@section('title', 'Detail Lokasi')

@section('content')
<div class="space-y-6">

    {{-- KARTU INFO LOKASI --}}
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
        {{-- Dekorasi Latar --}}
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-50 rounded-full opacity-50 blur-3xl pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row gap-6 items-start">
            
            {{-- Ikon Besar --}}
            <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-3xl shrink-0 shadow-inner border border-indigo-200">
                <i class="fa-solid fa-door-open"></i>
            </div>

            {{-- Info Teks --}}
            <div class="flex-1 w-full">
                <h1 class="text-2xl font-bold text-gray-900 mb-1 leading-tight">{{ $location->name }}</h1>
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 mb-6">
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded bg-gray-100 text-gray-800 border border-gray-200 uppercase tracking-widest w-max shadow-sm">
                        <i class="fa-solid fa-qrcode mr-1.5"></i> {{ $location->code ?: 'NO-CODE' }}
                    </span>
                    @if($location->full_address)
                        <span class="text-xs text-indigo-600 font-bold flex items-center gap-1.5 bg-indigo-50 px-2.5 py-1 rounded border border-indigo-100 w-max">
                            <i class="fa-solid fa-map text-[10px]"></i>
                            Hirarki: {{ $location->full_address }}
                        </span>
                    @endif
                </div>

                {{-- Statistik Singkat --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-desktop"></i></div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400">Total Aset</p>
                            <p class="font-black text-gray-800 text-lg leading-none">{{ $location->assets->count() }}</p>
                        </div>
                    </div>
                    <div class="bg-green-50 p-3 rounded-xl border border-green-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-check"></i></div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-green-600/70">Normal</p>
                            <p class="font-black text-green-700 text-lg leading-none">{{ $statusStats['normal'] }}</p>
                        </div>
                    </div>
                    <div class="bg-red-50 p-3 rounded-xl border border-red-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-red-600/70">Rusak</p>
                            <p class="font-black text-red-700 text-lg leading-none">{{ $statusStats['rusak'] }}</p>
                        </div>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-xl border border-yellow-100 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center shrink-0"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-yellow-600/70">DIPERBAIKI</p>
                            <p class="font-black text-yellow-700 text-lg leading-none">{{ $statusStats['maintenance'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL DAFTAR ASET DALAM LOKASI INI --}}
    <div>
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-list-ul text-blue-600"></i> Daftar Aset di Ruangan Ini
        </h3>

        @if($location->assets->isEmpty())
            <div class="bg-white p-12 text-center rounded-2xl border border-gray-100 shadow-sm flex items-center justify-center flex-col">
                <i class="fa-solid fa-box-open text-4xl text-gray-300 mb-3"></i>
                <h4 class="font-bold text-gray-600">Terpantau Kosong</h4>
                <p class="text-sm text-gray-400 mt-1">Belum ada aset IT yang terdaftar secara sistem di ruangan ini.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4">Informasi Aset</th>
                                <th class="px-6 py-4">Kategori Spesifik</th>
                                <th class="px-6 py-4">Status Kondisi</th>
                                <th class="px-6 py-4 text-center">Aksi / Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($location->assets as $asset)
                                <tr class="hover:bg-gray-50 transition group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold shrink-0 border border-blue-100 relative">
                                                @if($asset->category && $asset->category->icon)
                                                    <i class="{{ $asset->category->icon }}"></i>
                                                @else
                                                    <i class="fa-solid fa-box"></i>
                                                @endif
                                                
                                                @if($asset->status === 'rusak')
                                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 border-2 border-white rounded-full"></div>
                                                @elseif($asset->status === 'maintenance')
                                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-500 border-2 border-white rounded-full"></div>
                                                @endif
                                            </div>
                                            <div class="min-w-0 max-w-[200px] md:max-w-xs">
                                                <p class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors truncate">{{ $asset->name }}</p>
                                                <p class="text-[10px] text-gray-500 uppercase tracking-widest font-mono mt-0.5">
                                                    <i class="fa-solid fa-barcode"></i> {{ $asset->serial_number ?: 'NO-SN' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded text-xs font-semibold border border-gray-200 block w-max">
                                            {{ $asset->category ? $asset->category->name : 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColor = match($asset->status) {
                                                'normal' => 'bg-green-100 text-green-700 border-green-200',
                                                'rusak' => 'bg-red-100 text-red-700 border-red-200',
                                                'maintenance' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                                'hilang' => 'bg-gray-100 text-gray-700 border-gray-200',
                                                default => 'bg-gray-100 text-gray-600 border-gray-200'
                                            };
                                        @endphp
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-md uppercase {{ $statusColor }} border shadow-sm block w-max">
                                            {{ $asset->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('technician.assets.show', $asset->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors duration-200 shadow-sm border border-blue-100" title="Buka Detail Aset">
                                            <i class="fa-solid fa-folder-open"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
