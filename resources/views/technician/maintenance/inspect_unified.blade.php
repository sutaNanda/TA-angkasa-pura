@extends('layouts.technician')

@section('header_title', 'Tugas Perawatan Area')

@section('content')
<div class="container mx-auto px-4 pb-28" x-data="inspectionAreaForm()">
    
    {{-- Info Tugas & Lokasi --}}
    <div class="mb-6 mt-4">
        <span class="bg-orange-100 text-orange-700 text-[10px] font-black px-2 py-0.5 rounded-md uppercase tracking-widest border border-orange-200 mb-2 inline-block">
            TUGAS KESATUAN AREA
        </span>
        <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">{{ $templateName ?? 'Inspeksi Area' }}</h2>
        <p class="text-gray-500 text-sm mt-1">
            <i class="fa-solid fa-map-marker-alt text-blue-500 mr-2"></i>Lokasi: <span class="font-bold">{{ $maintenance->location->name ?? 'Keseluruhan' }}</span>
        </p>
    </div>

    @php
        $formAction = isset($maintenanceIdsStr) ? route('technician.locations.maintenance.inspect_group.store') : route('technician.locations.maintenance.inspect.store', $maintenance->id);
    @endphp
    <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" @submit.prevent="handleSubmit($event)">
        @csrf
        @if(isset($maintenanceIdsStr))
            <input type="hidden" name="maintenance_ids" value="{{ $maintenanceIdsStr }}">
            <input type="hidden" name="location_id" value="{{ $primaryLocation->id ?? null }}">
        @endif
        <input type="hidden" name="primary_template_id" value="{{ $primaryTemplateId }}">

        {{-- WADAH TABEL UTAMA --}}
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mb-6 inspect-wrapper">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap md:whitespace-normal inspect-table">

                    {{-- HEADER TABEL --}}
                    <thead class="bg-slate-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-4 text-left w-12 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-5 py-4 text-left min-w-[250px] text-xs font-semibold text-slate-500 uppercase tracking-wider">Deskripsi Pengecekan</th>
                            <th class="px-5 py-4 text-center w-24 text-xs font-semibold text-slate-500 uppercase tracking-wider">Error</th>
                            <th class="px-5 py-4 text-center w-20 text-xs font-semibold text-slate-500 uppercase tracking-wider">N/A</th>
                            <th class="px-5 py-4 text-center w-24 text-xs font-semibold text-slate-500 uppercase tracking-wider">Normal</th>
                            <th class="px-5 py-4 text-left min-w-[280px] text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan / Hasil</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50">
                        @php
                            $alphabets = range('a', 'z');
                            $groups = isset($groupedTemplates) ? $groupedTemplates : [
                                ['template_name' => $templateName, 'category_name' => $templateName, 'category_id' => null, 'items' => $items]
                            ];
                        @endphp

                        @foreach($groups as $groupIndex => $group)

                            {{-- LEVEL 1: BARIS KATEGORI SOP (misal: HARDWARE, ACCESS POINT) --}}
                            <tr class="section-header-row">
                                <td colspan="6" class="bg-slate-100 px-5 py-3 border-y border-gray-200">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex-shrink-0 w-5 h-5 rounded bg-slate-400/20 flex items-center justify-center">
                                            <i class="fa-solid fa-layer-group text-[10px] text-slate-500"></i>
                                        </span>
                                        <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">{{ $group['category_name'] ?? 'Umum' }}</span>
                                        @if(($group['template_name'] ?? '') !== ($group['category_name'] ?? '') && !empty($group['template_name']))
                                            <span class="text-gray-300 select-none">›</span>
                                            <span class="text-xs font-semibold text-slate-500">{{ $group['template_name'] }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            @php
                                $headerCount = 0;
                                $itemCount = 0;
                            @endphp

                            @foreach($group['items'] as $item)
                            @if($item->type === 'header')
                                @php $headerCount++; $itemCount = 0; @endphp
                                {{-- LEVEL 2: BARIS SUB-HEADER ITEM --}}
                                <tr class="section-header-row">
                                    <td colspan="6" class="bg-slate-50 px-5 py-2.5 border-b border-gray-100">
                                        <div class="flex items-center gap-2 pl-4">
                                            <span class="w-1 h-4 rounded-full bg-slate-300 flex-shrink-0"></span>
                                            <span class="text-sm font-semibold text-slate-700 col-header-text">
                                                <span class="col-header-no inline-block w-6 text-slate-400 font-bold">{{ $headerCount }}.</span>
                                                <span class="mobile-only-number mr-1 hidden">{{ $headerCount }}.</span>{{ $item->question }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @php
                                    // Cari aset yang relevan untuk kategori ini
                                    $groupCategoryId = $group['category_id'] ?? null;
                                    $relevantAssets = $groupCategoryId && isset($assetsByCategory[$groupCategoryId])
                                        ? $assetsByCategory[$groupCategoryId]
                                        : ($assets ?? collect());
                                    $gridId = 'triage-grid-' . $item->id;
                                @endphp

                                {{-- LEVEL 3: BARIS PERTANYAAN --}}
                                <tr x-data="{ isError: false }"
                                    class="border-b border-gray-100 hover:bg-slate-50/50 transition-colors duration-150 question-row"
                                    :class="isError ? 'bg-red-50/30' : ''">

                                    {{-- No. Sub-item --}}
                                    <td class="px-5 py-4 align-middle text-center w-12 col-alphabet">
                                        <span class="text-xs font-medium text-slate-400">{{ $alphabets[$itemCount] ?? '-' }}.</span>
                                    </td>

                                    {{-- Deskripsi Pertanyaan --}}
                                    <td class="px-5 py-4 align-middle whitespace-normal col-question">
                                        <span class="text-sm text-gray-800 leading-snug">{{ $item->question }}</span>
                                    </td>

                                    {{-- Radio: Error --}}
                                    <td class="px-5 py-4 align-middle text-center col-error">
                                        <input type="radio" name="answers[{{ $item->id }}]" value="fail"
                                               id="radio-fail-{{ $item->id }}"
                                               class="w-5 h-5 cursor-pointer border-gray-300 focus:ring-2 focus:ring-offset-1 accent-red-500 radio-input issue-trigger"
                                               required
                                               @change="isError = true; checkGlobalIssue()"
                                               data-grid="{{ $gridId }}" data-mode="error">
                                    </td>

                                    {{-- Radio: N/A --}}
                                    <td class="px-5 py-4 align-middle text-center col-na">
                                        <input type="radio" name="answers[{{ $item->id }}]" value="na"
                                               class="w-5 h-5 cursor-pointer border-gray-300 focus:ring-2 focus:ring-offset-1 accent-slate-400 radio-input"
                                               required
                                               @change="isError = false; checkGlobalIssue()"
                                               data-grid="{{ $gridId }}" data-mode="clear">
                                    </td>

                                    {{-- Radio: Normal --}}
                                    <td class="px-5 py-4 align-middle text-center col-normal">
                                        <input type="radio" name="answers[{{ $item->id }}]" value="pass"
                                               class="w-5 h-5 cursor-pointer border-gray-300 focus:ring-2 focus:ring-offset-1 accent-emerald-500 radio-input"
                                               required
                                               @change="isError = false; checkGlobalIssue()"
                                               data-grid="{{ $gridId }}" data-mode="clear">
                                    </td>

                                    {{-- Kolom Keterangan --}}
                                    <td class="px-5 py-3.5 align-top col-notes">
                                        <div class="space-y-2">
                                            @if($item->type === 'number')
                                                <div class="flex items-center gap-2">
                                                    <input type="number" step="any" name="notes[{{ $item->id }}]"
                                                           class="w-full bg-transparent border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all placeholder-gray-400"
                                                           placeholder="Ketik Angka Hasil..." required>
                                                    @if($item->unit)
                                                        <span class="flex-shrink-0 text-xs font-medium text-gray-500 bg-gray-50 px-2.5 py-2 rounded-lg border border-gray-200">{{ $item->unit }}</span>
                                                    @endif
                                                </div>
                                            @elseif($item->type === 'text')
                                                <input type="text" name="notes[{{ $item->id }}]"
                                                       class="w-full bg-transparent border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all placeholder-gray-400"
                                                       placeholder="Ketik hasil pengecekan..." required>
                                            @else
                                                <input type="text" name="notes[{{ $item->id }}]"
                                                       class="w-full bg-transparent border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all placeholder-gray-400"
                                                       placeholder="Keterangan opsional...">
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                {{-- BARIS MASS TRIAGE GRID (muncul saat Error dipilih) --}}
                                <tr id="{{ $gridId }}" class="hidden mass-triage-row border-b border-red-100">
                                    <td colspan="6" class="px-5 py-4 bg-red-50/60">

                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
                                                <i class="fa-solid fa-triangle-exclamation text-[9px] text-red-500"></i>
                                            </span>
                                            <span class="text-xs font-bold text-red-700 uppercase tracking-wide">Tandai Unit yang Bermasalah</span>
                                            <span class="ml-auto text-[10px] text-red-400 font-medium" id="count-{{ $item->id }}"></span>
                                        </div>

                                        {{-- HIDDEN INPUTS container (diisi JS) --}}
                                        <div id="hidden-inputs-{{ $item->id }}"></div>

                                        @if($relevantAssets->count() > 0)
                                            {{-- ASSET TILES GRID --}}
                                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2 mb-3">
                                                @foreach($relevantAssets as $assetTile)
                                                    <button type="button"
                                                            class="asset-tile group relative flex flex-col items-center justify-center gap-1 min-h-[64px] px-2 py-2 rounded-xl border-2 border-gray-200 bg-white text-center transition-all duration-150 active:scale-95 touch-manipulation"
                                                            data-asset-id="{{ $assetTile->id }}"
                                                            data-item-id="{{ $item->id }}"
                                                            data-asset-name="{{ $assetTile->name }}"
                                                            title="{{ $assetTile->name }} | SN: {{ $assetTile->serial_number ?? '-' }}">
                                                        <i class="fa-solid fa-desktop text-gray-300 text-base group-[.selected]:text-white transition-colors"></i>
                                                        <span class="text-[10px] font-bold text-gray-600 leading-tight group-[.selected]:text-white transition-colors text-center line-clamp-2">
                                                            {{ Str::limit($assetTile->name, 18) }}
                                                        </span>
                                                        {{-- Checkmark overlay --}}
                                                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full items-center justify-center hidden group-[.selected]:flex">
                                                            <i class="fa-solid fa-check text-white" style="font-size:8px"></i>
                                                        </span>
                                                    </button>
                                                @endforeach
                                            </div>

                                            {{-- Tombol: Masalah Area (bukan aset spesifik) --}}
                                            <button type="button"
                                                    class="asset-tile-general w-full flex items-center justify-center gap-2 py-2 px-4 rounded-xl border-2 border-dashed border-red-200 bg-white text-red-500 hover:bg-red-50 text-xs font-semibold transition-all active:scale-95"
                                                    data-item-id="{{ $item->id }}">
                                                <i class="fa-solid fa-building-circle-exclamation"></i>
                                                <span>Masalah Area Umum (Bukan Aset Spesifik)</span>
                                            </button>

                                            {{-- Counter & info --}}
                                            <p class="text-[10px] text-red-400 mt-2 flex items-center gap-1">
                                                <i class="fa-solid fa-circle-info"></i>
                                                Ketuk unit yang bermasalah. Tiket perbaikan terpisah akan dibuat untuk setiap unit.
                                            </p>
                                        @else
                                            {{-- Fallback jika tidak ada aset terdaftar --}}
                                            <button type="button"
                                                    class="asset-tile-general w-full flex items-center justify-center gap-2 py-2 px-4 rounded-xl border-2 border-dashed border-red-200 bg-white text-red-500 hover:bg-red-50 text-xs font-semibold transition-all active:scale-95"
                                                    data-item-id="{{ $item->id }}">
                                                <i class="fa-solid fa-building-circle-exclamation"></i>
                                                <span>Laporkan Masalah Area Umum</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                @php $itemCount++; @endphp
                            @endif
                            @endforeach

                        @endforeach

                        {{-- WADAH GLOBAL LAPORAN KERUSAKAN --}}
                        <tr class="bg-gray-50/80 global-notes-row transition-all duration-300" :class="hasGlobalIssue ? 'bg-red-50/80 ring-2 ring-red-400 inset-0' : 'hidden'" x-show="hasGlobalIssue" x-cloak>
                            <td colspan="6" class="p-5">
                                <div class="flex flex-col md:flex-row gap-6">
                                    <div class="flex-1">
                                        <label class="text-[10px] font-black uppercase tracking-widest mb-2 flex items-center gap-1 text-red-700">
                                            <i class="fa-solid fa-triangle-exclamation text-red-600 animate-pulse"></i> 
                                            <span>Detail Laporan Kerusakan</span> 
                                            <span class="text-red-500 ml-2 bg-red-100 px-2 py-0.5 rounded">*Wajib</span>
                                        </label>
                                        <textarea name="global_notes" rows="3" 
                                                  class="w-full p-3 border border-red-400 bg-white focus:ring-red-500 rounded-lg text-sm transition-colors" 
                                                  placeholder="Jelaskan detail kerusakannya secara keseluruhan di sini..." :required="hasGlobalIssue"></textarea>
                                    </div>
                                    <div class="w-full md:w-1/3">
                                        <label class="text-[10px] font-black text-red-700 uppercase tracking-widest mb-2 flex items-center gap-1">
                                            <i class="fa-solid fa-camera text-red-500"></i> Upload Foto Bukti Area
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="(photo, index) in globalPhotos" :key="index">
                                                <div class="relative w-16 h-16 rounded border border-gray-300 overflow-hidden group">
                                                    <img :src="photo.preview" class="w-full h-full object-cover">
                                                    <button type="button" @click="removePhoto(index)" class="absolute top-0 right-0 w-5 h-5 bg-red-500 text-white flex items-center justify-center text-[10px] opacity-0 group-hover:opacity-100 transition-opacity"><i class="fa-solid fa-xmark"></i></button>
                                                </div>
                                            </template>
                                            <label class="w-16 h-16 rounded border-2 border-dashed flex flex-col items-center justify-center cursor-pointer transition-colors border-red-300 hover:bg-red-100 text-red-400 bg-white">
                                                <input type="file" accept="image/*" class="hidden" multiple @change="addPhoto($event)">
                                                <i class="fa-solid fa-plus text-sm"></i>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tombol Kirim --}}
        <div class="sticky bottom-6 z-40 px-4 md:px-0">
            <button type="submit" id="submitBtn"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3.5 px-6 rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all flex justify-center items-center gap-2.5 disabled:opacity-60 disabled:cursor-not-allowed">
                <i id="submitIcon" class="fa-solid fa-check-circle text-base"></i>
                <span id="submitText" class="tracking-wide">TANDAI AREA SELESAI DI-CEK</span>
            </button>
        </div>
    </form>

    {{-- MODAL TRIAGE --}}
    <div x-data="{ show: false, ticketUrl: '#', locationUrl: '#', isSubmitting: false }"
         @open-triage-modal.window="show = true; ticketUrl = $event.detail.ticketUrl; locationUrl = $event.detail.locationUrl; isSubmitting = false"
         x-show="show" style="display: none;" 
         class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden relative z-10 p-6 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                <i class="fa-solid fa-triangle-exclamation text-3xl text-red-600"></i>
            </div>
            
            <h3 class="font-bold text-xl text-gray-800 mb-2">Temuan Masalah!</h3>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                Anda mendeteksi kerusakan pada area ini. Tiket perbaikan telah dibuat secara otomatis. Apakah Anda ingin memperbaikinya sekarang?
            </p>

            <div class="grid grid-cols-2 gap-3">
                <a :href="locationUrl" class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-gray-100 hover:bg-gray-50 transition active:scale-95">
                    <i class="fa-solid fa-list-check text-gray-400 text-xl mb-1"></i>
                    <span class="font-bold text-gray-600 text-xs">Biarkan Terbuka</span>
                    <span class="text-[10px] text-gray-400 mt-1">Ke Dashboard</span>
                </a>

                <a :href="ticketUrl" class="flex flex-col items-center justify-center p-3 rounded-xl bg-red-600 text-white shadow-lg shadow-red-200 hover:bg-red-700 transition active:scale-95">
                    <i class="fa-solid fa-screwdriver-wrench text-xl mb-1"></i>
                    <span class="font-bold text-xs">Perbaiki Sekarang</span>
                    <span class="text-[10px] text-red-100 mt-1">Buka Tiket</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function inspectionAreaForm() {
    return {
        hasGlobalIssue: false,
        globalPhotos: [], 
        
        checkGlobalIssue() {
            this.hasGlobalIssue = Array.from(document.querySelectorAll('.issue-trigger')).some(input => input.checked);
            
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitIcon = document.getElementById('submitIcon');

            if (this.hasGlobalIssue) {
                submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                submitBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                submitText.innerText = 'SELESAI & BUAT TIKET MASALAH';
                submitIcon.className = 'fa-solid fa-triangle-exclamation animate-pulse';
            } else {
                submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                submitText.innerText = 'TANDAI AREA SELESAI DI-CEK';
                submitIcon.className = 'fa-solid fa-check-circle';
            }
        },

        addPhoto(event) {
            const files = event.target.files;
            if (!files.length) return;
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => { 
                    this.globalPhotos.push({ file: file, preview: e.target.result }); 
                };
                reader.readAsDataURL(file);
            });
            event.target.value = '';
        },
        removePhoto(index) { 
            this.globalPhotos.splice(index, 1); 
        },
        
        async handleSubmit(event) {
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitIcon = document.getElementById('submitIcon');
            const originalText = submitText.innerText;
            const originalIconClass = submitIcon.className;
            
            submitBtn.disabled = true;
            submitText.innerText = 'MENYIMPAN...';
            submitIcon.className = 'fa-solid fa-spinner fa-spin';

            try {
                const formData = new FormData(form);
                
                this.globalPhotos.forEach(p => {
                    formData.append(`photos[]`, p.file);
                });

                let result;
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    result = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(result.message || 'Terjadi kesalahan internal server.');
                    }
                } catch (e) {
                    throw new Error(e.message || 'Gagal terhubung ke server. Cek koneksi Anda.');
                }

                if (result.status === 'success') {
                    if (result.has_issue) {
                        window.dispatchEvent(new CustomEvent('open-triage-modal', { 
                            detail: { 
                                ticketUrl: result.redirect_url_ticket,
                                locationUrl: result.redirect_url_location 
                            }
                        }));
                    } else {
                        window.location.href = result.redirect_url_location;
                    }
                } else {
                    throw new Error(result.message);
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan:\n' + error.message);
                
                submitBtn.disabled = false;
                submitText.innerText = originalText;
                submitIcon.className = originalIconClass;
            }
        }
    }
}

