@extends('layouts.technician')

@section('title', 'Detail Tugas')

@section('content')
<div x-data="{ showCompleteModal: false, showHandoverModal: false }" class="pb-48 relative min-h-screen">
    {{-- Header removed here --}}


    {{-- Content --}}
    <div class="p-4 space-y-4">
        {{-- Status Card --}}
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <div class="flex justify-between items-start">
                <div>
                    <span class="px-2 py-1 rounded text-xs font-bold 
                        @if($task->priority == 'high') bg-red-100 text-red-600 
                        @elseif($task->priority == 'medium') bg-orange-100 text-orange-600 
                        @else bg-green-100 text-green-600 @endif">
                        {{ ucfirst($task->priority) }} Priority
                    </span>
                    @if($task->asset)
                        <h2 class="font-bold text-gray-800 text-lg mt-2">{{ $task->asset->name }}</h2>
                        @if(!$task->asset->location_id)
                            <p class="text-xs border border-blue-200 bg-blue-50 text-blue-700 px-2 py-1 rounded-md inline-block mt-1 mb-1 font-bold shadow-sm"><i class="fa-solid fa-cloud mr-1"></i> Software / Virtual</p>
                            <p class="text-sm text-gray-600 mt-1"><i class="fa-solid fa-street-view mr-1 text-red-500"></i> <span class="text-xs text-gray-400 mr-1">Posisi Pelapor:</span> {{ $task->location->name ?? '-' }}</p>
                        @else
                            <p class="text-sm text-gray-500"><i class="fa-solid fa-location-dot mr-1"></i> {{ $task->asset->location->name ?? '-' }}</p>
                        @endif
                    @else
                        <div class="flex items-center gap-2 mb-1">
                            <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-0.5 rounded border border-gray-200 uppercase"><i class="fa-solid fa-expand mr-1"></i> Area Umum / Belum Spesifik</span>
                        </div>
                        <h2 class="font-bold text-gray-900 text-lg mt-1"><i class="fa-solid fa-door-open text-blue-500 mr-1.5"></i> {{ $task->location->name ?? 'Lokasi tidak diketahui' }}</h2>
                        <p class="text-[11px] text-orange-600 font-medium mt-1.5 bg-orange-50 inline-block px-2 py-1 rounded border border-orange-100"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Teknisi perlu mengidentifikasi aset/sumber masalah di lokasi.</p>
                    @endif
                </div>
                <div class="text-right">
                    <span class="block text-xs text-gray-400">Status</span>
                    <span class="font-bold text-sm
                        @if($task->status == 'handover') text-yellow-600
                        @elseif($task->status == 'completed') text-blue-600
                        @elseif($task->status == 'in_progress') text-cyan-600
                        @else text-gray-600 @endif uppercase">
                        {{ str_replace('_', ' ', $task->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Rejection/Re-open Alert --}}
        @php
            $reopenedHistory = $task->histories->where('action', 'reopened')->last();
        @endphp
        @if($reopenedHistory)
            <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 shadow-sm animate-pulse-once">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-red-700 text-sm">Tiket Ditolak & Di-reopen oleh Admin</h4>
                        <p class="text-red-600 text-sm mt-1 leading-relaxed">"{{ $reopenedHistory->description }}"</p>
                        <div class="flex items-center gap-2 mt-2 text-xs text-red-400">
                            <i class="fa-solid fa-user"></i>
                            <span>{{ $reopenedHistory->user->name ?? 'Admin' }}</span>
                            <span>&bull;</span>
                            <i class="fa-solid fa-clock"></i>
                            <span>{{ $reopenedHistory->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Issue Description --}}
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-2 border-b pb-2">Deskripsi Masalah</h3>
            <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">{{ $task->issue_description }}</p>
            
            @if(!empty($task->photos_before_urls) && count($task->photos_before_urls) > 0)
                <div class="mt-4">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Foto / Bukti Awal</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($task->photos_before_urls as $url)
                            <a href="{{ $url }}" target="_blank" class="rounded-xl overflow-hidden border border-gray-100 block shadow-sm hover:shadow-md transition hover:scale-105 bg-gray-50 aspect-video w-full max-w-sm sm:w-48 sm:aspect-square">
                                <img src="{{ $url }}" class="w-full h-full object-cover" alt="Foto Bukti">
                            </a>
                        @endforeach
                    </div>
                </div>
            @elseif($task->initial_photo)
                <div class="mt-4">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Foto / Bukti Awal</p>
                    <a href="{{ asset('storage/'.$task->initial_photo) }}" target="_blank" class="rounded-xl overflow-hidden border border-gray-100 block shadow-sm hover:shadow-md transition bg-gray-50 aspect-video w-full max-w-sm">
                        <img src="{{ asset('storage/'.$task->initial_photo) }}" class="w-full h-full object-cover" alt="Foto Bukti User">
                    </a>
                </div>
            @endif

            @if($task->reporter)
                <div class="mt-4 flex items-center gap-2 bg-purple-50 p-3 rounded-lg border border-purple-100">
                    <div class="w-8 h-8 rounded-full bg-purple-200 text-purple-700 flex items-center justify-center font-bold text-xs">
                        {{ substr($task->reporter->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-700">Pelapor (User)</p>
                        <p class="text-xs text-gray-600">{{ $task->reporter->name }} <span class="text-gray-400">({{ $task->reporter->email }})</span></p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Timeline / History (Optional, can be added later) --}}
        {{-- ... --}}

    </div>

    {{-- STICKY BOTTOM BAR (Above Bottom Nav) --}}
    @if($task->status != 'completed')
        <div class="fixed bottom-[64px] md:bottom-0 left-0 md:left-64 right-0 bg-white border-t border-gray-200 shadow-[0_-5px_10px_rgba(0,0,0,0.05)] z-40 ">
            
            {{-- TAMBAHAN: Wrapper pembatas lebar khusus desktop agar sejajar dengan konten --}}
            <div class="max-w-7xl mx-auto w-full p-4 md:px-8 flex gap-3 md:justify-center flex-wrap">
                
                {{-- KONDISI 1: Tugas Handover/Pool (Belum Diambil) --}}
                @if(in_array($task->status, ['open', 'handover']) && $task->technician_id == null)
                    <form action="{{ route('technician.tasks.claim', $task->id) }}" method="POST" class="w-full md:w-auto">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="w-full md:w-auto md:px-10 bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-blue-700 transition flex items-center justify-center gap-2 active:scale-95">
                            <i class="fa-solid fa-hand-holding-hand"></i> Ambil Tugas Ini
                        </button>
                    </form>

                {{-- KONDISI 2: Tugas Ditugaskan ke Saya (Belum Mulai) --}}
                @elseif($task->technician_id == auth()->id() && in_array($task->status, ['assigned', 'open']))
                    <form action="{{ route('technician.tasks.start', $task->id) }}" method="POST" class="w-full md:w-auto">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="w-full md:w-auto md:px-10 bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-blue-700 transition flex items-center justify-center gap-2 active:scale-95">
                            <i class="fa-solid fa-play"></i> Mulai Kerjakan
                        </button>
                    </form>
                
                {{-- KONDISI 3: Tugas Saya (In Progress atau Pending Part) --}}
                @elseif($task->technician_id == auth()->id() && in_array($task->status, ['in_progress', 'pending_part']))
                    {{-- Tombol Handover --}}
                    <button @click="showHandoverModal = true" class="flex-1 md:flex-none md:w-auto md:px-8 bg-yellow-500 text-white font-bold py-3 rounded-xl shadow hover:bg-yellow-600 transition flex items-center justify-center gap-2 active:scale-95">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i> Handover
                    </button>
                    
                    {{-- Tombol Selesai --}}
                    <button @click="showCompleteModal = true" class="flex-[2] md:flex-none md:w-auto md:px-12 bg-green-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-green-700 transition flex items-center justify-center gap-2 active:scale-95">
                        <i class="fa-solid fa-check-circle"></i> Selesai
                    </button>
                @endif
                
            </div>
        </div>
    @endif

    {{-- MODAL HANDOVER --}}
    <div x-show="showHandoverModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <div class="absolute inset-0 bg-black/50" @click="showHandoverModal = false"></div>

        <div class="bg-white w-full max-w-xs sm:max-w-sm rounded-2xl shadow-2xl p-6 relative z-10 overflow-hidden transform transition-all">
            <h3 class="font-bold text-lg text-gray-800 mb-4">Handover Tugas</h3>
            <form action="{{ route('technician.tasks.handover', $task->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                @if(!$task->asset_id && isset($locationAssets) && count($locationAssets) > 0)
                <div class="mb-4 bg-orange-50 p-3 rounded-xl border border-orange-200">
                    <label class="block text-[11px] font-bold text-orange-700 uppercase mb-2"><i class="fa-solid fa-microscope mr-1"></i> Identifikasi Aset (Opsional)</label>
                    <select name="asset_id" class="w-full rounded-lg border-2 border-orange-200 focus:border-orange-500 focus:ring-orange-500 text-sm p-2 bg-white">
                        <option value="">-- Biarkan Kosong / Pilih Aset --</option>
                        @foreach($locationAssets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ $asset->name }} 
                                @if($asset->asset_code)
                                    ({{ $asset->asset_code }})
                                @elseif(!$asset->location_id)
                                    (Virtual / Software)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Alasan Handover</label>
                    <textarea name="note" rows="3" class="w-full rounded-xl border-2 border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 text-sm p-2" placeholder="Jelaskan kendala kenapa tugas ini dihandover" required></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Bukti Foto (Opsional, Maks 5)</label>
                    <input type="file" id="handoverFileInput" multiple class="block w-full text-xs text-slate-500
                        file:mr-4 file:py-2.5 file:px-4
                        file:rounded-full file:border-0
                        file:text-xs file:font-semibold
                        file:bg-yellow-50 file:text-yellow-700
                        hover:file:bg-yellow-100" accept="image/*" onchange="handleNewPhotos(this, 'handover')">
                    <div id="handoverPreview" class="mt-3 flex gap-2 flex-wrap"></div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showHandoverModal = false" class="flex-1 py-3 rounded-xl bg-gray-100 text-gray-600 font-bold hover:bg-gray-200 text-sm">Batal</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-yellow-500 text-white font-bold hover:bg-yellow-600 shadow-lg shadow-yellow-200 text-sm">Simpan Handover</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL COMPLETE --}}
    <div x-show="showCompleteModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <div class="absolute inset-0 bg-black/50" @click="showCompleteModal = false"></div>

        <div class="bg-white w-full max-w-xs sm:max-w-sm rounded-2xl shadow-2xl p-6 relative z-10 overflow-hidden transform transition-all">
            <h3 class="font-bold text-lg text-gray-800 mb-4">Lapor Selesai</h3>
            <form action="{{ route('technician.tasks.complete', $task->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                @if(!$task->asset_id && isset($locationAssets) && count($locationAssets) > 0)
                <div class="mb-4 bg-orange-50 p-3 rounded-xl border border-orange-200">
                    <label class="block text-[11px] font-bold text-orange-700 uppercase mb-2"><i class="fa-solid fa-microscope mr-1"></i> Identifikasi Aset <span class="text-red-500">*</span></label>
                    <p class="text-[10px] text-orange-600 mb-2 leading-tight">Pelapor tidak mengetahui aset spesifik. Silakan pilih aset yang bermasalah di lokasi ini.</p>
                    <select name="asset_id" required class="w-full rounded-lg border-2 border-orange-200 focus:border-orange-500 focus:ring-orange-500 text-sm p-2 bg-white">
                        <option value="">-- Pilih Aset --</option>
                        @foreach($locationAssets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ $asset->name }} 
                                @if($asset->asset_code)
                                    ({{ $asset->asset_code }})
                                @elseif(!$asset->location_id)
                                    (Virtual / Software)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Catatan Perbaikan</label>
                    <textarea name="description" rows="3" class="w-full rounded-xl border-2 border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm p-2" placeholder="Apa yang sudah diperbaiki?" required></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Bukti Foto (Maks 5) <span class="text-red-500">*</span></label>
                    <input type="file" id="completeFileInput" multiple class="block w-full text-xs text-slate-500
                        file:mr-4 file:py-2.5 file:px-4
                        file:rounded-full file:border-0
                        file:text-xs file:font-semibold
                        file:bg-green-50 file:text-green-700
                        hover:file:bg-green-100" accept="image/*" onchange="handleNewPhotos(this, 'complete')">
                    <div id="completePreview" class="mt-3 flex gap-2 flex-wrap"></div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showCompleteModal = false" class="flex-1 py-3 rounded-xl bg-gray-50 text-gray-600 font-bold hover:bg-gray-100 text-sm">Batal</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 shadow-lg shadow-green-200 text-sm">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    // Managed file arrays per context
    const pendingPhotos = { handover: [], complete: [] };

    function handleNewPhotos(input, context) {
        if (!input.files || input.files.length === 0) return;

        Array.from(input.files).forEach(file => {
            pendingPhotos[context].push(file);
            const idx = pendingPhotos[context].length - 1;
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById(context + 'Preview');
                const wrapper = document.createElement('div');
                wrapper.className = 'relative';
                wrapper.id = `${context}-photo-${idx}`;
                const borderColor = context === 'handover' ? 'border-yellow-200' : 'border-green-200';
                wrapper.innerHTML = `
                    <img src="${e.target.result}" class="h-16 w-16 object-cover rounded-lg border ${borderColor} shadow-sm">
                    <button type="button" 
                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm transition" 
                        title="Hapus foto ini">
                        <i class="fa-solid fa-xmark"></i>
                    </button>`;
                wrapper.querySelector('button').addEventListener('click', () => removePhoto(context, idx));
                container.appendChild(wrapper);
            }
            reader.readAsDataURL(file);
        });
        input.value = '';
    }

    function removePhoto(context, idx) {
        pendingPhotos[context][idx] = null;
        const wrapper = document.getElementById(`${context}-photo-${idx}`);
        if (wrapper) {
            wrapper.style.transition = 'opacity 0.2s, transform 0.2s';
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'scale(0.8)';
            setTimeout(() => wrapper.remove(), 200);
        }
    }

    // Intercept form submissions to inject managed files
    document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            // Detect which context based on form action
            const action = form.getAttribute('action');
            let context = null;
            if (action.includes('handover')) context = 'handover';
            else if (action.includes('complete')) context = 'complete';
            if (!context || !pendingPhotos[context]) return;

            // Check if there are actual files (filter nulls)
            const files = pendingPhotos[context].filter(f => f !== null);
            
            // For complete modal, photos are required
            if (context === 'complete' && files.length === 0) {
                e.preventDefault();
                alert('Bukti foto wajib diunggah!');
                return;
            }

            // Inject files into hidden file inputs
            if (files.length > 0) {
                // Remove existing photos[] from native input
                const existingInput = form.querySelector('input[name="photos[]"]');
                if (existingInput) existingInput.remove();

                // Create a temporary container with DataTransfer to build a proper FileList
                const dt = new DataTransfer();
                files.forEach(f => dt.items.add(f));
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'file';
                hiddenInput.name = 'photos[]';
                hiddenInput.multiple = true;
                hiddenInput.files = dt.files;
                hiddenInput.style.display = 'none';
                form.appendChild(hiddenInput);
            }
        });
    });
</script>
@endsection
