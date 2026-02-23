@extends('layouts.technician')

@section('title', 'Daftar Tugas')

@section('header')
    <h1 class="font-bold text-lg text-center text-white">Daftar Tugas</h1>
@endsection

@section('content')
<div x-data="{ tab: '{{ $tab ?? 'my_tasks' }}' }">

    {{-- Header Tabs --}}
    <div class="flex p-1 bg-white rounded-xl shadow-sm mb-6 border border-gray-100">
        <button 
            @click="tab = 'my_tasks'" 
            :class="tab === 'my_tasks' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-gear"></i> Tugas Saya
            @if($myTasks->count() > 0)
                <span :class="tab === 'my_tasks' ? 'bg-white text-blue-600' : 'bg-blue-100 text-blue-600'" class="text-[10px] px-1.5 py-0.5 rounded-full">{{ $myTasks->count() }}</span>
            @endif
        </button>
        <button 
            @click="tab = 'pool'" 
            :class="tab === 'pool' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 py-2.5 text-sm font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
            <i class="fa-solid fa-box-open"></i> Pool Tugas
            @if($poolTasks->count() > 0)
                <span :class="tab === 'pool' ? 'bg-white text-blue-600' : 'bg-gray-200 text-gray-600'" class="text-[10px] px-1.5 py-0.5 rounded-full">{{ $poolTasks->count() }}</span>
            @endif
        </button>
    </div>

    {{-- TAB 1: TUGAS SAYA --}}
    <div x-show="tab === 'my_tasks'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
        <div class="space-y-4">
            @forelse($myTasks as $task)
                <div class="bg-white rounded-xl shadow-sm border-l-4 overflow-hidden relative
                    {{ match($task->priority) { 
                        'high' => 'border-l-red-500', 
                        'medium' => 'border-l-orange-500', 
                        default => 'border-l-blue-500' 
                    } }}">
                    
                    <div class="p-4">
                        {{-- Header Card --}}
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-mono px-2 py-0.5 rounded inline-block mb-1">
                                    #{{ $task->ticket_number }}
                                </span>
                                <h3 class="font-bold text-gray-800 leading-tight">{{ $task->issue_description }}</h3>
                            </div>
                            <span class="text-[10px] font-bold uppercase px-2 py-1 rounded
                                {{ match($task->status) {
                                    'in_progress' => 'bg-cyan-100 text-cyan-700',
                                    'pending_part' => 'bg-purple-100 text-purple-700',
                                    'assigned' => 'bg-blue-50 text-blue-600',
                                    default => 'bg-gray-100 text-gray-500'
                                } }}">
                                {{ str_replace('_', ' ', $task->status) }}
                            </span>
                        </div>
                        
                        {{-- Lokasi & Aset --}}
                        <div class="flex items-center gap-2 text-xs text-gray-500 mb-4">
                            <i class="fa-solid fa-location-dot text-gray-300"></i>
                            <span>{{ $task->asset->location->name }} &bull; {{ $task->asset->name }}</span>
                        </div>

                        {{-- Action Button --}}
                        <a href="{{ route('technician.tasks.show', $task->id) }}" class="block w-full text-center py-2.5 rounded-lg font-bold text-xs shadow-sm active:scale-95 transition
                            {{ $task->status == 'in_progress' ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                            @if($task->status == 'in_progress')
                                <i class="fa-solid fa-play mr-1"></i> Lanjutkan Pekerjaan
                            @else
                                <i class="fa-solid fa-flag mr-1"></i> Mulai Kerjakan
                            @endif
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-clipboard-check text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-gray-600 font-bold text-sm">Tidak ada tugas aktif</h3>
                    <p class="text-xs text-gray-400 mt-1">Anda sedang tidak mengerjakan tugas apapun.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- TAB 2: POOL TUGAS --}}
    <div x-show="tab === 'pool'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
        <div class="space-y-4">
            @forelse($poolTasks as $task)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                             <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide {{ match($task->priority) { 
                                'high' => 'text-red-700 bg-red-50 border border-red-100', 
                                'medium' => 'text-orange-700 bg-orange-50 border border-orange-100', 
                                default => 'text-blue-700 bg-blue-50 border border-blue-100' 
                            } }}">
                                {{ $task->priority }} Priority
                            </span>
                            
                            @if($task->source == 'manual_ticket')
                                <span class="bg-purple-100 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                                    <i class="fa-solid fa-user-tag"></i> Laporan User
                                </span>
                            @elseif($task->status == 'handover')
                                <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-0.5 rounded flex items-center gap-1">
                                    <i class="fa-solid fa-hand-holding-hand"></i> Handover
                                </span>
                            @endif
                        </div>

                        <h3 class="font-bold text-gray-800 text-sm mb-1">{{ $task->issue_description }}</h3>
                            @if($task->reporter && $task->source == 'manual_ticket')
                                <br><span class="text-purple-600 font-medium mt-1 inline-block"><i class="fa-solid fa-user text-[10px]"></i> {{ $task->reporter->name }}</span>
                            @endif
                        </p>

                        @if($task->initial_photo)
                            <div class="mb-3 rounded-lg overflow-hidden h-32 border border-gray-200 relative group-hover:opacity-90 transition">
                                <img src="{{ asset('storage/' . $task->initial_photo) }}" class="w-full h-full object-cover" alt="Bukti Foto">
                            </div>
                        @endif

                        <div class="flex gap-2">
                             <a href="{{ route('technician.tasks.show', $task->id) }}" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-xs font-bold hover:bg-gray-200 transition flex items-center justify-center gap-2 active:scale-95">
                                <i class="fa-solid fa-eye"></i> Detail
                            </a>
                            <form action="{{ route('technician.tasks.claim', $task->id) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="w-full bg-white border border-blue-600 text-blue-600 py-2 rounded-lg text-xs font-bold hover:bg-blue-50 transition flex items-center justify-center gap-2 active:scale-95">
                                    <i class="fa-solid fa-hand-point-up"></i> Ambil
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                     <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-box-open text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-gray-600 font-bold text-sm">Pool Kosong</h3>
                    <p class="text-xs text-gray-400 mt-1">Tidak ada tugas tersedia saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection