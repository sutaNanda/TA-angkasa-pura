@extends('layouts.technician')

@section('title', 'Detail Aset')

@section('content')
<div class="space-y-6">

    {{-- HEADER KEMBALI --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('technician.assets.index') }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h2 class="text-xl font-bold text-gray-800">Detail Aset</h2>
    </div>

    {{-- KARTU INFO UTAMA --}}
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden">
        {{-- Dekorasi Latar --}}
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 rounded-full opacity-50 blur-3xl pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row gap-6 items-start">
            
            {{-- Ikon Besar --}}
            <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-3xl shrink-0 shadow-inner border border-blue-200">
                @if($asset->category && $asset->category->icon)
                    <i class="{{ $asset->category->icon }}"></i>
                @else
                    <i class="fa-solid fa-box"></i>
                @endif
            </div>

            {{-- Info Teks --}}
            <div class="flex-1 w-full">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-2">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-1 leading-tight">{{ $asset->name }}</h1>
                        <p class="text-sm font-bold text-gray-500 font-mono tracking-wide"><i class="fa-solid fa-barcode mr-1"></i> {{ $asset->serial_number ?: 'NO-SN' }}</p>
                    </div>
                    
                    @php
                        $statusColor = match($asset->status) {
                            'normal' => 'bg-green-100 text-green-700 border-green-200',
                            'rusak' => 'bg-red-100 text-red-700 border-red-200',
                            'maintenance' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'hilang' => 'bg-gray-100 text-gray-700 border-gray-200',
                            default => 'bg-gray-100 text-gray-600 border-gray-200'
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-lg uppercase border {{ $statusColor }} self-start shrink-0 shadow-sm">
                        <i class="fa-solid fa-circle text-[8px] mr-2 {{ $asset->status === 'normal' ? 'text-green-500 animate-pulse' : 'text-current' }}"></i>
                        {{ $asset->status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <div class="bg-gray-50 px-4 py-3 rounded-xl border border-gray-100">
                        <span class="block text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">Kategori</span>
                        <p class="text-sm font-bold text-gray-800">{{ $asset->category ? $asset->category->name : '-' }}</p>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 rounded-xl border border-gray-100">
                        <span class="block text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">Lokasi / Ruangan</span>
                        <div class="flex flex-col">
                            <p class="text-sm font-bold text-gray-800 truncate mb-0.5">{{ $asset->location ? $asset->location->name : '-' }}</p>
                            @if($asset->location && $asset->location->parent)
                                <span class="text-[10px] text-gray-500 flex items-center gap-1 font-medium">
                                    <i class="fa-solid fa-arrow-turn-up text-[8px] rotate-90"></i>
                                    {{ $asset->location->parent->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($asset->purchase_date)
                    <div class="bg-gray-50 px-4 py-3 rounded-xl border border-gray-100 sm:col-span-2">
                        <span class="block text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">Tahun Pembelian</span>
                        <p class="text-sm font-bold text-gray-800"><i class="fa-regular fa-calendar mr-1"></i> {{ \Carbon\Carbon::parse($asset->purchase_date)->translatedFormat('d F Y') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- GALERI FOTO ASET --}}
    @if(is_array($asset->images) && count($asset->images) > 0)
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-images"></i>
            </div>
            Foto Aset
            <span class="text-xs text-gray-400 font-normal">({{ count($asset->images) }} foto)</span>
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($asset->images as $idx => $img)
                <div class="aspect-square rounded-xl overflow-hidden border border-gray-200 cursor-pointer hover:shadow-lg hover:border-blue-300 transition-all group"
                     onclick="openLightbox({{ $idx }})">
                    <img src="{{ asset('storage/' . $img) }}" 
                         alt="Foto aset {{ $idx + 1 }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </div>
            @endforeach
        </div>
    </div>

    {{-- LIGHTBOX OVERLAY --}}
    <div id="lightboxOverlay" class="fixed inset-0 z-50 bg-black/90 hidden flex items-center justify-center" onclick="closeLightbox()">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white/80 hover:text-white text-2xl z-50"><i class="fa-solid fa-xmark"></i></button>
        <button onclick="event.stopPropagation(); navigateLightbox(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white w-10 h-10 rounded-full flex items-center justify-center transition z-50"><i class="fa-solid fa-chevron-left"></i></button>
        <button onclick="event.stopPropagation(); navigateLightbox(1)" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/20 hover:bg-white/30 text-white w-10 h-10 rounded-full flex items-center justify-center transition z-50"><i class="fa-solid fa-chevron-right"></i></button>
        <img id="lightboxImage" src="" class="max-h-[85vh] max-w-[90vw] object-contain rounded-lg shadow-2xl" onclick="event.stopPropagation()">
        <div class="absolute bottom-6 left-0 right-0 text-center text-white/70 text-sm font-medium" id="lightboxCounter"></div>
    </div>

    <script>
        const lightboxImages = @json(array_map(fn($img) => asset('storage/' . $img), $asset->images));
        let lightboxIndex = 0;

        function openLightbox(idx) {
            lightboxIndex = idx;
            document.getElementById('lightboxImage').src = lightboxImages[idx];
            document.getElementById('lightboxCounter').innerText = `${idx + 1} / ${lightboxImages.length}`;
            document.getElementById('lightboxOverlay').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeLightbox() {
            document.getElementById('lightboxOverlay').classList.add('hidden');
            document.body.style.overflow = '';
        }
        function navigateLightbox(dir) {
            lightboxIndex += dir;
            if (lightboxIndex < 0) lightboxIndex = lightboxImages.length - 1;
            if (lightboxIndex >= lightboxImages.length) lightboxIndex = 0;
            document.getElementById('lightboxImage').src = lightboxImages[lightboxIndex];
            document.getElementById('lightboxCounter').innerText = `${lightboxIndex + 1} / ${lightboxImages.length}`;
        }
    </script>
    @endif

    {{-- RIWAYAT KERUSAKAN / TIKET TERAKHIR --}}
    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-6 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            5 Riwayat Tiket Kerusakan Terakhir
        </h3>

        @if($asset->workOrders->isEmpty())
            <div class="text-center py-6 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <p class="text-sm text-gray-500 font-medium">Belum ada riwayat kerusakan untuk aset ini.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($asset->workOrders as $ticket)
                    <div class="flex gap-4 p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors">
                        <div class="hidden sm:flex w-12 h-12 rounded-full bg-gray-100 items-center justify-center shrink-0">
                            <i class="fa-solid fa-tools text-gray-400"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-xs font-bold text-gray-500">{{ $ticket->created_at->translatedFormat('d M Y') }}</span>
                                @php
                                    $tColor = match($ticket->status) {
                                        'open' => 'bg-red-100 text-red-600',
                                        'in_progress' => 'bg-yellow-100 text-yellow-600',
                                        'completed' => 'bg-green-100 text-green-600',
                                        default => 'bg-gray-100 text-gray-600'
                                    };
                                    $tLabel = match($ticket->status) {
                                        'open' => 'Belum Ditangani',
                                        'in_progress' => 'Sedang Dikerjakan',
                                        'completed' => 'Selesai',
                                        default => ucwords(str_replace('_', ' ', $ticket->status))
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $tColor }}">{{ $tLabel }}</span>
                            </div>
                            <h4 class="font-bold text-sm text-gray-800 mb-1 leading-tight">{{ $ticket->issue_description }}</h4>
                            <p class="text-xs text-gray-500">Tiket: <span class="font-mono bg-white px-1 border rounded">{{ $ticket->ticket_number }}</span></p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
