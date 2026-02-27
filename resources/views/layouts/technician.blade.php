<!DOCTYPE html>
<html lang="id">
@php
    $hour = date('H');
    if ($hour >= 5 && $hour < 11) {
        $greeting = 'Pagi';
    } elseif ($hour >= 11 && $hour < 15) {
        $greeting = 'Siang';
    } elseif ($hour >= 15 && $hour < 18) {
        $greeting = 'Sore';
    } else {
        $greeting = 'Malam';
    }
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Teknisi - Asset Monitoring</title>
    
    <title>Teknisi - Asset Monitoring</title>
    
    {{-- Scripts/Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        body { -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    {{-- WRAPPER UTAMA --}}
    {{-- KEY FIX: `md:h-screen md:overflow-hidden` mengunci tinggi total layar agar sidebar tidak ikut scroll --}}
    <div class="flex flex-col min-h-screen md:flex-row md:h-screen md:overflow-hidden">

        {{-- SIDEBAR (Hanya Muncul di Laptop/Desktop) --}}
        <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-white flex-shrink-0 h-screen sticky top-0 overflow-hidden shadow-2xl z-50">
            <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-600/20">
                    <i class="fa-solid fa-screwdriver-wrench text-white text-sm"></i>
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-none tracking-tight">Teknisi App</h1>
                    <span class="text-[10px] text-slate-500 font-medium uppercase tracking-widest">Technician Panel</span>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <div class="px-3 mb-2 text-[10px] font-bold text-slate-600 uppercase tracking-widest">Operasional</div>

                <a href="{{ route('technician.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-all duration-200 group {{ request()->routeIs('technician.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400' }}">
                    <i class="fa-solid fa-home w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-semibold">Beranda</span>
                </a>
                <a href="{{ route('technician.tasks.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-all duration-200 group {{ request()->routeIs('technician.tasks.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400' }}">
                    <i class="fa-solid fa-clipboard-list w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-semibold">Tugas</span>
                </a>
                <a href="{{ route('technician.history.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-all duration-200 group {{ request()->routeIs('technician.history.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-semibold">Riwayat</span>
                </a>
                <a href="{{ route('technician.profile.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-all duration-200 group {{ request()->routeIs('technician.profile.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400' }}">
                    <i class="fa-solid fa-user w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-semibold">Profil</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div>
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Sistem Online</span>
                </div>
            </div>
        </aside>

        {{-- KONTEN UTAMA --}}
        {{-- KEY: `h-full` agar kolom konten ini mengisi sisa tinggi layar, bukan overflow --}}
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-hidden">

            {{-- HEADER MOBILE (Sticky Top) --}}
            <header class="bg-blue-600 text-white px-4 py-3 sticky top-0 z-30 shadow-md md:hidden flex justify-between items-center">
                <div class="flex flex-col">
                    <span class="text-[10px] text-blue-200 font-bold uppercase tracking-widest leading-none">Halaman</span>
                    <h2 class="text-sm font-bold truncate">@yield('title', 'Dashboard')</h2>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-blue-200 font-bold leading-none mb-1">Halo, Selamat {{ $greeting }}</p>
                    <p class="text-xs font-black truncate max-w-[120px]">{{ auth()->user()->name }}</p>
                </div>
            </header>

            {{-- HEADER DESKTOP (Premium Bar) --}}
            <header class="hidden md:flex items-center justify-between px-8 py-4 bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight">Halaman @yield('title', 'Dashboard')</h2>
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3 pl-6 border-l border-gray-200">
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900 leading-none mb-1">Selamat {{ $greeting }}, {{ auth()->user()->name }}</p>
                            <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Teknisi Lapangan</p>
                        </div>
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shadow-sm border border-blue-200">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 text-gray-400 hover:text-red-500 transition-colors duration-200 text-sm font-bold group">
                            <i class="fa-solid fa-arrow-right-from-bracket group-hover:translate-x-1 transition-transform"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- ISI KONTEN --}}
            <main class="flex-1 overflow-y-auto pb-24 md:pb-8 no-scrollbar">
                <div class="max-w-7xl mx-auto w-full p-4 md:p-8">
                    @yield('content')
                </div>
            </main>

            {{-- BOTTOM NAV (Hanya Muncul di Mobile) --}}
            <div class="md:hidden">
                @include('components.technician-bottom-nav')
            </div>

        </div>
    </div>

    {{-- GLOBAL ALERT SCRIPT --}}
    <script>
        window.addEventListener('load', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 2000,
                    toast: true,
                    position: 'top-end' // Di laptop di pojok kanan atas
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    toast: true,
                    position: 'top-end'
                });
            @endif

            {{-- GLOBAL VALIDATION ERROR ALERT (e.g. Form Validation) --}}
            @if($errors->any())
                let errorMsg = '';
                @foreach($errors->all() as $error)
                    errorMsg += '{{ $error }}\n';
                @endforeach
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: errorMsg,
                    toast: true,
                    position: 'top-end'
                });
            @endif
        });
    </script>
    {{-- Session Warning Component --}}
    <x-session-warning />
</body>
</html>
