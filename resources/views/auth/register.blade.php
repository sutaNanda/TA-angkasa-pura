<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    {{-- Favicon --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}">
    
    <title>Register - AviaTrack</title>

    {{-- Memuat CSS & JS via Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Alpine.js for Password Toggle --}}
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    {{-- Container Card --}}
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in my-8">

        {{-- Header / Logo --}}
        <div class="bg-blue-600 p-8 text-center uppercase tracking-widest">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white p-2 mb-4 shadow-lg">
                <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight">AviaTrack</h2>
            <p class="text-blue-100 text-xs mt-1 font-medium italic opacity-80">Pendaftaran Karyawan</p>
        </div>

        {{-- Form Register --}}
        <div class="p-8">
            
            @if(session('error'))
                <div class="bg-red-50 text-red-600 outline outline-1 outline-red-200 text-sm font-semibold p-3 rounded-lg mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">
                @csrf

                {{-- Input Nama --}}
                <div class="mb-5">
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" name="name" id="name"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('name') border-red-500 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="Contoh: Budi Santoso"
                            value="{{ old('name') }}"
                            required autofocus>
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Email --}}
                <div class="mb-5">
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" name="email" id="email"
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('email') border-red-500 bg-red-50 @else border-gray-300 @enderror"
                            placeholder="nama@angkasapura.co.id"
                            value="{{ old('email') }}"
                            required>
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Divisi --}}
                <div class="mb-5">
                    <label for="division" class="block text-sm font-bold text-gray-700 mb-2">Divisi / Unit</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <select name="division" id="division" required 
                            class="w-full pl-10 pr-4 py-2 border rounded-lg appearance-none bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('division') border-red-500 bg-red-50 @else border-gray-300 @enderror">
                            <option value="" disabled selected>-- Pilih Divisi --</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division }}" {{ old('division') == $division ? 'selected' : '' }}>{{ $division }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    @error('division')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Password --}}
                <div class="mb-5" x-data="{ show: false }">
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="password" id="password"
                            class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('password') border-red-500 bg-red-50 @enderror"
                            placeholder="••••••••"
                            required>
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none" tabindex="-1">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input Konfirmasi Password --}}
                <div class="mb-8" x-data="{ show: false }">
                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-check-double"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" id="password_confirmation"
                            class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            placeholder="••••••••"
                            required>
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none" tabindex="-1">
                            <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                {{-- Tombol Daftar --}}
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Daftar Sekarang</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:text-blue-800 transition">Login sekarang</a>
                </p>
            </div>
        </div>
    </div>

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
