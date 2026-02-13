@extends('layouts.technician')

@section('header')
    <div class="flex items-center gap-3">
        <a href="{{ route('technician.scan') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <p class="text-blue-100 text-xs">Lokasi Terdeteksi</p>
            <h1 class="font-bold text-lg leading-tight">{{ $location->name }}</h1>
        </div>
    </div>
@endsection

@section('content')
    {{-- Task Statistics --}}
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 rounded-xl mb-6 text-white shadow-lg">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-xl"></i>
                <h3 class="font-bold">Tugas Hari Ini</h3>
            </div>
            <span class="text-2xl font-bold">{{ $stats['total_tasks'] }}</span>
        </div>
        
        <div class="grid grid-cols-3 gap-2 text-xs">
            <div class="bg-white/20 rounded-lg p-2 text-center">
                <div class="font-bold text-lg">{{ $stats['daily_tasks'] }}</div>
                <div class="opacity-90">Harian</div>
            </div>
            <div class="bg-white/20 rounded-lg p-2 text-center">
                <div class="font-bold text-lg">{{ $stats['weekly_tasks'] }}</div>
                <div class="opacity-90">Mingguan</div>
            </div>
            <div class="bg-white/20 rounded-lg p-2 text-center">
                <div class="font-bold text-lg">{{ $stats['monthly_tasks'] }}</div>
                <div class="opacity-90">Bulanan</div>
            </div>
        </div>
    </div>

    {{-- Info Lokasi --}}
    <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-6 flex items-start gap-3">
        <i class="fa-solid fa-map-location-dot text-blue-600 text-xl mt-1"></i>
        <div>
            <p class="text-sm font-bold text-gray-800">{{ $location->description ?? 'Tidak ada deskripsi lokasi.' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stats['total_assets'] }} aset perlu dicek hari ini</p>
        </div>
    </div>

    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
        <i class="fa-solid fa-list-check text-green-600"></i> Daftar Aset & Tugas
    </h3>

    <div class="space-y-3">
        @forelse($assets as $asset)
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                {{-- Asset Header --}}
                <div class="flex justify-between items-start mb-3">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center">
                            <i class="fa-solid fa-cube text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800 text-sm">{{ $asset->name }}</h4>
                            <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $asset->serial_number ?? '-' }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $asset->category->name ?? 'Uncategorized' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Maintenance Tasks --}}
                <div class="space-y-2">
                    @foreach($asset->maintenances as $maintenance)
                        @php
                            $frequency = $maintenance->checklistTemplate->frequency;
                            $badgeColors = [
                                'daily' => 'bg-green-100 text-green-700 border-green-200',
                                'weekly' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'monthly' => 'bg-purple-100 text-purple-700 border-purple-200',
                            ];
                            $iconColors = [
                                'daily' => 'text-green-600',
                                'weekly' => 'text-blue-600',
                                'monthly' => 'text-purple-600',
                            ];
                        @endphp
                        
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border {{ $badgeColors[$frequency] ?? 'border-gray-200' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-clipboard-list {{ $iconColors[$frequency] ?? 'text-gray-600' }}"></i>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ $maintenance->checklistTemplate->name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $badgeColors[$frequency] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ strtoupper($frequency) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            <i class="fa-solid fa-clock"></i> ~{{ $maintenance->checklistTemplate->estimated_duration ?? 5 }} menit
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('technician.maintenance.start', $maintenance->id) }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-blue-700 transition active:scale-95 flex items-center gap-2">
                                <i class="fa-solid fa-play"></i> Mulai
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <i class="fa-solid fa-calendar-check text-gray-300 text-4xl mb-2"></i>
                <p class="text-gray-500 text-sm font-bold">Tidak ada tugas maintenance hari ini</p>
                <p class="text-gray-400 text-xs mt-1">Semua aset di lokasi ini sudah dicek atau tidak ada jadwal hari ini.</p>
            </div>
        @endforelse
    </div>
@endsection