@extends('layouts.admin')

@section('title', 'Edit Rencana Perawatan')

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
                <span class="text-gray-800 font-medium">Edit Rencana</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Rencana Perawatan</h1>
            <p class="text-sm text-gray-500">Perbarui detail rencana perawatan aset.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.plans.index') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                Batal
            </a>
            <button type="submit" form="editPlanForm" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Update Rencana
            </button>
        </div>
    </div>

    <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" id="editPlanForm">
        @csrf
        @method('PUT')

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
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Inspeksi / Rencana <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $plan->name) }}" required placeholder="Contoh: Pengecekan Rutin AC Bulanan" class="w-full rounded-lg focus:ring-blue-500 focus:border-blue-500 transition border-2 border-gray-700 pl-2 py-2">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Kategori Aset <span class="text-red-500">*</span></label>
                            <select name="category_id" x-model="categoryId" @change="fetchAssets" required class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm border-2 border-gray-700 pl-2 py-2">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Template --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Template Checklist <span class="text-red-500">*</span></label>
                            <select name="checklist_template_id" required class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition shadow-sm border-2 border-gray-700 pl-2 py-2">
                                <option value="">-- Pilih Template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ (old('checklist_template_id') ?? $plan->checklist_template_id) == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} ({{ $template->frequency ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('checklist_template_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Target Assets Selection --}}
                        <div x-show="categoryId" style="display: none;" class="mt-6 border border-gray-200 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <h4 class="font-bold text-gray-800 text-sm">Pilih Aset Spesifik</h4>
                                    <p class="text-xs text-gray-500">Biarkan kosong jika ingin berlaku untuk <strong>semua</strong> aset di kategori ini.</p>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                        <input type="text" x-model="searchQuery" placeholder="Cari by nama/SN..." class="pl-8 pr-3 py-1.5 text-xs border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full sm:w-48">
                                    </div>
                                    <button type="button" @click="selectAll()" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded-lg font-medium transition whitespace-nowrap">
                                        <span x-text="selectedAssets.length === filteredAssets.length && filteredAssets.length > 0 ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="p-0 max-h-[300px] overflow-y-auto bg-white">
                                <div x-show="isLoading" class="p-8 text-center text-gray-500">
                                    <i class="fa-solid fa-spinner fa-spin text-xl mb-2 text-blue-500"></i>
                                    <p class="text-sm">Memuat daftar aset...</p>
                                </div>
                                
                                <div x-show="!isLoading && filteredAssets.length === 0" style="display: none;" class="p-8 text-center text-gray-500">
                                    <i class="fa-solid fa-box-open text-3xl mb-3 text-gray-300"></i>
                                    <p class="text-sm">Tidak ada aset ditemukan.</p>
                                </div>

                                <ul x-show="!isLoading && filteredAssets.length > 0" class="divide-y divide-gray-100">
                                    <template x-for="asset in filteredAssets" :key="asset.id">
                                        <li>
                                            <label class="flex items-start gap-3 p-3 hover:bg-gray-50 cursor-pointer transition">
                                                <div class="pt-0.5">
                                                    <input type="checkbox" name="asset_ids[]" :value="asset.id" x-model="selectedAssets" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300 mt-1">
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-bold text-sm text-gray-800" x-text="asset.name"></div>
                                                    <div class="text-xs text-gray-500 mt-0.5 flex gap-3">
                                                        <span x-show="asset.serial_number"><i class="fa-solid fa-barcode mr-1"></i> <span x-text="asset.serial_number"></span></span>
                                                        <span x-show="asset.location"><i class="fa-solid fa-location-dot mr-1"></i> <span x-text="asset.location ? asset.location.name : '-'"></span></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="px-2 py-1 text-[10px] font-bold rounded-full" 
                                                          :class="{
                                                            'bg-green-100 text-green-700': asset.status === 'normal',
                                                            'bg-red-100 text-red-700': asset.status === 'rusak',
                                                            'bg-yellow-100 text-yellow-700': asset.status === 'maintenance'
                                                          }" x-text="asset.status.toUpperCase()"></span>
                                                </div>
                                            </label>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                            <div class="bg-gray-50 px-4 py-2 border-t border-gray-200 text-xs text-gray-500 flex justify-between">
                                <span x-text="`${selectedAssets.length} aset dipilih dari total ${assets.length}`"></span>
                                <span x-show="selectedAssets.length === 0" class="text-blue-600 font-medium"><i class="fa-solid fa-info-circle mr-1"></i> Akan berlaku untuk semua aset</span>
                            </div>
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
                            <input type="date" name="start_date" value="{{ old('start_date', $plan->start_date->format('Y-m-d')) }}" required class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition">
                            
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
                            <input type="checkbox" name="is_active" value="1" {{ (old('is_active') ?? $plan->is_active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                            <span class="text-sm font-bold text-gray-700">Aktifkan aturan ini</span>
                        </label>
                    </div>

                    <hr class="border-gray-100">

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea name="notes" rows="4" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Dilakukan oleh tim vendor...">{{ old('notes') ?? $plan->notes }}</textarea>
                    </div>

                    {{-- Info Card --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                         <h4 class="text-blue-800 font-bold text-xs mb-2 uppercase">Info Aset</h4>
                         <p class="text-sm text-gray-600">
                             Kategori: <strong>{{ $plan->category->name }}</strong><br>
                             Total Aset: <strong>{{ $plan->affected_assets_count }} unit</strong>
                         </p>
                    </div>

                </div>

            </div>
        </div>
    </form>
</div>

<script>
    function maintenancePlanForm() {
        return {
            frequency: '{{ old('frequency', $plan->frequency) }}',
            categoryId: '{{ old('category_id', $plan->category_id) }}',
            assets: [],
            selectedAssets: {!! json_encode(old('asset_ids', $plan->assets->pluck('id')->toArray())) !!}.map(String),
            searchQuery: '',
            isLoading: false,

            init() {
                if(this.categoryId) {
                    this.fetchAssets();
                }
            },

            get filteredAssets() {
                if (this.searchQuery === '') {
                    return this.assets;
                }
                const lowerCaseQuery = this.searchQuery.toLowerCase();
                return this.assets.filter(asset => {
                    return asset.name.toLowerCase().includes(lowerCaseQuery) || 
                           (asset.serial_number && asset.serial_number.toLowerCase().includes(lowerCaseQuery));
                });
            },

            async fetchAssets() {
                if (!this.categoryId) {
                    this.assets = [];
                    this.selectedAssets = [];
                    return;
                }
                
                this.isLoading = true;
                
                try {
                    const response = await fetch(`/admin/assets/by-category/${this.categoryId}?all=true`);
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        this.assets = result.data;
                        
                        // Hapus selectedAssets yang tidak ada di category ini
                        const validAssetIds = this.assets.map(a => a.id.toString());
                        this.selectedAssets = this.selectedAssets.filter(id => validAssetIds.includes(id.toString()));
                    }
                } catch (error) {
                    console.error('Error fetching assets:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            selectAll() {
                if (this.selectedAssets.length === this.filteredAssets.length && this.filteredAssets.length > 0) {
                    // Deselect all filtered
                    this.selectedAssets = [];
                } else {
                    // Select all filtered
                    this.selectedAssets = this.filteredAssets.map(asset => asset.id.toString());
                }
            }
        }
    }
</script>
@endsection
