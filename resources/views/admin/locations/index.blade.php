@extends('layouts.admin')

@section('title', 'Master Lokasi')

@section('content')

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE HEADER                                                  --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Master Lokasi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola hierarki lokasi penempatan aset</p>
    </div>
    <button onclick="openAddModal()"
        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-md shadow-blue-200 transition-all">
        <i class="fa-solid fa-plus"></i>
        Tambah Lokasi
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- STATS ROW                                                    --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6" id="statsRow">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
            <i class="fa-solid fa-building"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Gedung</p>
            <p class="text-xl font-bold text-gray-800" id="count_building">-</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600 flex-shrink-0">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Lantai</p>
            <p class="text-xl font-bold text-gray-800" id="count_floor">-</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
            <i class="fa-solid fa-door-open"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Ruangan</p>
            <p class="text-xl font-bold text-gray-800" id="count_room">-</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600 flex-shrink-0">
            <i class="fa-solid fa-map-location-dot"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Area/Lainnya</p>
            <p class="text-xl font-bold text-gray-800" id="count_area">-</p>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TREE TABLE                                                   --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    {{-- Table Toolbar --}}
    <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700">
            <i class="fa-solid fa-sitemap text-blue-500"></i>
            <span>Daftar Lokasi</span>
            <span id="totalBadge" class="ml-1 bg-blue-50 text-blue-600 text-xs font-bold px-2 py-0.5 rounded-full"></span>
        </div>
        <div class="relative">
            <input type="text" id="searchInput" placeholder="Cari lokasi..." oninput="filterTable(this.value)"
                class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 w-full sm:w-60">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-5 py-3 font-semibold">Nama Lokasi</th>
                    <th class="px-5 py-3 font-semibold">Kode</th>
                    <th class="px-5 py-3 font-semibold">Tipe</th>
                    <th class="px-5 py-3 font-semibold">Level</th>
                    <th class="px-5 py-3 font-semibold">Keterangan</th>
                    <th class="px-5 py-3 font-semibold text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody id="locationTableBody" class="divide-y divide-gray-50">
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 block text-blue-400"></i>
                        <span class="text-sm">Memuat data lokasi...</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL: TAMBAH LOKASI                                         --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="addModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('addModal')"></div>

    {{-- Panel --}}
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-fadeIn">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-location-dot text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Tambah Lokasi Baru</h3>
                        <p class="text-blue-200 text-xs">Isi data lokasi dengan lengkap</p>
                    </div>
                </div>
                <button onclick="closeModal('addModal')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <form id="addLocationForm" novalidate>
                @csrf
                <div class="px-6 py-5 space-y-4">

                    {{-- Nama & Kode --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Nama Lokasi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-building absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                                <input type="text" name="name" id="add_name" required
                                    placeholder="cth: Gedung A, Ruang Server..."
                                    class="w-full pl-8 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Kode <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <input type="text" name="code" id="add_code"
                                placeholder="cth: GD-A"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition font-mono">
                        </div>
                    </div>

                    {{-- Tipe & Parent --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Tipe <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="type" id="add_type" required
                                    class="w-full appearance-none pl-4 pr-8 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white">
                                    <option value="" disabled selected>-- Pilih Tipe --</option>
                                    <option value="building">🏢  Gedung (Building)</option>
                                    <option value="floor">📊  Lantai (Floor)</option>
                                    <option value="room">🚪  Ruangan (Room)</option>
                                    <option value="area">📍  Area / Lainnya</option>
                                    <option value="outdoor">🌿  Outdoor</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Induk Lokasi <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <div class="relative">
                                <select name="parent_id" id="add_parent_id"
                                    class="w-full appearance-none pl-4 pr-8 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white">
                                    <option value="">— Lokasi Utama (Top-level) —</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Deskripsi / Keterangan
                        </label>
                        <textarea name="description" id="add_description" rows="2"
                            placeholder="Informasi tambahan tentang lokasi ini..."
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"></textarea>
                    </div>

                    {{-- Error area --}}
                    <div id="addError" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i>
                        <span id="addErrorMsg"></span>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('addModal')"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit" id="addSubmitBtn"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-md shadow-blue-200 transition flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Lokasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL: EDIT LOKASI                                           --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="editModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('editModal')"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-fadeIn">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Edit Lokasi</h3>
                        <p class="text-amber-100 text-xs" id="editSubtitle">Memperbarui data lokasi</p>
                    </div>
                </div>
                <button onclick="closeModal('editModal')" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editLocationForm" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id">
                <div class="px-6 py-5 space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Nama Lokasi <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fa-solid fa-building absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                                <input type="text" name="name" id="edit_name" required
                                    class="w-full pl-8 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Kode</label>
                            <input type="text" name="code" id="edit_code"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                                Tipe <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="type" id="edit_type" required
                                    class="w-full appearance-none pl-4 pr-8 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition bg-white">
                                    <option value="building">🏢  Gedung (Building)</option>
                                    <option value="floor">📊  Lantai (Floor)</option>
                                    <option value="room">🚪  Ruangan (Room)</option>
                                    <option value="area">📍  Area / Lainnya</option>
                                    <option value="outdoor">🌿  Outdoor</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Induk Lokasi</label>
                            <div class="relative">
                                <select name="parent_id" id="edit_parent_id"
                                    class="w-full appearance-none pl-4 pr-8 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition bg-white">
                                    <option value="">— Lokasi Utama —</option>
                                </select>
                                <i class="fa-solid fa-chevron-down absolute right-3 top-3 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Deskripsi</label>
                        <textarea name="description" id="edit_description" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition resize-none"></textarea>
                    </div>

                    <div id="editError" class="hidden bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i>
                        <span id="editErrorMsg"></span>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-100 transition">
                        Batal
                    </button>
                    <button type="submit" id="editSubmitBtn"
                        class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold shadow-md shadow-amber-200 transition flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Tooltip custom --}}
<style>
    .animate-fadeIn { animation: fadeInScale 0.2s ease-out; }
    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.95) translateY(-8px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .location-indent { padding-left: calc(var(--indent) * 1.25rem); }
</style>

<script>
// ─────────────────────────────────────────────────
// State
// ─────────────────────────────────────────────────
let allLocations = []; // flat list for dropdown
let allFlat = [];      // flat list for table

const TYPE_CONFIG = {
    building : { label: 'Gedung',    icon: 'fa-building',         bg: 'bg-blue-50',   text: 'text-blue-700',   dot: 'bg-blue-500'   },
    floor    : { label: 'Lantai',    icon: 'fa-layer-group',      bg: 'bg-purple-50', text: 'text-purple-700', dot: 'bg-purple-500' },
    room     : { label: 'Ruangan',   icon: 'fa-door-open',        bg: 'bg-emerald-50',text: 'text-emerald-700',dot: 'bg-emerald-500'},
    area     : { label: 'Area',      icon: 'fa-map-location-dot', bg: 'bg-orange-50', text: 'text-orange-700', dot: 'bg-orange-500' },
    outdoor  : { label: 'Outdoor',   icon: 'fa-tree',             bg: 'bg-green-50',  text: 'text-green-700',  dot: 'bg-green-500'  },
};

// ─────────────────────────────────────────────────
// Load Data
// ─────────────────────────────────────────────────
async function loadLocations() {
    try {
        const res = await fetch('{{ route("admin.locations.tree") }}');
        const json = await res.json();
        const tree = json.data || [];

        // Flatten for table rendering (depth-first, with level)
        allFlat = [];
        flattenTree(tree, 0);

        allLocations = allFlat; // same for dropdown (all)

        renderTable(allFlat);
        populateParentDropdowns(allFlat);
        updateStats(allFlat);
    } catch (e) {
        document.getElementById('locationTableBody').innerHTML = `
            <tr><td colspan="6" class="px-5 py-10 text-center text-red-500 text-sm">
                <i class="fa-solid fa-circle-exclamation mr-2"></i> Gagal memuat data. Coba refresh halaman.
            </td></tr>`;
    }
}

function flattenTree(nodes, level) {
    nodes.forEach(node => {
        allFlat.push({ ...node, _level: level });
        if (node.children && node.children.length) {
            flattenTree(node.children, level + 1);
        }
    });
}

// ─────────────────────────────────────────────────
// Render Table
// ─────────────────────────────────────────────────
function renderTable(items) {
    const tbody = document.getElementById('locationTableBody');
    document.getElementById('totalBadge').textContent = items.length + ' Lokasi';

    if (!items.length) {
        tbody.innerHTML = `
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">
                <i class="fa-solid fa-folder-open text-4xl block mb-3 text-gray-300"></i>
                Belum ada data lokasi.
            </td></tr>`;
        return;
    }

    tbody.innerHTML = items.map(loc => {
        const cfg = TYPE_CONFIG[loc.type] || TYPE_CONFIG['area'];
        const indent = loc._level * 20;
        const hasChildren = loc.children && loc.children.length > 0;

        return `
        <tr class="hover:bg-blue-50/30 transition-colors group" data-name="${(loc.name||'').toLowerCase()} ${(loc.code||'').toLowerCase()} ${(loc.description||'').toLowerCase()}">
            <td class="px-5 py-3.5">
                <div style="padding-left: ${indent}px" class="flex items-center gap-2">
                    ${loc._level > 0 ? `<span class="text-gray-300 select-none">└</span>` : ''}
                    <div class="w-7 h-7 rounded-lg ${cfg.bg} ${cfg.text} flex items-center justify-center flex-shrink-0 text-xs">
                        <i class="fa-solid ${cfg.icon}"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm leading-tight">${loc.name}</p>
                        ${hasChildren ? `<p class="text-[10px] text-gray-400 mt-0.5">${loc.children.length} sub-lokasi</p>` : ''}
                    </div>
                </div>
            </td>
            <td class="px-5 py-3.5">
                <code class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-md font-mono">${loc.code || '—'}</code>
            </td>
            <td class="px-5 py-3.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ${cfg.bg} ${cfg.text}">
                    <span class="w-1.5 h-1.5 rounded-full ${cfg.dot} flex-shrink-0"></span>
                    ${cfg.label}
                </span>
            </td>
            <td class="px-5 py-3.5">
                <span class="text-xs text-gray-500">Level ${loc.level ?? loc._level}</span>
            </td>
            <td class="px-5 py-3.5 text-xs text-gray-500 max-w-[200px] truncate">${loc.description || '<span class="text-gray-300 italic">Tidak ada keterangan</span>'}</td>
            <td class="px-5 py-3.5">
                <div class="flex items-center justify-center gap-1">
                    <button onclick="openEditModal(${JSON.stringify(loc).replace(/"/g, '&quot;')})"
                        class="w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-600 flex items-center justify-center transition"
                        title="Edit">
                        <i class="fa-solid fa-pen text-xs"></i>
                    </button>
                    <button onclick="deleteLocation(${loc.id}, '${loc.name?.replace(/'/g, "\\'")}')"
                        class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center transition"
                        title="Hapus">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

// ─────────────────────────────────────────────────
// Stats
// ─────────────────────────────────────────────────
function updateStats(items) {
    const counts = { building: 0, floor: 0, room: 0, area: 0 };
    items.forEach(loc => {
        if (loc.type === 'building') counts.building++;
        else if (loc.type === 'floor') counts.floor++;
        else if (loc.type === 'room') counts.room++;
        else counts.area++;
    });
    document.getElementById('count_building').textContent = counts.building;
    document.getElementById('count_floor').textContent = counts.floor;
    document.getElementById('count_room').textContent = counts.room;
    document.getElementById('count_area').textContent = counts.area;
}

// ─────────────────────────────────────────────────
// Populate Parent Dropdowns
// ─────────────────────────────────────────────────
function populateParentDropdowns(items, excludeId = null) {
    const options = items
        .filter(l => l.id !== excludeId)
        .map(l => `<option value="${l.id}">${'— '.repeat(l._level)}${l.name}</option>`)
        .join('');

    document.getElementById('add_parent_id').innerHTML = '<option value="">— Lokasi Utama (Top-level) —</option>' + options;
    document.getElementById('edit_parent_id').innerHTML = '<option value="">— Lokasi Utama —</option>' + options;
}

// ─────────────────────────────────────────────────
// Filter / Search
// ─────────────────────────────────────────────────
function filterTable(q) {
    const rows = document.querySelectorAll('#locationTableBody tr[data-name]');
    rows.forEach(row => {
        const match = row.dataset.name.includes(q.toLowerCase().trim());
        row.style.display = match ? '' : 'none';
    });
}

// ─────────────────────────────────────────────────
// Modal Helpers
// ─────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openAddModal() {
    document.getElementById('addLocationForm').reset();
    document.getElementById('addError').classList.add('hidden');
    openModal('addModal');
}

function openEditModal(loc) {
    document.getElementById('edit_id').value          = loc.id;
    document.getElementById('edit_name').value        = loc.name || '';
    document.getElementById('edit_code').value        = loc.code || '';
    document.getElementById('edit_type').value        = loc.type || 'area';
    document.getElementById('edit_description').value = loc.description || '';
    document.getElementById('editSubtitle').textContent = loc.name;
    document.getElementById('editError').classList.add('hidden');

    // Re-populate without current item
    populateParentDropdowns(allFlat, loc.id);
    if (loc.parent_id) {
        document.getElementById('edit_parent_id').value = loc.parent_id;
    }
    openModal('editModal');
}

// ─────────────────────────────────────────────────
// AJAX Submit: Add
// ─────────────────────────────────────────────────
document.getElementById('addLocationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('addSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    document.getElementById('addError').classList.add('hidden');

    try {
        const fd = new FormData(this);
        const res = await fetch('{{ route("admin.locations.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: fd
        });
        const data = await res.json();

        if (!res.ok) {
            const msg = data.errors ? Object.values(data.errors).flat().join(' • ') : (data.message || 'Terjadi kesalahan.');
            showError('addError', 'addErrorMsg', msg);
        } else {
            closeModal('addModal');
            await loadLocations();
        }
    } catch {
        showError('addError', 'addErrorMsg', 'Gagal terhubung ke server.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Lokasi';
    }
});

// ─────────────────────────────────────────────────
// AJAX Submit: Edit
// ─────────────────────────────────────────────────
document.getElementById('editLocationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id  = document.getElementById('edit_id').value;
    const btn = document.getElementById('editSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    document.getElementById('editError').classList.add('hidden');

    try {
        const fd = new FormData(this);
        fd.append('_method', 'PUT');
        const res = await fetch(`/admin/locations/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: fd
        });
        const data = await res.json();

        if (!res.ok) {
            const msg = data.errors ? Object.values(data.errors).flat().join(' • ') : (data.message || 'Terjadi kesalahan.');
            showError('editError', 'editErrorMsg', msg);
        } else {
            closeModal('editModal');
            await loadLocations();
        }
    } catch {
        showError('editError', 'editErrorMsg', 'Gagal terhubung ke server.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan';
    }
});

// ─────────────────────────────────────────────────
// Delete
// ─────────────────────────────────────────────────
async function deleteLocation(id, name) {
    const result = await Swal.fire({
        title: 'Hapus Lokasi?',
        html: `<p class="text-sm text-gray-600">Anda akan menghapus lokasi <strong>"${name}"</strong>. Pastikan tidak ada aset atau sub-lokasi yang masih aktif.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    });
    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/admin/locations/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (!res.ok) {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Tidak dapat menghapus lokasi ini.', timer: 3000, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'success', title: 'Terhapus!', text: `Lokasi "${name}" berhasil dihapus.`, timer: 2000, showConfirmButton: false });
            loadLocations();
        }
    } catch {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal terhubung ke server.' });
    }
}

// ─────────────────────────────────────────────────
// Error Helper
// ─────────────────────────────────────────────────
function showError(boxId, msgId, msg) {
    document.getElementById(boxId).classList.remove('hidden');
    document.getElementById(msgId).textContent = msg;
}

// ─────────────────────────────────────────────────
// ESC to close modal
// ─────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal('addModal');
        closeModal('editModal');
    }
});

// ─────────────────────────────────────────────────
// Init
// ─────────────────────────────────────────────────
loadLocations();
</script>

@endsection