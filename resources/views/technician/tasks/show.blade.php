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
                    <h2 class="font-bold text-gray-800 text-lg mt-2">{{ $task->asset->name }}</h2>
                    <p class="text-sm text-gray-500"><i class="fa-solid fa-location-dot mr-1"></i> {{ $task->asset->location->name }}</p>
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

        {{-- Issue Description --}}
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-2 border-b pb-2">Deskripsi Masalah</h3>
            <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">{{ $task->issue_description }}</p>
            
            @if($task->initial_photo)
                <div class="mt-4">
                    <p class="text-xs font-bold text-gray-400 uppercase mb-2">Foto / Bukti Awal</p>
                    <div class="rounded-lg overflow-hidden border border-gray-200">
                        <img src="{{ asset('storage/'.$task->initial_photo) }}" class="w-full h-auto object-cover" alt="Foto Bukti User">
                    </div>
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
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Alasan Handover</label>
                    <textarea name="note" rows="3" class="w-full rounded-xl border-2 border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 text-sm p-2" placeholder="Jelaskan kendala kenapa tugas ini dihandover" required></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Bukti Foto (Opsional)</label>
                    <input type="file" name="photo" class="block w-full text-xs text-slate-500
                        file:mr-4 file:py-2.5 file:px-4
                        file:rounded-full file:border-0
                        file:text-xs file:font-semibold
                        file:bg-yellow-50 file:text-yellow-700
                        hover:file:bg-yellow-100" accept="image/*">
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
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Catatan Perbaikan</label>
                    <textarea name="description" rows="3" class="w-full rounded-xl border-2 border-gray-300 focus:border-green-500 focus:ring-green-500 text-sm p-2" placeholder="Apa yang sudah diperbaiki?" required></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Bukti Foto</label>
                    <input type="file" name="photo" class="block w-full text-xs text-slate-500
                        file:mr-4 file:py-2.5 file:px-4
                        file:rounded-full file:border-0
                        file:text-xs file:font-semibold
                        file:bg-green-50 file:text-green-700
                        hover:file:bg-green-100" accept="image/*" required>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showCompleteModal = false" class="flex-1 py-3 rounded-xl bg-gray-50 text-gray-600 font-bold hover:bg-gray-100 text-sm">Batal</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-green-600 text-white font-bold hover:bg-green-700 shadow-lg shadow-green-200 text-sm">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection