@extends('layouts.admin')

@section('title', 'Manajemen Departemen')
@section('page-title', 'Daftar Departemen / Divisi')

@section('content')
<div class="container-fluid px-4 py-6 w-full mx-auto max-w-full">
    
    {{-- Header Title --}}
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Departemen</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data departemen untuk pendaftaran akun pelapor.</p>
        </div>
    </div>

    {{-- TOOLBAR (Search & Actions) --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <form method="GET" action="{{ route('admin.departments.index') }}" class="relative w-full xl:w-auto flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama departemen..." class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-full text-sm shadow-sm transition-all text-gray-700">
        </form>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-wrap sm:flex-nowrap gap-3 w-full xl:w-auto shrink-0">
            @if(!auth()->user()->isManajer())
            <button onclick="openModal('addDepartmentModal')" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                <i class="fa-solid fa-plus"></i>
                <span class="whitespace-nowrap">Tambah Departemen</span>
            </button>
            @endif
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden w-full max-w-full">
        <div class="w-full overflow-x-auto relative custom-scrollbar">
            <table class="min-w-max w-full text-sm text-left text-gray-600 border-collapse">
                <thead class="bg-gray-50/80 text-gray-500 uppercase tracking-wider text-[11px] font-bold border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-12 text-center whitespace-nowrap">No</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap min-w-[200px]">Nama Departemen</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Deskripsi</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Akun Terhubung</th>
                        @if(!auth()->user()->isManajer())
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-gray-50/80 transition-colors duration-150 group">
                            <td class="px-6 py-4 text-center font-medium text-gray-400 text-xs whitespace-nowrap">
                                {{ ($departments->currentPage() - 1) * $departments->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="font-bold text-gray-900">{{ $dept->name }}</p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 truncate max-w-xs" title="{{ $dept->description }}">
                                    {{ $dept->description ?: '—' }}
                                </p>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($dept->user)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                        <i class="fa-solid fa-user-check"></i> {{ $dept->user->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-gray-50 text-gray-500 ring-1 ring-inset ring-gray-600/20">
                                        Belum Ada
                                    </span>
                                @endif
                            </td>

                            @if(!auth()->user()->isManajer())
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="openEditModal({{ $dept->id }}, '{{ $dept->name }}', '{{ $dept->description }}')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm focus:outline-none" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <button onclick="confirmDelete('{{ $dept->id }}', '{{ $dept->name }}')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-all shadow-sm focus:outline-none" title="Hapus">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <form id="delete-form-{{ $dept->id }}" action="{{ route('admin.departments.destroy', $dept->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !auth()->user()->isManajer() ? '5' : '4' }}" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-regular fa-building text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="font-bold text-gray-900 mb-1">Tidak ada data departemen</p>
                                    <p class="text-sm text-gray-500">Silakan tambahkan departemen baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- PAGINATION --}}
        @if($departments->hasPages())
            <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200 rounded-b-2xl">
                {{ $departments->withQueryString()->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL ADD --}}
    <div id="addDepartmentModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('addDepartmentModal')"></div>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fa-solid fa-plus"></i></span>
                            Tambah Departemen
                        </h3>
                    </div>
                    
                    <div class="bg-white px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Departemen <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition" placeholder="Contoh: IT Support">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition" placeholder="Opsional..."></textarea>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeModal('addDepartmentModal')" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition focus:outline-none">Batal</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-sm transition flex items-center justify-center gap-2 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:outline-none">
                            <i class="fa-solid fa-check"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="editDepartmentModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('editDepartmentModal')"></div>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="#" method="POST" id="editDepartmentForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-sm"><i class="fa-solid fa-pen-to-square"></i></span>
                            Edit Departemen
                        </h3>
                    </div>
                    
                    <div class="bg-white px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Departemen <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="edit_name" required class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2.5 outline-none shadow-sm transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                            <textarea name="description" id="edit_description" rows="3" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2.5 outline-none shadow-sm transition"></textarea>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeModal('editDepartmentModal')" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition focus:outline-none">Batal</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-600 shadow-sm transition flex items-center justify-center gap-2 focus:ring-2 focus:ring-orange-500 focus:ring-offset-1 focus:outline-none">
                            <i class="fa-solid fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        
        function openEditModal(id, name, description) {
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description !== 'null' ? description : '';
            
            const form = document.getElementById('editDepartmentForm');
            form.action = `/admin/departments/${id}`;

            openModal('editDepartmentModal');
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Departemen?',
                text: `Anda yakin ingin menghapus departemen ${name}? Pastikan tidak ada akun yang terhubung.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'rounded-xl',
                    cancelButton: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>
@endsection
