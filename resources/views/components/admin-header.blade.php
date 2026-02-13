<header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="h-16 flex items-center justify-between px-6">
        <h2 class="text-xl font-semibold text-gray-800 truncate">
            @yield('page-title', 'Halaman Admin')
        </h2>

        <div class="flex items-center gap-4 shrink-0">
            <span class="text-sm text-gray-600">Halo, <strong>{{ Auth::user()->name ?? 'Admin' }}</strong></span>
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fa-solid fa-user text-blue-600"></i>
            </div>
        </div>
    </div>
</header>
