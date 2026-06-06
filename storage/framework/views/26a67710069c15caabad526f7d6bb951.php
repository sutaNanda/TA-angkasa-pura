

<?php $__env->startSection('title', 'Log Aktivitas Sistem'); ?>
<?php $__env->startSection('page-title', 'Log Aktivitas Sistem'); ?>

<?php $__env->startSection('content'); ?>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-list-ul text-blue-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Total Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo e($stats['today']); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-right-to-bracket text-green-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Login Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo e($stats['logins']); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-plus text-blue-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Input Data</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo e($stats['creates']); ?></p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-trash text-red-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Hapus Data</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo e($stats['deletes']); ?></p>
            </div>
        </div>
    </div>

    
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="date" value="<?php echo e(request('date')); ?>"
                    class="w-full border-2 pl-2 py-2 mt-2 border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Pengguna</label>
                <select name="user_id" class="w-full border-2 pl-2 py-2 mt-2 border-gray-300 rounded-lg text-sm bg-white focus:ring-blue-500">
                    <option value="">Semua Pengguna</option>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                            <?php echo e($user->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Tipe Aktivitas</label>
                <select name="action" class="w-full border-2 pl-2 py-2 mt-2 border-gray-300 rounded-lg text-sm bg-white focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="login" <?php echo e(request('action') == 'login' ? 'selected' : ''); ?>>Login</option>
                    <option value="logout" <?php echo e(request('action') == 'logout' ? 'selected' : ''); ?>>Logout</option>
                    <option value="create" <?php echo e(request('action') == 'create' ? 'selected' : ''); ?>>Input Data</option>
                    <option value="update" <?php echo e(request('action') == 'update' ? 'selected' : ''); ?>>Ubah Data</option>
                    <option value="delete" <?php echo e(request('action') == 'delete' ? 'selected' : ''); ?>>Hapus Data</option>
                    <option value="verify" <?php echo e(request('action') == 'verify' ? 'selected' : ''); ?>>Verifikasi</option>
                    <option value="assign" <?php echo e(request('action') == 'assign' ? 'selected' : ''); ?>>Penugasan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Cari Keterangan</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                    placeholder="Kata kunci..."
                    class="w-full border-2 pl-2 py-2 mt-2 border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-1">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>
                <?php if(auth()->user()->role === 'manajer'): ?>
                <a href="<?php echo e(route('admin.audit.export', request()->all())); ?>" target="_blank"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-1" title="Export PDF">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
                <?php endif; ?>
                <a href="<?php echo e(route('admin.audit.index')); ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800">Riwayat Aktivitas</h3>
                <p class="text-xs text-gray-400 mt-0.5"><?php echo e($logs->total()); ?> entri ditemukan</p>
            </div>
            <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg">
                <i class="fa-solid fa-shield-halved text-green-500 mr-1"></i> Read-Only — Tidak dapat diubah
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Pengguna</th>
                        <th class="px-6 py-3">Aktivitas</th>
                        <th class="px-6 py-3">Modul</th>
                        <th class="px-6 py-3">Keterangan</th>
                        <th class="px-6 py-3">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $actionConfig = [
                                'login'   => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'label' => 'LOGIN',     'rowClass' => ''],
                                'logout'  => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'label' => 'LOGOUT',    'rowClass' => ''],
                                'create'  => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'label' => 'CREATE',    'rowClass' => ''],
                                'update'  => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'UPDATE',    'rowClass' => ''],
                                'delete'  => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'DELETE',    'rowClass' => 'border-l-4 border-red-400'],
                                'verify'  => ['bg' => 'bg-teal-100',   'text' => 'text-teal-700',   'label' => 'VERIFY',    'rowClass' => ''],
                                'assign'  => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'ASSIGN',    'rowClass' => ''],
                            ][$log->action] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => strtoupper($log->action), 'rowClass' => ''];
                        ?>
                        <tr class="hover:bg-gray-50/80 transition <?php echo e($actionConfig['rowClass']); ?>">
                            <td class="px-6 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">
                                <div class="font-medium text-gray-700"><?php echo e($log->created_at->format('d M Y')); ?></div>
                                <div class="text-[10px] text-gray-400"><?php echo e($log->created_at->format('H:i:s')); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($log->user): ?>
                                    <div class="flex items-center gap-3">
                                        <?php
                                            $roleColor = match(strtolower($log->user->role ?? '')) {
                                                'admin'   => 'bg-purple-100 text-purple-600',
                                                'teknisi' => 'bg-blue-100 text-blue-600',
                                                'manajer' => 'bg-emerald-100 text-emerald-600',
                                                default   => 'bg-slate-100 text-slate-600'
                                            };
                                            $initials = collect(explode(' ', $log->user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                                        ?>
                                        <div class="w-8 h-8 rounded-full shrink-0 <?php echo e($roleColor); ?> flex items-center justify-center font-bold text-[10px] shadow-sm overflow-hidden ring-2 ring-white">
                                            <?php if($log->user->avatar): ?>
                                                <img src="<?php echo e(asset('storage/' . $log->user->avatar)); ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?php echo e($initials); ?>

                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800 text-xs"><?php echo e($log->user->name); ?></div>
                                            <div class="text-[10px] text-gray-400 capitalize"><?php echo e($log->user->role); ?></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic">Sistem</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="<?php echo e($actionConfig['bg']); ?> <?php echo e($actionConfig['text']); ?> px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wider">
                                    <?php echo e($actionConfig['label']); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded-md"><?php echo e($log->module); ?></span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-700 max-w-xs">
                                <?php echo e($log->description); ?>

                            </td>
                            <td class="px-6 py-4 text-[10px] font-mono text-gray-400">
                                <?php echo e($log->ip_address ?? '-'); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <i class="fa-solid fa-clipboard-list text-4xl opacity-30"></i>
                                    <p class="font-medium">Belum ada log aktivitas yang tercatat.</p>
                                    <p class="text-xs">Log akan muncul seiring penggunaan sistem.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    
        <div class="px-6 py-4 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <span class="text-xs text-gray-500">
                Menampilkan <span class="font-bold text-gray-700"><?php echo e($logs->firstItem() ?? 0); ?></span>
                hingga <span class="font-bold text-gray-700"><?php echo e($logs->lastItem() ?? 0); ?></span>
                dari <span class="font-bold text-gray-700"><?php echo e($logs->total()); ?></span> log
            </span>

            <div class="flex items-center gap-2">
                
                <?php if($logs->onFirstPage()): ?>
                    <span class="w-8 h-8 flex items-center justify-center rounded-md text-gray-300 bg-gray-50 border border-gray-100 cursor-not-allowed" title="Halaman Pertama">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </span>
                <?php else: ?>
                    <a href="<?php echo e($logs->previousPageUrl()); ?>" class="w-8 h-8 flex items-center justify-center rounded-md text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:text-blue-600 transition shadow-sm" title="Halaman Sebelumnya">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </a>
                <?php endif; ?>

                
                <span class="text-xs text-gray-600 px-3 font-medium bg-gray-50 py-1.5 rounded-md border border-gray-100">
                    Hal <?php echo e($logs->currentPage()); ?> / <?php echo e($logs->lastPage()); ?>

                </span>

                
                <?php if($logs->hasMorePages()): ?>
                    <a href="<?php echo e($logs->nextPageUrl()); ?>" class="w-8 h-8 flex items-center justify-center rounded-md text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 hover:text-blue-600 transition shadow-sm" title="Halaman Selanjutnya">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </a>
                <?php else: ?>
                    <span class="w-8 h-8 flex items-center justify-center rounded-md text-gray-300 bg-gray-50 border border-gray-100 cursor-not-allowed" title="Halaman Terakhir">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Documents\tugas kuliah\TA\asset-monitoring\resources\views/admin/audit/index.blade.php ENDPATH**/ ?>