<?php $__env->startSection('title', 'Grup Teknisi'); ?>
<?php $__env->startSection('page-title', 'Manajemen Grup Teknisi'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-6 w-full mx-auto max-w-full">
    
    
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">Grup Teknisi</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola grup/tim teknisi untuk penugasan terjadwal dan kolaborasi.</p>
        </div>
    </div>

    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-6 gap-4">
        <div class="relative w-full xl:w-auto flex-1 max-w-md">
            
        </div>

        
        <div class="flex flex-wrap sm:flex-nowrap gap-3 w-full xl:w-auto shrink-0">
            <?php if(!auth()->user()->isManajer()): ?>
            <a href="<?php echo e(route('admin.groups.create')); ?>" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                <i class="fa-solid fa-plus"></i>
                <span class="whitespace-nowrap">Tambah Grup Baru</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden w-full max-w-full">
        <div class="w-full overflow-x-auto relative custom-scrollbar">
            <table class="min-w-max w-full text-sm text-left text-gray-600 border-collapse">
                <thead class="bg-gray-50/80 text-gray-500 uppercase tracking-wider text-[11px] font-bold border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-12 text-center whitespace-nowrap">No</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap min-w-[200px]">Nama Grup</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Deskripsi</th>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Jumlah Anggota</th>
                        <th scope="col" class="px-6 py-4 whitespace-nowrap">Dibuat Pada</th>
                        <?php if(!auth()->user()->isManajer()): ?>
                        <th scope="col" class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/80 transition-colors duration-150 group-row">
                            <td class="px-6 py-4 text-center font-medium text-gray-400 text-xs whitespace-nowrap">
                                <?php echo e(($groups->currentPage() - 1) * $groups->perPage() + $loop->iteration); ?>

                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <span class="<?php echo e($group->badge_class ?? 'bg-blue-100 text-blue-700 border-blue-200'); ?> px-3 py-1.5 rounded-full text-xs font-bold border inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-users"></i> <?php echo e($group->name); ?>

                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-xs text-gray-500 truncate max-w-xs" title="<?php echo e($group->description); ?>">
                                    <?php echo e($group->description ?: '—'); ?>

                                </p>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-bold text-xs ring-1 ring-inset ring-gray-200">
                                    <?php echo e($group->members_count ?? 0); ?>

                                </span>
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap font-medium">
                                <?php echo e($group->created_at->format('d M Y')); ?>

                            </td>

                            <?php if(!auth()->user()->isManajer()): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('admin.groups.edit', $group->id)); ?>" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm focus:outline-none" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    
                                    <button onclick="confirmDelete('<?php echo e($group->id); ?>', '<?php echo e($group->name); ?>')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-200 transition-all shadow-sm focus:outline-none" title="Hapus Grup">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                    <form id="delete-form-<?php echo e($group->id); ?>" action="<?php echo e(route('admin.groups.destroy', $group->id)); ?>" method="POST" class="hidden">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                    </form>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(!auth()->user()->isManajer() ? '6' : '5'); ?>" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fa-solid fa-users-slash text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="font-bold text-gray-900 mb-1">Tidak ada grup ditemukan</p>
                                    <p class="text-sm text-gray-500">Silakan buat grup baru untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        
        <?php if($groups->hasPages()): ?>
            <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-200 rounded-b-2xl">
                <?php echo e($groups->links()); ?>

            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Grup?',
                html: `Anda yakin ingin menghapus grup <strong>${name}</strong>?<br><br>Anggota yang ada di dalamnya tidak akan terhapus, hanya akan dilepas dari grup ini.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'rounded-xl',
                    cancelButton: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Documents\tugas kuliah\TA\asset-monitoring\resources\views/admin/groups/index.blade.php ENDPATH**/ ?>