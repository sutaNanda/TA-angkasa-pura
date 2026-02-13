@extends('layouts.admin')

@section('title', 'Tambah Aset')
@section('page-title', 'Tambah Aset Baru')

@section('content')
    <div class="max-w-4xl mx-auto">

        <a href="{{ route('admin.assets.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-blue-600 mb-4 transition text-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Aset
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="#" method="POST"> @csrf

                <h3 class="text-lg font-bold text-gray-800 border-b pb-4 mb-6">Formulir Aset</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Aset <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Contoh: Laptop Dell Latitude">
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Serial Number / Kode Barang</label>
                        <input type="text" name="serial_number" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Contoh: SN-2929102">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category_id" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="1">AC Split</option>
                            <option value="2">Komputer</option>
                            <option value="3">Genset</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi Penempatan <span class="text-red-500">*</span></label>
                        <select name="location_id" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                            <option value="">-- Pilih Lokasi --</option>
                            <option value="1">Gedung A - Lt. 1</option>
                            <option value="2">Gedung B - Ruang Server</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pembelian</label>
                        <input type="date" name="purchase_date" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status Awal</label>
                        <select name="status" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500 text-sm bg-white">
                            <option value="normal">Normal (Siap Pakai)</option>
                            <option value="warning">Warning (Butuh Cek)</option>
                            <option value="broken">Rusak</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Aset</label>
                        <div class="flex items-center justify-center w-full">
                            <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <i class="fa-solid fa-cloud-arrow-up text-2xl text-gray-400 mb-2"></i>
                                    <p class="text-xs text-gray-500">Klik untuk upload atau drag and drop</p>
                                    <p class="text-xs text-gray-400">(SVG, PNG, JPG or GIF)</p>
                                </div>
                                <input id="dropzone-file" type="file" class="hidden" />
                            </label>
                        </div>
                    </div>

                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="reset" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-600 text-sm hover:bg-gray-50 transition">
                        Reset
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                        Simpan Aset
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection
