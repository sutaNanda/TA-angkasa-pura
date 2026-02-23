@extends('layouts.technician')
@section('title', 'Dashboard') 

@section('header')

    <div class="flex justify-between items-center">
        <div>
            <p class="text-blue-100 text-xs">{{ $greeting }},</p>
            <h1 class="font-bold text-lg text-white">{{ $user->name }}</h1>
        </div>
        <!-- <a href="{{ route('technician.profile.index') }}" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm hover:bg-white/30 transition relative">
            <i class="fa-solid fa-bell text-white"></i>
            @if($stats['pending'] > 0)
                <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-blue-600"></span>
            @endif
        </a> -->
    </div>
@endsection

@section('content')
    
    {{-- 1. STATISTIK RINGKAS --}}
    <div class="grid grid-cols-2 gap-3 mb-5 mt-5">
        <a href="{{ route('technician.tasks.index', ['tab' => 'active']) }}" class="bg-white p-3 rounded-xl shadow-sm border-b-4 border-orange-500 active:scale-95 transition transform">
            <p class="text-[10px] text-gray-500 mb-1 font-bold uppercase">Tugas Saya</p>
            <div class="flex items-end justify-between">
                <span class="text-2xl font-bold text-gray-800">{{ $stats['pending'] }}</span>
                <i class="fa-solid fa-briefcase text-orange-100 text-2xl"></i>
            </div>
        </a>
        <a href="{{ route('technician.tasks.index', ['tab' => 'completed']) }}" class="bg-white p-3 rounded-xl shadow-sm border-b-4 border-green-500">
            <p class="text-[10px] text-gray-500 mb-1 font-bold uppercase">Selesai Hari Ini</p>
            <div class="flex items-end justify-between">
                <span class="text-2xl font-bold text-gray-800">{{ $stats['completed_today'] }}</span>
                <i class="fa-solid fa-clipboard-check text-green-100 text-2xl"></i>
            </div>
        </a>
    </div>

    {{-- 2. ALERT SECTIONS --}}
    
    {{-- A. HANDOVER ALERT --}}
    @if($handoverTasks->count() > 0)
        <div class="mb-4 bg-yellow-50 border border-yellow-400 rounded-xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-2 opacity-10">
                <i class="fa-solid fa-hand-holding-hand text-6xl text-yellow-600"></i>
            </div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-yellow-800 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Handover Task!
                    </h3>
                    <span class="bg-yellow-200 text-yellow-800 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $handoverTasks->count() }} Tersedia</span>
                </div>
                <p class="text-xs text-yellow-700 mb-3">Ada tuga handover dari shift sebelumnya. Segera ambil tindakan.</p>
                <a href="{{ route('technician.tasks.index', ['tab' => 'pool']) }}" class="inline-flex items-center gap-2 bg-yellow-500 text-white text-xs font-bold px-4 py-2 rounded-lg shadow hover:bg-yellow-600 transition active:scale-95">Lihat Handover <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    @endif

    {{-- B. USER REPORT ALERT --}}
    @if($userReports->count() > 0)
        <div class="mb-4 bg-purple-50 border border-purple-400 rounded-xl p-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-2 opacity-10">
                <i class="fa-solid fa-user-clock text-6xl text-purple-600"></i>
            </div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-purple-800 flex items-center gap-2">
                        <i class="fa-solid fa-bell"></i> Laporan User Baru!
                    </h3>
                    <span class="bg-purple-200 text-purple-800 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $userReports->count() }} Laporan</span>
                </div>
                <p class="text-xs text-purple-700 mb-3">Ada keluhan aset dari user/divisi lain yang butuh perbaikan.</p>
                <a href="{{ route('technician.tasks.index', ['tab' => 'pool']) }}" class="inline-flex items-center gap-2 bg-purple-600 text-white text-xs font-bold px-4 py-2 rounded-lg shadow hover:bg-purple-700 transition active:scale-95">Lihat Laporan <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    @endif

    {{-- C. GENERIC OPEN TASKS (Optional, low priority) --}}
    @if($handoverTasks->count() == 0 && $userReports->count() == 0 && $poolTasks->count() > 0)
        <div class="mb-4 bg-blue-50 border border-blue-300 rounded-xl p-4 shadow-sm relative overflow-hidden">
             <div class="flex justify-between items-start">
                <div>
                     <h3 class="font-bold text-blue-800 text-sm mb-1">Pool Tugas Tersedia</h3>
                     <p class="text-xs text-blue-600">Ada {{ $poolTasks->count() }} tugas di antrian pool.</p>
                </div>
                <a href="{{ route('technician.tasks.index', ['tab' => 'pool']) }}" class="bg-blue-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow">Cek Pool</a>
             </div>
        </div>
    @endif

    {{-- 3. TAB SYSTEM (Patroli vs Perbaikan) --}}
    <div x-data="{ tab: 'patrol' }" class="mb-24">
        
        {{-- Tab Navigation --}}
        <div class="flex p-1 bg-gray-100 rounded-xl mb-4">
            <button 
                @click="tab = 'patrol'" 
                :class="tab === 'patrol' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 text-sm font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fa-solid fa-clipboard-list"></i> Patroli Rutin
            </button>
            <button 
                @click="tab = 'ticket'" 
                :class="tab === 'ticket' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 text-sm font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2 relative">
                <i class="fa-solid fa-screwdriver-wrench"></i> Perbaikan
                @if($stats['pending'] > 0)
                    <span class="bg-red-500 text-white text-[9px] px-1.5 rounded-full ml-1">{{ $stats['pending'] }}</span>
                @endif
            </button>
        </div>

        {{-- Tab Content: Patroli (Grouped by Location) --}}
        <div x-show="tab === 'patrol'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div class="space-y-4">
                @forelse($patrols as $locationId => $assets)
                    @php
                        $location = $assets->first()->asset->location;
                        $locationName = $location->name ?? 'Lokasi Tidak Diketahui';
                        $pendingCount = $assets->count();
                        $previewAssets = $assets->take(4);
                    @endphp

    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm relative overflow-hidden opacity-75">
                        {{-- Header Card --}}
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-200 text-gray-500 flex items-center justify-center flex-shrink-0 font-bold border border-gray-300">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-700 leading-tight">{{ $locationName }}</h4>
                                    <p class="text-xs text-orange-600 font-bold mt-0.5">{{ $pendingCount }} Aset Pending</p>
                                </div>
                            </div>
                        </div>

                        {{-- Asset Preview (Avatars) --}}
                        <div class="flex items-center pl-1 mb-3">
                            @foreach($previewAssets as $item)
                                <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center text-[10px] text-gray-500 font-bold -ml-2 first:ml-0" title="{{ $item->asset->name }}">
                                    {{ substr($item->asset->name, 0, 1) }}
                                </div>
                            @endforeach
                            @if($pendingCount > 4)
                                <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white flex items-center justify-center text-[10px] text-gray-600 font-bold -ml-2">
                                    +{{ $pendingCount - 4 }}
                                </div>
                            @endif
                        </div>

                        {{-- Locked Indicator (No Button) --}}
                        <div class="mt-2 bg-gray-100 rounded-lg py-2 px-3 flex items-center justify-center gap-2 border border-gray-200 border-dashed">
                            <i class="fa-solid fa-location-dot text-gray-400 text-xs"></i>
                            <span class="text-[10px] font-semibold text-gray-500">Scan QR di Lokasi untuk Membuka</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <div class="bg-white w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm">
                            <i class="fa-solid fa-check text-green-500 text-2xl"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-800">Semua Beres!</h4>
                        <p class="text-xs text-gray-400">Tidak ada jadwal patroli tersisa hari ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Tab Content: Perbaikan --}}
        <div x-show="tab === 'ticket'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
            <div class="space-y-3">
                
                {{-- SECTION: POOL TASKS (Tersedia) --}}
                @if($poolTasks->count() > 0)
                    <h5 class="font-bold text-gray-500 text-xs uppercase mb-2 px-1">Tugas Tersedia (Pool)</h5>
                    @foreach($poolTasks as $task)
                        <a href="{{ route('technician.tasks.show', $task->id) }}" class="block bg-yellow-50 p-4 rounded-xl border border-yellow-200 shadow-sm active:scale-98 transition relative overflow-hidden group mb-3">
                            <div class="pl-1">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide text-yellow-700 bg-yellow-100 border border-yellow-200">
                                        Perlu Tindakan
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-mono bg-white px-1.5 py-0.5 rounded border border-gray-100">#{{ $task->ticket_number }}</span>
                                </div>
                                
                                <h4 class="font-bold text-gray-800 text-sm mb-2 leading-snug">{{ $task->issue_description }}</h4>
                                
                                <div class="flex items-center gap-3 text-xs text-gray-500 border-t border-yellow-100 pt-2 mt-2">
                                    <span class="flex items-center gap-1.5 truncate">
                                        <i class="fa-solid fa-cube text-yellow-400"></i> {{ $task->asset->name }}
                                    </span>
                                </div>
                            </div>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-yellow-300 group-hover:text-yellow-600 transition">
                                <i class="fa-solid fa-chevron-right"></i>
                            </div>
                        </a>
                    @endforeach

                    <h5 class="font-bold text-gray-500 text-xs uppercase mb-2 px-1 mt-6">Tugas Saya</h5>
                @endif

                {{-- SECTION: MY TASKS --}}
                @forelse($myTasks as $task)
                    <a href="{{ route('technician.tasks.show', $task->id) }}" class="block bg-white p-4 rounded-xl border border-gray-100 shadow-sm active:scale-98 transition relative overflow-hidden group">
                        
                        {{-- Priority Stripe --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ match($task->priority) { 'high' => 'bg-red-500', 'medium' => 'bg-orange-400', default => 'bg-blue-400' } }}"></div>

                        <div class="pl-3">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide {{ match($task->priority) { 
                                    'high' => 'text-red-700 bg-red-50 border border-red-100', 
                                    'medium' => 'text-orange-700 bg-orange-50 border border-orange-100', 
                                    default => 'text-blue-700 bg-blue-50 border border-blue-100' 
                                } }}">
                                    {{ $task->priority }} Priority
                                </span>
                                <span class="text-[10px] text-gray-400 font-mono bg-gray-50 px-1.5 py-0.5 rounded">#{{ $task->ticket_number }}</span>
                            </div>
                            
                            <h4 class="font-bold text-gray-800 text-sm mb-2 leading-snug">{{ $task->issue_description }}</h4>
                            
                            <div class="flex items-center gap-3 text-xs text-gray-500 border-t border-gray-50 pt-2 mt-2">
                                <span class="flex items-center gap-1.5 truncate">
                                    <i class="fa-solid fa-cube text-gray-300"></i> {{ $task->asset->name }}
                                </span>
                            </div>
                        </div>
                        
                        {{-- Chevron --}}
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 group-hover:text-blue-500 transition">
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                    </a>
                @empty
                    @if($poolTasks->count() == 0)
                        <div class="flex flex-col items-center justify-center py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <i class="fa-solid fa-mug-hot text-3xl text-gray-300 mb-2"></i>
                            <p class="text-sm font-bold text-gray-600">Santai Sejenak!</p>
                            <p class="text-xs text-gray-400">Tidak ada tiket perbaikan aktif.</p>
                        </div>
                    @endif
                @endforelse
            </div>
        </div>

    </div>

    {{-- FAB REMOVED (Use Bottom Nav Scan) --}}
@endsection