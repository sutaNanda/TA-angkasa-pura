<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('logo.svg')); ?>">
    
    <title>Lupa Password - AviaTrack</title>

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center p-4">

    
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">


        
        <div class="p-8">
            <div class="mb-6 text-center">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Lupa Password?</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Masukkan alamat email Anda yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang password Anda.</p>
            </div>

            <form action="<?php echo e(route('password.email')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                
                <div class="mb-6">
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email"
                            class="w-full pl-10 pr-4 py-2.5 mb-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 bg-red-50 text-red-900 <?php else: ?> border-gray-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            placeholder="nama@email.com"
                            value="<?php echo e(old('email')); ?>"
                            required autofocus>
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-xs mt-1 font-semibold"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>Kirim Link Reset</span>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>

            
            <div class="mt-8 mb-4 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
            </div>

            
            <div class="mt-6 text-center">
                <a href="<?php echo e(route('login')); ?>" class="text-sm font-bold text-blue-600 hover:text-blue-700 hover:underline inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Login
                </a>
            </div>

            <div class="mt-6 text-center">
                <p class="text-xs text-slate-400 font-medium">
                    &copy; <?php echo e(date('Y')); ?> AviaTrack. 
                </p>
            </div>
        </div>
    </div>

    
    <script>
        window.addEventListener('load', function() {
            <?php if($errors->any()): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: "<?php echo e($errors->first()); ?>",
                    confirmButtonColor: '#2563eb'
                });
            <?php endif; ?>

            <?php if(session('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "<?php echo e(session('success')); ?>",
                    showConfirmButton: false,
                    timer: 3000,
                    toast: true,
                    position: 'top'
                });
            <?php endif; ?>
        });
    </script>

    <style>
        /* Animasi Masuk Halus */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</body>
</html>
<?php /**PATH C:\Users\User\Documents\tugas kuliah\TA\asset-monitoring\resources\views/auth/passwords/email.blade.php ENDPATH**/ ?>