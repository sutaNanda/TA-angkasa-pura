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
            <p class="text-blue-100 text-sm mt-1">Isi formulir berikut untuk melaporkan kendala pada aset kantor.</p>
        </div>

        <div class="p-6 md:p-8" 
             x-data="ticketForm()" 
             x-init="$watch('selectedBuilding', value => fetchRooms(value)); $watch('selectedRoom', value => fetchAssets(value))">
            
            <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- DESKTOP GRID: Lokasi, Ruangan, Aset (3 Kolom) --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {{-- STEP 1: PILIH GEDUNG --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 h-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs mr-2">1</span>
                            Gedung / Area
                        </label>
                        <div class="relative">
                            <select x-model="selectedBuilding" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 h-11">
                                <option value="">-- Pilih Gedung --</option>
                                @foreach($rootLocations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-building absolute left-3 top-4 text-gray-400"></i>
                        </div>
                    </div>

                    {{-- STEP 2: PILIH RUANGAN --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 h-full" :class="!selectedBuilding ? 'opacity-50' : ''">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs mr-2">2</span>
                            Ruangan / Lantai
                        </label>
                        
                        <div class="relative">
                            <select x-model="selectedRoom" :disabled="!selectedBuilding || loadingRoom" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 h-11 disabled:bg-gray-100 transition">
                                <option value="">-- Pilih Ruangan --</option>
                                <template x-for="room in rooms" :key="room.id">
                                    <option :value="room.id" x-text="room.name"></option>
                                </template>
                            </select>
                            <i class="fa-solid fa-door-open absolute left-3 top-4 text-gray-400"></i>
                            
                            <div x-show="loadingRoom" class="absolute right-3 top-3">
                                <i class="fa-solid fa-circle-notch fa-spin text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 3: PILIH ASET --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 h-full" :class="!selectedRoom ? 'opacity-50' : ''">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs mr-2">3</span>
                            Aset Bermasalah
                        </label>
                        
                        <div class="relative">
                            <select name="asset_id" x-model="selectedAsset" :disabled="!selectedRoom || loadingAsset" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 h-11 disabled:bg-gray-100 transition">
                                <option value="">-- Pilih Aset --</option>
                                <template x-for="asset in assets" :key="asset.id">
                                    <option :value="asset.id" x-text="asset.name + ' (' + asset.serial_number + ')'"></option>
                                </template>
                            </select>
                            <i class="fa-solid fa-cube absolute left-3 top-4 text-gray-400"></i>
                            
                            <div x-show="loadingAsset" class="absolute right-3 top-3">
                                <i class="fa-solid fa-circle-notch fa-spin text-blue-600"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-1" x-text="selectedRoom ? (assets.length + ' aset tersedia.') : '-'"></p>
                    </div>

                </div>

                {{-- DESKTOP GRID: Deskripsi & detail lain --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-5" x-show="selectedAsset" x-transition>
                    
                    {{-- STEP 4: Deskripsi (Span 2) --}}
                    <div class="lg:col-span-2 space-y-4">
                         <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 inline-flex items-center justify-center text-xs mr-2">4</span>
                                Deskripsi Masalah
                            </label>
                            <textarea name="issue_description" rows="5" required class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder:text-gray-400 p-2" placeholder="Jelaskan kerusakan yang terjadi sedetail mungkin..."></textarea>
                        </div>
                    </div>

                    {{-- Detail Tambahan (Span 1) --}}
                    <div class="space-y-6">
                        {{-- Prioritas --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tingkat Urgensi</label>
                            <div class="relative">
                                <select name="priority" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 h-11">
                                    <option value="low">Low (Tidak Mendesak)</option>
                                    <option value="medium" selected>Medium (Perlu Perbaikan)</option>
                                    <option value="high">High (Darurat / Kritis)</option>
                                </select>
                                <i class="fa-solid fa-signal absolute left-3 top-3.5 text-gray-400"></i>
                            </div>
                        </div>

                        {{-- Foto Bukti --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Foto Bukti (Opsional)</label>
                            <div class="relative ">
                                <input type="file" name="photo" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg h-9 pt-1.5 pl-2">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUBMIT BUTTON --}}
                <div class="pt-10 border-t border-gray-100 flex justify-end">
                    <button type="submit" 
                            x-bind:disabled="!selectedAsset"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Laporan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    function ticketForm() {
        return {
            selectedBuilding: '',
            selectedRoom: '',
            selectedAsset: '',
            rooms: [],
            assets: [],
            loadingRoom: false,
            loadingAsset: false,

            // Fetch Rooms saat Gedung dipilih
            async fetchRooms(buildingId) {
                this.selectedRoom = '';
                this.selectedAsset = '';
                this.assets = [];
                this.rooms = [];
                
                if (!buildingId) return;

                this.loadingRoom = true;
                try {
                    const url = "{{ route('user.api.locations', ':id') }}".replace(':id', buildingId);
                    const response = await fetch(url);
                    const json = await response.json();
                    
                    if (json.status === 'success') {
                        this.rooms = json.data;
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'Gagal memuat data ruangan.', 'error');
                } finally {
                    this.loadingRoom = false;
                }
            },

            // Fetch Assets saat Ruangan dipilih
            async fetchAssets(roomId) {
                this.selectedAsset = '';
                this.assets = [];
                
                if (!roomId) return;

                this.loadingAsset = true;
                try {
                    const url = "{{ route('user.api.assets', ':id') }}".replace(':id', roomId);
                    const response = await fetch(url);
                    const json = await response.json();
                    
                    if (json.status === 'success') {
                        this.assets = json.data;
                    } 
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                } finally {
                    this.loadingAsset = false;
                }
            }
        }
    }
</script>
@endsection
