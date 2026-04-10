@extends('layouts.technician')

@section('title', 'Daftar Tugas')

@section('content')
<div x-data="{ tab: '{{ $tab ?? 'my_tasks' }}' }" class="max-w-5xl mx-auto pb-10">

    {{-- Header Tabs --}}
    <div class="flex p-1.5 bg-gray-100/80 backdrop-blur-sm rounded-2xl mb-8 border border-gray-200/60 max-w-xl mx-auto shadow-sm">
        <button 
            @click="tab = 'my_tasks'" 
            :class="tab === 'my_tasks' ? 'bg-white text-blue-700 shadow ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
            class="flex-1 py-3 text-sm font-bold rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-gear"></i> Tugas Saya
            @if($myTasks->count() > 0)
                <span :class="tab === 'my_tasks' ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-500'" class="text-[10px] px-2 py-0.5 rounded-full">{{ $myTasks->count() }}</span>
            @endif
        </button>
        <button 
            @click="tab = 'pool'" 
            :class="tab === 'pool' ? 'bg-white text-blue-700 shadow ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'"
            class="flex-1 py-3 text-sm font-bold rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
            <i class="fa-solid fa-box-open"></i> Pool Tugas
            @if($poolTasks->count() > 0)
                <span :class="tab === 'pool' ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-500'" class="text-[10px] px-2 py-0.5 rounded-full">{{ $poolTasks->count() }}</span>
            @endif
        </button>
    </div>

    {{-- TAB 1: TUGAS SAYA --}}
    <div x-show="tab === 'my_tasks'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($myTasks as $task)
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all border border-gray-100 overflow-hidden relative flex flex-col group">
                    <div class="absolute top-0 left-0 right-0 h-1 {{ match($task->priority) { 
                        'high' => 'bg-red-500', 
                        'medium' => 'bg-orange-500', 
                        default => 'bg-blue-500' 
                    } }}"></div>
                    
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-3 mt-1">
                            <div>
                                <span class="text-gray-400 text-[10px] font-mono font-bold tracking-wider mb-1 block">
                                    #{{ $task->ticket_number }}
                                </span>
                                <h3 class="font-bold text-gray-800 text-base leading-snug group-hover:text-blue-600 transition-colors">{{ $task->issue_description }}</h3>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-gray-500 mb-5 bg-gray-50/50 p-2.5 rounded-lg border border-gray-100">
                            <span class="font-bold uppercase tracking-wider text-[10px] px-2 py-0.5 rounded-md
                                {{ match($task->status) {
                                    'in_progress' => 'bg-cyan-100/50 text-cyan-700',
                                    'pending_part' => 'bg-purple-100/50 text-purple-700',
                                    'assigned' => 'bg-blue-50 text-blue-600',
                                    default => 'bg-gray-100 text-gray-500'
                                } }}">
                                {{ str_replace('_', ' ', $task->status) }}
                            </span>
                            <span class="text-gray-300">|</span>
                            @if($task->asset)
                                <div class="flex items-center gap-1.5">
                                    <i class="{{ $task->asset->location_id ? 'fa-solid fa-location-dot text-gray-400' : 'fa-solid fa-cloud text-blue-400' }}"></i>
                                    <span>{{ $task->location->name ?? '-' }}</span>
                                    <span class="text-gray-300">&bull;</span>
                                    <span class="font-bold text-gray-700">{{ $task->asset->name }}</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5 text-orange-600">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span class="font-bold">{{ $task->location->name ?? 'Area Tidak Diketahui' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-auto">
                            <a href="{{ route('technician.tasks.show', $task->id) }}" class="flex w-full items-center justify-center py-2.5 rounded-xl font-bold text-sm transition-all active:scale-95
                                {{ $task->status == 'in_progress' ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white border border-emerald-200' : 'bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200' }}">
                                @if($task->status == 'in_progress')
                                    <i class="fa-solid fa-play mr-2"></i> Lanjutkan Pekerjaan
                                @else
                                    <i class="fa-solid fa-bolt mr-2"></i> Mulai Kerjakan
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 bg-white rounded-3xl border border-dashed border-gray-200">
                    <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-clipboard-check text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-gray-700 font-bold text-base">Tidak ada tugas aktif</h3>
                    <p class="text-sm text-gray-400 mt-1">Anda sedang tidak mengerjakan tugas apapun.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- TAB 2: POOL TUGAS (REDESIGN HORIZONTAL LIST) --}}
    <div x-show="tab === 'pool'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
        <div class="space-y-4">
            @forelse($poolTasks as $task)
                {{-- KUNCI REDESIGN: flex-col di HP, flex-row di Layar Besar --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col sm:flex-row group hover:shadow-md transition-all duration-300">
                    
                    {{-- THUMBNAIL GAMBAR (Di Kiri pada Layar Besar) --}}
                    @if($task->initial_photo)
                        <div class="relative w-full sm:w-48 md:w-56 h-48 sm:h-auto shrink-0 bg-gray-50 border-b sm:border-b-0 sm:border-r border-gray-100 overflow-hidden">
                            <img src="{{ asset('storage/' . $task->initial_photo) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="Bukti Foto">
                            
                            @if(is_array($task->photos_before) && count($task->photos_before) > 1)
                                <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-md text-white text-[10px] px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5 shadow-sm">
                                    <i class="fa-solid fa-images text-[9px]"></i> +{{ count($task->photos_before) - 1 }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- KONTEN TEKS (Di Kanan pada Layar Besar) --}}
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                             <span class="text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider {{ match($task->priority) { 
                                'high' => 'text-red-700 bg-red-50 border border-red-100/50', 
                                'medium' => 'text-orange-700 bg-orange-50 border border-orange-100/50', 
                                default => 'text-blue-700 bg-blue-50 border border-blue-100/50' 
                            } }}">
                                <i class="fa-solid fa-flag mr-1 opacity-70"></i> {{ $task->priority }}
                            </span>
                            
                            @if($task->source == 'manual_ticket')
                                <span class="bg-purple-50 text-purple-700 border border-purple-100/50 text-[10px] font-bold px-2.5 py-1 rounded-md flex items-center gap-1">
                                    <i class="fa-solid fa-user-tag opacity-70"></i> Laporan User
                                </span>
                            @elseif($task->status == 'handover')
                                <span class="bg-yellow-50 text-yellow-700 border border-yellow-100/50 text-[10px] font-bold px-2.5 py-1 rounded-md flex items-center gap-1">
                                    <i class="fa-solid fa-hand-holding-hand opacity-70"></i> Handover
                                </span>
                            @endif
                        </div>

                        <h3 class="font-bold text-gray-800 text-lg mb-2 leading-snug group-hover:text-blue-600 transition-colors">{{ $task->issue_description }}</h3>
                        
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500 mb-5">
                            @if($task->asset)
                                <div class="flex items-center gap-1.5">
                                    <i class="{{ $task->asset->location_id ? 'fa-solid fa-location-dot text-gray-400' : 'fa-solid fa-cloud text-blue-400' }}"></i>
                                    <span>{{ $task->asset->location->name ?? ($task->location->name ?? '-') }}</span>
                                    <span class="text-gray-300">/</span>
                                    <span class="font-bold text-gray-700">{{ $task->asset->name }}</span>
                                </div>
                            @else
                                <div class="flex items-center gap-1.5 text-orange-600 bg-orange-50 px-2 py-0.5 rounded">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span class="font-bold">{{ $task->location->name ?? 'Area Umum' }}</span>
                                </div>
                            @endif

                            @if($task->reporter && $task->source == 'manual_ticket')
                                <div class="flex items-center gap-1.5 text-purple-600">
                                    <span class="text-gray-300">|</span>
                                    <i class="fa-solid fa-user-circle"></i>
                                    <span class="font-medium">{{ $task->reporter->name }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Tombol Aksi di Bawah Kanan --}}
                        <div class="mt-auto pt-2 flex flex-col sm:flex-row gap-3 sm:justify-end">
                             <a href="{{ route('technician.tasks.show', $task->id) }}" class="px-5 py-2.5 bg-gray-50 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-100 transition-all border border-gray-200 text-center active:scale-95">
                                Detail
                            </a>
                            <form action="{{ route('technician.tasks.claim', $task->id) }}" method="POST" class="sm:w-auto">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="w-full px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-all shadow-md shadow-blue-500/20 flex items-center justify-center gap-2 active:scale-95">
                                    <i class="fa-solid fa-hand-point-up"></i> Ambil Tugas
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-white rounded-3xl border border-dashed border-gray-200">
                     <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-inbox text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-gray-700 font-bold text-base">Pool Kosong</h3>
                    <p class="text-sm text-gray-400 mt-1">Belum ada tugas baru yang menunggu untuk diambil.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
