@extends('layouts.admin')

@section('title', 'Tambah Aturan PM')

@section('content')
<div class="container-fluid px-4 py-6 max-w-4xl">
    <div class="mb-6">
        <a href="{{ route('admin.plans.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Aturan
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Tambah Aturan Maintenance Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Buat aturan yang berlaku untuk semua aset dalam kategori</p>
    </div>

    <form action="{{ route('admin.plans.store') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        @csrf
        
        {{-- Category Selection --}}
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Pilih Kategori <span class="text-red-500">*</span>
            </label>
            <select name="category_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Aturan ini akan berlaku untuk SEMUA aset dalam kategori ini</p>
        </div>

        {{-- Checklist Template Selection --}}
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Template Checklist <span class="text-red-500">*</span>
            </label>
            <select name="checklist_template_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">-- Pilih Template --</option>
                @foreach($templates as $template)
                    <option value="{{ $template->id }}" {{ old('checklist_template_id') == $template->id ? 'selected' : '' }}>
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
            <div class="grid grid-cols-4 gap-4">
                <label class="flex items-center gap-2 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                    <input type="radio" name="frequency" value="daily" {{ old('frequency') == 'daily' ? 'checked' : '' }} required class="w-4 h-4 text-blue-600">
                    <div>
                        <p class="font-bold text-gray-800">Harian</p>
                        <p class="text-xs text-gray-500">Setiap hari</p>
                    </div>
                </label>
                <label class="flex items-center gap-2 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-500 transition">
                    <input type="radio" name="frequency" value="weekly" {{ old('frequency') == 'weekly' ? 'checked' : '' }} required class="w-4 h-4 text-purple-600">
                    <div>
                        <p class="font-bold text-gray-800">Mingguan</p>
                        <p class="text-xs text-gray-500">Pilih hari</p>
                    </div>
                </label>
                <label class="flex items-center gap-2 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-orange-500 transition">
                    <input type="radio" name="frequency" value="monthly" {{ old('frequency') == 'monthly' ? 'checked' : '' }} required class="w-4 h-4 text-orange-600">
                    <div>
                        <p class="font-bold text-gray-800">Bulanan</p>
                        <p class="text-xs text-gray-500">Pilih tanggal</p>
                    </div>
                </label>
                <label class="flex items-center gap-2 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-500 transition">
                    <input type="radio" name="frequency" value="yearly" {{ old('frequency') == 'yearly' ? 'checked' : '' }} required class="w-4 h-4 text-red-600">
                    <div>
                        <p class="font-bold text-gray-800">Tahunan</p>
                        <p class="text-xs text-gray-500">Pilih tanggal</p>
                    </div>
                </label>
            </div>
            @error('frequency')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Start Date --}}
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Tanggal Mulai / Referensi <span class="text-red-500">*</span>
            </label>
            <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2">
            <p class="text-xs text-gray-500 mt-1">
                <strong>Harian:</strong> Diabaikan (jalan setiap hari)<br>
                <strong>Mingguan:</strong> Menentukan hari dalam seminggu (misal: Senin)<br>
                <strong>Bulanan:</strong> Menentukan tanggal (misal: tanggal 1, 15, 31)<br>
                <strong>Tahunan:</strong> Menentukan tanggal dan bulan (misal: 1 Januari)
            </p>
            @error('start_date')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Notes --}}
        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">
                Catatan (Opsional)
            </label>
            <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
        </div>

        {{-- Active Status --}}
        <div class="mb-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                <span class="text-sm font-bold text-gray-700">Aktifkan aturan ini</span>
            </label>
        </div>

        {{-- Submit Buttons --}}
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition">
                <i class="fa-solid fa-save"></i> Simpan Aturan
            </button>
            <a href="{{ route('admin.plans.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-bold transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
