@extends('layouts.admin')

@section('title', 'Edit Grup Teknisi')
@section('page-title', 'Edit Grup Teknisi')

@section('content')
<div class="container-fluid px-4 py-6 w-full mx-auto max-w-3xl">
    
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.groups.index') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-blue-600 transition-colors shadow-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Edit Grup Teknisi</h1>
            <p class="text-sm text-gray-500 mt-1">Ubah data grup atau tambah/hapus anggota teknisi.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.groups.update', $group->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="px-6 py-8 space-y-6">
                {{-- Nama & Warna --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Grup <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-4 py-2.5 outline-none shadow-sm transition" placeholder="Contoh: Tim Mekanikal Pagi" value="{{ old('name', $group->name) }}">
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="color" class="block text-sm font-semibold text-gray-700 mb-1.5">Warna Label <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select name="color" id="color" required class="w-full appearance-none border border-gray-300 rounded-xl text-sm bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-4 py-2.5 outline-none shadow-sm transition">
                                <option value="blue" {{ old('color', $group->color) == 'blue' ? 'selected' : '' }}>Biru</option>
                                <option value="green" {{ old('color', $group->color) == 'green' ? 'selected' : '' }}>Hijau</option>
                                <option value="red" {{ old('color', $group->color) == 'red' ? 'selected' : '' }}>Merah</option>
                                <option value="yellow" {{ old('color', $group->color) == 'yellow' ? 'selected' : '' }}>Kuning</option>
                                <option value="purple" {{ old('color', $group->color) == 'purple' ? 'selected' : '' }}>Ungu</option>
                                <option value="orange" {{ old('color', $group->color) == 'orange' ? 'selected' : '' }}>Oranye</option>
                                <option value="gray" {{ old('color', $group->color) == 'gray' ? 'selected' : '' }}>Abu-abu</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                    <textarea name="description" id="description" rows="3" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-4 py-2.5 outline-none shadow-sm transition resize-none" placeholder="Tugas spesifik atau wilayah tanggung jawab grup ini...">{{ old('description', $group->description) }}</textarea>
                </div>

                {{-- Pilih Anggota --}}
                <div class="pt-4 border-t border-gray-100">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih Anggota Tim <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                    <p class="text-xs text-gray-500 mb-3">Daftar di bawah ini mencakup semua Admin dan Teknisi. Hapus centang untuk mengeluarkan anggota, atau centang untuk menambah anggota baru.</p>
                    
                    <div class="mb-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-search text-gray-400 text-sm"></i>
                            </div>
                            <input type="text" id="memberSearch" class="w-full border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 pl-9 pr-4 py-2 outline-none shadow-sm transition" placeholder="Cari nama atau email pengguna...">
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-xl max-h-64 overflow-y-auto p-4 space-y-2 custom-scrollbar">
                        @php
                            // Kumpulkan ID member grup ini agar mudah dicek checked atau tidak
                            $currentMemberIds = $group->members->pluck('id')->toArray();
                        @endphp
                        
                        @forelse($availableTechnicians as $technician)
                            <label class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-lg cursor-pointer hover:border-orange-300 transition-colors member-label">
                                <input type="checkbox" name="member_ids[]" value="{{ $technician->id }}" 
                                       {{ in_array($technician->id, old('member_ids', $currentMemberIds)) ? 'checked' : '' }}
                                       class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500 member-checkbox"
                                       data-name="{{ $technician->name }}"
                                       data-has-group="{{ ($technician->technician_group_id && $technician->technician_group_id !== $group->id) ? 'true' : 'false' }}"
                                       data-group-name="{{ $technician->group ? $technician->group->name : '' }}">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                            {{ $technician->name }}
                                            @if($technician->role === 'admin')
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-100 text-purple-700">Admin</span>
                                            @endif
                                        </p>
                                        @if($technician->technician_group_id && $technician->technician_group_id !== $group->id)
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">
                                                Saat ini: {{ $technician->group->name }}
                                            </span>
                                        @elseif($technician->technician_group_id === $group->id)
                                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                                Anggota Grup Ini
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-gray-500">{{ $technician->email }}</p>
                                </div>
                            </label>
                        @empty
                            <div class="text-center py-6 text-gray-400">
                                <i class="fa-solid fa-user-xmark text-2xl mb-2"></i>
                                <p class="text-sm font-medium">Semua teknisi sudah masuk di grup lain.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                <a href="{{ route('admin.groups.index') }}" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 text-center transition focus:outline-none">Batal</a>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-600 shadow-sm transition flex items-center justify-center gap-2 focus:ring-2 focus:ring-orange-500 focus:ring-offset-1 focus:outline-none">
                    <i class="fa-solid fa-save"></i> Update Grup
                </button>
            </div>
        </form>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            const isChecked = this.checked;
            const hasGroup = this.dataset.hasGroup === 'true';
            const groupName = this.dataset.groupName;
            const userName = this.dataset.name;

            if (isChecked && hasGroup) {
                // Prevent default check visually until confirmed
                this.checked = false;
                
                Swal.fire({
                    title: 'Pindah Grup?',
                    text: `${userName} saat ini sudah berada di "${groupName}". Apakah Anda yakin ingin memindahkannya ke grup ini?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f97316', // orange-500
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Pindahkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.checked = true;
                    }
                });
            }
        });
    });

    // Search filtering functionality
    const searchInput = document.getElementById('memberSearch');
    const memberLabels = document.querySelectorAll('.member-label');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            memberLabels.forEach(label => {
                const nameElement = label.querySelector('.font-bold');
                const emailElement = label.querySelector('.text-gray-500.text-\\[11px\\]');
                const name = nameElement ? nameElement.textContent.toLowerCase() : '';
                const email = emailElement ? emailElement.textContent.toLowerCase() : '';
                
                if (name.includes(term) || email.includes(term)) {
                    label.style.display = 'flex';
                } else {
                    label.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endpush
@endsection
