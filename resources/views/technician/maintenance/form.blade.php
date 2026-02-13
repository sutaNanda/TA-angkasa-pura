@extends('layouts.technician')

@section('header')
    <div class="flex items-center gap-3">
        <a href="{{ route('technician.scan') }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/30 transition backdrop-blur-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="font-bold text-lg text-white">Form Pengecekan</h1>
    </div>
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4 border-l-4 border-blue-600">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="font-bold text-gray-800 text-lg">AC Server Lt.1</h2>
                <p class="text-xs text-gray-500 mb-1">SN: AC-2023-001</p>
                <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded font-medium">
                    <i class="fa-solid fa-location-dot"></i> Gedung A - Ruang Server
                </span>
            </div>
            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400">
                <i class="fa-solid fa-image"></i>
            </div>
        </div>
    </div>

    <form action="#" method="POST"> @csrf
        
        <h3 class="font-bold text-gray-800 text-sm mb-3 uppercase tracking-wider ml-1">Daftar Pengecekan</h3>
        
        <div class="space-y-4 mb-6">
            
            <div class="bg-white p-4 rounded-xl shadow-sm">
                <label class="block text-sm font-bold text-gray-700 mb-2">1. Suhu Ruangan (°C)</label>
                <div class="flex items-center gap-2">
                    <input type="number" class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 bg-gray-50" placeholder="Contoh: 20">
                    <span class="text-gray-500 text-sm font-bold">°C</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm">
                <label class="block text-sm font-bold text-gray-700 mb-2">2. Kebersihan Filter Debu</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="check_filter" value="pass" class="peer sr-only" checked>
                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                            <i class="fa-solid fa-check-circle text-green-500 text-xl mb-1 block"></i>
                            <span class="text-xs font-bold text-gray-600 peer-checked:text-green-700">Bersih</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="check_filter" value="fail" class="peer sr-only">
                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-red-500 peer-checked:bg-red-50 transition">
                            <i class="fa-solid fa-circle-xmark text-red-500 text-xl mb-1 block"></i>
                            <span class="text-xs font-bold text-gray-600 peer-checked:text-red-700">Kotor / Berdebu</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm">
                <label class="block text-sm font-bold text-gray-700 mb-2">3. Kondisi Suara Mesin</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="check_sound" value="pass" class="peer sr-only" checked>
                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-green-500 peer-checked:bg-green-50 transition">
                            <i class="fa-solid fa-volume-low text-green-500 text-xl mb-1 block"></i>
                            <span class="text-xs font-bold text-gray-600 peer-checked:text-green-700">Halus</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="check_sound" value="fail" class="peer sr-only">
                        <div class="border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-red-500 peer-checked:bg-red-50 transition">
                            <i class="fa-solid fa-volume-high text-red-500 text-xl mb-1 block"></i>
                            <span class="text-xs font-bold text-gray-600 peer-checked:text-red-700">Berisik / Kasar</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm">
                <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Tambahan (Opsional)</label>
                <textarea rows="3" class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 bg-gray-50" placeholder="Tulis keterangan jika ada temuan..."></textarea>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm">
                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Bukti (Opsional)</label>
                <div class="flex items-center justify-center w-full">
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fa-solid fa-camera text-gray-400 mb-1"></i>
                            <p class="text-[10px] text-gray-500">Ambil Foto</p>
                        </div>
                        <input type="file" class="hidden" capture="environment" accept="image/*" />
                    </label>
                </div>
            </div>

        </div>

        <button type="submit" onclick="alert('Data berhasil disimpan! Status: NORMAL')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-600/30 transition transform active:scale-95 mb-6">
            <i class="fa-solid fa-paper-plane mr-2"></i> Simpan Laporan
        </button>

    </form>
@endsection