@extends('layouts.technician')

@section('header_title', 'Inspeksi Area')

@section('content')
<div class="container mx-auto px-4 pb-24" x-data="inspectionForm()">
    
    {{-- Info Lokasi --}}
    <div class="mb-6 mt-4">
        <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Inspeksi Area: {{ $location->name }}</h2>
        <p class="text-gray-500 text-sm mt-1"><i class="fa-solid fa-map-marker-alt text-blue-500 mr-2"></i>{{ $location->full_address }}</p>
    </div>

    <form action="{{ route('technician.locations.inspect.store', $location->id) }}" method="POST" enctype="multipart/form-data" @submit.prevent="handleSubmit($event)">
        @csrf

        {{-- WADAH TABEL UTAMA (EXCEL STYLE) --}}
        <div class="bg-white rounded-md border border-gray-300 overflow-hidden mb-8 inspect-wrapper">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap md:whitespace-normal inspect-table">
                    
                    {{-- HEADER TABEL (Persis seperti Excel) --}}
                    <thead>
                        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider border-b-2 border-slate-900">
                            <th class="p-3 text-center w-12 border-r border-slate-700">No</th>
                            <th class="p-3 min-w-[250px] border-r border-slate-700">Deskripsi Pengecekan</th>
                            <th class="p-3 text-center w-20 bg-red-900/50 border-r border-slate-700">Error <i class="fa-solid fa-xmark text-red-400 ml-1"></i></th>
                            <th class="p-3 text-center w-20 bg-green-900/50 border-r border-slate-700">Normal <i class="fa-solid fa-check text-green-400 ml-1"></i></th>
                            <th class="p-3 min-w-[200px]">Keterangan</th>
                        </tr>
                    </thead>

                    {{-- LOOPING ASET (Menggunakan Tbody terpisah per aset) --}}
                    @foreach($assets as $asset)
                        @php
                            $template = $asset->category->checklistTemplates->first();
                            $headerCount = 0;
                            $itemCount = 0;
                            $alphabets = range('a', 'z');
                        @endphp

                        <tbody class="border-b-8 border-gray-300" x-data="{ hasIssue: false, checkIssue() { this.hasIssue = Array.from($el.querySelectorAll('.issue-trigger')).some(input => input.checked); updateGlobalButtonState(); } }">
                            
                            {{-- BARIS JUDUL ASET (Pemisah antar aset) --}}
                            <tr class="bg-slate-200 border-y-2 border-slate-300 asset-header-row">
                                <td colspan="5" class="p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded bg-slate-800 text-white flex items-center justify-center text-sm shadow">
                                            <i class="{{ $asset->category->icon ?? 'fa-solid fa-cube' }}"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-800 uppercase text-sm leading-none">{{ $asset->name }}</h4>
                                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{{ $asset->category->name }} | SN: {{ $asset->serial_number ?? '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            @if($template)
                                @foreach($template->items as $item)
                                    @if($item->type === 'header')
                                        @php $headerCount++; $itemCount = 0; @endphp
                                        {{-- BARIS HEADER PERTANYAAN (Misal: 1. Hardware) --}}
                                        <tr class="bg-blue-50/50 border-b border-blue-100 section-header-row">
                                            <td class="p-2 text-center font-black text-blue-900 border-r border-blue-100 col-header-no">{{ $headerCount }}</td>
                                            <td colspan="4" class="p-2 font-black text-blue-900 uppercase tracking-wide text-xs col-header-text">
                                                <span class="mobile-only-number mr-1 hidden">{{ $headerCount }}.</span>{{ $item->question }}
                                            </td>
                                        </tr>
                                    @else
                                        {{-- BARIS PERTANYAAN (Misal: a. PC) --}}
                                        <tr class="hover:bg-gray-50 transition-colors border-b border-gray-200 question-row">
                                            <td class="p-2 text-center text-gray-500 font-bold text-xs border-r border-gray-200 col-alphabet">
                                                {{ $alphabets[$itemCount] ?? '-' }}.
                                            </td>
                                            <td class="p-2 text-gray-800 font-medium whitespace-normal border-r border-gray-200 col-question">
                                                {{ $item->question }}
                                            </td>
                                            
                                            {{-- KOLOM ERROR --}}
                                            <td class="p-2 text-center border-r border-gray-200 bg-red-50/30 hover:bg-red-50/80 transition-colors col-error">
                                                <input type="radio" name="answers[{{ $asset->id }}][{{ $item->id }}]" value="{{ $item->type === 'pass_fail' ? 'fail' : 'no' }}" 
                                                       class="w-5 h-5 text-red-600 border-gray-400 focus:ring-red-500 cursor-pointer radio-input issue-trigger" 
                                                       required @change="checkIssue">
                                            </td>
                                            
                                            {{-- KOLOM NORMAL --}}
                                            <td class="p-2 text-center border-r border-gray-200 bg-green-50/30 hover:bg-green-50/80 transition-colors col-normal">
                                                <input type="radio" name="answers[{{ $asset->id }}][{{ $item->id }}]" value="{{ $item->type === 'pass_fail' ? 'pass' : 'yes' }}" 
                                                       class="w-5 h-5 text-green-600 border-gray-400 focus:ring-green-500 cursor-pointer radio-input" 
                                                       required @change="checkIssue">
                                            </td>
                                            
                                            {{-- KOLOM KETERANGAN --}}
                                            <td class="p-2 col-notes">
                                                @if($item->type === 'number')
                                                    <input type="number" step="any" name="notes[{{ $asset->id }}][{{ $item->id }}]" class="w-full text-xs px-2 py-1.5 md:py-1.5 py-3 border border-gray-300 rounded focus:ring-blue-500" placeholder="Masukkan Angka..." required>
                                                @else
                                                    <input type="text" name="notes[{{ $asset->id }}][{{ $item->id }}]" class="w-full text-xs px-2 py-1.5 md:py-1.5 py-3 border border-gray-300 rounded focus:ring-blue-500" placeholder="Keterangan (Opsional)...">
                                                @endif
                                            </td>
                                        </tr>
                                        @php $itemCount++; @endphp
                                    @endif
                                @endforeach

                                {{-- BARIS CATATAN GLOBAL & FOTO PER ASET (BERUBAH MENJADI FORM LAPORAN KERUSAKAN JIKA ERROR) --}}
                                <tr class="bg-gray-50/80 global-notes-row transition-all duration-300" :class="hasIssue ? 'bg-red-50/80 ring-2 ring-red-400 inset-0' : ''">
                                    <td colspan="5" class="p-4" x-data="{ assetId: {{ $asset->id }} }">
                                        
                                        <div class="flex flex-col md:flex-row gap-6">
                                            <div class="flex-1">
                                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1 transition-colors" :class="hasIssue ? 'text-red-700' : ''">
                                                    <i class="fa-solid" :class="hasIssue ? 'fa-triangle-exclamation text-red-600 animate-pulse' : 'fa-comment-dots text-blue-500'"></i> 
                                                    <span x-text="hasIssue ? 'Detail Laporan Kerusakan Aset' : 'Kesimpulan / Catatan Aset'"></span> 
                                                    <span x-show="hasIssue" class="text-red-500 ml-2 bg-red-100 px-2 py-0.5 rounded">*Wajib</span>
                                                </label>
                                                <textarea name="global_notes[{{ $asset->id }}]" rows="2" 
                                                          :class="hasIssue ? 'border-red-400 bg-white focus:ring-red-500 placeholder-red-300' : 'border-gray-300 focus:ring-blue-500'"
                                                          class="w-full p-2 border rounded-lg text-sm transition-colors" 
                                                          :placeholder="hasIssue ? 'Jelaskan secara detail kerusakan yang ditemukan pada aset ini...' : 'Catatan tambahan untuk aset ini...'" :required="hasIssue"></textarea>
                                            </div>
                                            <div class="w-full md:w-1/3">
                                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1 transition-colors" :class="hasIssue ? 'text-red-700' : ''">
                                                    <i class="fa-solid fa-camera" :class="hasIssue ? 'text-red-500' : 'text-blue-500'"></i> Foto Bukti
                                                    <span x-show="hasIssue" class="text-red-500 ml-2 bg-red-100 px-2 py-0.5 rounded">*Wajib</span>
                                                </label>
                                                <div class="flex flex-wrap gap-2">
                                                    <template x-for="(photo, index) in photos[assetId]" :key="index">
                                                        <div class="relative w-12 h-12 rounded border border-gray-300 overflow-hidden group">
                                                            <img :src="photo.preview" class="w-full h-full object-cover">
                                                            <button type="button" @click="removePhoto(assetId, index)" class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white flex items-center justify-center text-[8px] opacity-0 group-hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
                                                        </div>
                                                    </template>
                                                    <label class="w-12 h-12 rounded border-2 border-dashed flex flex-col items-center justify-center cursor-pointer transition-colors"
                                                           :class="hasIssue ? 'border-red-300 hover:bg-red-100 text-red-400 bg-white' : 'border-gray-300 hover:bg-gray-100 text-gray-400'">
                                                        <input type="file" accept="image/*" class="hidden" @change="addPhoto(assetId, $event)" :required="hasIssue && photos[assetId].length === 0">
                                                        <i class="fa-solid fa-plus text-xs"></i>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-sm text-yellow-600 bg-yellow-50 font-bold border-b border-yellow-200">
                                        <i class="fa-solid fa-triangle-exclamation mr-2"></i> Belum ada Template SOP untuk kategori aset ini.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    @endforeach
                </table>
            </div>
        </div>

        {{-- Tombol Kirim Gabungan --}}
        <div class="sticky bottom-6 z-40 px-4 md:px-0">
            <button type="submit" id="submitBtn" class="w-full md:w-1/2 mx-auto bg-green-600 hover:bg-green-700 text-white font-black py-4 px-8 rounded-2xl shadow-2xl transition transform active:scale-[0.98] flex items-center justify-center gap-3 text-lg border-2 border-white disabled:opacity-70 disabled:cursor-not-allowed">
                <i id="submitIcon" class="fa-solid fa-check-circle"></i> 
                <span id="submitText">TANDAI SELESAI</span>
            </button>
        </div>
    </form>

    {{-- TRIAGE MODAL --}}
    <div x-data="{ show: false, ticketUrl: '#', locationUrl: '#' }"
         @open-triage-modal.window="show = true; ticketUrl = $event.detail.ticketUrl; locationUrl = $event.detail.locationUrl"
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
            
            <h3 class="font-bold text-xl text-gray-800 mb-2">Masalah Terdeteksi!</h3>
            <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                Beberapa aset ditandai <b>RUSAK</b>. Tiket perbaikan telah dibuat secara otomatis. Apakah Anda ingin memperbaikinya sekarang?
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
// Fungsi untuk mengecek secara global apakah ada isu di seluruh form
function updateGlobalButtonState() {
    const hasAnyIssue = Array.from(document.querySelectorAll('.issue-trigger')).some(input => input.checked);
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitIcon = document.getElementById('submitIcon');

    if (hasAnyIssue) {
        submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
        submitBtn.classList.add('bg-red-600', 'hover:bg-red-700');
        submitText.innerText = 'LAPORKAN MASALAH';
        submitIcon.className = 'fa-solid fa-triangle-exclamation animate-pulse';
    } else {
        submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
        submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
        submitText.innerText = 'TANDAI SELESAI';
        submitIcon.className = 'fa-solid fa-check-circle';
    }
}

function inspectionForm() {
    return {
        photos: {}, 
        init() {
            @foreach($assets as $asset)
                this.photos[{{ $asset->id }}] = [];
            @endforeach
            // Panggil sekali saat inisiasi untuk set warna awal tombol
            setTimeout(() => { updateGlobalButtonState(); }, 100);
        },
        addPhoto(assetId, event) {
            const files = event.target.files;
            if (!files.length) return;
            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => { 
                    this.photos[assetId].push({ file: file, preview: e.target.result }); 
                    // Memicu validasi ulang secara manual karena alpine tidak mendeteksi perubahan length secara langsung pada event submit native
                    event.target.required = false; 
                };
                reader.readAsDataURL(file);
            });
            event.target.value = '';
        },
        removePhoto(assetId, index) { 
            this.photos[assetId].splice(index, 1); 
            // Kembalikan atribut required jika foto kosong dan statusnya hasIssue
            const fileInput = document.querySelector(`tbody[x-data*="${assetId}"] input[type="file"]`);
            const hasIssue = document.querySelector(`tbody[x-data*="${assetId}"] .issue-trigger:checked`) !== null;
            if (fileInput && this.photos[assetId].length === 0 && hasIssue) {
                fileInput.required = true;
            }
        },
        
        // PENGIRIMAN FORM VIA AJAX AGAR HALAMAN TIDAK REFRESH
        async handleSubmit(event) {
            const form = event.target;
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitIcon = document.getElementById('submitIcon');
            const originalText = submitText.innerText;
            const originalIconClass = submitIcon.className;
            
            // Ubah tombol jadi loading
            submitBtn.disabled = true;
            submitText.innerText = 'MENYIMPAN...';
            submitIcon.className = 'fa-solid fa-spinner fa-spin';

            try {
                const formData = new FormData(form);
                
                // Masukkan file foto yang ada di state Alpine.js ke formData
                Object.keys(this.photos).forEach(assetId => {
                    const files = this.photos[assetId].map(p => p.file);
                    files.forEach(f => formData.append(`photos[${assetId}][]`, f));
                });

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Terjadi kesalahan jaringan atau server.');
                }

                if (result.status === 'success') {
                    if (result.has_issue) {
                        // TAMPILKAN MODAL JIKA ADA ERROR
                        window.dispatchEvent(new CustomEvent('open-triage-modal', { 
                            detail: { 
                                ticketUrl: result.redirect_url_ticket,
                                locationUrl: result.redirect_url_location 
                            }
                        }));
                    } else {
                        // LANGSUNG PINDAH KE DASHBOARD JIKA NORMAL SEMUA
                        window.location.href = result.redirect_url_location;
                    }
                } else {
                    throw new Error(result.message);
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan inspeksi: ' + error.message);
                
                // Kembalikan tombol jika gagal
                submitBtn.disabled = false;
                submitText.innerText = originalText;
                submitIcon.className = originalIconClass;
            }
        }
    }
}
</script>

