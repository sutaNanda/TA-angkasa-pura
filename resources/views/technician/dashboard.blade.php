@extends('layouts.technician')
@section('title', 'Dashboard') 

@section('content')
    
    {{-- 1. STATISTIK RINGKAS (HANYA 2 ITEM PENTING) --}}
    <div class="grid grid-cols-2 gap-4 mb-8 mt-4">
        {{-- Agenda Kerja: Gabungan Tugas Saya + Pool --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border-b-4 border-blue-500 hover:shadow-md transition-all duration-200 transform hover:-translate-y-1 group">
            <p class="text-[10px] text-gray-400 mb-1 font-bold uppercase tracking-widest leading-none">Penugasan & Pool</p>
            <div class="flex items-end justify-between">
                <span class="text-2xl font-black text-gray-900">{{ $poolTasks->count() + $myTasks->count() }}</span>
                <i class="fa-solid fa-clipboard-list text-blue-100 text-3xl group-hover:text-blue-200 group-hover:scale-110 transition-all"></i>
            </div>
        </div>

        {{-- Selesai Hari Ini --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border-b-4 border-green-500 hover:shadow-md transition-all duration-200 transform hover:-translate-y-1 group">
            <p class="text-[10px] text-gray-400 mb-1 font-bold uppercase tracking-widest leading-none">Selesai Hari Ini</p>
            <div class="flex items-end justify-between">
                <span class="text-2xl font-black text-gray-900">{{ $stats['completed_today'] }}</span>
                <i class="fa-solid fa-circle-check text-green-100 text-3xl group-hover:text-green-200 group-hover:scale-110 transition-all"></i>
            </div>
        </div>
    </div>

    <div class="lg:grid lg:grid-cols-3 lg:gap-8 items-start">
        {{-- LEFT COLUMN: ALERTS (1/3 on Desktop) --}}
        <div class="lg:col-span-1 space-y-4 mb-8 lg:mb-0">
            <h3 class="hidden lg:block font-bold text-gray-400 text-[10px] uppercase tracking-widest mb-4">Pemberitahuan</h3>
            
            {{-- A. HANDOVER ALERT --}}
            @if($handoverTasks->count() > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                    <div class="absolute -top-2 -right-2 p-2 opacity-5 scale-150 group-hover:rotate-12 transition-transform duration-500">
                        <i class="fa-solid fa-hand-holding-hand text-6xl text-yellow-600"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-yellow-800 flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation"></i> Handover!
                            </h3>
                        </div>
                        <p class="text-xs text-yellow-700 mb-4 leading-relaxed">Ada <strong>{{ $handoverTasks->count() }}</strong> tugas handover dari shift sebelumnya.</p>
                        <a href="{{ route('technician.tasks.index', ['tab' => 'pool']) }}" class="flex items-center justify-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white text-[10px] font-black uppercase tracking-widest px-4 py-2.5 rounded-xl shadow-lg shadow-yellow-500/20 transition active:scale-95">Lihat <i class="fa-solid fa-arrow-right text-[8px]"></i></a>
                    </div>
                </div>
            @endif

            {{-- B. USER REPORT ALERT --}}
            @if($userReports->count() > 0)
                <div class="bg-purple-50 border border-purple-200 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                    <div class="absolute -top-2 -right-2 p-2 opacity-5 scale-150 group-hover:rotate-12 transition-transform duration-500">
                        <i class="fa-solid fa-user-clock text-6xl text-purple-600"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-purple-800 flex items-center gap-2">
                                <i class="fa-solid fa-bell animate-bounce"></i> Laporan Baru
                            </h3>
                        </div>
                        <p class="text-xs text-purple-700 mb-4 leading-relaxed">Ada <strong>{{ $userReports->count() }}</strong> keluhan aset yang butuh pemeriksaan segera.</p>
                        <a href="{{ route('technician.tasks.index', ['tab' => 'pool']) }}" class="flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white text-[10px] font-black uppercase tracking-widest px-4 py-2.5 rounded-xl shadow-lg shadow-purple-600/20 transition active:scale-95">Detail <i class="fa-solid fa-arrow-right text-[8px]"></i></a>
                    </div>
                </div>
            @endif

            {{-- C. GENERIC OPEN TASKS --}}
            @if($handoverTasks->count() == 0 && $userReports->count() == 0 && $poolTasks->count() > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-center">
                        <div>
                             <h3 class="font-bold text-blue-800 text-sm mb-1">Pool Antrian</h3>
                             <p class="text-xs text-blue-600">Terdeteksi <strong>{{ $poolTasks->count() }}</strong> tugas baru.</p>
                        </div>
                        <a href="{{ route('technician.tasks.index', ['tab' => 'pool']) }}" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-xl shadow-lg shadow-blue-500/20 transition">Cek</a>
                    </div>
                </div>
            @endif

            @if($handoverTasks->count() == 0 && $userReports->count() == 0 && $poolTasks->count() == 0)
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 text-center lg:py-12">
                    <i class="fa-solid fa-circle-check text-gray-200 text-4xl mb-3"></i>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Semua Aman</p>
                </div>
            @endif
        </div>

        {{-- RIGHT COLUMN: TABS & TASKS (2/3 on Desktop) --}}
        <div class="lg:col-span-2">
            {{-- 3. TAB SYSTEM --}}
            <div x-data="{ tab: 'patrol' }" class="mb-10 md:mb-6">
                
                {{-- Tab Navigation --}}
                <div class="flex p-1.5 bg-gray-200/50 rounded-2xl mb-6">
                    <button 
                        @click="tab = 'patrol'" 
                        :class="tab === 'patrol' ? 'bg-white text-blue-600 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-3 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-clipboard-list"></i> Patroli
                    </button>
                    <button 
                        @click="tab = 'ticket'" 
                        :class="tab === 'ticket' ? 'bg-white text-blue-600 shadow-md ring-1 ring-black/5' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-3 text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200 flex items-center justify-center gap-2 relative">
                        <i class="fa-solid fa-screwdriver-wrench"></i> Perbaikan
                        @if($stats['pending'] > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-black h-5 w-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm ring-1 ring-red-500/20">{{ $stats['pending'] }}</span>
                        @endif
                    </button>
                </div>

                {{-- Tab Content: Patroli --}}
                <div x-show="tab === 'patrol'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translateY(10px)" x-transition:enter-end="opacity-100 translateY(0)">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($patrols as $locationId => $items)
                            @php
                                $firstItem = $items->first();
                                $location = $firstItem->location ?? ($firstItem->asset->location ?? null);
                                $locationName = $locationId == 0 ? 'Virtual / Software' : ($location->name ?? 'Lokasi Tidak Diketahui');
                                $pendingCount = $items->count();
                                $previewItems = $items->take(4);
                            @endphp

                            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group hover:border-blue-200 transition-colors duration-300">
                                {{-- Header Card --}}
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl {{ $locationId == 0 ? 'bg-blue-50 text-blue-500 border-blue-100 group-hover:bg-blue-100' : 'bg-gray-50 text-gray-400 border-gray-100 group-hover:bg-blue-50 group-hover:text-blue-500' }} flex items-center justify-center flex-shrink-0 font-bold border transition-colors">
                                            <i class="fa-solid {{ $locationId == 0 ? 'fa-cloud' : 'fa-location-dot' }}"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-gray-900 leading-tight">{{ $locationName }}</h4>
                                            <p class="text-[10px] text-orange-600 font-black uppercase tracking-widest mt-0.5">{{ $pendingCount }} Aset</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Asset Preview (Avatars) --}}
                                <div class="flex items-center pl-1 mb-4">
                                    @foreach($previewItems as $item)
                                        @php
                                            $displayName = $item->asset->name ?? $item->maintenancePlan->name ?? 'Aset';
                                        @endphp
                                        <div class="w-8 h-8 rounded-full bg-blue-50 border-2 border-white flex items-center justify-center text-[10px] text-blue-500 font-black -ml-2 first:ml-0 shadow-sm" title="{{ $displayName }}">
                                            {{ substr($displayName, 0, 1) }}
                                        </div>
                                    @endforeach
                                    @if($pendingCount > 4)
                                        <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-[9px] text-gray-500 font-bold -ml-2">
                                            +{{ $pendingCount - 4 }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Locked Indicator / Virtual Bypass Button --}}
                                @if($locationId == 0)
                                    <a href="{{ route('technician.scan.show', 0) }}" class="mt-2 bg-blue-50 hover:bg-blue-100 rounded-xl py-3 px-4 flex items-center justify-center gap-2 border border-blue-200 shadow-sm transition-colors active:scale-95 cursor-pointer group-hover:border-blue-300">
                                        <i class="fa-solid fa-cloud-arrow-up text-blue-600 text-sm"></i>
                                        <span class="text-[9px] font-black text-blue-700 uppercase tracking-widest leading-none">Mulai Patroli Virtual</span>
                                    </a>
                                @else
                                    <div class="mt-2 bg-gray-50 rounded-xl py-3 px-4 flex items-center justify-center gap-2 border border-gray-100 border-dashed">
                                        <i class="fa-solid fa-qrcode text-gray-300 text-xs"></i>
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none">Scan QR Lokasi</span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="col-span-full text-center py-16 bg-gray-50/50 rounded-2xl border-2 border-dashed border-gray-200">
                                <div class="bg-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                    <i class="fa-solid fa-calendar-check text-green-500 text-3xl"></i>
                                </div>
                                <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest">Jadwal Selesai</h4>
                                <p class="text-xs text-gray-400 font-medium">Semua area telah dipatroli hari ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab Content: Perbaikan --}}
                <div x-show="tab === 'ticket'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translateY(10px)" x-transition:enter-end="opacity-100 translateY(0)" style="display: none;">
                    <div class="space-y-4">
                        
                        {{-- SECTION: POOL TASKS --}}
                        @if($poolTasks->count() > 0)
                            <h5 class="font-black text-gray-400 text-[10px] uppercase tracking-widest py-2">Antrian Global ({{ $poolTasks->count() }})</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @foreach($poolTasks as $task)
                                    <a href="{{ route('technician.tasks.show', $task->id) }}" class="block bg-amber-50/50 p-5 rounded-2xl border border-amber-100 shadow-sm hover:shadow-md transition-all group relative overflow-hidden">
                                        <div class="flex justify-between items-start mb-3">
                                            <span class="text-[9px] font-black px-2 py-1 rounded uppercase tracking-widest text-amber-700 bg-amber-100 border border-amber-200">
                                                Pool Tugas
                                            </span>
                                            <span class="text-[9px] text-gray-400 font-mono bg-white px-1.5 py-0.5 rounded border border-gray-100 shadow-sm">#{{ $task->ticket_number }}</span>
                                        </div>
                                        
                                        <h4 class="font-bold text-gray-900 text-sm mb-3 leading-snug line-clamp-2">{{ $task->issue_description }}</h4>
                                        
                                        <div class="flex items-center gap-3 text-[10px] text-gray-500 font-semibold border-t border-amber-100/50 pt-3">
                                            <span class="flex items-center gap-1.5 truncate">
                                                <i class="fa-solid fa-cube text-amber-400"></i> {{ $task->asset ? $task->asset->name : 'Aset belum diidentifikasi' }}
                                            </span>
                                        </div>
                                        <div class="absolute right-4 bottom-4 text-amber-300 group-hover:translate-x-1 group-hover:text-amber-500 transition-all">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <h5 class="font-black text-gray-400 text-[10px] uppercase tracking-widest py-2 border-t border-gray-100 mt-4">Tugas Personal ({{ $myTasks->count() }})</h5>
                        @endif

                        {{-- SECTION: MY TASKS --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($myTasks as $task)
                                <a href="{{ route('technician.tasks.show', $task->id) }}" class="block bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all relative group">
                                    
                                    {{-- Priority Stripe --}}
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ match($task->priority) { 'high' => 'bg-red-500', 'medium' => 'bg-orange-400', default => 'bg-blue-400' } }}"></div>

                                    <div class="flex justify-between items-start mb-3">
                                        <span class="text-[9px] font-black px-2 py-1 rounded uppercase tracking-widest {{ match($task->priority) { 
                                            'high' => 'text-red-700 bg-red-50 border border-red-100', 
                                            'medium' => 'text-orange-700 bg-orange-50 border border-orange-100', 
                                            default => 'text-blue-700 bg-blue-50 border border-blue-100' 
                                        } }}">
                                            Prioritas {{ $task->priority }}
                                        </span>
                                        <span class="text-[9px] text-gray-400 font-mono bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100">#{{ $task->ticket_number }}</span>
                                    </div>
                                    
                                    <h4 class="font-bold text-gray-900 text-sm mb-3 leading-snug line-clamp-2 h-10">{{ $task->issue_description }}</h4>
                                    
                                    <div class="flex items-center gap-3 text-[10px] text-gray-500 font-semibold border-t border-gray-50 pt-3">
                                        <span class="flex items-center gap-1.5 truncate">
                                            <i class="fa-solid fa-cube text-gray-300 group-hover:text-blue-400 transition-colors"></i> {{ $task->asset ? $task->asset->name : 'Aset belum diidentifikasi' }}
                                        </span>
                                    </div>
                                    
                                    <div class="absolute right-4 bottom-4 text-gray-200 group-hover:translate-x-1 group-hover:text-blue-500 transition-all">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </div>
                                </a>
                            @empty
                                @if($poolTasks->count() == 0)
                                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-center bg-gray-50/50 rounded-2xl border-2 border-dashed border-gray-200">
                                        <div class="bg-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                            <i class="fa-solid fa-mug-hot text-gray-300 text-3xl"></i>
                                        </div>
                                        <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest">Santai Sejenak</h4>
                                        <p class="text-xs text-gray-400 font-medium">Tidak ada tiket perbaikan aktif untuk Anda.</p>
                                    </div>
                                @endif
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- FAB REMOVED (Use Bottom Nav Scan) --}}
@endsection