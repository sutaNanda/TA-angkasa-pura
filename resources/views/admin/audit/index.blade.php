@extends('layouts.admin')

@section('title', 'Log Aktivitas Sistem')
@section('page-title', 'Log Aktivitas Sistem')

@section('content')

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-list-ul text-blue-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Total Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['today'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-right-to-bracket text-green-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Login Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['logins'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-plus text-blue-500 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Input Data</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['creates'] }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-trash text-red-400 text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium">Hapus Data</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['deletes'] }}</p>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full border-2 pl-2 py-2 mt-2 border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Pengguna</label>
                <select name="user_id" class="w-full border-2 pl-2 py-2 mt-2 border-gray-300 rounded-lg text-sm bg-white focus:ring-blue-500">
                    <option value="">Semua Pengguna</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Tipe Aktivitas</label>
                <select name="action" class="w-full border-2 pl-2 py-2 mt-2 border-gray-300 rounded-lg text-sm bg-white focus:ring-blue-500">
                    <option value="">Semua</option>
                    <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Input Data</option>
                    <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Ubah Data</option>
                    <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Hapus Data</option>
                    <option value="verify" {{ request('action') == 'verify' ? 'selected' : '' }}>Verifikasi</option>
                    <option value="assign" {{ request('action') == 'assign' ? 'selected' : '' }}>Penugasan</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Cari Keterangan</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Kata kunci..."
                    class="w-full border-2 pl-2 py-2 mt-2 border-gray-300 rounded-lg text-sm focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="flex-1 bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-1">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>
                @if(auth()->user()->role === 'manajer')
                <a href="{{ route('admin.audit.export', request()->all()) }}" target="_blank"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center justify-center gap-1" title="Export PDF">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
                @endif
                <a href="{{ route('admin.audit.index') }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition" title="Reset Filter">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- LOG TABLE --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800">Riwayat Aktivitas</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $logs->total() }} entri ditemukan</p>
            </div>
            <span class="text-xs text-gray-400 bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg">
                <i class="fa-solid fa-shield-halved text-green-500 mr-1"></i> Read-Only — Tidak dapat diubah
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] font-bold tracking-wider border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Pengguna</th>
                        <th class="px-6 py-3">Aktivitas</th>
                        <th class="px-6 py-3">Modul</th>
                        <th class="px-6 py-3">Keterangan</th>
                        <th class="px-6 py-3">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    @forelse($logs as $log)
                        @php
                            $actionConfig = [
                                'login'   => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'label' => 'LOGIN',     'rowClass' => ''],
                                'logout'  => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'label' => 'LOGOUT',    'rowClass' => ''],
                                'create'  => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'label' => 'CREATE',    'rowClass' => ''],
                                'update'  => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'UPDATE',    'rowClass' => ''],
                                'delete'  => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'DELETE',    'rowClass' => 'border-l-4 border-red-400'],
                                'verify'  => ['bg' => 'bg-teal-100',   'text' => 'text-teal-700',   'label' => 'VERIFY',    'rowClass' => ''],
                                'assign'  => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'ASSIGN',    'rowClass' => ''],
                            ][$log->action] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => strtoupper($log->action), 'rowClass' => ''];
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition {{ $actionConfig['rowClass'] }}">
                            <td class="px-6 py-4 text-xs font-mono text-gray-500 whitespace-nowrap">
                                <div class="font-medium text-gray-700">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($log->user)
                                    <div class="flex items-center gap-3">
                                        @php
                                            $roleColor = match(strtolower($log->user->role ?? '')) {
                                                'admin'   => 'bg-purple-100 text-purple-600',
                                                'teknisi' => 'bg-blue-100 text-blue-600',
                                                'manajer' => 'bg-emerald-100 text-emerald-600',
                                                default   => 'bg-slate-100 text-slate-600'
                                            };
                                            $initials = collect(explode(' ', $log->user->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                                        @endphp
                                        <div class="w-8 h-8 rounded-full shrink-0 {{ $roleColor }} flex items-center justify-center font-bold text-[10px] shadow-sm overflow-hidden ring-2 ring-white">
                                            @if($log->user->avatar)
                                                <img src="{{ asset('storage/' . $log->user->avatar) }}" class="w-full h-full object-cover">
                                            @else
                                                {{ $initials }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800 text-xs">{{ $log->user->name }}</div>
                                            <div class="text-[10px] text-gray-400 capitalize">{{ $log->user->role }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs italic">Sistem</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="{{ $actionConfig['bg'] }} {{ $actionConfig['text'] }} px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wider">
                                    {{ $actionConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded-md">{{ $log->module }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-700 max-w-xs">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-4 text-[10px] font-mono text-gray-400">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <i class="fa-solid fa-clipboard-list text-4xl opacity-30"></i>
                                    <p class="font-medium">Belum ada log aktivitas yang tercatat.</p>
                                    <p class="text-xs">Log akan muncul seiring penggunaan sistem.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-500">
                Menampilkan {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log
            </span>
            {{ $logs->links() }}
        </div>
    </div>

@endsection