// ============================================================
// MASS TRIAGE GRID — Vanilla JS (tidak bergantung Alpine.js)
// ============================================================
document.addEventListener('DOMContentLoaded', function () {

    // --- 1. Hubungkan Radio Button dengan Triage Grid ---
    document.querySelectorAll('input[type="radio"][data-grid]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const gridId  = this.dataset.grid;
            const mode    = this.dataset.mode; // 'error' | 'clear'
            const gridRow = document.getElementById(gridId);
            if (!gridRow) return;

            if (mode === 'error') {
                // Tampilkan grid dengan animasi slide-down
                gridRow.classList.remove('hidden');
                gridRow.style.opacity = '0';
                gridRow.style.transform = 'translateY(-8px)';
                requestAnimationFrame(() => {
                    gridRow.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                    gridRow.style.opacity    = '1';
                    gridRow.style.transform  = 'translateY(0)';
                });
            } else {
                // Sembunyikan grid & reset semua pilihan
                gridRow.classList.add('hidden');
                resetGrid(gridId);
            }
        });
    });

    // --- 2. Helper: Reset semua tile & hidden inputs dalam satu grid ---
    function resetGrid(gridId) {
        const gridRow = document.getElementById(gridId);
        if (!gridRow) return;

        // Reset tiles
        gridRow.querySelectorAll('.asset-tile.selected').forEach(function (tile) {
            deselectTile(tile);
        });
        gridRow.querySelectorAll('.asset-tile-general.selected').forEach(function (btn) {
            btn.classList.remove('selected', 'bg-red-500', 'text-white', 'border-red-500');
            btn.classList.add('text-red-500');
        });

        // Hapus semua hidden inputs
        const itemId = getItemIdFromGrid(gridId);
        if (itemId) {
            const container = document.getElementById('hidden-inputs-' + itemId);
            if (container) container.innerHTML = '';
            updateCounter(itemId, 0);
        }
    }

    function getItemIdFromGrid(gridId) {
        return gridId.replace('triage-grid-', '');
    }

    // --- 3. Handle klik Asset Tile ---
    document.querySelectorAll('.asset-tile').forEach(function (tile) {
        tile.addEventListener('click', function () {
            const assetId  = this.dataset.assetId;
            const itemId   = this.dataset.itemId;
            const isSelected = this.classList.contains('selected');

            if (isSelected) {
                deselectTile(this);
                removeHiddenInput(itemId, assetId);
            } else {
                selectTile(this);
                addHiddenInput(itemId, assetId);
            }
            updateCounter(itemId);
        });
    });

    // --- 4. Handle klik "Masalah Area Umum" ---
    document.querySelectorAll('.asset-tile-general').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const itemId    = this.dataset.itemId;
            const isSelected = this.classList.contains('selected');

            if (isSelected) {
                // Deselect: hapus style & hidden input
                this.classList.remove('selected', 'bg-red-500', 'text-white', 'border-solid', 'border-red-500');
                this.classList.add('text-red-500', 'border-dashed');
                removeHiddenInput(itemId, 'area_general');
            } else {
                // Select
                this.classList.add('selected', 'bg-red-500', 'text-white', 'border-solid', 'border-red-500');
                this.classList.remove('text-red-500', 'border-dashed');
                addHiddenInput(itemId, 'area_general');
            }
            updateCounter(itemId);
        });
    });

    // --- 5. Tile visual helpers ---
    function selectTile(tile) {
        tile.classList.add('selected', 'bg-red-500', 'border-red-600', 'shadow-md', 'shadow-red-500/40', 'scale-105', 'z-10');
        tile.classList.remove('border-gray-200', 'bg-white');
        // Ubah ikon warna
        const icon = tile.querySelector('i');
        if (icon) { icon.classList.remove('text-gray-300'); icon.classList.add('text-white'); }
        const label = tile.querySelector('span.text-\\[10px\\]');
        if (label) { label.classList.add('text-white'); label.classList.remove('text-gray-600'); }
    }

    function deselectTile(tile) {
        tile.classList.remove('selected', 'bg-red-500', 'border-red-600', 'shadow-md', 'shadow-red-500/40', 'scale-105', 'z-10');
        tile.classList.add('border-gray-200', 'bg-white');
        const icon = tile.querySelector('i');
        if (icon) { icon.classList.add('text-gray-300'); icon.classList.remove('text-white'); }
        const label = tile.querySelector('span.text-\\[10px\\]');
        if (label) { label.classList.remove('text-white'); label.classList.add('text-gray-600'); }
    }

    // --- 6. Hidden Input management ---
    function addHiddenInput(itemId, assetId) {
        const container = document.getElementById('hidden-inputs-' + itemId);
        if (!container) return;
        // Cegah duplikat
        if (container.querySelector(`input[value="${assetId}"]`)) return;
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'failed_assets[' + itemId + '][]';
        input.value = assetId;
        container.appendChild(input);
    }

    function removeHiddenInput(itemId, assetId) {
        const container = document.getElementById('hidden-inputs-' + itemId);
        if (!container) return;
        const input = container.querySelector(`input[value="${assetId}"]`);
        if (input) input.remove();
    }

    // --- 7. Counter badge ---
    function updateCounter(itemId, forceCount) {
        const counter = document.getElementById('count-' + itemId);
        if (!counter) return;
        let count = forceCount;
        if (count === undefined) {
            const container = document.getElementById('hidden-inputs-' + itemId);
            count = container ? container.querySelectorAll('input').length : 0;
        }
        if (count === 0) {
            counter.textContent = '';
        } else {
            counter.textContent = count + ' unit dipilih';
        }
    }

});
</script>

