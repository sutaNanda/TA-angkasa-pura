<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Asset Monitoring</title>

    {{-- Memuat CSS & JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Alpine.js for Password Toggle --}}
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center p-4">

    {{-- Container Card --}}
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">

        {{-- Header / Logo --}}
        <div class="bg-blue-600 p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 text-white mb-4 backdrop-blur-sm">
                <i class="fa-solid fa-cube text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-white">Selamat Datang</h2>
            <p class="text-blue-100 text-sm mt-1">Sistem Monitoring Aset</p>
        </div>

        {{-- Form Login --}}
        <div class="p-8">
            <form action="{{ route('login.post') }}" method="POST">
                @csrf {{-- Wajib ada untuk keamanan Laravel --}}

                {{-- Input Email --}}
                <div class="mb-5">
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email"
                            class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('email') border-red-500 bg-red-50 text-red-900 @else border-gray-300 @enderror"
                            placeholder="nama@email.com"
                            value="{{ old('email') }}"
                            required autofocus>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Password --}}
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

                {{-- Ingat Saya --}}
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center text-sm text-gray-600 cursor-pointer hover:text-gray-800">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition">
                        <span class="ml-2">Ingat Saya</span>
                    </label>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-bold hidden">Lupa Password?</a>
                </div>

                {{-- Tombol Login --}}
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2">
                    <span>Masuk Aplikasi</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-xs text-gray-400">
                    &copy; {{ date('Y') }} Asset Management System. <br>PT Angkasa Pura Indonesia.
                </p>
            </div>
        </div>
    </div>

    {{-- Script SweetAlert (Menggunakan Window Load agar aman) --}}
    <script>
        window.addEventListener('load', function() {
            // Jika ada error validasi login (Password/Email Salah)
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: "{{ $errors->first() }}", // Mengambil pesan error pertama
                    confirmButtonColor: '#2563eb'
                });
            @endif

            // Jika ada pesan sukses (Misal: Logout berhasil)
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 1500,
                    toast: true,
                    position: 'top'
                });
            @endif
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
