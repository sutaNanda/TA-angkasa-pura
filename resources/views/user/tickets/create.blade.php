@extends('layouts.user')

@section('title', 'Buat Laporan Baru')

@section('content')
<div class="max-w-6xl mx-auto">
    
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('user.tickets.index') }}" class="hover:text-blue-600 transition font-medium">Riwayat Laporan</a>
        <i class="fa-solid fa-chevron-right text-[10px]"></i>
        <span class="text-gray-800 font-bold">Buat Laporan Baru</span>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden relative">
        
        {{-- Decorative SVG Background --}}
        <div class="absolute top-0 left-0 w-full h-36 bg-gradient-to-r from-blue-700 via-indigo-600 to-blue-800 z-0">
            <svg class="absolute inset-0 w-full h-full object-cover opacity-20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,160L80,144C160,128,320,96,480,106.7C640,117,800,171,960,176C1120,181,1280,139,1360,117.3L1440,96L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z"></path>
            </svg>
        </div>
        
        <div class="px-6 md:px-10 pt-8 pb-6 relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-4 py-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-blue-900 rounded-full text-xs font-semibold backdrop-blur-sm mb-3">
                    <i class="fa-solid fa-flag text-blue-800"></i> Sistem Pelaporan
                </span>
                <h1 class="text-2xl md:text-3xl text-blue-900 font-extrabold flex items-center gap-3">
                    Form Laporan Kerusakan
                </h1>
                <p class="text-blue-900 text-sm mt-2 opacity-90 max-w-2xl leading-relaxed">Bantu kami menjaga fasilitas tetap prima. Isi formulir berikut untuk melaporkan kendala di area operasional atau aset perusahaan secara spesifik.</p>
            </div>
        </div>

        <div class="p-6 md:p-10 relative z-10 bg-white" x-data="ticketForm()">
            
            @if ($errors->any())
                <div class="mb-8 bg-red-50 p-5 rounded-xl border border-red-200 flex items-start gap-4 shadow-sm animate-fade-in">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 text-red-500">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-red-800 mb-1">Peringatan: Gagal mengirim laporan</h4>
                        <ul class="list-disc list-inside text-xs text-red-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-8 bg-red-50 p-5 rounded-xl border border-red-200 flex items-start gap-4 shadow-sm animate-fade-in">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 text-red-500">
                        <i class="fa-solid fa-circle-exclamation text-xl"></i>
                    </div>
                    <p class="text-sm text-red-800 font-medium self-center">{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                {{-- SECTION 1: LOKASI --}}
                <div>
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4 border-b border-gray-100 pb-3">
                        <i class="fa-solid fa-map-location-dot text-blue-600"></i> 1. Identifikasi Titik Lokasi
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        
                        {{-- STEP 1: PILIH GEDUNG --}}
                        <div class="group relative bg-white border-2 border-gray-100 rounded-xl p-5 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-black shadow-sm border border-blue-100">1</span>
                                Gedung / Area
                            </label>
                            <div class="relative">
                                <select x-model="selectedBuilding" @change="onBuildingChange()" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-11 pr-10 py-3 text-sm font-medium appearance-none bg-gray-50 hover:bg-white shadow-inner transition-all cursor-pointer px-2">
                                    <option value="">Pilih Gedung</option>
                                    @foreach($rootLocations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 2: PILIH LANTAI --}}
                        <div class="group relative bg-white border-2 border-gray-100 rounded-xl p-5 transition-all duration-300" :class="!selectedBuilding ? 'opacity-40 grayscale pointer-events-none' : 'hover:border-blue-300 hover:shadow-md'">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-black shadow-sm border border-blue-100">2</span>
                                Lantai / Level <span class="text-[10px] font-medium text-gray-400 ml-1">(opsional)</span>
                            </label>
                            <div class="relative">
                                <select x-model="selectedFloor" @change="onFloorChange()" :disabled="!selectedBuilding || loadingFloor" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-11 pr-10 py-3 text-sm font-medium appearance-none bg-gray-50 hover:bg-white disabled:bg-gray-100 disabled:text-gray-400 disabled:shadow-none shadow-inner transition-all cursor-pointer px-2">
                                    <option value="">Area Umum Gedung / Pilih Lantai</option>
                                    <template x-for="floor in floors" :key="floor.id">
                                        <option :value="floor.id" x-text="floor.name"></option>
                                    </template>
                                </select>
                                <div x-show="!loadingFloor" class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                </div>
                                <div x-show="loadingFloor" class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        {{-- STEP 3: PILIH RUANGAN (Muncul dinamis) --}}
                        <div x-show="rooms.length > 0" x-transition class="group relative bg-white border-2 border-gray-100 rounded-xl p-5 hover:border-blue-300 hover:shadow-md transition-all duration-300">
                            <label class="block text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-black shadow-sm border border-blue-100">3</span>
                                Ruangan Spesifik <span class="text-[10px] font-medium text-gray-400 ml-1">(opsional)</span>
                            </label>
                            <div class="relative">
                                <select x-model="selectedRoom" @change="onRoomChange()" :disabled="loadingRoom" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-11 pr-10 py-3 text-sm font-medium appearance-none bg-gray-50 hover:bg-white disabled:bg-gray-100 disabled:text-gray-400 disabled:shadow-none shadow-inner transition-all cursor-pointer px-2">
                                    <option value="">Area Umum Lantai</option>
                                    <template x-for="room in rooms" :key="room.id">
                                        <option :value="room.id" x-text="room.name"></option>
                                    </template>
                                </select>
                                <div x-show="!loadingRoom" class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                </div>
                                <div x-show="loadingRoom" class="absolute right-4 top-1/2 -translate-y-1/2">
                                    <i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Status Terpilih --}}
                        <div x-show="finalLocationId" x-transition class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-5 flex items-center gap-4 shadow-sm">
                            <div class="w-12 h-12 rounded-full bg-green-100 text-green-500 flex items-center justify-center text-xl flex-shrink-0 shadow-inner">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-green-800">Titik Terkunci</p>
                                <p class="text-[11px] font-medium text-green-600 mt-0.5 leading-tight">Lokasi dipilih. Lanjut ke rincian di bawah.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="location_id" :value="finalLocationId">

                {{-- SECTION 2: FORM DETAIL --}}
                <div x-show="finalLocationId" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="space-y-6 pt-2">
                    
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4 border-b border-gray-100 pb-3">
                            <i class="fa-solid fa-clipboard-question text-blue-600"></i> 2. Rincian Kendala & Bukti
                        </h3>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                        {{-- Kiri: Deskripsi Masalah (Bigger column) --}}
                        <div class="lg:col-span-7 space-y-6">
                            
                            {{-- Textarea Info Laporan --}}
                            <div class="group bg-white rounded-xl border-2 border-gray-100 focus-within:border-blue-400 focus-within:ring-4 focus-within:ring-blue-50 transition-all p-1">
                                <div class="px-4 pt-3 pb-1">
                                    <label class="block text-sm font-bold text-gray-700">
                                        Deksripsi Masalah <span class="text-red-500">*</span>
                                    </label>
                                    <p class="text-xs text-gray-400 mt-0.5 mb-2">Ceritakan kronologi, titik tepat kendala di dalam ruangan (opsional jika besar), dan tanda-tanda kerusakan.</p>
                                </div>
                                <textarea name="issue_description" rows="5" required class="w-full placeholder:text-gray-300 text-sm font-medium bg-transparent px-4 pb-4 resize-y py-2" placeholder="Cth: AC dinding paling pojok dekat jendela meneteskan air terus menerus dan suhunya tidak dingin lagi walau sudah diremote ke 16 derajat."></textarea>
                            </div>

                            {{-- Optional Asset Selection --}}
                            <div class="bg-indigo-50/50 p-5 rounded-xl border border-indigo-100">
                                <label class="block text-sm font-bold text-indigo-900 mb-1 flex items-center gap-2">
                                    <i class="fa-solid fa-cube text-indigo-500"></i>
                                    Spesifikasi Aset <span class="text-[10px] bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-md ml-2 font-semibold">TENTATIF</span>
                                </label>
                                <p class="text-xs font-medium text-indigo-400 mb-4">Jika Anda mengetahui objek pastinya, pilih aset terkait agar mempermudah riwayat perawatan. Jika Anda tidak tahu barangnya, biarkan kosong.</p>
                                
                                <div class="relative">
                                    <select name="asset_id" x-model="selectedAsset" :disabled="loadingAsset" class="w-full border-indigo-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-3 pl-4 pr-10 text-sm font-medium appearance-none bg-white shadow-sm disabled:opacity-60 transition-all cursor-pointer">
                                        <option value="">Lainnya / Tidak Tahu</option>
                                        <template x-for="asset in assets" :key="asset.id">
                                            <option :value="asset.id" x-text="(asset.category && asset.category.name === 'Software & Lisensi' ? '[SOFTWARE] ' : '') + asset.name + ' (' + (asset.serial_number || 'Tanpa SN') + ')'"></option>
                                        </template>
                                    </select>
                                    <div x-show="loadingAsset" class="absolute right-4 top-1/2 -translate-y-1/2">
                                        <i class="fa-solid fa-circle-notch fa-spin text-indigo-600"></i>
                                    </div>
                                    <div x-show="!loadingAsset" class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-indigo-400">
                                        <i class="fa-solid fa-caret-down text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kanan: Prioritas & Upload (Smaller column) --}}
                        <div class="lg:col-span-5 space-y-6">
                            
                            {{-- Prioritas --}}
                            <div class="bg-white border-2 border-gray-100 rounded-xl p-5 hover:border-gray-200 transition-all">
                                <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-fire text-orange-500"></i> Tingkat Urgensi
                                </label>
                                <div class="relative group">
                                    <select name="priority" class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 pl-4 pr-10 py-3 text-sm font-bold text-gray-700 appearance-none bg-gray-50 hover:bg-white shadow-inner transition-all cursor-pointer group-hover:border-orange-300">
                                        <option value="low">🟡 Low (Tidak Terlalu Mendesak)</option>
                                        <option value="medium" selected>🟠 Medium (Perlu Perbaikan Standar)</option>
                                        <option value="high">🔴 High (Darurat / Operasional Terhenti)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Foto Bukti (Multi) Drag n Drop Style --}}
                            <div class="bg-white border-2 border-gray-100 rounded-xl p-5 hover:border-blue-300 transition-all">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-bold text-gray-700 flex items-center gap-2">
                                        <i class="fa-solid fa-camera text-blue-500"></i> Foto Temuan Kendala
                                    </label>
                                    <span class="text-[10px] font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded">Maks 5</span>
                                </div>
                                <p class="text-xs text-gray-400 mb-4 font-medium">Bantu teknisi mengidentifikasi alat dengan bukti foto.</p>
                                
                                <div class="relative w-full border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-blue-50 hover:border-blue-400 transition cursor-pointer group" onclick="document.getElementById('ticketFileInput').click()">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center group-hover:bg-blue-100 transition-colors mb-3">
                                            <i class="fa-solid fa-cloud-arrow-up text-xl text-gray-400 group-hover:text-blue-600 transition"></i>
                                        </div>
                                        <p class="text-xs font-bold text-gray-700 group-hover:text-blue-700">Pilih dari galeri / Kamera</p>
                                        <p class="text-[10px] text-gray-400 mt-1">PNG, JPG, JPEG (Max. 2MB)</p>
                                    </div>
                                    <input type="file" id="ticketFileInput" multiple class="hidden" accept="image/*" onchange="handleNewPhotos(this)">
                                </div>
                                
                                <div id="ticketPreview" class="mt-4 flex gap-3 flex-wrap"></div>
                            </div>

                        </div>
                    </div>

                    {{-- SUBMIT BUTTON --}}
                    <div class="pt-8 mt-4 flex justify-end">
                        <button type="submit" 
                                x-bind:disabled="!finalLocationId"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:-translate-y-0.5 transition-all flex items-center gap-3 disabled:opacity-50 disabled:hover:translate-y-0 disabled:shadow-none disabled:cursor-not-allowed">
                            Kirim Laporan Kerusakan <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.4s ease-out; }
