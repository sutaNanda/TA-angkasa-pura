@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    

    <div class="max-w-md w-full bg-white p-8 sm:p-10 rounded-3xl shadow-md border border-gray-100 relative z-10">
        
        {{-- Header & Ikon --}}
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-2xl bg-blue-50 border border-blue-100 shadow-sm mb-6">
                <i class="fa-solid fa-envelope-open-text text-2xl text-blue-600"></i>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Verifikasi Email Anda
            </h2>
            <p class="mt-3 text-sm text-gray-500 font-medium leading-relaxed">
                Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan ke kotak masuk Anda.
            </p>
        </div>

        {{-- Alert Notifikasi Pengiriman (Jika Ada) --}}
        @if (session('status') == 'verification-link-sent')
            <div class="mt-6 flex items-start gap-3 bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-200 shadow-sm transition-all animate-fade-in-up">
                <i class="fa-solid fa-circle-check mt-0.5 text-emerald-500"></i>
                <p class="text-[13px] font-semibold leading-relaxed">Link verifikasi baru telah berhasil dikirim ke alamat email Anda!</p>
            </div>
        @endif

        {{-- Form & Tombol Aksi --}}
        <div class="mt-8 flex flex-col gap-4">
            
            {{-- Tombol Kirim Ulang --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all hover:-translate-y-0.5 gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    Kirim Ulang Link Verifikasi
                </button>
            </form>

            {{-- Pemisah (Divider) --}}
            <div class="relative flex items-center py-2">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="flex-shrink-0 mx-4 text-gray-400 text-[10px] font-bold uppercase tracking-widest">Atau</span>
                <div class="flex-grow border-t border-gray-200"></div>
            </div>

            {{-- Tombol Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 border border-gray-300 text-sm font-bold rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:text-rose-600 hover:border-rose-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-all shadow-sm gap-2">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Logout dari Sistem
                </button>
            </form>
            
        </div>
    </div>
</div>

{{-- CSS Custom Tambahan untuk Animasi Alert --}}
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
    }
</style>
@endsection