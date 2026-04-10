<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="h-16 flex items-center justify-between px-6">
        <h2 class="text-xl font-semibold text-gray-800 truncate">
            @yield('page-title', 'Halaman Admin')
        </h2>

        <div class="flex items-center gap-4 shrink-0">
            {{-- Mengubah Halo menjadi Hello dan membuatnya selalu tampil --}}
            <span class="text-sm text-gray-700">Hello, <strong class="font-bold text-gray-900">{{ Auth::user()->name ?? 'Admin' }}</strong></span>
            
            <button type="button" onclick="openAdminProfileModal()" class="relative w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center border-2 border-transparent hover:border-blue-400 focus:outline-none transition group overflow-hidden shadow-sm">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover" alt="Profile">
                @else
                    <i class="fa-solid fa-user text-blue-600"></i>
                @endif
                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                    <i class="fa-solid fa-pen text-white text-[10px]"></i>
                </div>
            </button>
        </div>
    </div>
</header>

{{-- MODAL EDIT PROFILE ADMIN/MANAGER (Desain Baru) --}}
<div id="adminProfileModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeAdminProfileModal()"></div>
        
        {{-- Modal Panel --}}
        <div class="relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
            
            {{-- Header Modal Clean --}}
            <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-blue-600"></i> Edit Profil Saya
                </h3>
                <button type="button" onclick="closeAdminProfileModal()" class="text-gray-400 hover:text-gray-600 transition focus:outline-none">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white px-6 py-5">
                    
                    {{-- Avatar Preview & Upload --}}
                    <div class="flex flex-col items-center mb-6">
                        <div class="relative w-24 h-24 rounded-full border border-gray-200 bg-gray-50 shadow-sm overflow-hidden mb-2 group cursor-pointer" onclick="document.getElementById('adminAvatarInput').click()">
                            <img id="adminProfilePreview" src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://via.placeholder.com/150?text=No+Photo' }}" class="w-full h-full object-cover">
                            
                            {{-- Overlay Hover Upload --}}
                            <div class="absolute inset-0 bg-black/50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fa-solid fa-camera text-white mb-1 text-lg"></i>
                                <span class="text-[10px] font-medium text-white">Ubah Foto</span>
                            </div>
                        </div>
                        <input type="file" name="avatar" id="adminAvatarInput" class="hidden" accept="image/*" onchange="previewAdminAvatar(this)">
                        <p class="text-xs text-gray-400 text-center">Format: JPG, PNG (Max 2MB)</p>
                    </div>

                    {{-- Form Inputs --}}
                    <div class="space-y-4">
                        {{-- Nama Lengkap --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-user text-gray-400 text-sm"></i>
                                </div>
                                <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" class="pl-10 w-full border border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition shadow-sm" required placeholder="Masukkan nama Anda">
                            </div>
                        </div>
                        
                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-envelope text-gray-400 text-sm"></i>
                                </div>
                                <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" class="pl-10 w-full border border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition shadow-sm" required placeholder="email@contoh.com">
                            </div>
                        </div>
                        
                        {{-- Ubah Password --}}
                        <div class="pt-4 mt-2 border-t border-gray-100">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Password Baru <span class="text-xs text-gray-400 font-normal">(Opsional)</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-lock text-gray-400 text-sm"></i>
                                </div>
                                <input type="password" name="password" class="pl-10 w-full border border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition shadow-sm" placeholder="Biarkan kosong jika tidak diubah">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="bg-gray-50 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100">
                    <button type="button" onclick="closeAdminProfileModal()" class="w-full sm:w-auto inline-flex justify-center items-center rounded-lg border border-gray-300 px-5 py-2.5 bg-white text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-lg border border-transparent px-5 py-2.5 bg-blue-600 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <i class="fa-solid fa-check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAdminProfileModal() {
        document.getElementById('adminProfileModal').classList.remove('hidden');
    }
    
    function closeAdminProfileModal() {
        document.getElementById('adminProfileModal').classList.add('hidden');
    }

    function previewAdminAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('adminProfilePreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
