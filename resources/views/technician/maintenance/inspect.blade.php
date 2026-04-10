@extends('layouts.technician')

@section('content')
    {{-- Asset Info Card --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border-l-4 border-blue-500">
        <div class="flex items-start gap-3 mb-3">
            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-cube text-xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="font-bold text-gray-800 text-base">{{ $asset->name }}</h2>
                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $asset->serial_number ?? '-' }}</p>
                <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                    @if($asset->location_id)
                        <i class="fa-solid fa-location-dot text-orange-400"></i>
                        <span>{{ $asset->location->name }}</span>
                    @else
                        <i class="fa-solid fa-cloud text-blue-500"></i>
                        <span>Virtual / Software</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Checklist Form --}}
    <form id="inspectionForm" action="{{ isset($maintenance) ? route('technician.maintenance.complete', $maintenance->id) : route('technician.inspection.store') }}" method="POST">
        @csrf
        <input type="hidden" name="asset_id" value="{{ $asset->id }}">
        <input type="hidden" name="template_id" value="{{ $template->id }}">
        <input type="hidden" name="has_issue" id="hasIssue" value="0">

        {{-- Jarak bawah (mb-32) ditambahkan agar item paling bawah tidak tertutup tombol fixed --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-32 md:mb-8">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-4 py-3 border-b border-green-100">
                <h3 class="font-bold text-gray-800 flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-clipboard-check text-green-600"></i>
                    {{ $template->name }}
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ $template->description ?? 'Checklist harian' }}</p>
            </div>

            <div class="p-4 space-y-4">
                @foreach($template->items as $item)
                    @if($item->type === 'header')
                        <div class="pt-6 pb-2 border-b-2 border-gray-800 mb-2 mt-4 first:mt-0">
                            <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-blue-600"></i>
                                {{ $item->question }}
                            </h4>
                        </div>
                        @continue
                    @endif

                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $loop->iteration }}. {{ $item->question }}
                        </label>

                        @if($item->type === 'pass_fail')
                            {{-- Pass/Fail Radio Buttons (Bagus / Rusak) --}}
                            <div class="flex gap-3 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg border border-gray-200 bg-white hover:bg-green-50 transition">
                                    <input type="radio" name="answers[{{ $item->id }}]" value="pass" class="w-4 h-4 text-green-600 focus:ring-green-500" required>
                                    <span class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                        <i class="fa-solid fa-thumbs-up text-green-600"></i> Bagus
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg border border-gray-200 bg-white hover:bg-red-50 transition">
                                    <input type="radio" name="answers[{{ $item->id }}]" value="fail" class="w-4 h-4 text-red-600 focus:ring-red-500 issue-trigger" required>
                                    <span class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                        <i class="fa-solid fa-thumbs-down text-red-600"></i> Rusak
                                    </span>
                                </label>
                            </div>

                        @elseif($item->type === 'checkbox')
                            {{-- Ya/Tidak Checkbox --}}
                            <div class="flex gap-3 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg border border-gray-200 bg-white hover:bg-blue-50 transition">
                                    <input type="radio" name="answers[{{ $item->id }}]" value="Ya" class="w-4 h-4 text-blue-600 focus:ring-blue-500" required>
                                    <span class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                        <i class="fa-solid fa-check text-blue-600"></i> Ya
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-100 transition">
                                    <input type="radio" name="answers[{{ $item->id }}]" value="Tidak" class="w-4 h-4 text-gray-600 focus:ring-gray-500 issue-trigger" required>
                                    <span class="text-sm font-medium text-gray-700 flex items-center gap-1">
                                        <i class="fa-solid fa-xmark text-gray-600"></i> Tidak
                                    </span>
                                </label>
                            </div>

                        @elseif($item->type === 'number')
                            {{-- Numeric Input (Isian Angka) --}}
                            <div class="space-y-2 mt-2">
                                <div class="flex gap-2 items-center">
                                    <input type="number" step="0.01" name="answers[{{ $item->id }}]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white" placeholder="Masukkan nilai" required>
                                    @if($item->unit)
                                        <span class="text-sm px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 font-bold shadow-sm whitespace-nowrap">{{ $item->unit }}</span>
                                    @endif
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer mt-1">
                                    <input type="checkbox" class="w-4 h-4 text-red-600 focus:ring-red-500 rounded border-gray-300 issue-trigger">
                                    <span class="text-xs text-red-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Tandai jika nilai di luar standar (Rusak)
                                    </span>
                                </label>
                            </div>

                        @else
                            {{-- Text Input (Catatan Teks) --}}
                            <div class="space-y-2 mt-2">
                                <textarea name="answers[{{ $item->id }}]" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white" placeholder="Masukkan catatan teknis" required></textarea>
                                <label class="flex items-center gap-2 cursor-pointer mt-1">
                                    <input type="checkbox" class="w-4 h-4 text-red-600 focus:ring-red-500 rounded border-gray-300 issue-trigger">
                                    <span class="text-xs text-red-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Tandai jika isi catatan mengindikasikan kerusakan
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- DIPINDAHKAN KE SINI: Form Detail Masalah (Masuk ke normal flow, BUKAN fixed) --}}
            <div class="px-4 pb-4">
                <div id="issueDetails" class="hidden bg-red-50 p-4 rounded-xl border border-red-200 shadow-inner mt-2">
                    <h4 class="font-bold text-red-800 text-sm mb-3 border-b border-red-200 pb-2">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> Detail Laporan Kerusakan
                    </h4>
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-red-700 mb-1">Catatan Masalah <span class="text-red-500">*</span></label>
                        <textarea name="notes" rows="2" class="w-full text-sm border-2 pl-2 py-2 border-red-200 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="Jelaskan detail kerusakannya"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-red-700 mb-1">Foto Bukti (Maks 5) <span class="text-red-500">*</span></label>
                        <input type="file" id="inspectFileInput" multiple class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-red-100 file:text-red-700 hover:file:bg-red-200" accept="image/*" onchange="handleInspectPhotos(this)">
                        <div id="inspectPreview" class="mt-3 flex gap-2 flex-wrap"></div>
                    </div>
                    <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-red-100">
                        <div>
                            <p class="text-sm font-bold text-red-700">⚠️ Kerusakan Kritis?</p>
                            <p class="text-[10px] text-red-500">Tandai sebagai High Priority</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_critical" value="1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- HANYA TOMBOL INI YANG MELAYANG (FIXED) di mobile --}}
        <div class="inspect-submit-bar fixed left-0 right-0 bg-white border-t border-gray-200 shadow-[0_-5px_15px_rgba(0,0,0,0.08)] z-40 p-3 transition-all" 
             style="bottom: calc(64px + env(safe-area-inset-bottom, 0px));">
            
            <button type="submit" id="submitBtn" class="w-full max-w-3xl mx-auto bg-green-600 text-white font-bold py-3.5 rounded-xl hover:bg-green-700 transition shadow-lg flex items-center justify-center gap-2 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fa-solid fa-check-circle"></i>
                <span>Simpan Hasil Inspeksi</span>
            </button>
        </div>
    </form>

    <style>
        @media (min-width: 768px) {
            .inspect-submit-bar {
                position: relative !important;
                bottom: auto !important;
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
                padding: 0 !important;
                margin-top: 1rem;
            }
        }
    </style>

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
                Tiket perbaikan telah dibuat secara otomatis. Apakah Anda ingin memperbaikinya <b>sekarang</b>?
            </p>

            <div class="grid grid-cols-2 gap-3">
                <a :href="locationUrl" class="flex flex-col items-center justify-center p-3 rounded-xl border-2 border-gray-100 hover:bg-gray-50 transition active:scale-95">
                    <i class="fa-solid fa-person-walking-arrow-right text-gray-400 text-xl mb-1"></i>
                    <span class="font-bold text-gray-600 text-xs">Lanjut Patroli</span>
                    <span class="text-[10px] text-gray-400 mt-1">Simpan di Pending</span>
                </a>

                <a :href="ticketUrl" class="flex flex-col items-center justify-center p-3 rounded-xl bg-red-600 text-white shadow-lg shadow-red-200 hover:bg-red-700 transition active:scale-95">
                    <i class="fa-solid fa-screwdriver-wrench text-xl mb-1"></i>
                    <span class="font-bold text-xs">Perbaiki Sekarang</span>
                    <span class="text-[10px] text-red-100 mt-1">&lt; 15 Menit</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('inspectionForm');
            const issueDetails = document.getElementById('issueDetails');
            const submitBtn = document.getElementById('submitBtn');
            const submitBtnText = submitBtn.querySelector('span');
            const submitBtnIcon = submitBtn.querySelector('i');
            const hasIssueInput = document.getElementById('hasIssue');
            const issueTriggers = document.querySelectorAll('.issue-trigger');

            // 1. Logic Show/Hide Issue Details
            issueTriggers.forEach(trigger => {
                trigger.addEventListener('change', function() {
                    checkIssueStatus();
                });
            });

            // Also check on radio change (if user switches back to pass)
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', checkIssueStatus);
            });

            function checkIssueStatus() {
                const hasFailure = Array.from(issueTriggers).some(input => input.checked);
                hasIssueInput.value = hasFailure ? '1' : '0';

                if (hasFailure) {
                    issueDetails.classList.remove('hidden');
                    submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    submitBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                    submitBtnText.innerText = 'Laporkan Masalah';
                    submitBtnIcon.className = 'fa-solid fa-triangle-exclamation';
                } else {
                    issueDetails.classList.add('hidden');
                    submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                    submitBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                    submitBtnText.innerText = 'Tandai Selesai';
                    submitBtnIcon.className = 'fa-solid fa-check-circle';
                }
            }

            // Managed photos for inspect form
            let inspectPendingFiles = [];

            window.handleInspectPhotos = function(input) {
                if (!input.files || input.files.length === 0) return;
                Array.from(input.files).forEach(file => {
                    inspectPendingFiles.push(file);
                    const idx = inspectPendingFiles.length - 1;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const container = document.getElementById('inspectPreview');
                        const wrapper = document.createElement('div');
                        wrapper.className = 'relative';
                        wrapper.id = `inspect-photo-${idx}`;
                        wrapper.innerHTML = `
                            <img src="${e.target.result}" class="h-16 w-16 object-cover rounded-lg border border-red-200 shadow-sm">
                            <button type="button" 
                                class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm transition" 
                                title="Hapus foto ini">
                                <i class="fa-solid fa-xmark"></i>
                            </button>`;
                        wrapper.querySelector('button').addEventListener('click', () => {
                            inspectPendingFiles[idx] = null;
                            wrapper.style.transition = 'opacity 0.2s, transform 0.2s';
                            wrapper.style.opacity = '0';
                            wrapper.style.transform = 'scale(0.8)';
                            setTimeout(() => wrapper.remove(), 200);
                        });
                        container.appendChild(wrapper);
                    }
                    reader.readAsDataURL(file);
                });
                input.value = '';
            }

            // 2. AJAX Submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // UI Loading State
                submitBtn.disabled = true;
                const originalText = submitBtnText.innerText;
                submitBtnText.innerText = 'Menyimpan...';

                try {
                    const formData = new FormData(form);

                    // Inject managed photos
                    formData.delete('photos[]');
                    inspectPendingFiles.forEach(f => {
                        if (f) formData.append('photos[]', f);
                    });
                    
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    // Handle Validation Errors (422)
                    if (response.status === 422) {
                        const data = await response.json();
                        let errorMsg = Object.values(data.errors).flat().join('\n');
                        alert('Validasi Gagal:\n' + errorMsg);
                        throw new Error('Validation Error');
                    }

                    if (!response.ok) throw new Error('Network error');

                    const result = await response.json();

                    if (result.status === 'success') {
                        if (result.has_issue) {
                            // Show Triage Modal
                            window.dispatchEvent(new CustomEvent('open-triage-modal', { 
                                detail: { 
                                    ticketUrl: result.redirect_url_ticket,
                                    locationUrl: result.redirect_url_location 
                                }
                            }));
                        } else {
                            // Normal Success
                            // Ideally use a Toast library, but for now simple alert/redirect
                            // Or better: temporary redirect
                            window.location.href = result.redirect_url_location;
                        }
                    } else {
                        throw new Error(result.message || 'Unknown error');
                    }

                } catch (error) {
                    console.error('Error:', error);
                    if (error.message !== 'Validation Error') {
                        alert('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
                    }
                } finally {
                    submitBtn.disabled = false;
                    submitBtnText.innerText = originalText;
                }
            });
        });
    </script>
@endsection
