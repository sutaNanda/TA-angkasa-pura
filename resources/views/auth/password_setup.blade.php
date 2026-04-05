@extends('layouts.app')

@section('title', 'Setup Password Baru')

@section('content')


<style>
    .toggle-password {
        z-index: 50 !important;
        cursor: pointer !important;
        pointer-events: auto !important;
    }
</style>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    
    {{-- Aksen Dekorasi Background --}}
    <div class="absolute top-0 inset-x-0 h-72 z-0"></div>

    <div class="max-w-md w-full bg-white p-8 sm:p-10 rounded-3xl shadow-md border border-gray-100 relative z-10">
        
        {{-- Header --}}
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white p-2 mb-4 shadow-lg">
                <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Amankan Akun Anda
            </h2>
            <p class="mt-2 text-sm text-gray-500 font-medium leading-relaxed">
                Untuk alasan keamanan, Anda diwajibkan membuat kata sandi baru milik Anda sendiri sebelum dapat mengakses sistem.
            </p>
        </div>

        {{-- Form Area --}}
        <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST" x-data="{ showPass1: false, showPass2: false }">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                {{-- Input Password Baru --}}
                <div>
                    <label for="password" class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Password Baru <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" :type="showPass1 ? 'text' : 'password'" required 
                            class="block w-full pl-11 pr-12 py-3 border border-gray-300 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm transition-all shadow-sm" 
                            placeholder="Ketik password baru">
                        
                        {{-- Toggle Button Show/Hide (SVG for reliability) --}}
                        <button type="button" @click="showPass1 = !showPass1" 
                            class="absolute inset-y-0 right-0 w-12 flex items-center justify-center toggle-password text-gray-500 hover:text-blue-600 transition-colors"
                            title="Tampilkan/Sembunyikan">
                            <template x-if="!showPass1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </template>
                            <template x-if="showPass1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </template>
                        </button>
                    </div>
                    @error('password') 
                        <p class="text-rose-500 text-xs mt-1.5 font-medium flex items-center gap-1.5"><i class="fas fa-circle-exclamation"></i> {{ $message }}</p> 
                    @enderror
                </div>

                {{-- Input Konfirmasi Password --}}
                <div>
                    <label for="password_confirmation" class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-check-double text-gray-400"></i>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" :type="showPass2 ? 'text' : 'password'" required 
                            class="block w-full pl-11 pr-12 py-3 border border-gray-300 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-sm transition-all shadow-sm" 
                            placeholder="Ketik ulang password baru">
                        
                        {{-- Toggle Button Show/Hide (SVG for reliability) --}}
                        <button type="button" @click="showPass2 = !showPass2" 
                            class="absolute inset-y-0 right-0 w-12 flex items-center justify-center toggle-password text-gray-500 hover:text-blue-600 transition-colors"
                            title="Tampilkan/Sembunyikan">
                            <template x-if="!showPass2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </template>
                            <template x-if="showPass2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Persyaratan Keamanan Info Box --}}
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Saran Keamanan:</p>
                <ul class="text-xs text-gray-500 space-y-1.5 font-medium">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-blue-400"></i> Minimal 8 karakter panjangnya</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-blue-400"></i> Kombinasi huruf besar, kecil, & angka</li>
                </ul>
            </div>

            {{-- Submit Button --}}
            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all hover:-translate-y-0.5 gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Password & Lanjutkan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection