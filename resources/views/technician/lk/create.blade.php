@extends('layouts.technician')

@section('content')
    {{-- Info Alert --}}
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-6 flex gap-3">
        <i class="fa-solid fa-exclamation-triangle text-orange-600 text-xl mt-0.5"></i>
        <div>
            <p class="text-sm font-bold text-orange-800">Masalah Ditemukan</p>
            <p class="text-xs text-orange-700 mt-1">Silakan isi detail masalah yang ditemukan pada aset ini.</p>
        </div>
    </div>

    {{-- Asset Info --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border-l-4 border-red-500">
        <div class="flex items-start gap-3">
            <div class="w-12 h-12 rounded-lg bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-cube text-xl"></i>
            </div>
            <div class="flex-1">
                <h2 class="font-bold text-gray-800 text-base">{{ $asset->name }}</h2>
                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $asset->serial_number ?? '-' }}</p>
                <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                    <i class="fa-solid fa-location-dot text-orange-400"></i>
                    <span>{{ $asset->location->name }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('technician.lk.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="asset_id" value="{{ $asset->id }}">
        @if($patrolLog)
            <input type="hidden" name="patrol_log_id" value="{{ $patrolLog->id }}">
        @endif

        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-24">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-sm">Detail Masalah</h3>
            </div>

            <div class="p-4 space-y-4">
                {{-- Issue Description --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">
                        Deskripsi Masalah <span class="text-red-500">*</span>
                    </label>
                    <textarea name="issue_description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 text-sm" placeholder="Jelaskan masalah yang ditemukan secara detail..." required>{{ old('issue_description') }}</textarea>
                    @error('issue_description')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Priority --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">
                        Prioritas <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="relative">
                            <input type="radio" name="priority" value="low" class="peer sr-only" {{ old('priority') === 'low' ? 'checked' : '' }}>
                            <div class="cursor-pointer border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 transition">
                                <i class="fa-solid fa-circle text-blue-500 text-xs mb-1"></i>
                                <p class="text-xs font-bold text-gray-700">Rendah</p>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="priority" value="medium" class="peer sr-only" {{ old('priority') === 'medium' ? 'checked' : '' }} checked>
                            <div class="cursor-pointer border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-yellow-500 peer-checked:bg-yellow-50 transition">
                                <i class="fa-solid fa-circle text-yellow-500 text-xs mb-1"></i>
                                <p class="text-xs font-bold text-gray-700">Sedang</p>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="priority" value="high" class="peer sr-only" {{ old('priority') === 'high' ? 'checked' : '' }}>
                            <div class="cursor-pointer border-2 border-gray-200 rounded-lg p-3 text-center peer-checked:border-red-500 peer-checked:bg-red-50 transition">
                                <i class="fa-solid fa-circle text-red-500 text-xs mb-1"></i>
                                <p class="text-xs font-bold text-gray-700">Tinggi</p>
                            </div>
                        </label>
                    </div>
                    @error('priority')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Photo Upload --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2">
                        Foto Kondisi (Opsional)
                    </label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 transition text-center p-6 cursor-pointer" onclick="document.getElementById('photoInput').click()">
                        <div id="previewContainer" class="hidden">
                            <img id="imgPreview" class="h-40 mx-auto rounded-lg object-cover shadow-sm mb-2">
                            <p class="text-xs text-green-600 font-bold"><i class="fa-solid fa-check"></i> Foto terpilih</p>
                        </div>
                        <div id="uploadPlaceholder">
                            <i class="fa-solid fa-camera text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600 font-medium">Klik untuk ambil/upload foto</p>
                            <p class="text-xs text-gray-400 mt-1">Maksimal 5MB</p>
                        </div>
                        <input type="file" id="photoInput" name="initial_photo" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </div>
                    @error('initial_photo')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Sticky Bottom Button --}}
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] md:relative md:border-0 md:shadow-none md:bg-transparent md:p-0 z-20">
            <button type="submit" class="w-full bg-red-600 text-white font-bold py-3.5 rounded-xl hover:bg-red-700 transition shadow-lg flex items-center justify-center gap-2 active:scale-95">
                <i class="fa-solid fa-paper-plane"></i>
                Buat Laporan Kegiatan
            </button>
        </div>
    </form>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imgPreview').src = e.target.result;
                    document.getElementById('previewContainer').classList.remove('hidden');
                    document.getElementById('uploadPlaceholder').classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
