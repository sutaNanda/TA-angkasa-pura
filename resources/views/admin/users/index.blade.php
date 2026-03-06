@extends('layouts.admin')

@section('title', 'Manajemen User')
@section('page-title', 'Daftar Pengguna Sistem')

@section('content')
    {{-- ALERT MESSAGE --}}


    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        {{-- SEARCH FORM --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="relative w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-64 text-sm shadow-sm">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
        </form>

        @if(!auth()->user()->isManajer())
        <button onclick="openModal('addUserModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-sm w-full md:w-auto justify-center">
            <i class="fa-solid fa-user-plus"></i>
            Tambah User Baru
        </button>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 w-12 text-center">No</th> {{-- KOLOM BARU --}}
                        <th class="px-6 py-4">User Profile</th>
                        <th class="px-6 py-4">Role / Jabatan</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Terdaftar Sejak</th>
                        @if(!auth()->user()->isManajer())
                        <th class="px-6 py-4 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    
                    @forelse($users as $user)
                        @php
                            // Logika Warna Role
                            $roleColor = match($user->role) {
                                'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'teknisi' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'manajer' => 'bg-green-100 text-green-700 border-green-200',
                                'user' => 'bg-gray-100 text-gray-700 border-gray-200',
                                default => 'bg-gray-100 text-gray-700'
                            };
                            
                            // Logika Inisial Nama (Budi Santoso -> BS)
                            $initials = collect(explode(' ', $user->name))->map(function($segment) {
                                return strtoupper(substr($segment, 0, 1));
                            })->take(2)->join('');
                        @endphp

                        <tr class="hover:bg-gray-50 transition group">
                            {{-- INDEX NUMBER DINAMIS --}}
                            <td class="px-6 py-4 text-center font-bold text-gray-400 text-xs">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full {{ $user->role == 'admin' ? 'bg-purple-100 text-purple-600' : 'bg-blue-100 text-blue-600' }} flex items-center justify-center font-bold text-xs shadow-sm">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="{{ $roleColor }} px-3 py-1 rounded-full text-xs font-bold border capitalize">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-green-600 font-medium text-xs flex items-center gap-1">
                                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div> Aktif
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                            @if(!auth()->user()->isManajer())
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    {{-- Edit Button --}}
                                    <button onclick="openEditModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition shadow-sm" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    {{-- Delete Button --}}
                                    @if(auth()->id() !== $user->id)
                                        <button onclick="confirmDelete('{{ $user->id }}')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-red-100 text-red-600 hover:bg-red-200 transition shadow-sm" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @else
                                        <button class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-100 text-gray-400 cursor-not-allowed" title="Akun Sendiri">
                                            <i class="fa-solid fa-lock"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-400">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fa-solid fa-users-slash text-3xl text-gray-300"></i>
                                    </div>
                                    <p class="font-medium text-gray-500">Tidak ada data user ditemukan.</p>
                                    <p class="text-xs text-gray-400 mt-1">Coba kata kunci lain atau tambahkan user baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
        
        {{-- PAGINATION --}}
        @if($users->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL 1: ADD USER --}}
    <div id="addUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('addUserModal')"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-user-plus text-blue-600 mr-2"></i> Tambah User Baru</h3>
                        <!-- <button type="button" onclick="closeModal('addUserModal')" class="text-gray-400 hover:text-red-500 transition"><i class="fa-solid fa-xmark text-xl"></i></button> -->
                    </div>
                    
                    <div class="bg-white px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" required class="w-full border-gray-300 border-2 rounded-lg text-sm focus:ring-blue-500 p-2" placeholder="Contoh: Okayana">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Email (Untuk Login)</label>
                            <input type="email" name="email" required class="w-full border-gray-300 border-2 rounded-lg text-sm focus:ring-blue-500 p-2" placeholder="nama@email.com">
                        </div>
                        {{-- Password dihapus, auto-generate by system --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Role / Hak Akses</label>
                            <select name="role" required class="w-full border-gray-300 border-2 rounded-lg text-sm bg-white focus:ring-blue-500 p-2">
                                <option value="teknisi">Teknisi</option>
                                <option value="admin">Admin</option>
                                <option value="manajer">Manajer</option>
                            </select>
                            <p class="text-[10px] text-gray-500 mt-1"><i class="fa-solid fa-circle-info"></i> Teknisi akan memiliki akses ke fitur scan QR di HP.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm transition">Simpan User</button>
                        <button type="button" onclick="closeModal('addUserModal')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL 2: EDIT USER --}}
    <div id="editUserModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('editUserModal')"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                {{-- Form Action akan diisi via JS --}}
                <form action="#" method="POST" id="editUserForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900"><i class="fa-solid fa-pen-to-square text-yellow-600 mr-2"></i> Edit Data User</h3>
                    </div>
                    
                    <div class="bg-white px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" id="edit_name" required class="w-full border-2 border-gray-300 rounded-lg text-sm focus:ring-yellow-500 pl-2 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="edit_email" required class="w-full border-2 border-gray-300 rounded-lg text-sm bg-gray-100 text-gray-500 cursor-not-allowed pl-2 py-2" readonly title="Email tidak dapat diubah">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Password Baru</label>
                            <input type="password" name="password" class="w-full border-2 border-gray-300 rounded-lg text-sm focus:ring-yellow-500 pl-2 py-2" placeholder="Kosongkan jika tidak diganti">
                            <p class="text-[10px] text-orange-500 mt-1 font-medium">Isi hanya jika ingin mereset password user ini.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                            <select name="role" id="edit_role" required class="w-full border-2 border-gray-300 rounded-lg text-sm bg-white focus:ring-yellow-500 pl-2 py-2">
                                <option value="teknisi">Teknisi</option>
                                <option value="admin">Admin</option>
                                <option value="manajer">Manajer</option>
                            </select>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100 mt-10">
                        <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-bold text-white hover:bg-yellow-600 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Update Data
                        </button>

                        <button type="button" onclick="closeModal('editUserModal')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        
        function openEditModal(id, name, email, role) {
            // 1. Isi Form
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            
            // 2. Update Action URL Form secara dinamis
            const form = document.getElementById('editUserForm');
            form.action = `/admin/users/${id}`; // Ini kuncinya

            // 3. Buka Modal
            openModal('editUserModal');
        }

        function confirmDelete(userId) {
            Swal.fire({
                title: 'Nonaktifkan User?',
                text: "User tidak akan bisa login lagi.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Nonaktifkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + userId).submit();
                }
            });
        }
    </script>
@endsection