@extends('layouts.admin')

@section('title', 'Tambah Rencana Perawatan')

@section('content')
{{-- Alpine.js for interactivity --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

<div class="container-fluid px-4 py-6" x-data="{ frequency: '{{ old('frequency', 'daily') }}' }">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.plans.index') }}" class="hover:text-blue-600 transition">Rencana Perawatan</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-gray-800 font-medium">Buat Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Rencana Perawatan</h1>
            <p class="text-sm text-gray-500">Otomatisasi pembuatan tugas maintenance untuk aset.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.plans.index') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                Batal
            </a>
            <button type="submit" form="createPlanForm" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Simpan Rencana
            </button>
        </div>
    </div>

    <form action="{{ route('admin.plans.store') }}" method="POST" id="createPlanForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- LEFT COLUMN: MAIN SETTINGS --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Card 1: Target Asset --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-blue-100 text-blue-600 flex items-center justify-center text-xs"><i class="fa-solid fa-bullseye"></i></span>
                            Target Aset
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- Category --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Kategori Aset <span class="text-red-500">*</span></label>
                            <select name="category_id" required class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-2 bg-blue-50 text-blue-700 p-2 rounded border border-blue-100 inline-block">
                                <i class="fa-solid fa-circle-info mr-1"></i> Aturan ini akan berlaku untuk <strong>SEMUA</strong> aset dalam kategori ini.
                            </p>
                        </div>

                        {{-- Template --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Template Checklist <span class="text-red-500">*</span></label>
                            <select name="checklist_template_id" required class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                                <option value="">-- Pilih Template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ old('checklist_template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} ({{ $template->frequency ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('checklist_template_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Schedule --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-purple-100 text-purple-600 flex items-center justify-center text-xs"><i class="fa-solid fa-clock"></i></span>
                            Jadwal & Frekuensi
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        {{-- Visual Frequency Selector --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3">Frekuensi Perawatan <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                
                                {{-- Daily --}}
                                <label class="cursor-pointer group">
                                    <input type="radio" name="frequency" value="daily" x-model="frequency" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50/50 hover:bg-gray-50 border-gray-200">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                                            <i class="fa-solid fa-sun text-lg"></i>
                                        </div>
                                        <div class="font-bold text-gray-800 text-sm">Harian</div>
                                        <div class="text-[10px] text-gray-500">Setiap hari</div>
                                    </div>
                                </label>

                                {{-- Weekly --}}
                                <label class="cursor-pointer group">
                                    <input type="radio" name="frequency" value="weekly" x-model="frequency" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center peer-checked:border-purple-500 peer-checked:bg-purple-50/50 hover:bg-gray-50 border-gray-200">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                                            <i class="fa-solid fa-calendar-week text-lg"></i>
                                        </div>
                                        <div class="font-bold text-gray-800 text-sm">Mingguan</div>
                                        <div class="text-[10px] text-gray-500">Per pekan</div>
                                    </div>
                                </label>

                                {{-- Monthly --}}
                                <label class="cursor-pointer group">
                                    <input type="radio" name="frequency" value="monthly" x-model="frequency" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center peer-checked:border-orange-500 peer-checked:bg-orange-50/50 hover:bg-gray-50 border-gray-200">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                                            <i class="fa-solid fa-calendar-days text-lg"></i>
                                        </div>
                                        <div class="font-bold text-gray-800 text-sm">Bulanan</div>
                                        <div class="text-[10px] text-gray-500">Per bulan</div>
                                    </div>
                                </label>

                                {{-- Yearly --}}
                                <label class="cursor-pointer group">
                                    <input type="radio" name="frequency" value="yearly" x-model="frequency" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center peer-checked:border-red-500 peer-checked:bg-red-50/50 hover:bg-gray-50 border-gray-200">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                                            <i class="fa-solid fa-calendar text-lg"></i>
                                        </div>
                                        <div class="font-bold text-gray-800 text-sm">Tahunan</div>
                                        <div class="text-[10px] text-gray-500">Per tahun</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Dynamic Date Input --}}
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 transition-all duration-300">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Mulai / Referensi</label>
                            <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition">
                            
                            {{-- Dynamic Helper Text --}}
                            <div class="mt-3 flex gap-2 items-start text-xs text-gray-600">
                                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                                <div>
                                    <span x-show="frequency === 'daily'">
                                        <strong>Harian:</strong> Tugas maintenance akan dibuat secara otomatis <u>setiap hari</u> pada pukul 00:00.
                                    </span>
                                    <span x-show="frequency === 'weekly'" style="display: none;">
                                        <strong>Mingguan:</strong> Sistem akan mengambil <u>HARI</u> dari tanggal yang Anda pilih. <br>
                                        <em>Contoh: Jika Anda memilih tanggal <strong>10 Februari 2026 (Selasa)</strong>, maka tugas akan dibuat rutin setiap hari <strong>Selasa</strong>.</em>
                                    </span>
                                    <span x-show="frequency === 'monthly'" style="display: none;">
                                        <strong>Bulanan:</strong> Sistem akan mengambil <u>TANGGAL</u> dari input ini. <br>
                                        <em>Contoh: Jika pilih tanggal <strong>15</strong>, maka tugas dibuat setiap <strong>tanggal 15</strong> setiap bulannya.</em>
                                    </span>
                                    <span x-show="frequency === 'yearly'" style="display: none;">
                                        <strong>Tahunan:</strong> Sistem akan mengambil <u>TANGGAL & BULAN</u>. <br>
                                        <em>Contoh: Jika pilih <strong>17 Agustus</strong>, tugas akan dibuat setiap <strong>17 Agustus</strong> setiap tahun.</em>
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: ADDITIONAL INFO --}}
            <div class="space-y-6">
                
                {{-- Card 3: Status & Note --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6 sticky top-6">
                    
                    {{-- Active Status --}}
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                            <span class="text-sm font-bold text-gray-700">Aktifkan aturan ini</span>
                        </label>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea name="notes" rows="4" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Dilakukan oleh tim vendor...">{{ old('notes') }}</textarea>
                    </div>

                </div>

            </div>
        </div>
    </form>
</div>
@endsection
