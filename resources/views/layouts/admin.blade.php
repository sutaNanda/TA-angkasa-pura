<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - AviaTrack</title>
    
    {{-- Font Awesome (Boleh pakai CDN jika belum install via NPM) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Favicon --}}
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}">

    {{-- VITE: Ini sudah memuat Tailwind CSS & SweetAlert dari 'app.js' --}}
    {{-- Posisi harus DI DALAM <head>, bukan sebagai atribut --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">

        @include('components.admin-sidebar')

        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">

            @include('components.admin-header')

            <main class="w-full flex-grow p-6">
                @yield('content')
            </main>

            <footer class="bg-white p-4 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} AviaTrack
            </footer>
        </div>

    </div>

    {{-- SCRIPT ALERT GLOBAL --}}
    {{-- Kita tidak perlu link CDN SweetAlert lagi di sini karena sudah ada di app.js --}}
    <script>
        // Kita tunggu sampai window load agar 'Swal' dari app.js siap digunakan
        window.addEventListener('load', function() {
            
            // Cek Session Success
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3000,
                    toast: true,
                    position: 'top-end'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                });
            @endif

            // Cek Validation Error
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal!',
                    html: `
                        <ul class="text-left text-sm text-red-600 list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    `,
                });
            @endif

            // Cek Session Info/Message
            @if(session('message'))
                Swal.fire({
                    icon: 'info',
                    title: 'Info',
                    text: "{{ session('message') }}",
                });
            @endif
        });
    </script>
    {{-- Session Warning Component --}}
    <x-session-warning />
</body>
</html>
