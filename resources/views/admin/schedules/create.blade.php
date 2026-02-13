@extends('layouts.admin')

@section('title', 'Tambah Jadwal Maintenance')

@section('content')
<div class="container-fluid px-4 py-6 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.schedules.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Jadwal
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Jadwal Maintenance Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Assign checklist template ke aset dengan frekuensi tertentu</p>
    </div>

    <form action="{{ route('admin.schedules.store') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        @csrf
        
        {{-- Asset Selection --}}
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Pilih Aset <span class="text-red-500">*</span>
            </label>
            <select name="asset_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Pilih Aset --</option>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}">
                        {{ $asset->name }} ({{ $asset->category->name ?? 'No Category' }}) - {{ $asset->serial_number ?? 'No SN' }}
                    </option>
                @endforeach
            </select>
            @error('asset_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Checklist Template Selection --}}
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Template Checklist <span class="text-red-500">*</span>
            </label>
            <select name="checklist_template_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Pilih Template --</option>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}">
                        {{ $template->name }} ({{ $template->frequency ?? 'No Frequency' }})
                    </option>
                @endforeach
            </select>
            @error('checklist_template_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Frequency --}}
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Frekuensi <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-3 gap-4">
                <label class="flex items-center gap-2 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="frequency" value="daily" required class="w-4 h-4 text-blue-600">
                    <div>
                        <p class="font-bold text-gray-800">Harian</p>
                        <p class="text-xs text-gray-500">Setiap hari</p>
                    </div>
                </label>
                <label class="flex items-center gap-2 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 transition">
                    <input type="radio" name="frequency" value="weekly" required class="w-4 h-4 text-purple-600">
                    <div>
                        <p class="font-bold text-gray-800">Mingguan</p>
                        <p class="text-xs text-gray-500">Pilih hari</p>
                    </div>
                </label>
                <label class="flex items-center gap-2 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-orange-500 transition">
                    <input type="radio" name="frequency" value="monthly" required class="w-4 h-4 text-orange-600">
                    <div>
                        <p class="font-bold text-gray-800">Bulanan</p>
                        <p class="text-xs text-gray-500">Pilih tanggal</p>
                    </div>
                </label>
            </div>
            @error('frequency')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Day of Week (for weekly) --}}
        <div id="weeklyOptions" class="mb-6 hidden">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Hari dalam Seminggu
            </label>
            <select name="day_of_week" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <option value="">-- Pilih Hari --</option>
                <option value="1">Senin</option>
                <option value="2">Selasa</option>
                <option value="3">Rabu</option>
                <option value="4">Kamis</option>
                <option value="5">Jumat</option>
                <option value="6">Sabtu</option>
                <option value="7">Minggu</option>
            </select>
        </div>

        {{-- Day of Month (for monthly) --}}
        <div id="monthlyOptions" class="mb-6 hidden">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Tanggal dalam Bulan
            </label>
            <select name="day_of_month" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                <option value="">-- Pilih Tanggal --</option>
                @for($i = 1; $i <= 31; $i++)
                    <option value="{{ $i }}">Tanggal {{ $i }}</option>
                @endfor
            </select>
        </div>

        {{-- Preferred Time (Optional) --}}
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Waktu Preferensi (Opsional)
            </label>
            <input type="time" name="preferred_time" class="w-full border border-gray-300 rounded-lg px-4 py-2">
            <p class="text-xs text-gray-500 mt-1">Waktu yang disarankan untuk melakukan maintenance</p>
        </div>

        {{-- Active Status --}}
        <div class="mb-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 rounded">
                <span class="text-sm font-bold text-gray-700">Aktifkan jadwal ini</span>
            </label>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition">
                <i class="fa-solid fa-save"></i> Simpan Jadwal
            </button>
            <a href="{{ route('admin.schedules.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-bold transition">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
// Show/hide day selection based on frequency
document.querySelectorAll('input[name="frequency"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('weeklyOptions').classList.add('hidden');
        document.getElementById('monthlyOptions').classList.add('hidden');
        
        if (this.value === 'weekly') {
            document.getElementById('weeklyOptions').classList.remove('hidden');
        } else if (this.value === 'monthly') {
            document.getElementById('monthlyOptions').classList.remove('hidden');
        }
    });
});
</script>
@endsection
