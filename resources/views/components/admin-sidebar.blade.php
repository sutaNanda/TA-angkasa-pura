<aside class="w-64 bg-slate-800 text-white flex flex-col transition-all duration-300">
    <div class="h-16 flex items-center justify-center border-b border-slate-700 bg-slate-900">
        <div class="flex items-center gap-2 font-bold text-xl tracking-wider">
            <i class="fa-solid fa-plane-departure text-blue-400"></i>
            <span>ANGKASA PURA</span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;">
        <ul class="space-y-1 px-2">

            {{-- DASHBOARD --}}
            <li>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-600 transition {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
            </li>

            <li class="px-4 pt-4 pb-2 text-xs text-gray-400 font-bold uppercase tracking-widest">Data Referensi</li>

            <li>
                <a href="{{ route('admin.assets.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('admin.assets.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-sitemap w-5 text-center"></i>
                    <span class="text-sm font-medium">Inventaris Aset</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('admin.categories.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-tags w-5 text-center"></i>
                    <span class="text-sm font-medium">Kategori Aset</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.checklists.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('admin.checklists.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-list-check w-5 text-center"></i>
                    <span class="text-sm font-medium">Template Checklist</span>
                </a>
            </li>

            <li class="px-4 pt-4 pb-2 text-xs text-gray-400 font-bold uppercase tracking-widest">Operasional</li>

            <li>
                <a href="{{ route('admin.maintenances.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('admin.maintenances.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-map-location-dot w-5 text-center"></i>
                    <span class="text-sm font-medium">Inspeksi & Monitoring</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.plans.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('admin.plans.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-calendar-check w-5 text-center"></i>
                    <span class="text-sm font-medium">Perawatan Terjadwal</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.work-orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('admin.work-orders.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-screwdriver-wrench w-5 text-center"></i>
                    <span class="text-sm font-medium">Work Order & Perbaikan</span>
                </a>
            </li>

            <li class="px-4 pt-4 pb-2 text-xs text-gray-400 font-bold uppercase tracking-widest">Manajemen</li>

            <li>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('admin.users.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span class="text-sm font-medium">Manajemen Pengguna</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.audit.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition {{ request()->routeIs('admin.audit.*') ? 'bg-blue-600' : '' }}">
                    <i class="fa-solid fa-shield-halved w-5 text-center"></i>
                    <span class="text-sm font-medium">Log Aktivitas</span>
                </a>
            </li>

        </ul>
    </nav>

    <div class="p-4 border-t border-slate-700">
        {{-- 1. Tombol Trigger --}}
        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="flex items-center gap-3 px-4 py-2 text-red-400 hover:text-red-300 transition text-sm font-medium cursor-pointer">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Keluar</span>
        </a>

        {{-- 2. Form Hidden (Wajib untuk Laravel Auth) --}}
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</aside>
