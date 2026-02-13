@extends('layouts.technician')

@section('header')
    <div class="flex items-center gap-3">
        <a href="{{ route('technician.dashboard') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 text-white transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="font-bold text-lg">Scan QR Lokasi</h1>
    </div>
@endsection

@section('content')
    <div class="bg-white p-4 rounded-xl shadow-sm mb-6 text-center">
        <div id="reader" class="w-full rounded-lg overflow-hidden bg-black"></div>
        <p class="text-xs text-gray-500 mt-3">Arahkan kamera ke QR Code pada pintu ruangan.</p>
    </div>

    {{-- Input Manual (Opsional, jika kamera error) --}}
    <div class="text-center">
        <p class="text-xs text-gray-400 mb-2">- atau masukkan kode manual -</p>
        <form id="manualForm" class="flex gap-2">
            <input type="text" id="manualInput" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm uppercase" placeholder="Contoh: LOC-001">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-sm">Cek</button>
        </form>
    </div>

    {{-- Library HTML5-QRCode --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Konfigurasi Scanner
        const html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        // Fungsi jika Scan Berhasil
        const onScanSuccess = (decodedText, decodedResult) => {
            // Hentikan kamera agar tidak scan berkali-kali
            html5QrCode.stop().then(() => {
                checkLocation(decodedText);
            }).catch(err => console.log(err));
        };

        // Mulai Kamera Belakang (environment)
        html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
        .catch(err => {
            console.log("Error starting camera: ", err);
            Swal.fire('Error', 'Gagal membuka kamera. Pastikan izin kamera aktif.', 'error');
        });

        // Fungsi AJAX Cek ke Server
        function checkLocation(code) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mencari lokasi dengan kode: ' + code,
                didOpen: () => { Swal.showLoading() }
            });

            // Kirim ke Backend Laravel
            fetch("{{ route('technician.scan.process') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ qr_code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Lokasi Ditemukan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = data.redirect_url;
                    });
                } else {
                    Swal.fire('Gagal', data.message, 'error').then(() => {
                        // Restart kamera jika gagal
                        html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
                    });
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            });
        }

        // Handle Input Manual
        document.getElementById('manualForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let code = document.getElementById('manualInput').value;
            if(code) checkLocation(code);
        });
    </script>
@endsection