{{-- MAGIC RESPONSIVE CSS HANYA UNTUK HP --}}
<style>
    [x-cloak] { display: none !important; }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* CSS INI HANYA BEKERJA DI HP (<768px), DESKTOP DIJAMIN AMAN! */
    @media (max-width: 767px) {
        .inspect-table, .inspect-table tbody, .inspect-table tr, .inspect-table td, .inspect-table th {
            display: block;
            width: 100%;
        }
        .inspect-table thead { display: none; }
        .inspect-table tbody {
            margin-bottom: 24px;
            border: 1px solid #d1d5db !important;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .col-alphabet { display: none !important; }
        .asset-header-row td { padding: 16px !important; }
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
        .col-error, .col-normal {
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
        .col-error::before {
            content: "ERROR ✖";
            font-size: 11px;
            font-weight: 900;
            color: #dc2626;
            margin-bottom: 8px;
        }
        .col-normal::before {
            content: "NORMAL ✔";
            font-size: 11px;
            font-weight: 900;
            color: #16a34a;
            margin-bottom: 8px;
        }
        .radio-input {
            width: 24px !important;
            height: 24px !important;
        }
        .col-notes {
            flex: 0 0 100%;
            border: none !important;
            padding: 4px 0 0 0 !important;
        }
        .col-notes input {
            padding: 12px 16px !important;
            border-radius: 10px !important;
            background-color: #f9fafb;
        }
        .global-notes-row td {
            padding: 16px !important;
        }
        .inspect-wrapper {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection