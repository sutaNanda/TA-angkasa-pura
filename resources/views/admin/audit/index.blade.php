@extends('layouts.admin')

@section('title', 'Audit System Logs')
@section('page-title', 'Log Aktivitas Sistem')

@section('content')
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal</label>
                <input type="date" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">User / Aktor</label>
                <select class="w-full border-gray-300 rounded-lg text-sm bg-white focus:ring-blue-500">
                    <option value="">Semua User</option>
                    <option value="1">Admin</option>
                    <option value="2">Budi Santoso</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Tipe Aktivitas</label>
                <select class="w-full border-gray-300 rounded-lg text-sm bg-white focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="auth">Login/Logout</option>
                    <option value="create">Input Data (Create)</option>
                    <option value="update">Edit Data (Update)</option>
                    <option value="delete">Hapus Data (Delete)</option>
                </select>
            </div>

            <div>
                <button type="button" class="w-full bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <i class="fa-solid fa-search mr-1"></i> Cari Log
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Aktivitas</th>
                        <th class="px-6 py-4">Modul</th>
                        <th class="px-6 py-4">Detail / Keterangan</th>
                        <th class="px-6 py-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    <tr class="hover:bg-red-50 transition border-l-4 border-red-500">
                        <td class="px-6 py-4 font-mono text-xs">02 Feb 2026<br>15:30:22</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800">Administrator</span>
                            <div class="text-[10px] text-purple-600">Role: Admin</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                DELETE
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium">Data Aset</td>
                        <td class="px-6 py-4 text-gray-800">
                            Menghapus aset <span class="font-mono bg-gray-100 px-1">PC-OLD-01</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400">192.168.1.10</td>
                    </tr>

                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-xs">02 Feb 2026<br>14:00:05</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800">Budi Santoso</span>
                            <div class="text-[10px] text-blue-600">Role: Teknisi</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                LOGIN
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium">Authentication</td>
                        <td class="px-6 py-4 text-gray-800">
                            User berhasil masuk ke sistem
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400">182.253.11.5</td>
                    </tr>

                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-xs">02 Feb 2026<br>14:05:10</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800">Budi Santoso</span>
                            <div class="text-[10px] text-blue-600">Role: Teknisi</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                CREATE
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium">Maintenance</td>
                        <td class="px-6 py-4 text-gray-800">
                            Input Logbook Rutin: <span class="font-mono bg-gray-100 px-1">AC-SRV-01</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400">182.253.11.5</td>
                    </tr>

                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-xs">02 Feb 2026<br>10:15:00</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800">Administrator</span>
                            <div class="text-[10px] text-purple-600">Role: Admin</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                UPDATE
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium">Manajemen User</td>
                        <td class="px-6 py-4 text-gray-800">
                            Mengubah password user: <span class="font-mono bg-gray-100 px-1">agus.setiawan</span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400">192.168.1.10</td>
                    </tr>

                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-xs">02 Feb 2026<br>09:59:00</td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800">Agus Setiawan</span>
                            <div class="text-[10px] text-blue-600">Role: Teknisi</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider">
                                LOGOUT
                            </span>
                        </td>
                        <td class="px-6 py-4 font-medium">Authentication</td>
                        <td class="px-6 py-4 text-gray-800">
                            User keluar dari aplikasi
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400">36.72.11.20</td>
                    </tr>

                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-500">Menampilkan 50 log terakhir</span>
            <div class="flex gap-1">
                <button class="px-3 py-1 border rounded text-xs hover:bg-gray-50">Prev</button>
                <button class="px-3 py-1 border rounded text-xs bg-slate-800 text-white">1</button>
                <button class="px-3 py-1 border rounded text-xs hover:bg-gray-50">Next</button>
            </div>
        </div>
    </div>
@endsection