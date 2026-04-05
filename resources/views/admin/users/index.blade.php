@extends('layouts.admin')

@section('title', 'Manajemen User')
@section('page-title', 'Daftar Pengguna Sistem')

@section('content')
<div class="container-fluid px-4 py-6 w-full mx-auto max-w-full">
    
    {{-- Header Title --}}
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Pengguna Sistem</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola data teknisi, admin, dan manajer yang memiliki akses ke sistem.</p>
        </div>
    </div>

    {{-- TOOLBAR (Search & Actions) --}}
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        {{-- SEARCH FORM --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="relative w-full xl:w-auto flex-1 max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 w-full text-sm shadow-sm transition-all text-gray-700">
        </form>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-wrap sm:flex-nowrap gap-3 w-full xl:w-auto shrink-0">
            @if(auth()->user()->role === 'manajer')
            <button onclick="openModal('exportProductivityModal')" class="w-full sm:w-auto bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-sm focus:ring-2 focus:ring-gray-200">
                <i class="fa-solid fa-chart-line text-emerald-500"></i>
                <span class="whitespace-nowrap">Laporan Produktivitas</span>
            </button>
            @endif

            @if(!auth()->user()->isManajer())
            <button onclick="openModal('addUserModal')" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                <i class="fa-solid fa-user-plus"></i>
                <span class="whitespace-nowrap">Tambah User Baru</span>
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
                        <th scope="col" class="px-6 py-4 whitespace-nowrap min-w-[250px]">User Profile</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Role / Jabatan</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Shift</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Status</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Terdaftar Sejak</th>
                        @if(!auth()->user()->isManajer())
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($users as $user)
                        @php
                            // Logika Warna Role
                            $roleColor = match(strtolower($user->role)) {
                                'admin'   => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                                'teknisi' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                'manajer' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                default   => 'bg-gray-50 text-gray-700 ring-gray-600/20'
                            };
                            
                            // Logika Inisial Nama
                            $initials = collect(explode(' ', $user->name))->map(function($segment) {
                                return strtoupper(substr($segment, 0, 1));
                            })->take(2)->join('');
                        @endphp

                        <tr class="hover:bg-gray-50/80 transition-colors duration-150 group">
                            <td class="px-6 py-4 text-center font-medium text-gray-400 text-xs whitespace-nowrap">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-10 h-10 rounded-full shrink-0 {{ $user->role == 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center font-bold text-xs shadow-sm overflow-hidden ring-2 ring-white">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </div>
                                    <div class="flex flex-col ml-3">
                                        <p class="font-bold text-gray-900 text-sm group-hover:text-blue-600 transition-colors">{{ $user->name }}</p>
                                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset {{ $roleColor }}">
                                    {{ $user->role }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->shift)
                                    <span class="{{ $user->shift->badge_class }} px-2.5 py-1 rounded-full text-[10px] font-bold border inline-flex items-center gap-1.5">
                                        <i class="{{ $user->shift->icon_class }}"></i> {{ $user->shift->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs italic">—</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div> Aktif
                                </span>
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap font-medium">
                                {{ $user->created_at->format('d M Y') }}
                            </td>

                            @if(!auth()->user()->isManajer())
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Edit Button --}}
                                    <button onclick="openEditModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}', '{{ $user->shift_id }}')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm focus:outline-none" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    {{-- Delete Button --}}
                                    @if(auth()->id() !== $user->id)
                                        <button onclick="confirmDelete('{{ $user->id }}')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-all shadow-sm focus:outline-none" title="Hapus/Nonaktifkan">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                        <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @else
                                        <button class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed" title="Ini adalah akun Anda">
                                            <i class="fa-solid fa-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !auth()->user()->isManajer() ? '7' : '6' }}" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-users-slash text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="font-bold text-gray-900 mb-1">Tidak ada data user ditemukan</p>
                                    <p class="text-sm text-gray-500">Coba kata kunci lain atau tambahkan user baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- PAGINATION --}}
        @if($users->hasPages())
            <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200 rounded-b-2xl">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>

    {{-- ======================== MODALS ======================== --}}

    {{-- MODAL 0: EXPORT PRODUKTIVITAS --}}
    <div id="exportProductivityModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeModal('exportProductivityModal')"></div>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                <form action="{{ route('admin.export.technician-productivity') }}" method="GET" target="_blank" onsubmit="setTimeout(() => closeModal('exportProductivityModal'), 500)">
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm"><i class="fa-solid fa-chart-line"></i></span>
                            Ekspor Produktivitas
                        </h3>
                        <button type="button" onclick="closeModal('exportProductivityModal')" class="text-gray-400 hover:text-gray-600 transition focus:outline-none">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="bg-white px-6 py-6 space-y-5">
                        <p class="text-sm text-gray-500 leading-relaxed">Pilih periode laporan untuk menampilkan rekapan hasil kerja (inspeksi & perbaikan) tiap teknisi.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Dari Tanggal</label>
                                <input type="date" name="start_date" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 px-3 py-2.5 outline-none shadow-sm transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 px-3 py-2.5 outline-none shadow-sm transition">
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-2 bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 text-xs"></i>
                            <p class="text-[11px] text-gray-600 leading-relaxed font-medium">Biarkan tanggal kosong jika ingin mencetak rekapitulasi data dari seluruh waktu (All Time).</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeModal('exportProductivityModal')" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition focus:outline-none">Batal</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 shadow-sm transition flex items-center justify-center gap-2 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 focus:outline-none">
                            <i class="fa-solid fa-download"></i> Cetak PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 1: ADD USER --}}
    <div id="addUserModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('addUserModal')"></div>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm"><i class="fa-solid fa-user-plus"></i></span>
                            Tambah User Baru
                        </h3>
                    </div>
                    
                    <div class="bg-white px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition" placeholder="Contoh: Budi Santoso">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition" placeholder="budi@angkasapura.co.id">
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Role / Hak Akses <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="role" required class="w-full appearance-none border border-gray-300 rounded-xl text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition">
                                        <option value="teknisi">Teknisi</option>
                                        <option value="admin">Admin</option>
                                        <option value="manajer">Manajer</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Shift Kerja <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                                <div class="relative">
                                    <select name="shift_id" class="w-full appearance-none border border-gray-300 rounded-xl text-sm bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 px-3 py-2.5 outline-none shadow-sm transition">
                                        <option value="">— Tidak Ada —</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-start gap-2.5 text-[11px] text-gray-600 bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                            <div>
                                <strong>Catatan Sistem:</strong><br>
                                Password default akan dibuat otomatis oleh sistem dan dikirimkan ke email yang bersangkutan (atau menggunakan password default jika SMTP mati). Teknisi butuh akun untuk scan QR Code di aplikasi mobile.
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeModal('addUserModal')" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition focus:outline-none">Batal</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 shadow-sm transition flex items-center justify-center gap-2 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:outline-none">
                            <i class="fa-solid fa-check"></i> Simpan User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 2: EDIT USER --}}
    <div id="editUserModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('editUserModal')"></div>
            
            <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form action="#" method="POST" id="editUserForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center text-sm"><i class="fa-solid fa-pen-to-square"></i></span>
                            Edit Data User
                        </h3>
                    </div>
                    
                    <div class="bg-white px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="edit_name" required class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2.5 outline-none shadow-sm transition">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat Email</label>
                            <div class="relative">
                                <input type="email" name="email" id="edit_email" required class="w-full border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-500 cursor-not-allowed pl-10 pr-3 py-2.5 outline-none" readonly title="Email tidak dapat diubah sebagai primary identifier">
                                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Role / Hak Akses <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="role" id="edit_role" required class="w-full appearance-none border border-gray-300 rounded-xl text-sm bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2.5 outline-none shadow-sm transition">
                                        <option value="teknisi">Teknisi</option>
                                        <option value="admin">Admin</option>
                                        <option value="manajer">Manajer</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Shift Kerja</label>
                                <div class="relative">
                                    <select name="shift_id" id="edit_shift_id" class="w-full appearance-none border border-gray-300 rounded-xl text-sm bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2.5 outline-none shadow-sm transition">
                                        <option value="">— Tidak Ada —</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Reset Password <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                            <input type="password" name="password" class="w-full border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 px-3 py-2.5 outline-none shadow-sm transition" placeholder="Ketik password baru jika ingin mengubahnya...">
                            <p class="text-[10px] text-orange-500 mt-1.5 font-medium"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Biarkan kosong jika tidak ingin mengubah password akun ini.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                        <button type="button" onclick="closeModal('editUserModal')" class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition focus:outline-none">Batal</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-orange-500 text-white rounded-xl text-sm font-bold hover:bg-orange-600 shadow-sm transition flex items-center justify-center gap-2 focus:ring-2 focus:ring-orange-500 focus:ring-offset-1 focus:outline-none">
                            <i class="fa-solid fa-save"></i> Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        
        function openEditModal(id, name, email, role, shiftId) {
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_shift_id').value = shiftId || '';
            
            const form = document.getElementById('editUserForm');
            form.action = `/admin/users/${id}`;

            openModal('editUserModal');
        }

        function confirmDelete(userId) {
            Swal.fire({
                title: 'Nonaktifkan Akun User?',
                text: "User yang bersangkutan tidak akan bisa login ke dalam sistem lagi. Data riwayat kerjanya tetap akan disimpan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', // red-500
                cancelButtonColor: '#9ca3af', // gray-400
                confirmButtonText: 'Ya, Nonaktifkan!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'rounded-xl',
                    cancelButton: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + userId).submit();
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