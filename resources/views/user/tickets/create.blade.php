@extends('layouts.user')

@section('title', 'Buat Laporan Baru')

@section('content')
<div class="max-w-6xl mx-auto">
    
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="{{ route('user.tickets.index') }}" class="hover:text-blue-600 transition">Riwayat Laporan</a>
        <i class="fa-solid fa-chevron-right text-xs"></i>
        <span class="text-gray-800 font-medium">Buat Laporan Baru</span>
    </div>

    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-blue-600 p-6 text-white">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> Form Laporan Kerusakan
            </h1>
            <p class="text-blue-100 text-sm mt-1">Isi formulir berikut untuk melaporkan kendala di area kantor.</p>
        </div>

        <div class="p-6 md:p-8" x-data="ticketForm()">
            
            @if ($errors->any())
                <div class="mb-6 bg-red-50 p-4 rounded-xl border border-red-200 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 text-lg"></i>
                    <div>
                        <h4 class="text-sm font-bold text-red-800 mb-1">Gagal mengirim laporan</h4>
                        <ul class="list-disc list-inside text-xs text-red-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 p-4 rounded-xl border border-red-200 flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 mt-0.5 text-lg"></i>
                    <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- LOCATION CASCADE --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    
                    {{-- STEP 1: PILIH GEDUNG --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs mr-2">1</span>
                            Gedung / Area
                        </label>
                        <div class="relative mt-2">
                            <i class="fa-solid fa-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            <select x-model="selectedBuilding" @change="onBuildingChange()" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 pr-10 py-2.5 text-sm md:text-base appearance-none bg-white shadow-sm transition-all hover:border-blue-300 cursor-pointer">
                                <option value="">-- Pilih Gedung --</option>
                                @foreach($rootLocations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 2: PILIH LANTAI --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200" :class="!selectedBuilding ? 'opacity-50' : ''">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs mr-2">2</span>
                            Lantai
                        </label>
                        <div class="relative mt-2">
                            <i class="fa-solid fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            <select x-model="selectedFloor" @change="onFloorChange()" :disabled="!selectedBuilding || loadingFloor" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 pr-10 py-2.5 text-sm md:text-base appearance-none bg-white shadow-sm disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed transition-all hover:border-blue-300 cursor-pointer">
                                <option value="">-- Pilih Lantai --</option>
                                <template x-for="floor in floors" :key="floor.id">
                                    <option :value="floor.id" x-text="floor.name"></option>
                                </template>
                            </select>
                            <div x-show="!loadingFloor" class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                            <div x-show="loadingFloor" class="absolute right-3 top-1/2 -translate-y-1/2">
                                <i class="fa-solid fa-circle-notch fa-spin text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 3: PILIH RUANGAN (Muncul dinamis jika lantai punya sub-lokasi) --}}
                    <div x-show="rooms.length > 0" x-transition class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs mr-2">3</span>
                            Ruangan
                        </label>
                        <div class="relative mt-2">
                            <i class="fa-solid fa-door-open absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                            <select x-model="selectedRoom" @change="onRoomChange()" :disabled="loadingRoom" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 pr-10 py-2.5 text-sm md:text-base appearance-none bg-white shadow-sm disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed transition-all hover:border-blue-300 cursor-pointer">
                                <option value="">-- Pilih Ruangan --</option>
                                <template x-for="room in rooms" :key="room.id">
                                    <option :value="room.id" x-text="room.name"></option>
                                </template>
                            </select>
                            <div x-show="!loadingRoom" class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                            <div x-show="loadingRoom" class="absolute right-3 top-1/2 -translate-y-1/2">
                                <i class="fa-solid fa-circle-notch fa-spin text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Placeholder jika step 3 belum muncul (agar grid tetap rapi) --}}
                    <div x-show="rooms.length === 0 && selectedFloor" class="bg-green-50 p-4 rounded-lg border border-green-200 flex items-center gap-3">
                        <i class="fa-solid fa-check-circle text-green-500 text-xl"></i>
                        <div>
                            <p class="text-sm font-bold text-green-700">Lokasi dipilih</p>
                            <p class="text-xs text-green-600">Tidak ada sub-ruangan. Lanjut isi detail di bawah.</p>
                        </div>
                    </div>
                </div>

                {{-- Hidden input: final location_id --}}
                <input type="hidden" name="location_id" :value="finalLocationId">

                {{-- FORM DETAIL (Muncul setelah lokasi final dipilih) --}}
                <div x-show="finalLocationId" x-transition class="space-y-6">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        {{-- Deskripsi Masalah (Span 2) --}}
                        <div class="lg:col-span-2 space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs mr-2"><span x-text="rooms.length > 0 ? '4' : '3'"></span></span>
                                    Deskripsi Masalah <span class="text-red-500">*</span>
                                </label>
                                <textarea name="issue_description" rows="5" required class="w-full border-2 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 p-2" placeholder="Jelaskan kerusakan yang terjadi sedetail mungkin. Contoh: AC di ruangan tidak dingin, lampu mati, dll."></textarea>
                            </div>
                        </div>

                        {{-- Kolom Kanan --}}
                        <div class="space-y-4">
                            
                            {{-- Aset (Opsional) --}}
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <label class="block text-sm font-bold text-gray-700 mb-1">
                                    <i class="fa-solid fa-cube text-blue-400 mr-1"></i>
                                    Aset Bermasalah <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                                </label>
                                <p class="text-xs text-gray-500 mb-2">Jika diketahui, pilih aset spesifik. Jika tidak, teknisi akan mengidentifikasi saat pengecekan.</p>
                                
                                <div class="relative">
                                    <select name="asset_id" x-model="selectedAsset" :disabled="loadingAsset" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2 text-sm appearance-none bg-white shadow-sm disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed transition-all hover:border-blue-300 cursor-pointer">
                                        <option value="">-- Tidak tahu / Belum pasti --</option>
                                        <template x-for="asset in assets" :key="asset.id">
                                            <option :value="asset.id" x-text="(asset.category && asset.category.name === 'Software & Lisensi' ? '[SOFTWARE] ' : '') + asset.name + ' (' + (asset.serial_number || 'No SN') + ')'"></option>
                                        </template>
                                    </select>
                                    <div x-show="loadingAsset" class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <i class="fa-solid fa-circle-notch fa-spin text-blue-600"></i>
                                    </div>
                                </div>
                                <p class="text-[11px] text-blue-500 mt-1 italic" x-show="assets.length > 0" x-text="assets.length + ' aset tersedia di lokasi ini'"></p>
                            </div>

                            {{-- Prioritas --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Tingkat Urgensi</label>
                                <div class="relative">
                                    <i class="fa-solid fa-signal absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                    <select name="priority" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 pr-10 py-2.5 text-sm md:text-base appearance-none bg-white shadow-sm transition-all hover:border-blue-300 cursor-pointer">
                                        <option value="low">Low (Tidak Mendesak)</option>
                                        <option value="medium" selected>Medium (Perlu Perbaikan)</option>
                                        <option value="high">High (Darurat / Kritis)</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                        <i class="fa-solid fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Foto Bukti (Multi) --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Foto Bukti (Opsional, Maks 5)</label>
                                <input type="file" id="ticketFileInput" multiple class="block w-full text-xs text-slate-500
                                    file:mr-4 file:py-2.5 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-xs file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100" accept="image/*" onchange="handleNewPhotos(this)">
                                <div id="ticketPreview" class="mt-3 flex gap-2 flex-wrap"></div>
                            </div>
                        </div>
                    </div>

                    {{-- SUBMIT BUTTON --}}
                    <div class="pt-10 border-t border-gray-100 flex justify-end">
                        <button type="submit" 
                                x-bind:disabled="!finalLocationId"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Laporan
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    // Managed file array for multi-photo
    const pendingPhotos = [];

    function handleNewPhotos(input) {
        if (!input.files || input.files.length === 0) return;
        Array.from(input.files).forEach(file => {
            pendingPhotos.push(file);
            const idx = pendingPhotos.length - 1;
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById('ticketPreview');
                const wrapper = document.createElement('div');
                wrapper.className = 'relative';
                wrapper.id = 'ticket-photo-' + idx;
                wrapper.innerHTML = '<img src="' + e.target.result + '" class="h-16 w-16 object-cover rounded-lg border border-blue-200 shadow-sm">'
                    + '<button type="button" class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm transition" title="Hapus foto ini">'
                    + '<i class="fa-solid fa-xmark"></i></button>';
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
            wrapper.style.transition = 'opacity 0.2s, transform 0.2s';
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
                if (this.rooms.length > 0 && this.selectedRoom) return this.selectedRoom;
                if (this.rooms.length === 0 && this.selectedFloor) return this.selectedFloor;
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
                if (!this.selectedFloor) return;

                this.loadingRoom = true;
                try {
                    const locUrl = "{{ route('user.api.locations', ':id') }}".replace(':id', this.selectedFloor);
                    const locResponse = await fetch(locUrl);
                    const locJson = await locResponse.json();
                    if (locJson.status === 'success' && locJson.data.length > 0) {
                        this.rooms = locJson.data;
                    } else {
                        this.rooms = [];
                        await this.fetchAssets(this.selectedFloor);
                    }
                } catch (error) { console.error(error); }
                finally { this.loadingRoom = false; }
            },

            async onRoomChange() {
                this.selectedAsset = '';
                this.assets = [];
                if (!this.selectedRoom) return;
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
