@extends('layouts.admin')

@section('title', 'Tambah Rencana Perawatan')

@section('content')

<div class="container-fluid px-4 py-6 max-w-7xl mx-auto" x-data="maintenancePlanForm()">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.plans.index') }}" class="hover:text-blue-600 font-medium transition-colors">Rencana Perawatan</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-gray-800 font-semibold">Buat Baru</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Tambah Rencana Perawatan</h1>
            <p class="text-sm text-gray-500 mt-1">Otomatisasi pembuatan tugas maintenance untuk banyak kategori aset sekaligus.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto mt-2 md:mt-0">
            <a href="{{ route('admin.plans.index') }}" class="w-full md:w-auto text-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-lg text-sm font-medium transition shadow-sm focus:ring-2 focus:ring-gray-200 focus:outline-none">
                Batal
            </a>
            <button type="submit" form="createPlanForm" class="w-full md:w-auto inline-flex justify-center items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:outline-none">
                <i class="fa-solid fa-save"></i> Simpan Rencana
            </button>
        </div>
    </div>

    <form action="{{ route('admin.plans.store') }}" method="POST" id="createPlanForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- LEFT COLUMN: MAIN SETTINGS --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Card 1: Name & Template Configs --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-white px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2.5 text-lg">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm border border-blue-100"><i class="fa-solid fa-bullseye"></i></span>
                            Konfigurasi Target
                        </h3>
                    </div>
                    <div class="p-6 space-y-8">
                        {{-- Name Input --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Inspeksi / Rencana <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Pengecekan Rutin Ruang PIONA" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm outline-none">
                            @error('name') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <hr class="border-gray-100">

                        {{-- Multi-Category Repeater --}}
                        <div>
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700">Kategori & Template Target <span class="text-red-500">*</span></label>
                                    <p class="text-xs text-gray-500 mt-0.5">Tentukan kategori aset dan checklist yang akan digunakan.</p>
                                </div>
                                <button type="button" @click="addConfig" class="inline-flex items-center gap-1.5 text-xs bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 px-3 py-1.5 rounded-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-green-500/20">
                                    <i class="fa-solid fa-plus"></i> Tambah Kategori
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(config, index) in configs" :key="index">
                                    <div class="flex flex-col sm:flex-row gap-4 p-5 bg-gray-50/50 rounded-xl border border-gray-200 relative transition-all hover:border-blue-300 hover:shadow-sm group">
                                        <div class="flex-1">
                                            <label class="block text-[10px] uppercase tracking-wider font-bold text-gray-500 mb-1.5">Kategori Aset</label>
                                            <select :name="`configs[${index}][category_id]`" x-model="config.category_id" @change="fetchAssets" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm outline-none bg-white">
                                                <option value="">Pilih Kategori</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-[10px] uppercase tracking-wider font-bold text-gray-500 mb-1.5">Template Checklist</label>
                                            <select :name="`configs[${index}][template_id]`" x-model="config.template_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm outline-none bg-white">
                                                <option value="">Pilih Template</option>
                                                @foreach($templates as $template)
                                                    <option value="{{ $template->id }}">{{ $template->name }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex items-end pb-1">
                                            <button type="button" @click="removeConfig(index)" x-show="configs.length > 1" class="w-10 h-10 bg-white border border-gray-200 text-gray-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 rounded-lg transition-all flex items-center justify-center focus:outline-none" title="Hapus baris ini">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            @error('configs') <p class="text-red-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Target Type Selector --}}
                        <div class="mt-8 border-t border-gray-100 pt-8">
                            <label class="block text-sm font-semibold text-gray-700 mb-4">Metode Generate Tiket <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="cursor-pointer group">
                                    <input type="radio" name="target_type" value="asset" x-model="targetType" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50/40 peer-checked:ring-1 peer-checked:ring-blue-500 hover:bg-gray-50 border-gray-200 bg-white">
                                        <div class="flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fa-solid fa-box text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-sm">Per Aset Individu</div>
                                                <div class="text-xs text-gray-500 mt-1 leading-relaxed">Sistem akan membuat 1 tiket terpisah untuk setiap masing-masing aset.</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="target_type" value="location" x-model="targetType" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 peer-checked:border-purple-500 peer-checked:bg-purple-50/40 peer-checked:ring-1 peer-checked:ring-purple-500 hover:bg-gray-50 border-gray-200 bg-white">
                                        <div class="flex items-start gap-4">
                                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <i class="fa-solid fa-layer-group text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-sm">Kesatuan Area/Lokasi</div>
                                                <div class="text-xs text-gray-500 mt-1 leading-relaxed">Sistem membuat 1 tiket gabungan (Area-centric) yang berisi banyak aset sekaligus.</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- {{-- Target Assets Selection (Visible if Target = Asset) --}} -->
                        <div x-show="targetType === 'asset' && selectedCategoryIds.length > 0" x-transition.opacity.duration.300ms class="mt-6 border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                            <div class="bg-gray-50 px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h4 class="font-semibold text-gray-800 text-sm">Pilih Aset Spesifik</h4>
                                    <p class="text-[11px] text-gray-500 mt-0.5">Kosongkan centang jika aturan ini berlaku untuk <strong>semua</strong> aset.</p>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text" x-model="searchQueryAsset" placeholder="Cari nama/SN..." class="pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-full sm:w-48 transition-all outline-none">
                                    </div>
                                    <button type="button" @click="selectAllAssets()" class="text-xs bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-2 rounded-lg font-medium transition-colors whitespace-nowrap focus:outline-none">
                                        <span x-text="selectedAssets.length === filteredAssets.length && filteredAssets.length > 0 ? 'Batal Semua' : 'Pilih Semua'"></span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-0 max-h-[350px] overflow-y-auto">
                                <div x-show="isLoading" class="p-12 text-center text-gray-500">
                                    <i class="fa-solid fa-circle-notch fa-spin text-3xl mb-3 text-blue-500"></i>
                                    <p class="text-sm font-medium">Memuat daftar aset...</p>
                                </div>
                                
                                <div x-show="!isLoading && filteredAssets.length === 0" style="display: none;" class="p-12 text-center text-gray-500">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-gray-100">
                                        <i class="fa-solid fa-box-open text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-sm font-medium">Tidak ada aset ditemukan pada kategori terpilih.</p>
                                </div>

                                <ul x-show="!isLoading && filteredAssets.length > 0" class="divide-y divide-gray-100">
                                    <template x-for="asset in filteredAssets" :key="asset.id">
                                        <li>
                                            <label class="flex items-start gap-4 p-4 hover:bg-blue-50/50 cursor-pointer transition-colors group">
                                                <div class="pt-0.5">
                                                    <input type="checkbox" name="asset_ids[]" :value="asset.id" x-model="selectedAssets" class="w-4.5 h-4.5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 focus:ring-2 transition-all cursor-pointer mt-0.5">
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="font-semibold text-sm text-gray-900 group-hover:text-blue-700 transition-colors" x-text="asset.name"></span>
                                                        <span class="text-[9px] px-2 py-0.5 bg-gray-100 border border-gray-200 text-gray-600 rounded font-bold uppercase tracking-wide" x-text="asset.category?.name"></span>
                                                    </div>
                                                    <div class="text-[11px] text-gray-500 flex gap-4 font-medium">
                                                        <span x-show="asset.serial_number" title="Serial Number"><i class="fa-solid fa-barcode mr-1 opacity-50"></i> <span x-text="asset.serial_number"></span></span>
                                                        <span x-show="asset.location" title="Lokasi"><i class="fa-solid fa-location-dot mr-1 opacity-50"></i> <span x-text="asset.location ? asset.location.name : '-'"></span></span>
                                                    </div>
                                                </div>
                                                <div class="hidden sm:block">
                                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-md border" 
                                                          :class="{
                                                              'bg-green-50 text-green-700 border-green-200': asset.status === 'normal',
                                                              'bg-red-50 text-red-700 border-red-200': asset.status === 'rusak',
                                                              'bg-orange-50 text-orange-700 border-orange-200': asset.status === 'maintenance'
                                                          }" x-text="asset.status.toUpperCase()"></span>
                                                </div>
                                            </label>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            
                            {{-- Footer Asset Selection --}}
                            <div class="bg-blue-50/50 px-5 py-3 border-t border-blue-100 flex justify-between items-center transition-all">
                                <div class="text-xs font-semibold text-blue-800">
                                    <i class="fa-solid fa-check-double mr-1.5"></i>
                                    <span x-text="`${selectedAssets.length} dari ${filteredAssets.length} aset terpilih`"></span>
                                </div>
                                <div x-show="selectedAssets.length === 0" class="bg-blue-600 text-white px-3 py-1 rounded-full text-[10px] font-bold tracking-wide shadow-sm">
                                    Berlaku untuk SEMUA aset
                                </div>
                            </div>
                        </div>

                        {{-- Target Locations Selection (Visible if Target = Location) --}}
                        <div x-show="targetType === 'location'" x-transition.opacity.duration.300ms class="mt-6 border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                            <div class="bg-gray-50 px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h4 class="font-semibold text-gray-800 text-sm">Pilih Lokasi Perawatan</h4>
                                    <p class="text-[11px] text-gray-500 mt-0.5">Pilih area/kesatuan ruang mana saja yang termasuk dalam jadwal ini.</p>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text" x-model="searchQueryLocation" placeholder="Cari nama lokasi..." class="pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 w-full sm:w-48 transition-all outline-none">
                                    </div>
                                    <button type="button" @click="selectAllLocations()" class="text-xs bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-2 rounded-lg font-medium transition-colors whitespace-nowrap focus:outline-none">
                                        <span x-text="selectedLocations.length === filteredLocations.length && filteredLocations.length > 0 ? 'Batal Semua' : 'Pilih Semua'"></span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-0 max-h-[350px] overflow-y-auto">
                                <ul x-show="filteredLocations.length > 0" class="divide-y divide-gray-100">
                                    <template x-for="location in filteredLocations" :key="location.id">
                                        <li>
                                            <label class="flex items-center gap-4 p-4 hover:bg-purple-50/50 cursor-pointer transition-colors group">
                                                <div>
                                                    <input type="checkbox" name="location_ids[]" :value="location.id" x-model="selectedLocations" class="w-4.5 h-4.5 text-purple-600 rounded border-gray-300 focus:ring-purple-500 focus:ring-2 transition-all cursor-pointer">
                                                </div>
                                                <div class="flex-1">
                                                    <span class="font-semibold text-sm text-gray-900 group-hover:text-purple-700 transition-colors" x-text="location.name"></span>
                                                </div>
                                            </label>
                                        </li>
                                    </template>
                                </ul>
                                <div x-show="filteredLocations.length === 0" class="p-12 text-center text-gray-500">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-gray-100">
                                        <i class="fa-solid fa-location-dot text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-sm font-medium">Tidak ada lokasi ditemukan.</p>
                                </div>
                            </div>
                            
                            {{-- Footer Location Selection --}}
                            <div class="bg-purple-50/50 px-5 py-3 border-t border-purple-100 flex justify-between items-center transition-all">
                                <div class="text-xs font-semibold text-purple-800">
                                    <i class="fa-solid fa-check-double mr-1.5"></i>
                                    <span x-text="`${selectedLocations.length} lokasi terpilih`"></span>
                                </div>
                                <div x-show="selectedLocations.length === 0" class="text-[11px] font-semibold text-red-500">
                                    <i class="fa-solid fa-circle-exclamation mr-1"></i> Wajib pilih minimal 1 lokasi!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Schedule --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-white px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-900 flex items-center gap-2.5 text-lg">
                            <span class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-sm border border-orange-100"><i class="fa-solid fa-calendar-clock"></i></span>
                            Jadwal & Frekuensi
                        </h3>
                    </div>
                    <div class="p-6 space-y-8">
                        
                        {{-- Visual Frequency Selector --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Siklus Perawatan <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @php
                                    $frequencies = [
                                        ['value' => 'daily', 'icon' => 'fa-sun', 'color' => 'blue', 'title' => 'Harian', 'desc' => 'Setiap hari'],
                                        ['value' => 'weekly', 'icon' => 'fa-calendar-week', 'color' => 'purple', 'title' => 'Mingguan', 'desc' => 'Per pekan'],
                                        ['value' => 'monthly', 'icon' => 'fa-calendar-days', 'color' => 'orange', 'title' => 'Bulanan', 'desc' => 'Per bulan'],
                                        ['value' => 'yearly', 'icon' => 'fa-calendar', 'color' => 'red', 'title' => 'Tahunan', 'desc' => 'Per tahun'],
                                    ];
                                @endphp

                                @foreach($frequencies as $freq)
                                <label class="cursor-pointer group">
                                    <input type="radio" name="frequency" value="{{ $freq['value'] }}" x-model="frequency" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center bg-white border-gray-200 hover:bg-gray-50 hover:border-gray-300 peer-checked:border-{{ $freq['color'] }}-500 peer-checked:bg-{{ $freq['color'] }}-50/40 peer-checked:ring-1 peer-checked:ring-{{ $freq['color'] }}-500">
                                        <div class="w-10 h-10 mx-auto rounded-full bg-{{ $freq['color'] }}-100 text-{{ $freq['color'] }}-600 flex items-center justify-center mb-2.5 transition-transform duration-300 group-hover:scale-110">
                                            <i class="fa-solid {{ $freq['icon'] }} text-lg"></i>
                                        </div>
                                        <div class="font-bold text-gray-800 text-sm">{{ $freq['title'] }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $freq['desc'] }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Shift Selector --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Shift Kerja Target <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                                <select name="shift_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm outline-none bg-white">
                                    <option value="">— Semua Shift (Tugas Berulang di Tiap Shift) —</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>
                                            {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-gray-500 mt-2 leading-relaxed">Pilih shift jika tugas ini hanya khusus teknisi pada shift tertentu.</p>
                            </div>

                            {{-- Dynamic Date & Time Input --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal & Jam Eksekusi Target <span class="text-red-500">*</span></label>
                                <div x-data="{ timeValue: '{{ old('start_time') }}' }">
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        {{-- Input Tanggal --}}
                                        <div class="flex-1">
                                            <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all outline-none cursor-pointer text-gray-700" title="Tanggal Mulai">
                                        </div>
                                        
                                        {{-- Input Jam --}}
                                        <div class="w-full sm:w-40 shrink-0">
                                            <input type="time" name="start_time" x-model="timeValue" class="rounded-xl border border-gray-300 bg-gray-50/50 px-4 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all outline-none font-medium cursor-pointer text-gray-700" title="Jam Eksekusi Spesifik">
                                        </div>
                                    </div>
                                    
                                    {{-- Helper & Tombol Clear --}}
                                    <div class="mt-2.5 flex items-center justify-between px-1">
                                        <p class="text-[11px] text-gray-500">Kosongkan jam untuk tugas <strong>sepanjang hari</strong>.</p>
                                        
                                        {{-- Tombol ini hanya muncul jika jam sudah terisi --}}
                                        <button type="button" x-show="timeValue" @click="timeValue = ''" x-transition.opacity class="text-[11px] text-red-500 hover:text-red-700 font-medium transition-colors focus:outline-none flex items-center gap-1.5 bg-red-50 px-2 py-1 rounded-md">
                                            <i class="fa-solid fa-eraser"></i> Hapus Jam
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Dynamic Helper Text --}}
                                <div class="mt-3 flex gap-2.5 items-start text-[11px] text-gray-600 bg-gray-50/80 p-3 rounded-lg border border-gray-200/60">
                                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 text-xs shrink-0"></i>
                                    <div class="leading-relaxed font-medium">
                                        <span x-show="frequency === 'daily'">Generate otomatis <strong>setiap hari</strong> mulai tanggal ini.</span>
                                        <span x-show="frequency === 'weekly'" style="display: none;">Generate setiap <strong>hari yang sama</strong> per minggunya.</span>
                                        <span x-show="frequency === 'monthly'" style="display: none;">Generate pada <strong>tanggal yang sama</strong> setiap bulannya.</span>
                                        <span x-show="frequency === 'yearly'" style="display: none;">Generate pada <strong>tanggal & bulan tersebut</strong> setiap tahun.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: ADDITIONAL INFO --}}
            <div class="space-y-6">
                
                {{-- Card 3: Status & Note --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-6 lg:sticky lg:top-8">
                    
                    {{-- Active Status Toggle --}}
                    <div>
                        <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200 shadow-sm hover:border-blue-300 transition-colors cursor-pointer" @click="$refs.statusToggle.click()">
                            <div>
                                <span class="block text-sm font-bold text-gray-900">Status Aturan</span>
                                <span class="block text-[11px] text-gray-500 mt-0.5">Aktifkan untuk mulai berjalan otomatis</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer pointer-events-none">
                                <input type="checkbox" x-ref="statusToggle" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 shadow-inner"></div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-align-left text-gray-400"></i> Catatan Pelaksanaan <span class="text-gray-400 font-normal text-xs">(Opsional)</span>
                        </label>
                        <textarea name="notes" rows="5" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm outline-none resize-none" placeholder="Tulis instruksi khusus atau catatan untuk teknisi di sini...">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Warning Alert --}}
                    <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 flex items-start gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                        <div class="text-[11px] text-amber-800 leading-relaxed font-medium">
                            <strong>Informasi Penjadwalan:</strong><br>
                            Aturan baru atau perubahan jadwal akan mulai dieksekusi secara otomatis oleh sistem pada siklus <span class="bg-amber-100 px-1 py-0.5 rounded text-amber-900">Tengah Malam (00:00)</span> berikutnya.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    // Fungsi Alpine.js dibiarkan persis sama karena sudah berjalan dengan baik.
    function maintenancePlanForm() {
        return {
            targetType: '{{ old('target_type', 'asset') }}',
            frequency: '{{ old('frequency', 'daily') }}',
            configs: {!! json_encode(array_values(old('configs', [['category_id' => '', 'template_id' => '']]))) !!},
            assets: [],
            selectedAssets: {!! json_encode(old('asset_ids', [])) !!},
            locations: {!! json_encode($locations) !!},
            selectedLocations: {!! json_encode(old('location_ids', [])) !!},
            searchQueryAsset: '',
            searchQueryLocation: '',
            isLoading: false,

            init() {
                this.fetchAssets();
            },

            addConfig() {
                this.configs.push({ category_id: '', template_id: '' });
            },

            removeConfig(index) {
                this.configs.splice(index, 1);
                this.fetchAssets();
            },

            get selectedCategoryIds() {
                return this.configs
                    .map(c => c.category_id)
                    .filter(id => id !== '');
            },

            get filteredAssets() {
                if (this.searchQueryAsset === '') {
                    return this.assets;
                }
                const lowerCaseQuery = this.searchQueryAsset.toLowerCase();
                return this.assets.filter(asset => {
                    return asset.name.toLowerCase().includes(lowerCaseQuery) || 
                           (asset.serial_number && asset.serial_number.toLowerCase().includes(lowerCaseQuery));
                });
            },

            get filteredLocations() {
                if (this.searchQueryLocation === '') {
                    return this.locations;
                }
                const lowerCaseQuery = this.searchQueryLocation.toLowerCase();
                return this.locations.filter(location => {
                    return location.name.toLowerCase().includes(lowerCaseQuery);
                });
            },

            async fetchAssets() {
                const ids = this.selectedCategoryIds;
                if (ids.length === 0) {
                    this.assets = [];
                    this.selectedAssets = [];
                    return;
                }
                
                this.isLoading = true;
                
                try {
                    const response = await fetch(`/admin/assets/by-categories?category_ids=${ids.join(',')}&all=true`);
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        this.assets = result.data;
                        
                        const validAssetIds = this.assets.map(a => a.id.toString());
                        this.selectedAssets = this.selectedAssets.filter(id => validAssetIds.includes(id.toString()));
                    }
                } catch (error) {
                    console.error('Error fetching assets:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            selectAllAssets() {
                if (this.selectedAssets.length === this.filteredAssets.length && this.filteredAssets.length > 0) {
                    this.selectedAssets = [];
                } else {
                    this.selectedAssets = this.filteredAssets.map(asset => asset.id.toString());
                }
            },

            selectAllLocations() {
                if (this.selectedLocations.length === this.filteredLocations.length && this.filteredLocations.length > 0) {
                    this.selectedLocations = [];
                } else {
                    this.selectedLocations = this.filteredLocations.map(location => location.id.toString());
                }
            }
        }
    }
</script>
@endsection
