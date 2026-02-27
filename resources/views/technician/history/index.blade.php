@extends('layouts.technician')

@section('title', 'Riwayat Kerja')

@section('content')
<div x-data="{ 
    tab: 'patrol', 
    showPatrolModal: false, 
    selectedPatrol: null,
    showWorkOrderModal: false, 
    selectedWorkOrder: null
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
        <div class="space-y-8 relative border-l-2 border-gray-200 ml-3 pl-6 pb-10 min-h-[300px]">
            @forelse($groupedPatrols as $date => $items)
                @php
                    $dateObj = \Carbon\Carbon::parse($date);
                    $label = $dateObj->isToday() ? "Hari Ini (" . $dateObj->format('d M') . ")" : $dateObj->translatedFormat('l, d F Y');
                @endphp

                <div class="relative animate-fade-in-up">
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
                                'checklist' => $item->checklistTemplate->items->map(function($q) use ($answers) {
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
                                })->values()->toArray() // Reset keys for JSON
                            ];
                        @endphp

                        <div class="bg-white p-3.5 rounded-xl shadow-sm border border-gray-100 mb-3 relative overflow-hidden group hover:border-blue-200 transition">
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
        <div class="space-y-8 relative border-l-2 border-gray-200 ml-3 pl-6 pb-10 min-h-[300px]">
             @forelse($groupedWorkOrders as $date => $items)
                <div class="relative animate-fade-in-up">
                    <span class="absolute -left-[33px] top-0 bg-white text-blue-600 border-blue-200 w-9 h-9 rounded-full flex items-center justify-center border-2 shadow-sm text-[10px] font-bold z-10">
                        {{ \Carbon\Carbon::parse($date)->format('d') }}
                    </span>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 mt-2">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</h3>

                     @foreach($items as $item)
                        @php
                            // Prepare data for Alpine (Evidence/Handover)
                            $handoverHistory = $item->histories->where('user_id', auth()->id())->where('action', 'handover')->first();
                            $woData = [
                                'ticket_number' => $item->ticket_number,
                                'asset_name' => $item->asset->name,
                                'location_name' => $item->asset->location->name,
                                'status' => $item->status,
                                'issue_description' => $item->issue_description,
                                'completed_date' => $item->updated_at->format('d M Y H:i'),
                                // Data Completed
                                'photo' => $item->last_progress_photo ? asset('storage/' . $item->last_progress_photo) : null,
                                // Data Handover
                                'handover_note' => $handoverHistory ? $handoverHistory->description : 'Tidak ada catatan handover.',
                            ];
                        @endphp

                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-3 relative overflow-hidden group hover:border-blue-200 transition">
                            <div class="flex justify-between items-start mb-2">
                                <span class="bg-gray-100 text-gray-600 text-[10px] font-mono px-2 py-0.5 rounded">#{{ $item->ticket_number }}</span>
                                
                                @if($item->status == 'completed')
                                    <span class="bg-green-100 text-green-600 text-[10px] px-2 py-0.5 rounded font-bold uppercase flex items-center gap-1">
                                        <i class="fa-solid fa-check-circle"></i> Selesai
                                    </span>
                                @elseif($item->status == 'handover')
                                    <span class="bg-yellow-100 text-yellow-600 text-[10px] px-2 py-0.5 rounded font-bold uppercase flex items-center gap-1">
                                        <i class="fa-solid fa-arrow-right-arrow-left"></i> Handover
                                    </span>
                                @endif
                            </div>

                            <h4 class="font-bold text-gray-800 text-sm mb-1 leading-snug">{{ $item->issue_description }}</h4>
                            <p class="text-xs text-gray-500 mb-3">
                                <i class="fa-solid fa-cube mr-1"></i> {{ $item->asset->name }} ({{ $item->asset->location->name }})
                            </p>

                            {{-- BUTTON DETAIL UNIVERSAL --}}
                            <button 
                                @click="selectedWorkOrder = {{ json_encode($woData) }}; showWorkOrderModal = true"
                                class="w-full py-2 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold hover:bg-blue-100 transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-eye"></i> Lihat Detail
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
        
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showPatrolModal = false"></div>
        
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
        
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="showWorkOrderModal = false"></div>
        
        <div class="bg-white w-full max-w-xs sm:max-w-sm rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] relative z-10 transform transition-all">
            {{-- Header --}}
            <div class="relative bg-gray-100">
                {{-- Toggle Image/Placeholder --}}
                <template x-if="selectedWorkOrder?.status === 'completed' && selectedWorkOrder?.photo">
                    <img :src="selectedWorkOrder?.photo" class="w-full h-56 object-cover">
                </template>
                <template x-if="selectedWorkOrder?.status === 'completed' && !selectedWorkOrder?.photo">
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

                <button @click="showWorkOrderModal = false" class="absolute top-2 right-2 w-8 h-8 bg-black/50 text-white rounded-full flex items-center justify-center hover:bg-black/70 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-5 overflow-y-auto">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-gray-800 text-lg leading-tight" x-text="selectedWorkOrder?.issue_description"></h3>
                </div>
                <p class="text-xs text-gray-400 mb-4" x-text="selectedWorkOrder?.completed_date"></p>
                
                {{-- Info Asset --}}
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-4 pb-4 border-b border-gray-100">
                    <span class="bg-gray-100 px-2 py-1 rounded font-mono" x-text="'#' + selectedWorkOrder?.ticket_number"></span>
                    <span x-text="selectedWorkOrder?.asset_name"></span>
                </div>

                {{-- Content based on Status --}}
                <template x-if="selectedWorkOrder?.status === 'completed'">
                    <div class="bg-green-50 p-4 rounded-xl border border-green-100">
                        <p class="text-xs font-bold text-green-700 mb-1">Catatan Penyelesaian:</p>
                        <p class="text-sm text-green-900 leading-relaxed" x-text="selectedWorkOrder?.issue_description"></p>
                    </div>
                </template>

                <template x-if="selectedWorkOrder?.status === 'handover'">
                    <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100">
                        <p class="text-xs font-bold text-yellow-700 mb-1">Alasan Handover:</p>
                        <p class="text-sm text-yellow-900 leading-relaxed" x-text="selectedWorkOrder?.handover_note"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
