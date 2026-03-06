@extends('layouts.admin')

@section('title', 'Aturan Preventive Maintenance')

@section('content')
<div class="container-fluid px-4 py-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Rencana Perawatan Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola rencana perawatan otomatis berdasarkan kategori aset</p>
        </div>
        @if(!auth()->user()->isManajer())
        <div class="flex gap-2">
            <button onclick="generateTasksNow()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                <i class="fa-solid fa-bolt"></i> Generate Tasks Sekarang
            </button>
            <a href="{{ route('admin.plans.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                <i class="fa-solid fa-plus"></i> Tambah Aturan
            </a>
        </div>
        @endif
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Total Aturan</p>
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

        <div class="bg-red-50 p-4 rounded-lg shadow-sm border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-red-700 uppercase font-bold">Tahunan</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['yearly'] }}</p>
                </div>
                <i class="fa-solid fa-calendar text-3xl text-red-300"></i>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 w-12 text-center text-xs font-bold text-gray-600 uppercase">No</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Rencana / Kategori</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Template Checklist</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Frekuensi</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase">Jadwal</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Aset Terdampak</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Status</th>
                    @if(!auth()->user()->isManajer())
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-center text-xs font-bold text-gray-400">
                            {{ ($plans->currentPage() - 1) * $plans->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-gray-400"></i>
                                <div>
                                    <p class="font-bold text-sm text-gray-800">{{ $plan->name }}</p>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($plan->categories as $cat)
                                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-bold uppercase">{{ $cat->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1">
                                @foreach($plan->templates as $tpl)
                                    <span class="text-xs text-gray-700 font-medium">• {{ $tpl->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $badgeColors = [
                                    'daily' => 'bg-blue-100 text-blue-700',
                                    'weekly' => 'bg-purple-100 text-purple-700',
                                    'monthly' => 'bg-orange-100 text-orange-700',
                                    'yearly' => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $badgeColors[$plan->frequency] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ strtoupper($plan->frequency) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $plan->schedule_description }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-lg font-bold text-gray-700">{{ $plan->affected_assets_count }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(!auth()->user()->isManajer())
                            <button onclick="toggleActive({{ $plan->id }}, {{ $plan->is_active ? 'true' : 'false' }})" 
                                    class="px-3 py-1 rounded-full text-xs font-bold transition {{ $plan->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                {{ $plan->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </button>
                            @else
                            <button class="px-3 py-1 rounded-full text-xs font-bold cursor-default {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $plan->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </button>
                            @endif
                        </td>
                        @if(!auth()->user()->isManajer())
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.plans.edit', $plan->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 transition">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <button onclick="deletePlan({{ $plan->id }})" 
                                        class="text-red-600 hover:text-red-800 transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <i class="fa-solid fa-calendar-xmark text-4xl mb-2 text-gray-300"></i>
                            <p>Belum ada aturan maintenance. <a href="{{ route('admin.plans.create') }}" class="text-blue-600 hover:underline">Tambah aturan baru</a></p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($plans->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $plans->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function toggleActive(id, currentStatus) {
    // Determine new status and colors for preview
    const isActivating = !currentStatus;
    const actionText = isActivating ? 'aktifkan' : 'nonaktifkan';
    
    Swal.fire({
        title: `Konfirmasi Status`,
        text: `Apakah Anda yakin ingin meng-${actionText} rencana perawatan ini?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: isActivating ? '#10B981' : '#6B7280', // Green or Gray
        cancelButtonColor: '#d33',
        confirmButtonText: isActivating ? 'Ya, Aktifkan!' : 'Ya, Nonaktifkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch(`/admin/plans/${id}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message, // Use message from server
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => location.reload());
                } else {
                    throw new Error(data.message || 'Gagal mengubah status');
                }
            })
            .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'));
        }
    });
}

function deletePlan(id) {
    Swal.fire({
        title: 'Hapus Rencana Ini?',
        text: "Data yang dihapus tidak dapat dikembalikan! Aturan ini akan berhenti berjalan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch(`/admin/plans/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: data.message || 'Rencana perawatan berhasil dihapus.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => location.reload());
                } else {
                    throw new Error(data.message || 'Gagal menghapus');
                }
            })
            .catch(err => Swal.fire('Error', err.message || 'Gagal menghapus data.', 'error'));
        }
    });
}

function generateTasksNow() {
    Swal.fire({
        title: 'Generate Tasks Sekarang?',
        text: "Sistem akan memeriksa semua aturan aktif dan membuat tugas maintenance untuk HARI INI jika jadwal sesuai.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#10B981', // Green
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Jalankan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang Memproses...',
                text: 'Mohon tunggu, ini mungkin butuh beberapa detik tergantung jumlah aset.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('/admin/plans/generate-now', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => {
                if (res.ok) {
                    // Note: The controller redirects back with session flash.
                    // But since we use fetch, we handle UI here.
                    // Actually, controller returns redirect, fetch follows it.
                    // But to show sweetalert consistent, we reload.
                    // Ideally controller should return JSON for AJAX.
                    // Assuming controller redirects back to index.
                    location.reload(); 
                } else {
                   throw new Error('Gagal generate');
                }
            })
            .catch(err => {
                // If fetch fails (network) or controller error
                // The controller currently returns REDIRECT. Fetch transparently follows redirects.
                // So res.ok is likely true for the redirected page (index).
                // We reload to see the session flash message handled by layout.
                location.reload();
            });
        }
    });
}
</script>
@endsection
