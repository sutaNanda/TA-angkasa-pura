@extends('layouts.admin')

@section('title', 'Tambah Rencana Perawatan')

@section('content')
{{-- Alpine.js for interactivity --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

<div class="container-fluid px-4 py-6" x-data="maintenancePlanForm()">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.plans.index') }}" class="hover:text-blue-600 transition">Rencana Perawatan</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-gray-800 font-medium">Buat Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Rencana Perawatan</h1>
            <p class="text-sm text-gray-500">Otomatisasi pembuatan tugas maintenance untuk banyak kategori aset sekaligus.</p>
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
                
                {{-- Card 1: Name & Template Configs (REPEATER) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-blue-100 text-blue-600 flex items-center justify-center text-xs"><i class="fa-solid fa-bullseye"></i></span>
                            Konfigurasi Target
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Inspeksi / Rencana <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Pengecekan Rutin Ruang PIONA" class="w-full rounded-lg focus:ring-blue-500 focus:border-blue-500 transition border-2 border-gray-700 pl-2 py-2">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <hr class="border-gray-100">

                        {{-- Multi-Category Repeater --}}
                        <div>
                            <div class="flex justify-between items-center mb-3">
                                <label class="block text-sm font-bold text-gray-700">Kategori & Template Target <span class="text-red-500">*</span></label>
                                <button type="button" @click="addConfig" class="text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-plus font-bold"></i> Tambah Kategori
                                </button>
                            </div>

                            <div class="space-y-3">
                                <template x-for="(config, index) in configs" :key="index">
                                    <div class="flex flex-col sm:flex-row gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200 relative group transition-all hover:border-blue-200">
                                        <div class="flex-1">
                                            <label class="block text-[10px] uppercase tracking-wider font-bold text-gray-500 mb-1">Kategori Aset</label>
                                            <select :name="`configs[${index}][category_id]`" x-model="config.category_id" @change="fetchAssets" required class="w-full rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 transition border-gray-300 py-2">
                                                <option value="">Pilih Kategori</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-[10px] uppercase tracking-wider font-bold text-gray-500 mb-1">Template Checklist</label>
                                            <select :name="`configs[${index}][template_id]`" x-model="config.template_id" required class="w-full rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 transition border-gray-300 py-2">
                                                <option value="">Pilih Template</option>
                                                @foreach($templates as $template)
                                                    <option value="{{ $template->id }}">{{ $template->name }} ({{ $template->frequency ?? '-' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex items-end pb-0.5">
                                            <button type="button" @click="removeConfig(index)" x-show="configs.length > 1" class="w-10 h-10 bg-red-50 text-red-500 hover:bg-red-500 hover:text-white rounded-lg transition-colors flex items-center justify-center">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            @error('configs') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Target Type Selector --}}
                        <div class="mt-8 border-t border-gray-100 pt-6">
                            <label class="block text-sm font-bold text-gray-700 mb-3">Target Pengecekan <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex-1 cursor-pointer group">
                                    <input type="radio" name="target_type" value="asset" x-model="targetType" class="peer sr-only">
                                    <div class="p-3 rounded-xl border-2 transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50/50 hover:bg-gray-50 border-gray-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                <i class="fa-solid fa-box text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-800 text-sm">Per Aset</div>
                                                <div class="text-[10px] text-gray-500">Buat tiket terpisah untuk tiap aset</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer group">
                                    <input type="radio" name="target_type" value="location" x-model="targetType" class="peer sr-only">
                                    <div class="p-3 rounded-xl border-2 transition-all duration-200 peer-checked:border-purple-500 peer-checked:bg-purple-50/50 hover:bg-gray-50 border-gray-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center">
                                                <i class="fa-solid fa-layer-group text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-800 text-sm">Kesatuan Lokasi</div>
                                                <div class="text-[10px] text-gray-500">Tiket gabungan untuk 1 area (Area-Centric)</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Target Assets Selection (Visible if Target = Asset) --}}
                        <div x-show="targetType === 'asset' && selectedCategoryIds.length > 0" x-transition class="mt-6 border border-gray-200 rounded-xl overflow-hidden shadow-inner bg-gray-50/30">
                            <div class="bg-gray-100/50 px-4 py-3 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm">Pilih Aset Spesifik</h4>
                                    <p class="text-xs text-gray-500 italic">Biarkan kosong jika ingin berlaku untuk <strong>semua</strong> aset di kategori-kategori terpilih.</p>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text" x-model="searchQueryAsset" placeholder="Cari by nama/SN..." class="pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full sm:w-48 shadow-sm">
                                    </div>
                                    <button type="button" @click="selectAllAssets()" class="text-xs bg-white border border-blue-200 hover:bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap shadow-sm">
                                        <span x-text="selectedAssets.length === filteredAssets.length && filteredAssets.length > 0 ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-0 max-h-[400px] overflow-y-auto bg-white">
                                <div x-show="isLoading" class="p-12 text-center text-gray-500">
                                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-3 text-blue-500"></i>
                                    <p class="text-sm font-medium">Mengambil daftar aset dari berbagai kategori...</p>
                                </div>
                                
                                <div x-show="!isLoading && filteredAssets.length === 0" style="display: none;" class="p-12 text-center text-gray-500">
                                    <i class="fa-solid fa-box-open text-4xl mb-4 text-gray-200"></i>
                                    <p class="text-sm">Tidak ada aset ditemukan pada kategori tersebut.</p>
                                </div>

                                <ul x-show="!isLoading && filteredAssets.length > 0" class="divide-y divide-gray-100">
                                    <template x-for="asset in filteredAssets" :key="asset.id">
                                        <li>
                                            <label class="flex items-start gap-3 p-4 hover:bg-blue-50/30 cursor-pointer transition">
                                                <div class="pt-1">
                                                    <input type="checkbox" name="asset_ids[]" :value="asset.id" x-model="selectedAssets" class="w-4.5 h-4.5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 transition-all cursor-pointer">
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-sm text-gray-900" x-text="asset.name"></span>
                                                        <span class="text-[10px] px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-bold uppercase tracking-wider" x-text="asset.category?.name"></span>
                                                    </div>
                                                    <div class="text-xs text-gray-400 mt-1 flex gap-4 font-medium">
                                                        <span x-show="asset.serial_number"><i class="fa-solid fa-barcode mr-1.5 opacity-50"></i> <span x-text="asset.serial_number"></span></span>
                                                        <span x-show="asset.location"><i class="fa-solid fa-location-dot mr-1.5 opacity-50"></i> <span x-text="asset.location ? asset.location.name : '-'"></span></span>
                                                    </div>
                                                </div>
                                                <div class="hidden sm:block">
                                                    <span class="px-2 py-1 text-[9px] font-black rounded-lg border tracking-widest uppercase shadow-sm" 
                                                          :class="{
                                                            'bg-green-50 text-green-700 border-green-100': asset.status === 'normal',
                                                            'bg-red-50 text-red-700 border-red-100': asset.status === 'rusak',
                                                            'bg-orange-50 text-orange-700 border-orange-100': asset.status === 'maintenance'
                                                          }" x-text="asset.status"></span>
                                                </div>
                                            </label>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div class="bg-blue-50/50 px-5 py-3 border-t border-gray-200 text-xs text-blue-600 flex justify-between font-bold items-center">
                                <div>
                                    <i class="fa-solid fa-check-double mr-1.5"></i>
                                    <span x-text="`${selectedAssets.length} aset terpilih`"></span>
                                </div>
                                <div x-show="selectedAssets.length === 0" class="bg-blue-600 text-white px-3 py-1 rounded-full text-[10px] uppercase tracking-widest animate-pulse">
                                    Berlaku untuk SEMUA aset
                                </div>
                            </div>
                        </div>

                        {{-- Target Locations Selection (Visible if Target = Location) --}}
                        <div x-show="targetType === 'location'" x-transition class="mt-6 border border-gray-200 rounded-xl overflow-hidden shadow-inner bg-gray-50/30">
                            <div class="bg-purple-50/50 px-4 py-3 border-b border-purple-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm text-purple-900">Pilih Lokasi Ruangan</h4>
                                    <p class="text-xs text-gray-500 italic">Pilih area/kesatuan ruang mana saja yang termasuk dalam jadwal perlakuan ini.</p>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text" x-model="searchQueryLocation" placeholder="Cari by nama..." class="pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 w-full sm:w-48 shadow-sm">
                                    </div>
                                    <button type="button" @click="selectAllLocations()" class="text-xs bg-white border border-purple-200 hover:bg-purple-50 text-purple-700 px-3 py-1.5 rounded-lg font-bold transition whitespace-nowrap shadow-sm">
                                        <span x-text="selectedLocations.length === filteredLocations.length && filteredLocations.length > 0 ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-0 max-h-[400px] overflow-y-auto bg-white">
                                <ul x-show="filteredLocations.length > 0" class="divide-y divide-gray-100">
                                    <template x-for="location in filteredLocations" :key="location.id">
                                        <li>
                                            <label class="flex items-start gap-3 p-4 hover:bg-purple-50/30 cursor-pointer transition">
                                                <div class="pt-1">
                                                    <input type="checkbox" name="location_ids[]" :value="location.id" x-model="selectedLocations" class="w-4.5 h-4.5 text-purple-600 rounded focus:ring-purple-500 border-gray-300 transition-all cursor-pointer">
                                                </div>
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-sm text-gray-900" x-text="location.name"></span>
                                                    </div>
                                                </div>
                                            </label>
                                        </li>
                                    </template>
                                </ul>
                                <div x-show="filteredLocations.length === 0" class="p-12 text-center text-gray-500">
                                    <i class="fa-solid fa-location-dot text-4xl mb-4 text-gray-200"></i>
                                    <p class="text-sm">Tidak ada lokasi ditemukan.</p>
                                </div>
                            </div>
                            <div class="bg-purple-50/50 px-5 py-3 border-t border-purple-200 text-xs text-purple-700 flex justify-between font-bold items-center">
                                <div>
                                    <i class="fa-solid fa-check-double mr-1.5"></i>
                                    <span x-text="`${selectedLocations.length} lokasi terpilih`"></span>
                                </div>
                                <div x-show="selectedLocations.length === 0" class="text-red-500">
                                    Mohon pilih minimal 1 lokasi!
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Schedule --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 text-center sm:text-left">
                        <h3 class="font-bold text-gray-800 flex items-center justify-center sm:justify-start gap-2">
                            <span class="w-6 h-6 rounded bg-purple-100 text-purple-600 flex items-center justify-center text-xs"><i class="fa-solid fa-clock"></i></span>
                            Jadwal & Frekuensi
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        
                        {{-- Visual Frequency Selector --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3 ml-1">Frekuensi Perawatan <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                
                                {{-- Daily --}}
                                <label class="cursor-pointer group">
                                    <input type="radio" name="frequency" value="daily" x-model="frequency" class="peer sr-only">
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50/50 hover:bg-gray-50 border-gray-200 shadow-sm">
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
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center peer-checked:border-purple-500 peer-checked:bg-purple-50/50 hover:bg-gray-50 border-gray-200 shadow-sm">
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
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center peer-checked:border-orange-500 peer-checked:bg-orange-50/50 hover:bg-gray-50 border-gray-200 shadow-sm">
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
                                    <div class="p-4 rounded-xl border-2 transition-all duration-200 text-center peer-checked:border-red-500 peer-checked:bg-red-50/50 hover:bg-gray-50 border-gray-200 shadow-sm">
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
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 transition-all duration-300">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Mulai / Referensi</label>
                            <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                            
                            {{-- Dynamic Helper Text --}}
                            <div class="mt-4 flex gap-3 items-start text-xs text-gray-500 bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                                <i class="fa-solid fa-circle-info text-blue-500 mt-1"></i>
                                <div class="leading-relaxed">
                                    <span x-show="frequency === 'daily'">
                                        <strong>Harian:</strong> Tugas akan digenerate <u>setiap hari</u> pada pukul 00:00 untuk semua aset di kategori terpilih.
                                    </span>
                                    <span x-show="frequency === 'weekly'" style="display: none;">
                                        <strong>Mingguan:</strong> Tugas digenerate pada hari yang sama setiap pekannya berdasarkan tanggal di atas.
                                    </span>
                                    <span x-show="frequency === 'monthly'" style="display: none;">
                                        <strong>Bulanan:</strong> Tugas digenerate setiap tanggal tersebut setiap bulannya.
                                    </span>
                                    <span x-show="frequency === 'yearly'" style="display: none;">
                                        <strong>Tahunan:</strong> Tugas digenerate sekali setahun pada tanggal dan bulan tersebut.
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6 lg:sticky lg:top-6">
                    
                    {{-- Active Status --}}
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded-lg focus:ring-blue-500 border-blue-300 transition-all">
                            <span class="text-sm font-bold text-blue-900">Aktifkan Aturan</span>
                        </label>
                    </div>

                    <hr class="border-gray-50">

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-note-sticky text-gray-400"></i> Catatan Internal
                        </label>
                        <textarea name="notes" rows="6" class="w-full border-gray-200 rounded-xl text-sm focus:ring-blue-500 focus:border-blue-500 pl-3 py-3 shadow-sm" placeholder="Contoh: Pastikan teknisi mematikan daya sebelum pengecekan.">{{ old('notes') }}</textarea>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100 text-[10px] text-yellow-800 leading-relaxed font-medium">
                        <i class="fa-solid fa-triangle-exclamation mr-1 text-yellow-600"></i>
                        PENTING: Perubahan jadwal ini akan mulai berlaku pada siklus generate berikutnya (Tengah Malam).
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    function maintenancePlanForm() {
        return {
            targetType: '{{ old('target_type', 'asset') }}',
            frequency: '{{ old('frequency', 'daily') }}',
            configs: [
                { category_id: '', template_id: '' }
            ],
            assets: [],
            selectedAssets: {!! json_encode(old('asset_ids', [])) !!},
            locations: {!! json_encode($locations) !!},
            selectedLocations: {!! json_encode(old('location_ids', [])) !!},
            searchQueryAsset: '',
            searchQueryLocation: '',
            isLoading: false,

            init() {
                // Initial fetch if we have old data (not implemented fully for old state here, but good practice)
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
                    // Use the new plural endpoint
                    const response = await fetch(`/admin/assets/by-categories?category_ids=${ids.join(',')}&all=true`);
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        this.assets = result.data;
                        
                        // Sync current selection (remove assets that are no longer in selected categories)
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
