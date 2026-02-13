@extends('layouts.admin')

@section('title', 'Data Lokasi')
@section('page-title', 'Master Lokasi Penempatan')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div class="relative">
            <input type="text" placeholder="Cari lokasi..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 w-64 text-sm">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
        </div>
        <button onclick="openModal('addLocationModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-plus"></i> Tambah Lokasi
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs">
                <tr>
                    <th class="px-6 py-4 w-10">No</th>
                    <th class="px-6 py-4">Nama Lokasi</th>
                    <th class="px-6 py-4">Keterangan</th>
                    <th class="px-6 py-4 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">1</td>
                    <td class="px-6 py-4 font-bold text-gray-800">Gedung A - Lantai 1</td>
                    <td class="px-6 py-4 text-gray-500">Area Lobby dan Resepsionis</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="openEditLocation('1', 'Gedung A - Lantai 1', 'Area Lobby dan Resepsionis')" class="text-yellow-500 hover:text-yellow-600">
                                <i class="fa-solid fa-pen-to-square text-lg"></i>
                            </button>
                            <button class="text-red-500 hover:text-red-600">
                                <i class="fa-solid fa-trash text-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">2</td>
                    <td class="px-6 py-4 font-bold text-gray-800">Ruang Server Utama</td>
                    <td class="px-6 py-4 text-gray-500">Akses terbatas (Fingerprint)</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="openEditLocation('2', 'Ruang Server Utama', 'Akses terbatas')" class="text-yellow-500 hover:text-yellow-600">
                                <i class="fa-solid fa-pen-to-square text-lg"></i>
                            </button>
                            <button class="text-red-500 hover:text-red-600">
                                <i class="fa-solid fa-trash text-lg"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="addLocationModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('addLocationModal')"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                <form action="#" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Lokasi Baru</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi <span class="text-red-500">*</span></label>
                                <input type="text" name="name" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Gedung B">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Deskripsi</label>
                                <textarea name="description" rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Area parkir belakang"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                        <button type="button" onclick="closeModal('addLocationModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="editLocationModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('editLocationModal')"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                <form action="#" method="POST" id="editLocationForm">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Lokasi</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
                                <input type="text" name="name" id="edit_location_name" class="w-full border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                                <textarea name="description" id="edit_location_desc" rows="3" class="w-full border-gray-300 rounded-lg text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 sm:ml-3 sm:w-auto sm:text-sm">Update</button>
                        <button type="button" onclick="closeModal('editLocationModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        
        function openEditLocation(id, name, desc) {
            document.getElementById('edit_location_name').value = name;
            document.getElementById('edit_location_desc').value = desc;
            openModal('editLocationModal');
        }
    </script>
@endsection