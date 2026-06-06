<aside class="w-64 bg-slate-800 text-white flex flex-col transition-all duration-300">
    <div class="h-20 flex items-center border-b border-slate-700 bg-slate-900 px-4">
        <div class="flex items-center gap-3 font-bold text-lg tracking-tight">
            <div class="w-12 h-12 bg-white rounded-lg p-1.5 flex-shrink-0">
                <img src="<?php echo e(asset('logo.svg')); ?>" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div class="flex flex-col">
                <span class="text-white text-2xl leading-none mb-1">AVIATRACK</span>
                <?php if(auth()->user()->role === 'admin'): ?>
                    <span class="text-[10px] text-slate-500 font-medium uppercase tracking-widest">Admin Panel</span>
                <?php elseif(auth()->user()->role === 'manajer'): ?>
                    <span class="text-[10px] text-slate-500 font-medium uppercase tracking-widest">Manajer Panel</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 scrollbar-hide" style="scrollbar-width: none; -ms-overflow-style: none;">
        <ul class="space-y-1 px-2">

            
            <li>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-600 transition <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                    <span class="text-sm font-medium">Dashboard</span>
                </a>
            </li>

            <li class="px-4 pt-4 pb-2 text-xs text-gray-400 font-bold uppercase tracking-widest">Data Referensi</li>

            <li>
                <a href="<?php echo e(route('admin.assets.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.assets.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-sitemap w-5 text-center"></i>
                    <span class="text-sm font-medium">Inventaris Aset</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('admin.categories.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.categories.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-tags w-5 text-center"></i>
                    <span class="text-sm font-medium">Kategori</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('admin.checklists.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.checklists.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-list-check w-5 text-center"></i>
                    <span class="text-sm font-medium">Template Checklist</span>
                </a>
            </li>

            <li class="px-4 pt-4 pb-2 text-xs text-gray-400 font-bold uppercase tracking-widest">Operasional</li>

            <li>
                <a href="<?php echo e(route('admin.maintenances.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.maintenances.index') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-clipboard-check w-5 text-center"></i>
                    <span class="text-sm font-medium">Riwayat Patroli</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('admin.plans.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.plans.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-calendar-check w-5 text-center"></i>
                    <span class="text-sm font-medium">Perawatan Terjadwal</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('admin.work-orders.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.work-orders.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-screwdriver-wrench w-5 text-center"></i>
                    <span class="text-sm font-medium">Work Order & Perbaikan</span>
                </a>
            </li>

            <li class="px-4 pt-4 pb-2 text-xs text-gray-400 font-bold uppercase tracking-widest">Manajemen</li>

            <li>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.users.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span class="text-sm font-medium">Manajemen Pengguna</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('admin.groups.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.groups.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-users-gear w-5 text-center"></i>
                    <span class="text-sm font-medium">Grup Teknisi</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('admin.departments.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.departments.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-regular fa-building w-5 text-center"></i>
                    <span class="text-sm font-medium">Departemen</span>
                </a>
            </li>

            <li>
                <a href="<?php echo e(route('admin.audit.index')); ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-700 transition <?php echo e(request()->routeIs('admin.audit.*') ? 'bg-blue-600' : ''); ?>">
                    <i class="fa-solid fa-shield-halved w-5 text-center"></i>
                    <span class="text-sm font-medium">Log Aktivitas</span>
                </a>
            </li>



        </ul>
    </nav>

    <div class="p-4 border-t border-slate-700">
        
        <a href="<?php echo e(route('logout')); ?>"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
            class="flex items-center gap-3 px-4 py-2 text-red-400 hover:text-red-300 transition text-sm font-medium cursor-pointer">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Keluar</span>
        </a>

        
        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden">
            <?php echo csrf_field(); ?>
        </form>
    </div>
</aside>
<?php /**PATH C:\Users\User\Documents\tugas kuliah\TA\asset-monitoring\resources\views/components/admin-sidebar.blade.php ENDPATH**/ ?>