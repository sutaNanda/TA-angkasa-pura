@auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Konfigurasi Lifetime dari server (dalam milidetik)
            const sessionLifetime = {{ config('session.lifetime') }} * 60 * 1000;
            const warningTime = 5 * 60 * 1000; // Muncul 5 menit sebelum habis
            
            let warningTimer;
            let logoutTimer;

            function startSessionTimers() {
                // Bersihkan timer lama jika ada
                clearTimeout(warningTimer);
                clearTimeout(logoutTimer);

                // Set Timer Warning (Sisa 5 menit)
                warningTimer = setTimeout(showSessionWarning, sessionLifetime - warningTime);

                // Set Timer Auto Logout (Pas habis)
                logoutTimer = setTimeout(forceLogout, sessionLifetime);
            }

            function showSessionWarning() {
                Swal.fire({
                    title: 'Sesi Akan Berakhir!',
                    text: "Sesi Anda akan berakhir dalam 5 menit karena tidak ada aktivitas. Klik 'Lanjutkan' untuk tetap login.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Lanjutkan Sesi',
                    cancelButtonText: 'Logout Sekarang',
                    timer: warningTime, // Tutup otomatis jika waktu habis
                    timerProgressBar: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        extendSession();
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        document.getElementById('logout-form').submit();
                    }
                });
            }

            function extendSession() {
                fetch("{{ route('session.keep-alive') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        startSessionTimers(); // Reset timer
                        Swal.fire({
                            icon: 'success',
                            title: 'Sesi Diperpanjang',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                })
                .catch(error => {
                    console.error('Gagal memperpanjang sesi:', error);
                    // Jika gagal (misal koneksi putus), jangan reset timer, biarkan logout jalan
                });
            }

            function forceLogout() {
                window.location.reload(); // Reload akan memaksa redirect ke login
            }

            // Jalankan timer saat load
            startSessionTimers();
        });
    </script>
@endauth
