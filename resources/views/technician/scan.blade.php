<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Scanner - Asset Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-black h-screen w-screen overflow-hidden flex flex-col relative">

    <div class="absolute top-0 left-0 right-0 p-4 z-20 flex justify-between items-center text-white">
        <a href="{{ route('technician.dashboard') }}" class="w-10 h-10 flex items-center justify-center bg-black/40 rounded-full backdrop-blur-sm">
            <i class="fa-solid fa-xmark text-xl"></i>
        </a>
        <div class="bg-black/40 px-3 py-1 rounded-full backdrop-blur-sm text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-bolt text-yellow-400"></i> Flash Off
        </div>
    </div>

    <div class="flex-1 relative flex items-center justify-center bg-gray-900">
        <div class="absolute inset-0 opacity-30 bg-[url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop')] bg-cover bg-center"></div>
        
        <div class="relative w-64 h-64 border-2 border-blue-500 rounded-3xl shadow-[0_0_0_9999px_rgba(0,0,0,0.7)] z-10 flex items-center justify-center">
            <div class="absolute top-0 left-0 right-0 h-0.5 bg-blue-500 shadow-[0_0_15px_rgba(59,130,246,1)] animate-[scan_2s_infinite]"></div>
            
            <p class="absolute -bottom-12 text-white text-sm font-medium text-center w-full opacity-80">
                Arahkan kamera ke QR Code Aset
            </p>
        </div>
    </div>

    <div class="absolute bottom-10 left-0 right-0 px-6 z-30">
        <a href="{{ route('technician.maintenance.form') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl text-center shadow-lg transition transform active:scale-95">
            <i class="fa-solid fa-qrcode mr-2"></i> [SIMULASI] QR Terbaca!
        </a>
        <p class="text-gray-500 text-[10px] text-center mt-2">*Klik tombol di atas untuk simulasi scan sukses</p>
    </div>

    <style>
        @keyframes scan {
            0% { top: 0; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
    </style>
</body>
</html>