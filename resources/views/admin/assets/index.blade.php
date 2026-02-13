@extends('layouts.admin')

@section('title', 'Data Aset')
@section('page-title', 'Manajemen Data Aset')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex gap-2 w-full md:w-auto">
            <div class="relative">
                <input type="text" placeholder="Cari aset..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-64 text-sm">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
            </div>
        </div>

        <button onclick="openModal('addAssetModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition shadow-sm">
            <i class="fa-solid fa-plus"></i>
            Tambah Aset
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs">
                    <tr>
                        <th class="px-6 py-4">Informasi Aset</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Lokasi</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded bg-gray-200 flex items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">AC Server Lt.1</p>
                                    <p class="text-xs text-gray-500">SN: AC-2023-001</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">Elektronik</td>
                        <td class="px-6 py-4">Ruang Server A</td>
                        <td class="px-6 py-4">
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">Normal</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="openEditModal('AC Server Lt.1', 'AC-2023-001')" class="w-8 h-8 rounded flex items-center justify-center bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="w-8 h-8 rounded flex items-center justify-center bg-red-100 text-red-600 hover:bg-red-200 transition">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="addAssetModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('addAssetModal')"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">
                <form action="#" method="POST"> @csrf

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tambah Aset Baru</h3>
                        <button type="button" onclick="closeModal('addAssetModal')" class="text-gray-400 hover:text-gray-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aset</label>
                                <input type="text" name="name" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Laptop Dell">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                                <input type="text" name="serial_number" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="SN-XXXX">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <select name="category_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Pilih Kategori</option>
                                    <option value="1">AC Split</option>
                                    <option value="2">Genset</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                <select name="location_id" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Pilih Lokasi</option>
                                    <option value="1">Gedung A</option>
                                    <option value="2">Gedung B</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Beli</label>
                                <input type="date" name="purchase_date" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Awal</label>
                                <select name="status" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="normal">Normal</option>
                                    <option value="warning">Warning</option>
                                    <option value="broken">Rusak</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Data
                        </button>
                        <button type="button" onclick="closeModal('addAssetModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editAssetModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('editAssetModal')"></div>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">
                <form action="#" method="POST" id="editForm">
                    @csrf
                    @method('PUT')

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Data Aset</h3>
                        <button type="button" onclick="closeModal('editAssetModal')" class="text-gray-400 hover:text-gray-500">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Aset</label>
                                <input type="text" name="name" id="edit_name" class="w-full border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                                <input type="text" name="serial_number" id="edit_serial" class="w-full border-gray-300 rounded-lg text-sm">
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-red-500 italic">* Fitur edit lengkap akan aktif saat data database masuk.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 sm:ml-3 sm:w-auto sm:text-sm">
                            Update Perubahan
                        </button>
                        <button type="button" onclick="closeModal('editAssetModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Buka Modal
        function openModal(modalID) {
            document.getElementById(modalID).classList.remove('hidden');
        }

        // Fungsi Tutup Modal
        function closeModal(modalID) {
            document.getElementById(modalID).classList.add('hidden');
        }

        // Fungsi Buka Modal Edit & Isi Data Dummy
        // Nanti parameter ini diganti dengan data asli dari database
        function openEditModal(name, serial) {
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_serial').value = serial;
            openModal('editAssetModal');
        }
    </script>
@endsection