</style>

<script>
    const pendingPhotos = [];

    function handleNewPhotos(input) {
        if (!input.files || input.files.length === 0) return;
        
        const maxFiles = 5;
        const currentCount = pendingPhotos.filter(f => f !== null).length;
        
        if (currentCount + input.files.length > maxFiles) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Foto',
                text: `Anda hanya bisa mengunggah maksimal ${maxFiles} foto pembuktian.`
            });
            input.value = '';
            return;
        }

        Array.from(input.files).forEach(file => {
            pendingPhotos.push(file);
            const idx = pendingPhotos.length - 1;
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById('ticketPreview');
                const wrapper = document.createElement('div');
                wrapper.className = 'relative group animate-fade-in';
                wrapper.id = 'ticket-photo-' + idx;
                wrapper.innerHTML = `
                    <div class="h-20 w-20 rounded-xl overflow-hidden border-2 border-gray-200 shadow-sm relative">
                        <img src="${e.target.result}" class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <button type="button" class="w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm transform scale-75 group-hover:scale-100 transition" title="Hapus foto ini">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                `;
                wrapper.querySelector('button').addEventListener('click', () => removePhoto(idx));
                container.appendChild(wrapper);
            }
            reader.readAsDataURL(file);
        });
        input.value = '';
    }

    function removePhoto(idx) {
        pendingPhotos[idx] = null;
        const wrapper = document.getElementById('ticket-photo-' + idx);
        if (wrapper) {
            wrapper.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
            wrapper.style.opacity = '0';
            wrapper.style.transform = 'scale(0.8)';
            setTimeout(() => wrapper.remove(), 200);
        }
    }

    // Intercept form submission to inject managed photos
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[enctype="multipart/form-data"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const files = pendingPhotos.filter(f => f !== null);
                if (files.length > 0) {
                    const dt = new DataTransfer();
                    files.forEach(f => dt.items.add(f));
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'file';
                    hiddenInput.name = 'photos[]';
                    hiddenInput.multiple = true;
                    hiddenInput.files = dt.files;
                    hiddenInput.style.display = 'none';
                    form.appendChild(hiddenInput);
                }
            });
        }
    });

    function ticketForm() {
        return {
            selectedBuilding: '',
            selectedFloor: '',
            selectedRoom: '',
            selectedAsset: '',
            floors: [],
            rooms: [],
            assets: [],
            loadingFloor: false,
            loadingRoom: false,
            loadingAsset: false,

            get finalLocationId() {
                if (this.selectedRoom) return this.selectedRoom;
                if (this.selectedFloor) return this.selectedFloor;
                if (this.selectedBuilding) return this.selectedBuilding;
                return '';
            },

            async onBuildingChange() {
                this.selectedFloor = '';
                this.selectedRoom = '';
                this.selectedAsset = '';
                this.floors = [];
                this.rooms = [];
                this.assets = [];
                if (!this.selectedBuilding) return;

                this.loadingFloor = true;
                try {
                    const url = "{{ route('user.api.locations', ':id') }}".replace(':id', this.selectedBuilding);
                    const response = await fetch(url);
                    const json = await response.json();
                    if (json.status === 'success') { this.floors = json.data; }
                    
                    await this.fetchAssets(this.selectedBuilding);
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'Gagal memuat data lantai.', 'error');
                } finally { this.loadingFloor = false; }
            },

            async onFloorChange() {
                this.selectedRoom = '';
                this.selectedAsset = '';
                this.rooms = [];
                this.assets = [];
                if (!this.selectedFloor) {
                    if (this.selectedBuilding) this.fetchAssets(this.selectedBuilding);
                    return;
                }

                this.loadingRoom = true;
                try {
                    const locUrl = "{{ route('user.api.locations', ':id') }}".replace(':id', this.selectedFloor);
                    const locResponse = await fetch(locUrl);
                    const locJson = await locResponse.json();
                    if (locJson.status === 'success' && locJson.data.length > 0) {
                        this.rooms = locJson.data;
                    } else {
                        this.rooms = [];
                    }
                    await this.fetchAssets(this.selectedFloor);
                } catch (error) { console.error(error); }
                finally { this.loadingRoom = false; }
            },

            async onRoomChange() {
                this.selectedAsset = '';
                this.assets = [];
                if (!this.selectedRoom) {
                    if (this.selectedFloor) this.fetchAssets(this.selectedFloor);
                    return;
                }
                await this.fetchAssets(this.selectedRoom);
            },

            async fetchAssets(locationId) {
                this.loadingAsset = true;
                try {
                    const url = "{{ route('user.api.assets', ':id') }}".replace(':id', locationId);
                    const response = await fetch(url);
                    const json = await response.json();
                    if (json.status === 'success') { this.assets = json.data; }
                } catch (error) { console.error(error); }
                finally { this.loadingAsset = false; }
            }
        }
    }
</script>
@endsection
