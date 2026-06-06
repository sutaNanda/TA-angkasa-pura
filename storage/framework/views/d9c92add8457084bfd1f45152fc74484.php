

<?php $__env->startSection('title', 'Profil Saya'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-2xl shadow-sm border-l-4 border-blue-500">
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1">Total Laporan</p>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-black text-gray-900"><?php echo e($totalTickets); ?></span>
                <i class="fa-solid fa-list-check text-blue-100 text-3xl"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border-l-4 border-green-500">
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1">Bulan Ini</p>
            <div class="flex items-center justify-between">
                <span class="text-2xl font-black text-gray-900"><?php echo e($ticketsThisMonth); ?></span>
                <i class="fa-solid fa-calendar-check text-green-100 text-3xl"></i>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-circle-user text-blue-500"></i> Informasi Pribadi
            </h3>
        </div>
        
        <form action="<?php echo e(route('user.profile.update')); ?>" method="POST" enctype="multipart/form-data" class="p-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <div class="flex flex-col md:flex-row gap-8">
                
                <div class="flex flex-col items-center space-y-4">
                    <div class="relative group">
                        <?php if($user->avatar): ?>
                            <img src="<?php echo e(asset('storage/' . $user->avatar)); ?>" id="avatar-preview" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                        <?php else: ?>
                            <div id="avatar-preview-fallback" class="w-32 h-32 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-4xl font-bold border-4 border-white shadow-lg ">
                                <?php echo e(substr($user->name, 0, 1)); ?>

                            </div>
                            <img src="" id="avatar-preview" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg hidden">
                        <?php endif; ?>
                        
                        
                        <label for="avatarImage" class="absolute inset-0 bg-black/50 text-white rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <i class="fa-solid fa-camera text-2xl mb-1"></i>
                            <span class="text-xs font-medium">Ubah Foto</span>
                        </label>
                    </div>
                    
                    <div>
                        <input type="file" name="avatar" id="avatarImage" class="hidden" accept="image/jpeg,image/png,image/jpg">
                        <label for="avatarImage" class="cursor-pointer text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-4 py-2 rounded-xl border border-blue-100 transition inline-block">
                            <i class="fa-solid fa-upload mr-1"></i> Pilih File
                        </label>
                    </div>
                    <p class="text-[10px] text-gray-400 text-center max-w-[150px]">Format JPG/PNG. Maksimal 2MB.</p>
                </div>

                
                <div class="flex-1 space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                        <div class="relative border-2 border-gray-200 rounded-xl">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-user text-gray-400"></i>
                            </div>
                            <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" class="pl-10 w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 transition text-sm py-2" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Alamat Email</label>
                        <div class="relative border-2 border-gray-200 rounded-xl">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="pl-10 w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 transition text-sm py-2" required>
                        </div>
                    </div>

                    <div class="pt-1 ml-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl shadow-md cursor-pointer flex items-center gap-2 text-sm">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-red-500"></i> Keamanan Akun
            </h3>
        </div>

        <form action="<?php echo e(route('user.profile.password')); ?>" method="POST" class="p-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="space-y-5 max-w-2xl">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Password Saat Ini</label>
                    <div class="relative border-2 border-gray-200 rounded-xl">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="current_password" class="pl-10 w-full rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 transition text-sm py-2" required placeholder="Masukkan password lama">
                    </div>
                    <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div x-data="{ password: '' }">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Password Baru</label>
                    <div class="relative border-2 border-gray-200 rounded-xl mb-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-key text-gray-400"></i>
                        </div>
                        <input x-model="password" type="password" name="new_password" class="pl-10 w-full rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 transition text-sm py-2" required placeholder="Minimal 8 karakter + simbol">
                    </div>
                    
                    
                    <div x-show="password.length > 0" x-cloak class="bg-gray-50 p-3 rounded-lg border border-gray-200 mb-2">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Syarat Keamanan:</p>
                        <ul class="text-[11px] space-y-1">
                            <li :class="password.length >= 8 ? 'text-green-600 font-semibold' : 'text-red-500 font-medium'">
                                <i class="fa-solid" :class="password.length >= 8 ? 'fa-check' : 'fa-xmark'"></i> Minimal 8 karakter
                            </li>
                            <li :class="(/[A-Z]/.test(password) && /[a-z]/.test(password)) ? 'text-green-600 font-semibold' : 'text-red-500 font-medium'">
                                <i class="fa-solid" :class="(/[A-Z]/.test(password) && /[a-z]/.test(password)) ? 'fa-check' : 'fa-xmark'"></i> Huruf Besar & Kecil
                            </li>
                            <li :class="/[0-9]/.test(password) ? 'text-green-600 font-semibold' : 'text-red-500 font-medium'">
                                <i class="fa-solid" :class="/[0-9]/.test(password) ? 'fa-check' : 'fa-xmark'"></i> Mengandung Angka
                            </li>
                            <li :class="/[^A-Za-z0-9]/.test(password) ? 'text-green-600 font-semibold' : 'text-red-500 font-medium'">
                                <i class="fa-solid" :class="/[^A-Za-z0-9]/.test(password) ? 'fa-check' : 'fa-xmark'"></i> Simbol Kreatif (!@# dll)
                            </li>
                        </ul>
                    </div>

                    <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Konfirmasi Password Baru</label>
                    <div class="relative border-2 border-gray-200 rounded-xl">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-check-double text-gray-400"></i>
                        </div>
                        <input type="password" name="new_password_confirmation" class="pl-10 w-full rounded-xl border-gray-200 focus:border-red-500 focus:ring-red-500 transition text-sm py-2" required placeholder="Ketik ulang password baru">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-xl shadow-md cursor-pointer flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Preview Avatar Image before upload
    document.getElementById('avatarImage').addEventListener('change', function(e) {
        if(e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatar-preview');
                const fallback = document.getElementById('avatar-preview-fallback');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                
                if(fallback) {
                    fallback.classList.add('hidden');
                }
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\User\Documents\tugas kuliah\TA\asset-monitoring\resources\views/user/profile/index.blade.php ENDPATH**/ ?>