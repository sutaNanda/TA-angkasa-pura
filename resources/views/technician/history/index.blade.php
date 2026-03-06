@extends('layouts.technician')

@section('title', 'Riwayat Kerja')

@section('content')
<div x-data="{ 
    tab: 'patrol', 
    showPatrolModal: false, 
    selectedPatrol: null,
    showWorkOrderModal: false, 
    selectedWorkOrder: null,
    filterPatrol: 'all',
    filterWorkOrder: 'all'
}">

    {{-- FILTER BULAN & TAHUN --}}
    <form action="{{ route('technician.history.index') }}" method="GET" class="flex justify-between items-center mb-6 bg-white p-3 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <span class="text-xs font-bold text-gray-500">Periode</span>
        </div>
        <div class="flex gap-2">
            {{-- Select Bulan --}}
            <div class="relative">
                <select name="month" onchange="this.form.submit()" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 py-1.5 px-3 pr-8 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </div>
            </div>
            
            {{-- Select Tahun --}}
            <div class="relative">
                <select name="year" onchange="this.form.submit()" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 py-1.5 px-3 pr-8 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    @for ($y = now()->year; $y >= now()->year - 2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </div>
            </div>
        </div>
    </form>

    {{-- STATISTIK RINGKAS --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-white p-3 rounded-xl shadow-sm border-b-4 border-blue-500 text-center">
            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Total Scan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total_scan'] }}</p>
        </div>
        <div class="bg-white p-3 rounded-xl shadow-sm border-b-4 border-green-500 text-center">
            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Perbaikan Selesai</p>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total_fix'] }}</p>
        </div>
    </div>

    {{-- TABS --}}
    <div class="flex p-1 bg-gray-100 rounded-xl mb-6">
        <button 
            @click="tab = 'patrol'" 
            :class="tab === 'patrol' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 py-2 text-sm font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
            <i class="fa-solid fa-qrcode"></i> Riwayat Patroli
        </button>
        <button 
            @click="tab = 'work_order'" 
            :class="tab === 'work_order' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
            class="flex-1 py-2 text-sm font-bold rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
            <i class="fa-solid fa-screwdriver-wrench"></i> Riwayat Perbaikan
        </button>
    </div>

    {{-- KONTEN TAB: PATROLI --}}
    <div x-show="tab === 'patrol'" x-transition:enter="transition ease-out duration-300" style="display: none;">
        
        {{-- Filter Status Patroli --}}
        <div class="flex gap-2 overflow-x-auto pb-4 mb-2 no-scrollbar px-1">
            <button @click="filterPatrol = 'all'" 
                    :class="filterPatrol === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-200'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors shadow-sm">
                Semua Status
            </button>
            <button @click="filterPatrol = 'normal'" 
                    :class="filterPatrol === 'normal' ? 'bg-green-600 text-white border-transparent' : 'bg-white text-green-600 hover:bg-green-50 border border-green-200'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-check-circle"></i> Normal
            </button>
            <button @click="filterPatrol = 'issue'" 
                    :class="filterPatrol === 'issue' ? 'bg-red-500 text-white border-transparent' : 'bg-white text-red-500 hover:bg-red-50 border border-red-200'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-triangle-exclamation"></i> Issue
            </button>
        </div>

        <div class="space-y-8 relative border-l-2 border-gray-200 ml-3 pl-6 pb-10 min-h-[300px]">
            @php $hasVisiblePatrols = false; @endphp
            @forelse($groupedPatrols as $date => $items)
                @php
                    $dateObj = \Carbon\Carbon::parse($date);
                    $label = $dateObj->isToday() ? "Hari Ini (" . $dateObj->format('d M') . ")" : $dateObj->translatedFormat('l, d F Y');
                @endphp

                <div class="relative animate-fade-in-up date-group-patrol" x-data="{ visibleCount: {{ count($items) }} }" x-show="visibleCount > 0">
                    <span class="absolute -left-[33px] top-0 {{ $dateObj->isToday() ? 'bg-blue-600 text-white border-blue-200' : 'bg-white text-gray-500 border-gray-200' }} w-9 h-9 rounded-full flex items-center justify-center border-2 shadow-sm text-[10px] font-bold z-10">
                        {{ $dateObj->format('d') }}
                    </span>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 mt-2">{{ $label }}</h3>

                    @foreach($items as $item)
                        @php
                            $isIssue = $item->status == 'issue_found';
                            
                            // Parse Inspection Data (handle double encoding if necessary)
                            $answers = $item->inspection_data;
                            if (is_string($answers)) {
                                $answers = json_decode($answers, true);
                            }

                            // Prepare data for Alpine (Checklist)
                            $patrolData = [
                                'asset_name' => $item->asset->name,
                                'location_name' => $item->asset->location->name,
                                'time' => $item->created_at->format('H:i'),
                                'status' => $item->status,
                                'notes' => $item->notes ?? '-', // Handle notes if available
                                'checklist' => ($item->checklistTemplate && $item->checklistTemplate->items) ? $item->checklistTemplate->items->map(function($q) use ($answers) {
                                    $ans = $answers[$q->id] ?? '-';
                                    
                                    // Determine display text for Pass/Fail
                                    $displayText = $ans;
                                    $isIssueItem = false;

                                    if ($q->type == 'pass_fail') {
                                        if ($ans == 'pass') {
                                            $displayText = 'Normal / OK';
                                        } elseif ($ans == 'fail') {
                                            $displayText = 'Ada Masalah';
                                            $isIssueItem = true;
                                        }
                                    } elseif ($q->type == 'numeric' && $q->unit) {
                                        $displayText .= ' ' . $q->unit;
                                    }

                                    return [
                                        'question' => $q->question,
                                        'answer' => $displayText,
                                        'is_issue' => $isIssueItem
                                    ];
                                })->values()->toArray() : [], // Reset keys and fallback to empty
                            ];
                        @endphp
                        @php
                            $isVisibleCondition = "filterPatrol === 'all' || (filterPatrol === 'normal' && " . ($isIssue ? 'false' : 'true') . ") || (filterPatrol === 'issue' && " . ($isIssue ? 'true' : 'false') . ")";
                        @endphp

                        <div x-show="{{ $isVisibleCondition }}" 
                             x-effect="if({{ $isVisibleCondition }}) { visibleCount++ } else { visibleCount-- }"
                             x-init="$watch('filterPatrol', value => { 
                                 // Reset the count mechanism to re-evaluate 
                                 visibleCount = 0; 
                                 setTimeout(() => { if({{ $isVisibleCondition }}) visibleCount++; }, 10);
                             })"
                             class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 mb-3 relative overflow-hidden group hover:border-blue-200 transition filter-item-patrol">
                            <div class="absolute left-0 top-0 bottom-0 w-1 {{ $isIssue ? 'bg-red-500' : 'bg-green-500' }}"></div>
                            
                            <div class="flex justify-between items-start mb-2">
                                <span class="{{ $isIssue ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }} text-[10px] px-2 py-0.5 rounded font-bold uppercase tracking-wide flex items-center gap-1">
                                    <i class="fa-solid {{ $isIssue ? 'fa-triangle-exclamation' : 'fa-check-circle' }}"></i> {{ $isIssue ? 'Issue' : 'Normal' }}
                                </span>
                                <button @click="selectedPatrol = {{ json_encode($patrolData) }}; showPatrolModal = true" class="text-gray-400 hover:text-blue-600 transition">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>

                            <h4 class="font-bold text-gray-800 text-sm line-clamp-1">{{ $item->asset->name }}</h4>
                            <p class="text-xs text-gray-500 mb-1">
                                <i class="fa-solid fa-location-dot mr-1"></i> {{ $item->asset->location->name }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="flex flex-col items-center justify-center pt-10 text-gray-400">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-qrcode text-2xl"></i>
                    </div>
                    <p class="text-sm font-bold">Belum ada scan patroli.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- KONTEN TAB: PERBAIKAN (WORK ORDER) --}}
    <div x-show="tab === 'work_order'" x-transition:enter="transition ease-out duration-300" style="display: none;">
        
        {{-- Filter Status Perbaikan --}}
        <div class="flex gap-2 overflow-x-auto pb-4 mb-2 no-scrollbar px-1">
            <button @click="filterWorkOrder = 'all'" 
                    :class="filterWorkOrder === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-200'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors shadow-sm">
                Semua Status
            </button>
            <button @click="filterWorkOrder = 'completed'" 
                    :class="filterWorkOrder === 'completed' ? 'bg-yellow-500 text-white border-transparent' : 'bg-white text-yellow-600 hover:bg-yellow-50 border border-yellow-200'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-clock-rotate-left"></i> Menunggu Verif
            </button>
            <button @click="filterWorkOrder = 'verified'" 
                    :class="filterWorkOrder === 'verified' ? 'bg-green-600 text-white border-transparent' : 'bg-white text-green-600 hover:bg-green-50 border border-green-200'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-check-double"></i> Verified
            </button>
            <button @click="filterWorkOrder = 'handover'" 
                    :class="filterWorkOrder === 'handover' ? 'bg-pink-500 text-white border-transparent' : 'bg-white text-pink-600 hover:bg-pink-50 border border-pink-200'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-handshake"></i> Handover
            </button>
        </div>

        <div class="space-y-8 relative border-l-2 border-gray-200 ml-3 pl-6 pb-10 min-h-[300px]">
             @forelse($groupedWorkOrders as $date => $items)
                <div class="relative animate-fade-in-up date-group-wo" x-data="{ visibleCountWo: {{ count($items) }} }" x-show="visibleCountWo > 0">
                    <span class="absolute -left-[33px] top-0 bg-white text-blue-600 border-blue-200 w-9 h-9 rounded-full flex items-center justify-center border-2 shadow-sm text-[10px] font-bold z-10">
                        {{ \Carbon\Carbon::parse($date)->format('d') }}
                    </span>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 mt-2">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</h3>

                     @foreach($items as $item)
                        @php
                            // Prepare data for Alpine (Evidence/Handover)
                            $handoverHistory = $item->histories->where('user_id', auth()->id())->where('action', 'handover')->first();
                            $completedHistory = $item->histories->where('action', 'completed')->last();
                            $woData = [
                                'ticket_number' => $item->ticket_number,
                                'asset_name' => $item->asset ? $item->asset->name : ($item->location ? $item->location->name : 'Tidak diketahui'),
                                'location_name' => $item->asset ? ($item->asset->location->name ?? '-') : ($item->location ? $item->location->name : '-'),
                                'status' => $item->status,
                                'issue_description' => $item->issue_description,
                                'completed_date' => $item->updated_at->format('d M Y, H:i'),
                                // Data Completed
                                'photo' => $item->last_progress_photo ? asset('storage/' . $item->last_progress_photo) : null,
                                'completed_note' => $completedHistory ? $completedHistory->description : 'Perbaikan telah diselesaikan oleh teknisi.',
                                'completed_photos_urls' => ($completedHistory && is_array($completedHistory->photos)) ? array_map(fn($p) => asset('storage/' . $p), $completedHistory->photos) : [],
                                // Data Handover
                                'handover_note' => $handoverHistory ? $handoverHistory->description : 'Tidak ada catatan handover.',
                                'handover_photo' => ($handoverHistory && $handoverHistory->photo) ? asset('storage/' . $handoverHistory->photo) : null,
                                'handover_photos_urls' => ($handoverHistory && is_array($handoverHistory->photos)) ? array_map(fn($p) => asset('storage/' . $p), $handoverHistory->photos) : [],
                            ];

                            // Status Logic for UI
                            $statusColor = 'bg-gray-100 text-gray-600 border-gray-200';
                            $statusIcon = 'fa-file';
                            $statusText = 'History';
                            $borderColor = 'border-l-gray-300';

                            if ($item->status == 'completed') {
                                $statusColor = 'bg-yellow-50 text-yellow-700 border-yellow-200';
                                $statusIcon = 'fa-clock-rotate-left';
                                $statusText = 'Menunggu Verif';
                                $borderColor = 'border-l-yellow-400';
                            } elseif ($item->status == 'verified') {
                                $statusColor = 'bg-green-50 text-green-700 border-green-200';
                                $statusIcon = 'fa-check-double';
                                $statusText = 'Verified';
                                $borderColor = 'border-l-green-500';
                            } elseif ($item->status == 'handover') {
                                $statusColor = 'bg-pink-50 text-pink-700 border-pink-200';
                                $statusIcon = 'fa-handshake';
                                $statusText = 'Handover';
                                $borderColor = 'border-l-pink-500';
                            }

                            $isWoVisibleCondition = "filterWorkOrder === 'all' || filterWorkOrder === '{$item->status}'";
                        @endphp

                        <div x-show="{{ $isWoVisibleCondition }}"
                             x-init="$watch('filterWorkOrder', value => { 
                                 visibleCountWo = 0; 
                                 setTimeout(() => { if({{ $isWoVisibleCondition }}) visibleCountWo++; }, 10);
                             })"
                             class="bg-white p-4 mb-4 rounded-xl shadow-sm border border-gray-100 {{ $borderColor }} border-l-4 relative group hover:shadow-md transition-all duration-200 filter-item-wo">
                            
                            {{-- Header Card --}}
                            <div class="flex justify-between items-center mb-3">
                                <span class="bg-gray-50 text-gray-600 text-[10px] font-mono px-2 py-1 rounded border border-gray-200 font-bold tracking-wider">
                                    <i class="fa-solid fa-hashtag text-gray-400"></i> {{ $item->ticket_number }}
                                </span>
                                <span class="{{ $statusColor }} text-[10px] px-2.5 py-1 rounded-full font-bold uppercase flex items-center gap-1.5 shadow-sm border">
                                    <i class="fa-solid {{ $statusIcon }}"></i> {{ $statusText }}
                                </span>
                            </div>

                            {{-- Issue / Title --}}
                            <div class="mb-3">
                                <h4 class="font-bold text-gray-800 text-sm mb-2 leading-snug">{{ \Illuminate\Support\Str::limit($item->issue_description, 70) }}</h4>
                                
                                <div class="flex flex-col gap-1.5 text-xs text-gray-500 bg-gray-50 p-2.5 rounded-lg border border-gray-100">
                                    @if($item->asset)
                                        <div class="flex items-center gap-2">
                                            <div class="w-4 flex justify-center"><i class="fa-solid fa-cube text-blue-500"></i></div>
                                            <span class="font-semibold text-gray-700">{{ $item->asset->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-4 flex justify-center"><i class="fa-solid fa-location-dot text-red-500"></i></div>
                                            <span>{{ $item->asset->location->name ?? '-' }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <div class="w-4 flex justify-center"><i class="fa-solid fa-location-dot text-red-500"></i></div>
                                            <span class="font-semibold text-gray-700">{{ $item->location->name ?? 'Lokasi tidak diketahui' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-4 flex justify-center"><i class="fa-solid fa-cube text-orange-500"></i></div>
                                            <span class="text-orange-500 italic">Aset belum pasti</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-2 mt-1 pt-1.5 border-t border-gray-200">
                                        <div class="w-4 flex justify-center"><i class="fa-regular fa-clock text-gray-400"></i></div>
                                        <span>Update: <span class="text-gray-700 font-medium">{{ $item->updated_at->format('d M Y, H:i') }}</span></span>
                                    </div>
                                </div>
                            </div>

                            {{-- BUTTON DETAIL UNIVERSAL --}}
                            <button 
                                @click="selectedWorkOrder = {{ json_encode($woData) }}; showWorkOrderModal = true"
                                class="w-full py-2.5 bg-white hover:bg-blue-50 text-gray-600 hover:text-blue-700 border border-gray-200 hover:border-blue-300 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-sm">
                                <i class="fa-solid fa-eye"></i> Lihat Detail Laporan
                            </button>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="flex flex-col items-center justify-center pt-10 text-gray-400">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <i class="fa-solid fa-screwdriver-wrench text-2xl"></i>
                    </div>
                    <p class="text-sm font-bold">Belum ada riwayat perbaikan.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL 1: DETAIL PATROLI --}}
    <div x-show="showPatrolModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
        
        <div class="absolute inset-0 bg-black/50" @click="showPatrolModal = false"></div>
        
        <div class="bg-white w-full max-w-xs sm:max-w-sm rounded-2xl shadow-xl overflow-hidden max-h-[85vh] flex flex-col relative z-10 transform transition-all">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800">Detail Pengecekan</h3>
                <button @click="showPatrolModal = false" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="p-4 overflow-y-auto" x-if="selectedPatrol">
                <div class="mb-4">
                    <h4 class="font-bold text-gray-800 text-lg" x-text="selectedPatrol?.asset_name"></h4>
                    <p class="text-xs text-gray-500"><i class="fa-solid fa-location-dot"></i> <span x-text="selectedPatrol?.location_name"></span> &bull; <span x-text="selectedPatrol?.time"></span></p>
                </div>

                {{-- Checklist Table --}}
                <div class="border rounded-lg overflow-hidden">
                    <template x-for="(item, index) in selectedPatrol?.checklist" :key="index">
                        <div class="flex justify-between items-center p-3 border-b last:border-0 hover:bg-gray-50">
                            <div class="flex-1 pr-2">
                                <p class="text-xs font-semibold text-gray-700" x-text="item.question"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold" 
                                      :class="item.is_issue ? 'text-red-600' : 'text-green-600'" 
                                      x-text="item.answer"></span>
                                <i class="fa-solid" :class="item.is_issue ? 'fa-triangle-exclamation text-red-500' : 'fa-check text-green-500'"></i>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div x-show="selectedPatrol?.notes && selectedPatrol?.notes !== '-'" class="mt-4 bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                    <p class="text-xs font-bold text-yellow-700">Catatan Tambahan:</p>
                    <p class="text-sm text-yellow-800" x-text="selectedPatrol?.notes"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL 2: DETAIL PERBAIKAN / HANDOVER --}}
    <div x-show="showWorkOrderModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
        
        <div class="absolute inset-0 bg-black/50" @click="showWorkOrderModal = false"></div>
        
        <div class="bg-white w-full max-w-xs sm:max-w-sm rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] relative z-10 transform transition-all">
            {{-- Header --}}
            <div class="relative bg-gray-100">
                {{-- Toggle Image/Placeholder --}}
                <template x-if="['completed', 'verified'].includes(selectedWorkOrder?.status) && selectedWorkOrder?.photo">
                    <img :src="selectedWorkOrder?.photo" class="w-full h-56 object-cover">
                </template>
                <template x-if="['completed', 'verified'].includes(selectedWorkOrder?.status) && !selectedWorkOrder?.photo">
                    <div class="w-full h-40 bg-gray-200 flex flex-col items-center justify-center text-gray-400">
                        <i class="fa-solid fa-image-slash text-3xl mb-2"></i>
                        <span class="text-xs font-bold">Tidak ada foto bukti</span>
                    </div>
                </template>
                <template x-if="selectedWorkOrder?.status === 'handover'">
                    <div class="w-full h-24 bg-yellow-100 flex items-center justify-center p-4">
                        <div class="text-center text-yellow-700">
                            <i class="fa-solid fa-arrow-right-arrow-left text-2xl mb-1"></i>
                            <h3 class="font-bold">Status: Handover</h3>
                        </div>
                    </div>
                </template>

                <button @click="showWorkOrderModal = false" class="absolute top-2 right-2 w-8 h-8 bg-black/50 text-white rounded-full flex items-center justify-center hover:bg-black/50 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-5 overflow-y-auto">
            <div class="p-5 overflow-y-auto">
                {{-- Header Modal Info --}}
                <div class="flex justify-between items-start mb-4 pb-4 border-b border-gray-100">
                    <div>
                        <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded font-mono text-xs font-bold border border-blue-100 mb-2 inline-block shadow-sm" x-text="'#' + selectedWorkOrder?.ticket_number"></span>
                        <h4 class="font-bold text-gray-800 text-lg leading-tight" x-text="selectedWorkOrder?.asset_name"></h4>
                        <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                            <i class="fa-solid fa-location-dot text-red-500"></i>
                            <span x-text="selectedWorkOrder?.location_name"></span>
                        </div>
                    </div>
                </div>

                {{-- Detail Masalah Awal --}}
                <div class="mb-5">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><i class="fa-solid fa-clipboard-question text-gray-400"></i> Laporan Masalah / Kendala</p>
                    <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-100 text-gray-700 text-sm font-medium leading-relaxed">
                        <span x-text="selectedWorkOrder?.issue_description"></span>
                    </div>
                </div>

                {{-- Content based on Status --}}
                <template x-if="['completed', 'verified'].includes(selectedWorkOrder?.status)">
                    <div class="mb-2">
                        <p class="text-[10px] font-bold text-green-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><i class="fa-solid fa-check-double text-green-500"></i> Catatan Teknisi Terkait Penyelesaian</p>
                        <div class="bg-green-50 p-4 rounded-xl border border-green-200 shadow-sm relative overflow-hidden">
                            <div class="absolute -right-2 -top-2 text-green-600/10"><i class="fa-solid fa-check-circle text-6xl"></i></div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-white text-green-700 text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">Updated: <span x-text="selectedWorkOrder?.completed_date"></span></span>
                            </div>
                            <p class="text-sm text-green-800 font-medium leading-relaxed drop-shadow-sm relative z-10" x-html="selectedWorkOrder?.completed_note"></p>
                            
                            {{-- Foto Bukti Perbaikan --}}
                            <template x-if="selectedWorkOrder?.completed_photos_urls?.length > 0">
                                <div class="mt-3 relative z-10">
                                    <div class="bg-green-100 text-[10px] text-center py-1 font-bold text-green-700 mb-2 rounded">Foto Bukti Perbaikan dari Teknisi</div>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(url, index) in selectedWorkOrder?.completed_photos_urls" :key="index">
                                            <div class="rounded-lg overflow-hidden border border-green-200 shadow-sm">
                                                <img :src="url" class="h-24 w-24 object-cover hover:opacity-90 transition cursor-pointer" @click="window.open(url, '_blank')">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="selectedWorkOrder?.status === 'handover'">
                    <div class="mb-2">
                        <p class="text-[10px] font-bold text-yellow-600 uppercase tracking-wider mb-1.5 flex items-center gap-1.5"><i class="fa-solid fa-handshake text-yellow-500"></i> Alasan Melakukan Handover Shift</p>
                        <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-200 shadow-sm relative overflow-hidden">
                            <div class="absolute -right-2 -top-2 text-yellow-600/10"><i class="fa-solid fa-arrow-right-arrow-left text-6xl"></i></div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-white text-yellow-700 text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">Updated: <span x-text="selectedWorkOrder?.completed_date"></span></span>
                            </div>
                            <p class="text-sm text-yellow-800 font-medium leading-relaxed drop-shadow-sm relative z-10" x-html="selectedWorkOrder?.handover_note"></p>
                            
                            {{-- Foto Bukti Handover --}}
                            <template x-if="selectedWorkOrder?.handover_photos_urls?.length > 0">
                                <div class="mt-3 relative z-10">
                                    <div class="bg-yellow-100 text-[10px] text-center py-1 font-bold text-yellow-700 mb-2 rounded">Foto Lampiran Handover</div>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="(url, index) in selectedWorkOrder?.handover_photos_urls" :key="index">
                                            <div class="rounded-lg overflow-hidden border border-yellow-200 shadow-sm">
                                                <img :src="url" class="h-24 w-24 object-cover hover:opacity-90 transition cursor-pointer" @click="window.open(url, '_blank')">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
