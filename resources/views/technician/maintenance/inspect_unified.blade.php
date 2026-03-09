@extends('layouts.technician')

@section('header_title', 'Tugas Perawatan Area')

@section('content')
<div class="container mx-auto px-4 pb-24" x-data="inspectionAreaForm()">
    
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

        {{-- WADAH TABEL UTAMA (EXCEL STYLE CLEAN) --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-300 overflow-hidden mb-8 inspect-wrapper">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap md:whitespace-normal inspect-table">
                    
                    {{-- HEADER TABEL --}}
                    <thead>
                        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider border-b-2 border-slate-900">
                            <th class="p-3 text-center w-12 border-r border-slate-700">No</th>
                            <th class="p-3 min-w-[250px] border-r border-slate-700">Deskripsi Pengecekan</th>
                            <th class="p-3 text-center w-20 bg-red-900/50 border-r border-slate-700">Error <i class="fa-solid fa-xmark text-red-400 ml-1"></i></th>
                            <th class="p-3 text-center w-20 bg-gray-600/50 border-r border-slate-700">N/A <i class="fa-solid fa-minus text-gray-300 ml-1"></i></th>
                            <th class="p-3 text-center w-20 bg-green-900/50 border-r border-slate-700">Normal <i class="fa-solid fa-check text-green-400 ml-1"></i></th>
                            <th class="p-3 min-w-[300px]">Keterangan / Hasil Pengecekan</th>
                        </tr>
                    </thead>

                    <tbody class="border-b-8 border-gray-300">
                        @php
                            $alphabets = range('a', 'z');
                            // Normalize to grouped structure even if single template is passed
                            $groups = isset($groupedTemplates) ? $groupedTemplates : [
                                ['category_name' => $templateName, 'items' => $items]
                            ];
                        @endphp

                        @foreach($groups as $groupIndex => $group)
                            {{-- MAIN CATEGORY HEADER --}}
                            <tr class="bg-indigo-900 border-y-2 border-indigo-950 section-header-row">
                                <td colspan="6" class="p-4 font-black text-white uppercase tracking-widest text-sm text-center shadow-inner">
                                    Kategori Inspeksi: {{ $group['category_name'] }}
                                </td>
                            </tr>
                            
                            @php
                                $headerCount = 0;
                                $itemCount = 0;
                            @endphp

                            @foreach($group['items'] as $item)
                            @if($item->type === 'header')
                                @php $headerCount++; $itemCount = 0; @endphp
                                {{-- BARIS HEADER KATEGORI --}}
                                <tr class="bg-blue-50/80 border-y-2 border-blue-100 section-header-row">
                                    <td class="p-3 text-center font-black text-blue-900 border-r border-blue-100 text-sm col-header-no">{{ $headerCount }}</td>
                                    <td colspan="5" class="p-3 font-black text-blue-900 uppercase tracking-widest text-xs col-header-text">
                                        <span class="mobile-only-number mr-1 hidden">{{ $headerCount }}.</span>{{ $item->question }}
                                    </td>
                                </tr>
                            @else
                                {{-- BARIS PERTANYAAN --}}
                                <tr x-data="{ isError: false }" class="hover:bg-gray-50 transition-colors border-b border-gray-200 question-row" :class="isError ? 'bg-red-50/40' : ''">
                                    
                                    <td class="p-2 text-center text-gray-500 font-bold text-xs border-r border-gray-200 col-alphabet">
                                        {{ $alphabets[$itemCount] ?? '-' }}.
                                    </td>
                                    
                                    <td class="p-2 text-gray-800 font-bold whitespace-normal border-r border-gray-200 pl-4 text-sm col-question">
                                        {{ $item->question }}
                                    </td>
                                    
                                    <td class="p-2 text-center border-r border-gray-200 bg-red-50/30 hover:bg-red-50/80 transition-colors col-error">
                                        <input type="radio" name="answers[{{ $item->id }}]" value="fail" 
                                               class="w-5 h-5 text-red-600 border-gray-400 focus:ring-red-500 cursor-pointer radio-input issue-trigger" 
                                               required @change="isError = true; checkGlobalIssue()">
                                    </td>

                                    <td class="p-2 text-center border-r border-gray-200 bg-gray-50 hover:bg-gray-100 transition-colors col-na">
                                        <input type="radio" name="answers[{{ $item->id }}]" value="na" 
                                               class="w-5 h-5 text-gray-500 border-gray-400 focus:ring-gray-400 cursor-pointer radio-input" 
                                               required @change="isError = false; checkGlobalIssue()">
                                    </td>
                                    
                                    <td class="p-2 text-center border-r border-gray-200 bg-green-50/30 hover:bg-green-50/80 transition-colors col-normal">
                                        <input type="radio" name="answers[{{ $item->id }}]" value="pass" 
                                               class="w-5 h-5 text-green-600 border-gray-400 focus:ring-green-500 cursor-pointer radio-input" 
                                               required @change="isError = false; checkGlobalIssue()">
                                    </td>
                                    
                                    {{-- KOLOM KETERANGAN (MENGGUNAKAN LOGIKA TIPE PERTANYAAN) --}}
                                    <td class="p-2 col-notes align-top">
                                        <div class="space-y-2">
                                            {{-- MUNCULKAN INPUT SESUAI TIPE DI ADMIN --}}
                                            @if($item->type === 'number')
                                                <div class="flex items-center gap-2">
                                                    <input type="number" step="any" name="notes[{{ $item->id }}]" class="w-full text-sm px-3 py-2 border-2 border-blue-300 bg-blue-50 focus:bg-white rounded-lg focus:ring-blue-500 font-bold text-blue-900 shadow-sm" placeholder="Ketik Angka Hasil..." required>
                                                    @if($item->unit)
                                                        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-2 rounded-md border border-gray-200">{{ $item->unit }}</span>
                                                    @endif
                                                </div>
                                            @elseif($item->type === 'text')
                                                <input type="text" name="notes[{{ $item->id }}]" class="w-full text-sm px-3 py-2 border-2 border-blue-300 bg-blue-50 focus:bg-white rounded-lg focus:ring-blue-500 font-bold text-blue-900 shadow-sm" placeholder="Ketik Hasil Pengecekan..." required>
                                            @else
                                                <input type="text" name="notes[{{ $item->id }}]" class="w-full text-xs px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 bg-white" placeholder="Keterangan opsional...">
                                            @endif
                                            
                                            {{-- DROPDOWN ASET (HANYA MUNCUL JIKA ERROR DIKLIK) --}}
                                            <div x-show="isError" x-collapse x-cloak class="mt-2">
                                                <select name="failed_asset_ids[{{ $item->id }}]" class="w-full text-xs px-3 py-2 border border-red-300 bg-red-50 text-red-800 rounded-lg focus:ring-red-500 font-bold shadow-sm" :required="isError">
                                                    <option value="">-- Tandai Aset yang Rusak --</option>
                                                    <option value="area_general">⚠️ Bukan Aset (Masalah Ruangan Umum)</option>
                                                    <optgroup label="Daftar Aset di Area Ini">
                                                        @foreach($assets as $assetOption)
                                                            <option value="{{ $assetOption->id }}">{{ $assetOption->name }} (SN: {{ $assetOption->serial_number ?? '-' }})</option>
                                                        @endforeach
                                                    </optgroup>
                                                </select>
                                                <p class="text-[9px] text-red-500 mt-1 font-bold ml-1"><i class="fa-solid fa-link"></i> Pilih aset agar masuk riwayat kerusakan.</p>
                                            </div>
                                        </div>
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
        <div class="sticky bottom-6 z-40 px-4 md:px-0 mt-8">
            <button type="submit" id="submitBtn" class="w-full md:w-1/2 mx-auto bg-green-600 hover:bg-green-700 text-white font-black py-4 px-8 rounded-2xl shadow-2xl transition transform active:scale-[0.98] flex items-center justify-center gap-3 text-lg border-2 border-white disabled:opacity-70 disabled:cursor-not-allowed">
                <i id="submitIcon" class="fa-solid fa-check-circle"></i> 
                <span id="submitText">TANDAI AREA SELESAI DI-CEK</span>
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