<style>
    [x-cloak] { display: none !important; }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    @media (max-width: 767px) {
        .inspect-table, .inspect-table tbody, .inspect-table tr, .inspect-table td, .inspect-table th { display: block; width: 100%; }
        .inspect-table thead { display: none; }
        
        .inspect-table tbody { 
            margin-bottom: 24px; 
            border: 1px solid #d1d5db !important; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            background: white;
        }
        
        .col-alphabet { display: none !important; }
        .col-header-no { display: none !important; } 
        .mobile-only-number { display: inline !important; } 
        
        .section-header-row td { 
            border: none !important; 
            padding: 12px 16px !important; 
            font-size: 14px !important; 
        }
        
        .question-row { 
            display: flex; 
            flex-wrap: wrap; 
            padding: 16px; 
            gap: 12px; 
            border-bottom: 1px solid #e5e7eb; 
        }
        
        .col-question { 
            flex: 0 0 100%; 
            border: none !important; 
            padding: 0 !important; 
            font-size: 14px !important; 
            font-weight: 700 !important; 
            color: #1f2937 !important; 
        }
        
        .col-error, .col-na, .col-normal { 
            flex: 1; 
            border: 1px solid #e5e7eb !important; 
            border-radius: 12px; 
            padding: 12px !important; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            position: relative; 
        }
        
        .col-error::before { content: "ERROR ✖"; font-size: 11px; font-weight: 900; color: #dc2626; margin-bottom: 8px; }
        .col-na::before { content: "N/A ➖"; font-size: 11px; font-weight: 900; color: #6b7280; margin-bottom: 8px; }
        .col-normal::before { content: "NORMAL ✔"; font-size: 11px; font-weight: 900; color: #16a34a; margin-bottom: 8px; }
        
        .radio-input { width: 24px !important; height: 24px !important; }
        
        .col-notes { 
            flex: 0 0 100%; 
            border: none !important; 
            padding: 4px 0 0 0 !important; 
        }
        
        .col-notes input, .col-notes select { 
            padding: 12px 16px !important; 
            border-radius: 10px !important; 
            background-color: #f9fafb; 
            border: 1px solid #d1d5db !important;
        }
        
        .global-notes-row td { padding: 16px !important; }
        .inspect-wrapper { background-color: transparent !important; border: none !important; box-shadow: none !important; }
    }
</style>
@endsection