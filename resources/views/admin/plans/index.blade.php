@extends('layouts.admin')

@section('title', 'Aturan Preventive Maintenance')
@section('page-title', 'Rencana Maintenance')

@section('content')
<div class="container-fluid px-4 py-6 max-w-7xl mx-auto w-full overflow-hidden">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rencana Perawatan</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola aturan dan jadwal perawatan otomatis berdasarkan kategori aset.</p>
        </div>
        
        @if(!auth()->user()->isManajer())
        <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <button onclick="generateTasksNow()" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium transition shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                <i class="fa-solid fa-bolt text-yellow-500"></i> Generate Hari Ini
            </button>
            <a href="{{ route('admin.plans.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                <i class="fa-solid fa-plus"></i> Tambah Aturan
            </a>
        </div>
        @endif
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-3 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-days text-xl text-gray-400"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-sm border border-green-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-green-600 uppercase tracking-wider">Aktif</p>
                    <p class="text-2xl font-bold text-green-700 mt-1">{{ $stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                    <i class="fa-solid fa-check-circle text-xl text-green-500"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-sm border border-blue-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wider">Harian</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $stats['daily'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
                    <i class="fa-solid fa-sun text-xl text-blue-500"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-sm border border-purple-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-purple-600 uppercase tracking-wider">Mingguan</p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $stats['weekly'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-week text-xl text-purple-500"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-5 rounded-xl shadow-sm border border-orange-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-orange-600 uppercase tracking-wider">Bulanan</p>
                    <p class="text-2xl font-bold text-orange-700 mt-1">{{ $stats['monthly'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-alt text-xl text-orange-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-red-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-red-600 uppercase tracking-wider">Tahunan</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $stats['yearly'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center">
                    <i class="fa-solid fa-calendar text-xl text-red-500"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="w-full max-w-full bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto w-full">
            <table class="w-full min-w-[1000px] divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-12">No</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rencana & Kategori</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Checklist</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siklus</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jadwal</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aset</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        @if(!auth()->user()->isManajer())
                        <th scope="col" class="px-6 py-4 pr-8 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-gray-50/50 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                {{ ($plans->currentPage() - 1) * $plans->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="mt-1">
                                        <i class="fa-solid fa-layer-group text-gray-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $plan->name }}</p>
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            @foreach($plan->categories as $cat)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 border border-gray-200 whitespace-nowrap">
                                                    {{ $cat->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <ul class="space-y-1">
                                    @foreach($plan->templates as $tpl)
                                        <li class="text-sm text-gray-600 flex items-center gap-1.5 whitespace-nowrap">
                                            <i class="fa-solid fa-circle text-[6px] text-gray-300"></i>
                                            {{ $tpl->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $badgeColors = [
                                        'daily' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                        'weekly' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                                        'monthly' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                        'yearly' => 'bg-red-50 text-red-700 ring-red-600/20',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium ring-1 ring-inset {{ $badgeColors[$plan->frequency] ?? 'bg-gray-50 text-gray-600 ring-gray-500/10' }}">
                                    {{ strtoupper($plan->frequency) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1 min-w-[120px]">
                                    @if($plan->shift)
                                        <div>
                                            <span class="{{ $plan->shift->badge_class }} inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium border whitespace-nowrap">
                                                <i class="{{ $plan->shift->icon_class }}"></i> {{ $plan->shift->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-500 whitespace-nowrap">Semua Shift</span>
                                    @endif
                                    <span class="text-sm text-gray-600 mt-1 line-clamp-2" title="{{ $plan->schedule_description }}">
                                        {{ $plan->schedule_description }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-50 border border-gray-200">
                                    <span class="text-sm font-semibold text-gray-700">{{ $plan->affected_assets_count }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if(!auth()->user()->isManajer())
                                    <button onclick="toggleActive({{ $plan->id }}, {{ $plan->is_active ? 'true' : 'false' }})" 
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 {{ $plan->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200 focus:ring-green-500' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 focus:ring-gray-500' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $plan->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $plan->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                        {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>
                            
                            @if(!auth()->user()->isManajer())
                            <td class="px-6 py-4 pr-8 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('admin.plans.edit', $plan->id) }}" 
                                       class="text-gray-400 hover:text-blue-600 transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                                    </a>
                                    <button onclick="deletePlan({{ $plan->id }})" 
                                            class="text-gray-400 hover:text-red-600 transition-colors" title="Hapus">
                                        <i class="fa-solid fa-trash-can text-lg"></i>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !auth()->user()->isManajer() ? '8' : '7' }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3 border border-gray-100">
                                        <i class="fa-solid fa-calendar-xmark text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-gray-500 font-medium">Belum ada aturan maintenance.</p>
                                    @if(!auth()->user()->isManajer())
                                        <a href="{{ route('admin.plans.create') }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-2 hover:underline">
                                            + Tambah aturan baru
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($plans->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $plans->links() }}
            </div>
        @endif
    </div>
</div>

<script>
// Fungsi JavaScript Anda tetap sama persis, saya hanya merapikan formatnya
function toggleActive(id, currentStatus) {
    const isActivating = !currentStatus;
    const actionText = isActivating ? 'aktifkan' : 'nonaktifkan';
    
    Swal.fire({
        title: `Konfirmasi Status`,
        text: `Apakah Anda yakin ingin meng-${actionText} rencana perawatan ini?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: isActivating ? '#10B981' : '#6B7280',
        cancelButtonColor: '#EF4444',
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
                        text: data.message,
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
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
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
        confirmButtonColor: '#3B82F6', 
        cancelButtonColor: '#6B7280',
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
                    location.reload(); 
                } else {
                   throw new Error('Gagal generate');
                }
            })
            .catch(err => {
                location.reload();
            });
        }
    });
}
</script>
@endsection
