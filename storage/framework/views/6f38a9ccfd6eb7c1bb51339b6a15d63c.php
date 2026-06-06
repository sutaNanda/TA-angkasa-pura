<nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-white border-t border-gray-200 z-50 pb-safe shadow-[0_-5px_10px_rgba(0,0,0,0.02)] ">
    <div class="flex justify-between items-center h-16 px-2">
        
        
        <a href="<?php echo e(route('technician.dashboard')); ?>" class="flex flex-1 flex-col items-center justify-center h-full <?php echo e(request()->routeIs('technician.dashboard') ? 'text-blue-600' : 'text-gray-400 hover:text-gray-600'); ?>">
            <i class="fa-solid fa-house text-xl mb-1"></i>
            <span class="text-[10px] font-medium">Beranda</span>
        </a>

        
        <a href="<?php echo e(route('technician.tasks.index')); ?>" class="flex flex-1 flex-col items-center justify-center h-full <?php echo e(request()->routeIs('technician.tasks.*') ? 'text-blue-600' : 'text-gray-400 hover:text-gray-600'); ?>">
            <div class="relative">
                <i class="fa-solid fa-clipboard-list text-xl mb-1"></i>
                
                
                <?php if(isset($pendingCount) && $pendingCount > 0): ?>
                    <span class="absolute -top-1.5 -right-2 bg-red-500 text-white text-[9px] min-w-[16px] h-4 px-1 flex items-center justify-center rounded-full border border-white font-bold">
                        <?php echo e($pendingCount > 9 ? '9+' : $pendingCount); ?>

                    </span>
                <?php endif; ?>
            </div>
            <span class="text-[10px] font-medium">Tugas</span>
        </a>

        
        <div class="relative w-16 h-16 flex justify-center z-50">
            <button type="button" @click="showScanOptions = true" class="bg-blue-600 text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg shadow-blue-600/40 border-4 border-white transform transition hover:scale-105 hover:bg-blue-700 active:scale-95">
                <i class="fa-solid fa-qrcode text-2xl"></i>
            </button>
        </div>

        
        <a href="<?php echo e(route('technician.history.index')); ?>" class="flex flex-1 flex-col items-center justify-center h-full <?php echo e(request()->routeIs('technician.history.*') ? 'text-blue-600' : 'text-gray-400 hover:text-gray-600'); ?>">
            <i class="fa-solid fa-clock-rotate-left text-xl mb-1"></i>
            <span class="text-[10px] font-medium">Riwayat</span>
        </a>

        
        <a href="<?php echo e(route('technician.assets.index')); ?>" class="flex flex-1 flex-col items-center justify-center h-full <?php echo e(request()->routeIs('technician.assets.*') ? 'text-blue-600' : 'text-gray-400 hover:text-gray-600'); ?>">
            <i class="fa-solid fa-box text-xl mb-1"></i>
            <span class="text-[10px] font-medium">Inventaris</span>
        </a>

    </div>
</nav>
<?php /**PATH C:\Users\User\Documents\tugas kuliah\TA\asset-monitoring\resources\views/components/technician-bottom-nav.blade.php ENDPATH**/ ?>