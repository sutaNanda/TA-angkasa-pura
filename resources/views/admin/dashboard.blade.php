@extends('layouts.admin')

@section('title', 'Dashboard Koordinator')
@section('page-title', 'Dashboard Kinerja Operasional')

@section('content')
    {{-- HEADER TANGGAL & SHIFT --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <p class="text-sm text-gray-500">Pantauan Real-time</p>
            <h2 class="text-xl font-bold text-gray-800 capitalize">
                <i class="fa-regular fa-calendar mr-2 text-blue-600"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </h2>
        </div>

        {{-- Shift Indicator (Bisa dibuat dinamis berdasarkan jam nanti) --}}
        <div class="flex bg-white p-1 rounded-lg border border-gray-200 shadow-sm">
            @php $hour = date('H'); @endphp
            <button class="px-4 py-1.5 text-sm font-bold rounded shadow-sm {{ $hour >= 8 && $hour < 16 ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-50' }}">
                Shift Pagi
            </button>
            <button class="px-4 py-1.5 text-sm font-bold rounded shadow-sm {{ $hour >= 16 || $hour < 8 ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-50' }}">
                Shift Malam
            </button>
        </div>
    </div>

    {{-- 4 KARTU UTAMA --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        {{-- Progress Patroli --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-blue-600 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Patroli Hari Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">
                        {{ $stats['patrol_done'] }} <span class="text-sm text-gray-400 font-normal">/ {{ $stats['patrol_total'] }}</span>
                    </h3>
                </div>
                <div class="w-10 h-10 rounded bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-map-location-dot text-xl"></i>
                </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-1000" style="width: {{ $stats['patrol_percent'] }}%"></div>
            </div>
            <p class="text-[10px] text-blue-600 mt-2 font-medium">{{ $stats['patrol_percent'] }}% Area Selesai</p>
        </div>

        {{-- Open Ticket --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-red-500 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Tiket Open/Proses</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['lk_open'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded bg-red-50 text-red-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
            </div>
            <p class="text-[10px] text-red-600 mt-4 font-medium flex items-center gap-1">
                <i class="fa-solid fa-circle-exclamation"></i> Perlu Tindakan Segera
            </p>
        </div>

        {{-- Handover --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-orange-400 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Handover / Pending</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['lk_handover'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded bg-orange-50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-handshake text-xl"></i>
                </div>
            </div>
            <p class="text-[10px] text-orange-600 mt-4 font-medium">Operan Shift</p>
        </div>

        {{-- Teknisi --}}
        <div class="bg-white p-5 rounded-xl shadow-sm border-l-4 border-green-500 relative overflow-hidden group hover:-translate-y-1 transition duration-300">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Teknisi Terdaftar</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['tech_active'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded bg-green-50 text-green-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i class="fa-solid fa-users-gear text-xl"></i>
                </div>
            </div>
            <p class="text-[10px] text-green-600 mt-4 font-medium">Personil Siap</p>
        </div>
    </div>

    {{-- SECTION GRAFIK --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Grafik Line: Tren Tiket --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800"><i class="fa-solid fa-chart-line text-blue-600 mr-2"></i>Tren Kerusakan (7 Hari)</h3>
            </div>
            <div class="h-64">
                <canvas id="ticketTrendChart"></canvas>
            </div>
        </div>

        {{-- Grafik Donut: Komposisi Status --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-chart-pie text-purple-600 mr-2"></i>Status Tiket</h3>
            <div class="h-48 relative flex justify-center">
                <canvas id="ticketStatusChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                <div>
                    <span class="block font-bold text-gray-800">{{ $ticketStatus['open'] }}</span>
                    <span class="text-gray-500">Open</span>
                </div>
                <div>
                    <span class="block font-bold text-gray-800">{{ $ticketStatus['pending'] }}</span>
                    <span class="text-gray-500">Pending</span>
                </div>
                <div>
                    <span class="block font-bold text-gray-800">{{ $ticketStatus['completed'] }}</span>
                    <span class="text-gray-500">Selesai</span>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION BAWAH: Aset Bermasalah & Aktivitas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ASET PALING BERMASALAH --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 mr-2"></i> Top 5 Aset Bermasalah
                </h3>
                <span class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-500">Bulan Ini</span>
            </div>

            <div class="space-y-5">
                @forelse($problematicAssets as $index => $item)
                    @php
                        // Hitung persentase bar visual (Max 100% berdasarkan item pertama yg paling banyak)
                        $maxTotal = $problematicAssets->first()->total;
                        $percent = ($item->total / $maxTotal) * 100;

                        // Warna bar gradasi berdasarkan urutan
                        $barColor = match($index) {
                            0 => 'bg-red-500',
                            1 => 'bg-red-400',
                            2 => 'bg-orange-400',
                            default => 'bg-gray-400'
                        };
                    @endphp

                    <div class="relative group">
                        <div class="flex justify-between items-end mb-1">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-5 h-5 rounded bg-gray-100 text-[10px] font-bold text-gray-500">
                                    #{{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition">{{ $item->asset->name }}</p>
                                    <p class="text-[10px] text-gray-500"><i class="fa-solid fa-location-dot"></i> {{ $item->asset->location->name ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-gray-800">{{ $item->total }}</span>
                                <span class="text-[10px] text-gray-500 ml-1">Tiket</span>
                            </div>
                        </div>
                        {{-- Bar Visual --}}
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all duration-1000" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <i class="fa-regular fa-face-smile text-3xl mb-2"></i>
                        <p class="text-sm">Tidak ada aset bermasalah bulan ini.</p>
                    </div>
                @endforelse
            </div>

            @if($problematicAssets->count() > 0)
                <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                    <a href="{{ route('admin.work-orders.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800">Analisa Lebih Lanjut &rarr;</a>
                </div>
            @endif
        </div>

        {{-- AKTIVITAS TERBARU (FEED) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full">
            <div class="p-5 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">
                    <i class="fa-solid fa-clock-rotate-left text-gray-600 mr-2"></i> Aktivitas Tiket Terbaru
                </h3>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4 max-h-[400px]">
                @forelse($recentActivities as $activity)
                    @php
                        $statusColor = match($activity->status) {
                            'open' => 'bg-gray-100 text-gray-600 border-gray-200',
                            'in_progress' => 'bg-blue-50 text-blue-600 border-blue-200',
                            'handover' => 'bg-orange-50 text-orange-600 border-orange-200',
                            'completed' => 'bg-green-50 text-green-600 border-green-200',
                            'verified' => 'bg-green-100 text-green-700 border-green-300',
                            default => 'bg-gray-50 text-gray-500'
                        };
                    @endphp

                    <div class="relative pl-4 border-l-2 border-gray-100 pb-2">
                        <div class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full bg-blue-400 border-2 border-white ring-1 ring-gray-100"></div>

                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-[10px] text-gray-400 font-mono mb-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                <p class="text-sm font-bold text-gray-800">{{ $activity->ticket_number }}</p>
                            </div>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border {{ $statusColor }} uppercase">{{ $activity->status }}</span>
                        </div>

                        <p class="text-xs text-gray-600 mt-1 line-clamp-1">
                            <span class="font-bold">{{ $activity->technician->name ?? 'System' }}:</span>
                            "{{ $activity->issue_description }}"
                        </p>
                    </div>
                @empty
                    <p class="text-center text-gray-400 text-sm py-4">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- SCRIPT CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 1. CHART LINE (TREN TIKET)
        const ctxTrend = document.getElementById('ticketTrendChart').getContext('2d');
        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartDates) !!}, // Data dari Controller
                datasets: [{
                    label: 'Tiket Masuk',
                    data: {!! json_encode($chartValues) !!}, // Data dari Controller
                    borderColor: '#2563eb', // Blue-600
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    tension: 0.4, // Garis melengkung
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. CHART DOUGHNUT (STATUS)
        const ctxStatus = document.getElementById('ticketStatusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Open/Proses', 'Pending', 'Selesai'],
                datasets: [{
                    data: [
                        {{ $ticketStatus['open'] }},
                        {{ $ticketStatus['pending'] }},
                        {{ $ticketStatus['completed'] }}
                    ],
                    backgroundColor: [
                        '#ef4444', // Red (Open)
                        '#f97316', // Orange (Pending)
                        '#22c55e'  // Green (Completed)
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%', // Lubang tengah
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
@endsection
