@extends('layouts.technician')

@section('header')
    <div class="flex items-center gap-3">
        <a href="{{ route('technician.scan') }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 transition backdrop-blur-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="font-bold text-lg text-white">Patroli Area</h1>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4 border-l-4 border-blue-600 relative overflow-hidden">
        <i class="fa-solid fa-map-location-dot absolute right-[-10px] bottom-[-10px] text-6xl text-gray-100"></i>
        <h2 class="font-bold text-gray-800 text-lg relative z-10">Ruang Server Utama</h2>
        <p class="text-xs text-gray-500 mb-1 relative z-10">Lantai 2 - Gedung IT</p>
        <div class="flex items-center gap-2 mt-2 relative z-10">
            <span class="bg-blue-100 text-blue-700 text-[10px] px-2 py-1 rounded font-bold">
                Total Aset: 4 Unit
            </span>
            <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-1 rounded font-bold">
                Shift: Pagi
            </span>
        </div>
    </div>

    <form action="#" method="POST">
        @csrf

        <h3 class="font-bold text-gray-800 text-sm mb-3 uppercase tracking-wider ml-1 flex justify-between items-center">
            Daftar Aset di Area Ini
            <span class="text-[10px] text-gray-400 font-normal">Wajib dicek semua</span>
        </h3>

        <div class="space-y-4 mb-20">

            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">AC Standing 01</h4>
                        <p class="text-[10px] text-gray-500">SN: ACS-2023-001</p>
                    </div>
                    <div class="w-8 h-8 bg-blue-50 text-blue-600 rounded flex items-center justify-center text-xs">
                        <i class="fa-solid fa-snowflake"></i>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="status[1]" value="normal" class="peer sr-only" checked>
                        <div class="border border-gray-200 rounded-lg p-2 text-center peer-checked:bg-green-50 peer-checked:border-green-500 transition">
                            <span class="text-xs font-bold text-gray-500 peer-checked:text-green-700">
                                <i class="fa-solid fa-check mr-1"></i> Normal
                            </span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status[1]" value="rusak" class="peer sr-only" onchange="toggleReason(1)">
                        <div class="border border-gray-200 rounded-lg p-2 text-center peer-checked:bg-red-50 peer-checked:border-red-500 transition">
                            <span class="text-xs font-bold text-gray-500 peer-checked:text-red-700">
                                <i class="fa-solid fa-xmark mr-1"></i> Masalah
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">UPS Rackmount 3000VA</h4>
                        <p class="text-[10px] text-gray-500">SN: UPS-APC-99</p>
                    </div>
                    <div class="w-8 h-8 bg-orange-50 text-orange-600 rounded flex items-center justify-center text-xs">
                        <i class="fa-solid fa-battery-half"></i>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 mb-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="status[2]" value="normal" class="peer sr-only">
                        <div class="border border-gray-200 rounded-lg p-2 text-center peer-checked:bg-green-50 peer-checked:border-green-500 transition">
                            <span class="text-xs font-bold text-gray-500 peer-checked:text-green-700">Normal</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="status[2]" value="rusak" class="peer sr-only" checked>
                        <div class="border border-gray-200 rounded-lg p-2 text-center peer-checked:bg-red-50 peer-checked:border-red-500 transition">
                            <span class="text-xs font-bold text-gray-500 peer-checked:text-red-700">Masalah</span>
                        </div>
                    </label>
                </div>

                <div class="bg-red-50 p-3 rounded-lg border border-red-100 animate-fade-in-down">
                    <p class="text-[10px] text-red-600 mb-2 font-bold">Kondisi tidak normal. Buat Laporan Kegiatan (LK)?</p>
                    <a href="{{ route('technician.lk.create') }}" class="block w-full text-center bg-red-600 text-white text-xs font-bold py-2 rounded shadow hover:bg-red-700">
                        <i class="fa-solid fa-file-pen mr-1"></i> Buat LK Sekarang
                    </a>
                </div>
            </div>

             <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">APAR (Powder)</h4>
                        <p class="text-[10px] text-gray-500">Exp: Des 2026</p>
                    </div>
                    <div class="w-8 h-8 bg-red-50 text-red-600 rounded flex items-center justify-center text-xs">
                        <i class="fa-solid fa-fire-extinguisher"></i>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Jarum Pressure di Hijau?</span>
                        <input type="checkbox" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4" checked>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Segel Utuh?</span>
                        <input type="checkbox" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4" checked>
                    </div>
                </div>
            </div>

        </div>

        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] max-w-md mx-auto z-40">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition">
                <i class="fa-solid fa-save mr-2"></i> Simpan Logbook Area
            </button>
        </div>

    </form>
@endsection
