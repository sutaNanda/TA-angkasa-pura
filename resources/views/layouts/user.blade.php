<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}">

    <title>@yield('title') - Portal User</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">
    
    <div class="min-h-screen flex flex-col md:flex-row">
        
        {{-- MOBILE OVERLAY --}}
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 md:hidden"
             x-cloak>
        </div>

        {{-- DESKTOP SIDEBAR (Static, Hidden on Mobile) --}}
        <aside class="hidden md:flex flex-col w-64 bg-slate-800 text-white flex-shrink-0 h-screen sticky top-0 overflow-hidden">
            <div class="p-6 border-b border-slate-700 flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center p-1.5 shadow-lg">
                    <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-none tracking-tight">User Hub</h1>
                    <span class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">Enterprise</span>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <div class="px-3 mb-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Main Menu</div>
                
                <a href="{{ route('user.tickets.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-700 transition-all duration-200 group {{ request()->routeIs('user.tickets.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300' }}">
                    <i class="fa-solid fa-list-check w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-semibold">Riwayat Laporan</span>
                </a>

                <a href="{{ route('user.tickets.create') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-700 transition-all duration-200 group {{ request()->routeIs('user.tickets.create') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300' }}">
                    <i class="fa-solid fa-plus-circle w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-semibold">Buat Laporan Baru</span>
                </a>

                <a href="{{ route('user.profile.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-700 transition-all duration-200 group {{ request()->routeIs('user.profile.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-300' }}">
                    <i class="fa-solid fa-user w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-semibold">Profil Saya</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-700 bg-slate-800/50">
                <div class="flex items-center gap-3 px-2 py-2 mb-2">
                    <i class="fa-solid fa-circle-question text-slate-500 text-xs"></i>
                    <span class="text-xs text-slate-400">Butuh bantuan? Hubungi IT Center</span>
                </div>
            </div>
        </aside>

        {{-- MOBILE SIDEBAR (Fixed, Hidden on Desktop) --}}
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 text-white transition-transform duration-300 transform md:hidden flex flex-col"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            
            <div class="p-4 border-b border-slate-700 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center p-1.5 shadow-lg">
                        <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-none tracking-tight">Portal User</h1>
                        <span class="text-[10px] text-slate-400 font-medium uppercase tracking-widest leading-none mt-1">Asset Monitoring</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('user.tickets.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('user.tickets.index') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                    <i class="fa-solid fa-list-check w-5 text-center"></i>
                    <span class="text-sm font-medium">Riwayat Laporan</span>
                </a>

                <a href="{{ route('user.tickets.create') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('user.tickets.create') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                    <i class="fa-solid fa-plus-circle w-5 text-center"></i>
                    <span class="text-sm font-medium">Buat Laporan Baru</span>
                </a>

                <a href="{{ route('user.profile.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('user.profile.index') ? 'bg-blue-600 text-white' : 'text-slate-300' }}">
                    <i class="fa-solid fa-user w-5 text-center"></i>
                    <span class="text-sm font-medium">Profil Saya</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-700">
                <div class="flex items-center gap-3 mb-4 px-2">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-8 h-8 rounded-full object-cover">
                    @else
                        <div class="w-8 h-8 rounded-full bg-slate-600 flex items-center justify-center">
                            <i class="fa-solid fa-user text-xs"></i>
                        </div>
                    @endif
                    <div class="overflow-hidden">
                        <p class="text-sm font-bold truncate">{{ Auth::user()->name ?? 'User' }}</p>
                        <p class="text-xs text-slate-400 truncate">Karyawan</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-slate-700 hover:bg-red-600 text-slate-300 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                    </button>
                </form>
            </div>
        </aside>

        {{-- MOBILE HEADER --}}
        <div class="md:hidden bg-slate-800 text-white p-4 flex justify-between items-center sticky top-0 z-30 shadow-md">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="text-white focus:outline-none">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-cube text-blue-500"></i>
                    <span class="font-bold">Portal User</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                 {{-- Optional: Profile/Logout on header too --}}
            </div>
        </div>

        {{-- MAIN CONTENT AREA --}}
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50">
            {{-- DESKTOP TOP-BAR (Sticky & Glassmorphism) --}}
            <header class="hidden md:flex items-center justify-between px-8 py-4 bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight">@yield('title')</h2>
                </div>
                
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3 pl-6 border-l border-gray-200">
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900 leading-none mb-1">{{ Auth::user()->name ?? 'User' }}</p>
                            <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest">Karyawan</p>
                        </div>
                        @if(Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 shadow-sm">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold shadow-sm border border-blue-200">
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 text-gray-400 hover:text-red-500 transition-colors duration-200 text-sm font-bold group">
                            <i class="fa-solid fa-arrow-right-from-bracket group-hover:translate-x-1 transition-transform"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-8">
                <div class="max-w-7xl mx-auto w-full">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl shadow-sm flex justify-between items-center animate-fade-in-down">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                                    <i class="fa-solid fa-check-circle"></i>
                                </div>
                                <p class="text-sm font-medium">{{ session('success') }}</p>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600 transition truncate"><i class="fa-solid fa-times"></i></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl shadow-sm flex justify-between items-center animate-fade-in-down">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <p class="text-sm font-medium">{{ session('error') }}</p>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 transition truncate"><i class="fa-solid fa-times"></i></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
    {{-- Session Warning Component --}}
    <x-session-warning />
</body>
</html>
