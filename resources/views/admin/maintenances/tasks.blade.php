@extends('layouts.admin')

@section('title', 'Jadwal Monitoring')
@section('page-title', 'Daftar Tugas Monitoring & Maintenance')

@section('content')
    {{-- FILTER SECTION --}}
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" action="{{ route('admin.maintenances.tasks') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-auto">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>
            <div class="w-full md:w-auto">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>

            <div class="w-full md:w-64">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Cari Lokasi / Aset</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
                </div>
            </div>

            <div class="flex gap-2 w-full md:w-auto ml-auto">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.maintenances.tasks') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TABLE TASKS --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs">
                <tr>
                    <th class="px-6 py-4 w-12 text-center">No</th>
                    <th class="px-6 py-4">Rencana Pelaksanaan</th>
                    <th class="px-6 py-4">Target Lokasi / Aset</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Teknisi</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-center font-bold text-gray-400 text-xs">
                            {{ ($tasks->currentPage() - 1) * $tasks->perPage() + $loop->iteration }}.
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800 text-xs">
                                <i class="fa-regular fa-calendar-days mr-1 text-blue-500"></i> 
                                {{ $task->scheduled_date->translatedFormat('d F Y') }}
                            </div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase font-mono">ID: TASK-{{ $task->id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($task->location)
                                <div class="font-bold text-blue-700 text-xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-layer-group"></i> {{ $task->location->name }}
                                </div>
                            @elseif($task->asset)
                                <div class="font-bold text-gray-800 text-xs">{{ $task->asset->name }}</div>
                            @else
                                <span class="text-gray-400 italic">Target tidak teridentifikasi</span>
                            @endif
                            <div class="text-[10px] text-gray-500 mt-1">Rule: {{ $task->maintenancePlan->name ?? 'Manual' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    'in_progress' => 'bg-blue-50 text-blue-600 border-blue-100 animate-pulse',
                                    'completed' => 'bg-green-50 text-green-600 border-green-100',
                                ];
                                $statusLabel = [
                                    'pending' => 'Belum Mulai',
                                    'in_progress' => 'Sedang Jalan',
                                    'completed' => 'Selesai',
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 {{ $statusClasses[$task->status] ?? 'bg-gray-50 text-gray-400' }} px-3 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider">
                                {{ $statusLabel[$task->status] ?? $task->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($task->technician)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold text-blue-600">
                                        {{ substr($task->technician->name, 0, 1) }}
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">{{ $task->technician->name }}</span>
                                </div>
                            @else
                                <span class="text-[10px] text-gray-400 italic tracking-wide">Belum Ada Klaim</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($task->status !== 'completed')
                                <button onclick="openRescheduleModal({{ json_encode(['id' => $task->id, 'current_date' => $task->scheduled_date->format('Y-m-d'), 'target' => $task->location->name ?? $task->asset->name ?? 'Unknown']) }})" 
                                        class="bg-white border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all shadow-sm flex items-center gap-1 mx-auto">
                                    <i class="fa-solid fa-clock-rotate-left"></i> Reschedule
                                </button>
                            @else
                                <span class="text-[10px] text-gray-400 font-bold uppercase"><i class="fa-solid fa-check-double mr-1"></i> Terlaksana</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <i class="fa-solid fa-calendar-xmark text-3xl mb-3 block opacity-20"></i>
                            <p class="text-sm">Tidak ada tugas yang ditemukan.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $tasks->withQueryString()->links() }}
    </div>

    {{-- MODAL RESCHEDULE --}}
    <div id="rescheduleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeRescheduleModal()"></div>

            <div class="relative inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-md border border-slate-200">
                <form id="rescheduleForm" method="POST" action="">
                    @csrf
                    <div class="bg-white px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800">Geser Jadwal Monitoring</h3>
                        <p class="text-xs text-slate-400 mt-1" id="rescheduleTargetInfo">Target: -</p>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tanggal Baru Pelaksanaan</label>
                            <input type="date" name="scheduled_date" id="rescheduleDateInput" required min="{{ date('Y-m-d') }}" 
                                   class="w-full bg-slate-50 border-slate-200 rounded-xl focus:ring-blue-500 text-sm font-bold p-3">
                            <p class="text-[10px] text-slate-400 mt-2 font-medium italic">Catatan: Tugas akan muncul di dashboard teknisi sesuai tanggal yang baru.</p>
                        </div>
                    </div>

                    <div class="bg-white px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
                        <button type="button" onclick="closeRescheduleModal()" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">Batal</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/30 transition transform active:scale-95">
                            Simpan Jadwal Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openRescheduleModal(data) {
            const modal = document.getElementById('rescheduleModal');
            const info = document.getElementById('rescheduleTargetInfo');
            const input = document.getElementById('rescheduleDateInput');
            const form = document.getElementById('rescheduleForm');

            info.innerText = 'Reschedule Tugas: ' + data.target + ' (ID: #' + data.id + ')';
            input.value = data.current_date;
            form.action = `/admin/maintenance-tasks/${data.id}/reschedule`;

            modal.classList.remove('hidden');
        }

        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').classList.add('hidden');
        }
    </script>
@endsection
