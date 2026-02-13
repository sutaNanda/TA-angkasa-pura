@extends('layouts.admin')

@section('title', 'Jadwal Preventive Maintenance')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Jadwal Preventive Maintenance</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola jadwal inspeksi harian, mingguan, dan bulanan</p>
        </div>
        <div class="flex gap-2">
            <button onclick="generateTasksNow()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                <i class="fa-solid fa-bolt"></i> Generate Tasks Sekarang
            </button>
            <a href="{{ route('admin.schedules.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                <i class="fa-solid fa-plus"></i> Tambah Jadwal
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Total Jadwal</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
                </div>
                <i class="fa-solid fa-calendar-days text-3xl text-gray-300"></i>
            </div>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg shadow-sm border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-green-700 uppercase font-bold">Aktif</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $stats['active'] }}</p>
                </div>
                <i class="fa-solid fa-check-circle text-3xl text-green-300"></i>
            </div>
        </div>
        
        <div class="bg-blue-50 p-4 rounded-lg shadow-sm border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-blue-700 uppercase font-bold">Harian</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $stats['daily'] }}</p>
                </div>
                <i class="fa-solid fa-sun text-3xl text-blue-300"></i>
            </div>
        </div>
        
        <div class="bg-purple-50 p-4 rounded-lg shadow-sm border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-purple-700 uppercase font-bold">Mingguan</p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $stats['weekly'] }}</p>
                </div>
                <i class="fa-solid fa-calendar-week text-3xl text-purple-300"></i>
            </div>
        </div>
        
        <div class="bg-orange-50 p-4 rounded-lg shadow-sm border border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-orange-700 uppercase font-bold">Bulanan</p>
                    <p class="text-2xl font-bold text-orange-700 mt-1">{{ $stats['monthly'] }}</p>
                </div>
                <i class="fa-solid fa-calendar-alt text-3xl text-orange-300"></i>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Aset</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Template Checklist</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Frekuensi</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Jadwal</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($schedules as $schedule)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-cube text-gray-400"></i>
                                <div>
                                    <p class="font-bold text-sm text-gray-800">{{ $schedule->asset->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $schedule->asset->serial_number ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $schedule->asset->category->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                            {{ $schedule->checklistTemplate->name }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $badgeColors = [
                                    'daily' => 'bg-blue-100 text-blue-700',
                                    'weekly' => 'bg-purple-100 text-purple-700',
                                    'monthly' => 'bg-orange-100 text-orange-700',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $badgeColors[$schedule->frequency] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ strtoupper($schedule->frequency) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $schedule->schedule_description }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="toggleActive({{ $schedule->id }}, {{ $schedule->is_active ? 'true' : 'false' }})" 
                                    class="px-3 py-1 rounded-full text-xs font-bold transition {{ $schedule->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                {{ $schedule->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.schedules.edit', $schedule->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <button onclick="deleteSchedule({{ $schedule->id }})" 
                                        class="text-red-600 hover:text-red-800 transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <i class="fa-solid fa-calendar-xmark text-4xl mb-2 text-gray-300"></i>
                            <p>Belum ada jadwal maintenance. <a href="{{ route('admin.schedules.create') }}" class="text-blue-600 hover:underline">Tambah jadwal baru</a></p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($schedules->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function toggleActive(id, currentStatus) {
    fetch(`/admin/schedules/${id}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => alert('Gagal mengubah status'));
}

function deleteSchedule(id) {
    if (!confirm('Yakin ingin menghapus jadwal ini?')) return;
    
    fetch(`/admin/schedules/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(() => location.reload())
    .catch(err => alert('Gagal menghapus jadwal'));
}

function generateTasksNow() {
    if (!confirm('Generate tasks untuk hari ini sekarang?')) return;
    
    fetch('/admin/schedules/generate-now', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(() => {
        alert('Tasks berhasil di-generate!');
        location.reload();
    })
    .catch(err => alert('Gagal generate tasks'));
}
</script>
@endsection
