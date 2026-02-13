@extends('layouts.technician')

@section('title', 'Profil Saya')

@section('header')
    <h1 class="font-bold text-lg text-center text-white">Profil Saya</h1>
@endsection

@section('content')
<div x-data="{ editModal: false, passwordModal: false }" class="pb-24">

    {{-- HEADER PROFIL --}}
    <div class="relative bg-white rounded-2xl shadow-sm p-6 mb-6 text-center overflow-hidden">
        {{-- Background Decoration --}}
        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-b from-blue-50 to-white opacity-80"></div>
        
        <div class="relative z-10 flex flex-col items-center">
            {{-- Avatar Wrapper --}}
            <div class="relative w-28 h-28 mb-4 group">
                <div class="w-full h-full rounded-full p-1 bg-white shadow-lg">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Profile" class="w-full h-full rounded-full object-cover">
                    @else
                        <div class="w-full h-full rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                
                {{-- Edit Photo Button --}}
                <button @click="editModal = true" class="absolute bottom-1 right-1 w-9 h-9 bg-white text-gray-600 rounded-full shadow-md flex items-center justify-center border border-gray-100 hover:text-blue-600 hover:scale-110 transition duration-200">
                    <i class="fa-solid fa-camera text-sm"></i>
                </button>
            </div>

            {{-- Name & Role --}}
            <h2 class="text-xl font-bold text-gray-800 mb-1">{{ $user->name }}</h2>
            <p class="text-sm text-gray-400 mb-3">{{ $user->email }}</p>
            
            {{-- Badges --}}
            <div class="flex items-center gap-2">
                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                    {{ $user->role ?? 'Teknisi' }}
                </span>
                <!-- <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Shift Pagi
                </span> -->
            </div>
        </div>
    </div>

    {{-- STATISTIK (2 Kolom) --}}
    <div class="grid grid-cols-2 gap-4 mb-8">
        {{-- Selesai Bulan Ini --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-100 relative overflow-hidden shadow-sm">
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Selesai Bulan Ini</p>
                <h3 class="text-2xl font-bold uppercase tracking-wider text-gray-800">{{ $completedThisMonth }} <span class="text-sm font-bold text-gray-400 uppercase tracking-wider">Tugas</span></h3>
            </div>
            <i class="fa-solid fa-calendar-check absolute -bottom-2 -right-2 text-6xl text-gray-800 opacity-10 rotate-12"></i>
        </div>

        {{-- Total Selesai --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-100 relative overflow-hidden shadow-sm">
            <div class="relative z-10">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Total Selesai</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $completedTotal }}</h3>
            </div>
            <i class="fa-solid fa-check absolute -bottom-1 -right-1 text-5xl text-gray-800 opacity-20"></i>
        </div>
    </div>

    {{-- MENU GROUPS --}}
    <div class="space-y-6">
        
        {{-- Group 1: Akun --}}
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase ml-4 mb-2 tracking-wider">Akun</h3>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <button @click="editModal = true" class="w-full flex items-center justify-between p-4 border-b border-gray-50 hover:bg-gray-50 transition text-left group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fa-solid fa-user-pen"></i>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">Edit Data Diri</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </button>
                <button @click="passwordModal = true" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition text-left group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <span class="text-sm font-semibold text-gray-700">Ganti Password</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </button>
            </div>
        </div>

        <!-- {{-- Group 2: Preferensi --}} -->
        <!-- <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase ml-4 mb-2 tracking-wider">Preferensi</h3>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <span class="text-sm font-semibold text-gray-700">Notifikasi Aplikasi</span>
                </div>
                {{-- Toggle Switch Dummy --}}
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" value="" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div> -->

        {{-- Group 3: Lainnya --}}
        <div>
            <h3 class="text-xs font-bold text-gray-400 uppercase ml-4 mb-2 tracking-wider">Lainnya</h3>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="button" @click="$el.closest('form').submit()" class="w-full flex items-center justify-between p-4 hover:bg-red-50 group transition text-left">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-red-50 text-red-500 flex items-center justify-center group-hover:bg-red-100 transition">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </div>
                            <span class="text-sm font-semibold text-red-600">Keluar Aplikasi</span>
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="text-center mt-8 mb-4">
        <p class="text-xs text-gray-400">Asset Management System v1.0.0</p>
    </div>

    {{-- MODAL EDIT DATA DIRI --}}
    <div x-show="editModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="editModal = false"></div>

        {{-- Modal Content --}}
        <div class="bg-white w-full max-w-xs sm:max-w-sm rounded-2xl shadow-xl relative z-10 overflow-hidden transform transition-all">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800">Edit Profil</h3>
                <button @click="editModal = false" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('technician.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-5">
                @csrf
                @method('PUT')

                <div class="flex justify-center mb-5">
                    <div class="relative w-24 h-24">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" id="preview-avatar" class="w-full h-full rounded-full object-cover border-4 border-gray-100 shadow-sm">
                        @else
                            <div class="w-full h-full rounded-full bg-blue-100 flex items-center justify-center text-blue-500 text-3xl font-bold border-4 border-gray-100 shadow-sm">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        <label for="avatar-upload" class="absolute bottom-0 right-0 bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-700 transition shadow-md border-2 border-white">
                            <i class="fa-solid fa-camera text-xs"></i>
                        </label>
                        <input type="file" id="avatar-upload" name="avatar" class="hidden" accept="image/*" onchange="document.getElementById('preview-avatar').src = window.URL.createObjectURL(this.files[0])">
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition font-medium text-sm" required>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition font-medium text-sm" required>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 active:scale-[0.98] transition shadow-lg shadow-blue-200 text-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL GANTI PASSWORD --}}
    <div x-show="passwordModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="passwordModal = false"></div>

        <div class="bg-white w-full max-w-xs sm:max-w-sm rounded-2xl shadow-xl relative z-10 overflow-hidden transform transition-all">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800">Ganti Password</h3>
                <button @click="passwordModal = false" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('technician.profile.password') }}" method="POST" class="p-5">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div x-data="{ show: false }">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Password Saat Ini</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="current_password" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 outline-none transition pl-10 pr-10 text-sm @error('current_password') border-red-500 @enderror" placeholder="••••••••" required>
                            <i class="fa-solid fa-lock absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                            <button type="button" @click="show = !show" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-data="{ show: false }">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Password Baru</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="new_password" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 outline-none transition pl-10 pr-10 text-sm @error('new_password') border-red-500 @enderror" placeholder="Min. 12 karakter" required>
                            <i class="fa-solid fa-key absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                            <button type="button" @click="show = !show" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">
                            <i class="fa-solid fa-circle-info text-blue-500 mr-0.5"></i> 
                            Min. 12 karakter (Huruf besar, kecil, angka & simbol).
                        </p>
                        @error('new_password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-data="{ show: false }">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Ulangi Password Baru</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="new_password_confirmation" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 outline-none transition pl-10 pr-10 text-sm" placeholder="• • • • • • • •" required>
                            <i class="fa-solid fa-check absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                            <button type="button" @click="show = !show" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-orange-500 text-white font-bold py-3 rounded-xl hover:bg-orange-600 active:scale-[0.98] transition shadow-lg shadow-orange-200 text-sm">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
