<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('logo.svg')); ?>">
    
    <title>Login - AviaTrack</title>

    
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center p-4">

    
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">

        
        <div class="bg-blue-600 p-8 text-center tracking-widest">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white p-2 mb-4 shadow-lg">
                <img src="<?php echo e(asset('logo.svg')); ?>" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h2 class="text-4xl font-bold text-white tracking-tight">AviaTrack</h2>
        </div>

        
        <div class="p-8">
            <form action="<?php echo e(route('login.post')); ?>" method="POST" x-data="{ loading: false }" @submit="loading = true">
                <?php echo csrf_field(); ?> 

                
                <div class="mb-5">
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                    <div class="relative ">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email"
                            class="w-full pl-10 pr-4 py-2.5 mb-1.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition <?php $__errorArgs = ['email'];
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

                
                <div class="mb-6" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="password" id="password"
                            class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="••••••••"
                            required>
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none" tabindex="-1">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-6">
                    <!-- <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        <label for="remember" class="ml-2 text-sm font-medium text-gray-700">Ingat Saya</label>
                    </div> -->
                    <a href="<?php echo e(route('password.request')); ?>" class="text-sm font-bold text-blue-600 hover:text-blue-700 hover:underline">
                        Lupa Password?
                    </a>
                </div>

                
                <button type="submit" 
                        :disabled="loading" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed">
                    <span x-show="!loading">Masuk Aplikasi</span>
                    <i x-show="!loading" class="fa-solid fa-arrow-right-to-bracket"></i>
                    
                    <span x-show="loading" x-cloak>Sedang Memproses...</span>
                    <i x-show="loading" x-cloak class="fa-solid fa-circle-notch fa-spin"></i>
                </button>
            </form>

            
            <div class="mt-8 mb-4 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
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
            // Jika ada error validasi login (Password/Email Salah)
            <?php if($errors->any()): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: "<?php echo e($errors->first()); ?>", // Mengambil pesan error pertama
                    confirmButtonColor: '#2563eb'
                });
            <?php endif; ?>

            // Jika ada pesan sukses (Misal: Logout berhasil)
            <?php if(session('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "<?php echo e(session('success')); ?>",
                    showConfirmButton: false,
                    timer: 1500,
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
<?php /**PATH C:\Users\User\Documents\tugas kuliah\TA\asset-monitoring\resources\views/auth/login.blade.php ENDPATH**/ ?>