<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Teknisi - Asset Monitoring</title>
    
    @php
        // Detect if accessing via ngrok or external URL
        $isNgrok = str_contains(request()->getHost(), 'ngrok');
        $isExternal = !in_array(request()->getHost(), ['localhost', '127.0.0.1']) && !str_starts_with(request()->getHost(), '192.168.');
    @endphp
    
    @if(!$isNgrok && !$isExternal)
        {{-- VITE ASSETS (Only for local development) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    
    {{-- TAILWIND CDN (Primary for ngrok/external, fallback for local) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    @if(!$isNgrok && !$isExternal)
        {{-- Fallback check for local development --}}
        <script>
            // Check if Tailwind loaded from Vite, if not CDN is already loaded above
            window.addEventListener('load', function() {
                console.log('Tailwind loaded via CDN for compatibility');
            });
        </script>
    @endif
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Trik supaya tampilan serasa App di HP */
        body { -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }

        /* Hide Scrollbar for cleaner look */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    {{-- WRAPPER UTAMA: Di HP full width, di Laptop ada max-width dan shadow --}}
    <div class="flex flex-col min-h-screen md:flex-row md:max-w-full md:mx-auto md:bg-white md:shadow-xl md:my-0 overflow-hidden">

        {{-- SIDEBAR (Hanya Muncul di Laptop/Desktop) --}}
        <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-white p-6">
            <div class="flex items-center gap-3 font-bold text-xl mb-10">
                <i class="fa-solid fa-cube text-blue-500"></i>
                <span>Teknisi App</span>
            </div>

            <nav class="flex-1 space-y-2">
                <a href="{{ route('technician.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 {{ request()->routeIs('technician.dashboard') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-home w-5"></i> Beranda
                </a>
                <a href="{{ route('technician.tasks.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 {{ request()->routeIs('technician.tasks.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-clipboard-list w-5"></i> Tugas
                </a>
                <a href="{{ route('technician.history.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 {{ request()->routeIs('technician.history.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left w-5"></i> Riwayat
                </a>
                <a href="{{ route('technician.profile.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 {{ request()->routeIs('technician.profile.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-user w-5"></i> Profil
                </a>
            </nav>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:text-red-300 w-full text-left">
                    <i class="fa-solid fa-right-from-bracket w-5"></i> Keluar
                </button>
            </form>
        </aside>

        {{-- KONTEN UTAMA --}}
        <div class="flex-1 flex flex-col relative h-screen md:h-auto overflow-hidden bg-gray-50 md:bg-white">

            {{-- HEADER MOBILE (Sticky Top) --}}
            <header class="bg-blue-600 text-white p-4 sticky top-0 z-30 shadow-md md:hidden">
                @yield('header')
            </header>

            {{-- HEADER DESKTOP (Simple) --}}
            <header class="hidden md:flex justify-between items-center p-6 border-b border-gray-100 bg-white sticky top-0 z-20">
                <h2 class="font-bold text-xl text-gray-800">@yield('title', 'Dashboard')</h2>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-gray-800">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">Teknisi Lapangan</p>
                    </div>
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                    @else
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                </div>
            </header>

            {{-- ISI KONTEN --}}
            <main class="flex-1 overflow-y-auto pb-24 md:pb-6 p-4 md:p-8 no-scrollbar">
                @yield('content')
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
</body>
</html>
