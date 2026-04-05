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
    
    {{-- Favicon --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}">
    
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
        /* Fix SweetAlert z-index to be above our custom modal (z-9999) */
        .swal2-container { z-index: 10000 !important; }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800">

    {{-- WRAPPER UTAMA --}}
    {{-- KEY FIX: `md:h-screen md:overflow-hidden` mengunci tinggi total layar agar sidebar tidak ikut scroll --}}
    <div x-data="{ showScanOptions: false }" class="flex flex-col min-h-screen md:flex-row md:h-screen md:overflow-hidden relative">

        {{-- SIDEBAR (Hanya Muncul di Laptop/Desktop) --}}
        <aside class="hidden md:flex flex-col w-64 bg-slate-900 text-white flex-shrink-0 h-screen sticky top-0 overflow-hidden shadow-2xl z-50">
            <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center p-1.5 shadow-lg">
                    <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-full h-full object-contain">
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
                
                <div class="px-3 mb-2 mt-4 text-[10px] font-bold text-slate-600 uppercase tracking-widest">Data Referensi</div>
                <a href="{{ route('technician.assets.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-800 transition-all duration-200 group {{ request()->routeIs('technician.assets.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-400' }}">
                    <i class="fa-solid fa-box text-center group-hover:scale-110 transition-transform w-5"></i>
                    <span class="text-sm font-semibold">Inventaris Aset</span>
                </a>
                
                {{-- Tombol Scan Khusus Desktop --}}
                <button type="button" @click="showScanOptions = true" class="w-full flex items-center gap-3 px-4 py-3 mt-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:opacity-90 transition-all duration-200 shadow-md shadow-blue-500/20 group">
                    <i class="fa-solid fa-qrcode text-center group-hover:scale-110 transition-transform w-5"></i>
                    <span class="text-sm font-bold">Scan QR Area</span>
                </button>
            </nav>

            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                <div class="flex items-center gap-3 px-2 py-2">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">PT Angkasa Pura Indonesia</span>
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
                <a href="{{ route('technician.profile.index') }}" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                    <div class="text-right">
                        <p class="text-[10px] text-blue-200 font-bold leading-none mb-1">Halo</p>
                        <p class="text-xs font-black truncate max-w-[100px]">{{ auth()->user()->name }}</p>
                    </div>
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-8 h-8 rounded-full object-cover border border-white/50 shadow-sm">
                    @else
                        <div class="w-8 h-8 rounded-full bg-white text-blue-600 flex items-center justify-center font-bold shadow-sm border border-blue-400 text-xs">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                </a>
            </header>

            {{-- HEADER DESKTOP (Premium Bar) --}}
            <header class="hidden md:flex items-center justify-between px-8 py-4 bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight">Halaman @yield('title', 'Dashboard')</h2>
                </div>
                
                <div class="flex items-center gap-6">
                    <a href="{{ route('technician.profile.index') }}" class="flex items-center gap-3 pl-6 border-l border-gray-200 hover:opacity-80 transition-opacity">
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900 leading-none mb-1">Hallo, {{ auth()->user()->name }}</p>
                            <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Teknisi Lapangan</p>
                        </div>
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shadow-sm border border-blue-200">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </a>

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

        {{-- GLOBAL SCAN OPTIONS MODAL --}}
        <div x-cloak x-show="showScanOptions" style="z-index: 9999;" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4" x-transition.opacity>
            <div class="bg-white w-full max-w-sm rounded-3xl shadow-2xl overflow-hidden animate-fadeIn" @click.away="showScanOptions = false">
                <div class="p-6 text-center relative border-b border-gray-100">
                    <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 shadow-inner">
                        <i class="fa-solid fa-expand"></i>
                    </div>
                    <h3 class="font-black text-gray-800 text-xl mb-1">Pilih Metode Scan</h3>
                    <p class="text-sm text-gray-500">Bagaimana Anda ingin menemukan area / aset?</p>
                    
                    <button @click="showScanOptions = false" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-full text-gray-400 transition">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <div class="p-6 flex flex-col gap-3">
                    <a href="{{ route('technician.scan') }}" class="flex items-center gap-4 bg-blue-600 hover:bg-blue-700 text-white px-5 py-4 rounded-2xl font-bold shadow-md shadow-blue-600/20 transition group">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-camera text-xl"></i>
                        </div>
                        <div class="text-left flex-1">
                            <div class="text-base leading-tight">Gunakan Kamera</div>
                            <div class="text-[10px] font-normal text-blue-200 uppercase tracking-wide">Scan QR Code secara langsung</div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-white/50"></i>
                    </a>

                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-gray-200"></div>
                        <span class="flex-shrink-0 mx-4 text-gray-400 text-xs font-bold uppercase tracking-widest">ATAU</span>
                        <div class="flex-grow border-t border-gray-200"></div>
                    </div>

                    <form id="globalManualScanForm" class="flex flex-col gap-3">
                        <div class="relative">
                            <input type="text" id="globalManualScanInput" required class="w-full bg-gray-50 border border-gray-200 rounded-2xl pl-11 pr-4 py-3.5 text-sm font-bold text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-transparent transition-all uppercase p-2" placeholder="Nama atau ID Ruangan">
                        </div>
                        <button type="submit" class="bg-slate-800 hover:bg-black text-white px-5 py-4 rounded-2xl font-bold flex justify-center items-center gap-2 shadow-md transition">
                            Cari Manual <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </button>
                    </form>
                </div>
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
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
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

        // GLOBAL MANUAL SCAN LOGIC
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('globalManualScanForm');
            if(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const code = document.getElementById('globalManualScanInput').value;
                    if(code) {
                        // Tutup modal Alpine (mengakses scope x-data terdekat)
                        const modalElem = document.querySelector('[x-data="{ showScanOptions: false }"]');
                        if(modalElem && modalElem.__x) {
                            modalElem.__x.$data.showScanOptions = false;
                        }

                        // Tampilkan Loading
                        Swal.fire({
                            title: 'Mencari Data...',
                            text: 'Memproses kode: ' + code,
                            didOpen: () => { Swal.showLoading() },
                            allowOutsideClick: false
                        });

                        // Kirim request ke backend scan process
                        fetch("{{ route('technician.scan.process') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({ qr_code: code })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Ditemukan!',
                                    showConfirmButton: false,
                                    timer: 1000
                                }).then(() => {
                                    window.location.href = data.redirect_url;
                                });
                            } else {
                                Swal.fire('Gagal', data.message, 'error').then(() => {
                                    if(modalElem && modalElem.__x) {
                                        modalElem.__x.$data.showScanOptions = true;
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                        });
                    }
                });
            }
        });
    </script>
    {{-- Session Warning Component --}}
    <x-session-warning />
</body>
</html>
