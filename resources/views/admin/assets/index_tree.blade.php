@extends('layouts.admin')

@section('title', 'Manajemen Lokasi')
@section('page-title', 'Struktur & Denah Lokasi')

@section('content')
    <div class="flex flex-col lg:flex-row h-[calc(100vh-140px)] gap-6 relative">

        {{-- TOGGLE BUTTON (Mobile & Desktop) --}}
        <button onclick="toggleSidebar()" 
                class="fixed lg:absolute top-4 left-4 z-50 bg-blue-600 text-white w-10 h-10 rounded-lg shadow-lg hover:bg-blue-700 transition flex items-center justify-center lg:hidden"
                id="sidebarToggleBtn">
            <i class="fa-solid fa-bars"></i>
        </button>

        {{-- SIDEBAR: LOCATION TREE --}}
        <div id="locationSidebar" 
             class="fixed lg:relative inset-y-0 left-0 z-40 w-80 lg:w-1/3 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-full transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
            
            {{-- Overlay untuk mobile --}}
            <div onclick="toggleSidebar()" 
                 class="fixed inset-0 bg-black/50 lg:hidden hidden" 
                 id="sidebarOverlay"></div>
            
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-xl relative z-10">
                <h3 class="font-bold text-gray-800 text-sm">Struktur Lokasi</h3>
                <div class="flex gap-2">
                    <button onclick="openLocationModal()" class="text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded transition text-xs font-bold border border-blue-200 bg-white flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> Utama
                    </button>
                    <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:bg-gray-100 w-8 h-8 rounded transition flex items-center justify-center">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar" id="locationTreeContainer">
                <div class="flex flex-col items-center justify-center h-40 text-gray-400">
                    <i class="fa-solid fa-circle-notch fa-spin text-blue-500 text-2xl mb-3"></i>
                    <span class="text-xs">Memuat struktur...</span>
                </div>
            </div>
        </div>

    <style>
        /* Guide Line Hierarchy */
        .children-container {
            position: relative;
            margin-left: 1.25rem; /* 20px */
            padding-left: 0.75rem; /* 12px */
            border-left: 1px solid #e5e7eb; /* gray-200 */
        }
        /* Active Node Style */
        .tree-node-active {
            background-color: #eff6ff; /* blue-50 */
            color: #2563eb; /* blue-600 */
            font-weight: 600;
        }
    </style>
    <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden relative h-full">

            {{-- Header Area --}}
            <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-start">
                <div class="flex gap-4">
                    <div class="w-16 h-16 bg-white p-1 rounded-lg shadow-sm border border-gray-200 shrink-0">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=DEMO" alt="QR" class="w-full h-full object-contain opacity-30 transition-opacity duration-300" id="headerQr">
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-xl font-bold text-gray-800 truncate max-w-[200px] lg:max-w-md" id="headerTitle">Pilih Lokasi</h2>
                            <span class="bg-gray-200 text-gray-600 text-[10px] px-2 py-0.5 rounded border border-gray-300 font-bold uppercase hidden" id="headerIdBadge">ID: <span id="headerId">-</span></span>
                        </div>
                        <p class="text-sm text-gray-500 mb-1 line-clamp-1" id="headerBreadcrumb">Silakan pilih lokasi di menu kiri untuk melihat aset.</p>
                    </div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button onclick="printLocationQr()" title="Pilih lokasi terlebih dahulu" class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition group relative" id="btnPrintQr" disabled>
                        <i class="fa-solid fa-print mr-1"></i> QR
                        <span class="absolute hidden group-disabled:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-max bg-gray-800 text-white text-[10px] px-2 py-1 rounded shadow-lg z-50">
                            Pilih lokasi dulu
                        </span>
                    </button>
                    <button onclick="openAssetModal()" title="Pilih lokasi terlebih dahulu" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center gap-2 group relative" id="btnAddAsset" disabled>
                        <i class="fa-solid fa-plus"></i> Tambah Aset
                        <span class="absolute hidden group-disabled:block bottom-full mb-2 left-1/2 -translate-x-1/2 w-max bg-gray-800 text-white text-[10px] px-2 py-1 rounded shadow-lg z-50">
                            Pilih lokasi dulu
                        </span>
                    </button>
                </div>
            </div>

            {{-- Table Area --}}
            <div class="flex-1 overflow-y-auto relative flex flex-col">
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="bg-white text-gray-500 uppercase font-bold text-xs sticky top-0 z-10 border-b shadow-sm">
                            <tr>
                                <th class="px-6 py-4 w-12 text-center bg-gray-50">No</th>
                                <th class="px-6 py-4 w-16 bg-gray-50">Foto</th>
                                <th class="px-6 py-4 bg-gray-50">Nama Aset</th>
                                <th class="px-6 py-4 bg-gray-50">Kategori</th>
                                <th class="px-6 py-4 bg-gray-50">Status</th>
                                <th class="px-6 py-4 text-center w-32 bg-gray-50">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="assetTableBody"></tbody>
                    </table>

                    {{-- Empty State --}}
                    <div id="emptyState" class="flex flex-col items-center justify-center h-64 text-gray-400 hidden">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fa-solid fa-map-location-dot text-3xl text-gray-300"></i>
                        </div>
                        <p class="font-medium text-gray-600">Lokasi ini belum memiliki aset.</p>
                        <p class="text-xs mt-1 text-gray-400 mb-4">Tambahkan aset baru untuk lokasi ini.</p>
                        <button onclick="openAssetModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md transition flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Tambah Aset
                        </button>
                    </div>
                </div>

                {{-- Pagination --}}
                <div id="paginationContainer" class="p-3 border-t border-gray-100 bg-gray-50 flex justify-between items-center hidden">
                    <span class="text-xs text-gray-500">
                        Halaman <span id="pageCurrent" class="font-bold text-gray-700">1</span> dari <span id="pageTotal" class="font-bold text-gray-700">1</span>
                    </span>
                    <div class="flex gap-1">
                        <button onclick="changePage('prev')" id="btnPrev" class="px-3 py-1 bg-white border rounded-md text-xs font-medium text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">Prev</button>
                        <button onclick="changePage('next')" id="btnNext" class="px-3 py-1 bg-white border rounded-md text-xs font-medium text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODALS --}}
    {{-- ========================================== --}}

    {{-- 1. LOCATION MODAL --}}
    <div id="locationModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('locationModal')"></div>

            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-md sm:w-full">
                <form id="locationForm" onsubmit="submitLocationForm(event)">
                    <input type="hidden" name="parent_id" id="locParentId">
                    <input type="hidden" name="id" id="locId">

                    <div class="bg-white px-6 py-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900" id="locModalTitle">Tambah Lokasi</h3>
                        <button type="button" onclick="closeModal('locationModal')" class="text-gray-400 hover:text-gray-500 transition"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div id="parentInfoBox" class="bg-blue-50 p-3 rounded-lg border border-blue-100 text-xs text-blue-700 hidden flex items-center gap-2">
                            <i class="fa-solid fa-level-up-alt"></i> Sub-lokasi dari: <strong id="parentNameDisplay">-</strong>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lokasi <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="locName" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Gedung A, Lantai 1..." required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" id="locDesc" rows="3" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Keterangan tambahan..."></textarea>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center">
                        <button type="button" id="btnDeleteLoc" onclick="deleteLocation()" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase hidden transition flex items-center gap-1">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                        <div class="flex gap-3 justify-end w-full">
                            <button type="button" onclick="closeModal('locationModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                            <button type="submit" id="btnSaveLoc" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md transition">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2. ASSET MODAL --}}
    <div id="assetModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('assetModal')"></div>

            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                <form id="assetForm" onsubmit="submitAssetForm(event)" enctype="multipart/form-data">
                    <input type="hidden" name="location_id" id="modalLocationId">
                    <input type="hidden" name="id" id="assetId">

                    <div class="bg-white px-6 py-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900" id="assetModalTitle">Tambah Aset Baru</h3>
                        <button type="button" onclick="closeModal('assetModal')" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark text-xl"></i></button>
                    </div>

                    <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-100 flex items-center gap-2 text-sm text-blue-800">
                            <i class="fa-solid fa-location-dot"></i> Lokasi Penempatan: <span class="font-bold" id="modalLocationName">-</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Aset <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="assetName" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500" required placeholder="Contoh: AC Daikin 2PK">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                                <select name="category_id" id="assetCategory" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Serial Number</label>
                                <input type="text" name="serial_number" id="assetSN" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500" placeholder="SN-12345">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Status</label>
                                <select name="status" id="assetStatus" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500">
                                    <option value="normal">Normal</option>
                                    <option value="rusak">Rusak</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="hilang">Hilang</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Beli</label>
                                <input type="date" name="purchase_date" id="assetDate" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Foto Aset</label>
                            <div class="flex items-center gap-4">
                                <label class="block w-full">
                                    <span class="sr-only">Choose file</span>
                                    <input type="file" name="image" id="assetImage" onchange="previewFile()" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"/>
                                </label>
                                <img src="" id="previewImage" class="h-16 w-16 object-cover rounded-lg border border-gray-200 hidden shadow-sm">
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex justify-between items-center mb-3">
                                <label class="block text-sm font-bold text-gray-700">Spesifikasi Teknis</label>
                                <button type="button" onclick="addSpecRow()" class="text-xs bg-green-50 text-green-700 px-3 py-1.5 rounded-lg border border-green-200 hover:bg-green-100 font-bold transition flex items-center gap-1">
                                    <i class="fa-solid fa-plus"></i> Tambah
                                </button>
                            </div>
                            <div id="specsContainer" class="space-y-2 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <p class="text-xs text-gray-400 text-center italic" id="emptySpecMsg">Belum ada spesifikasi.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl">
                        <button type="button" onclick="closeModal('assetModal')" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" id="btnSaveAsset" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md transition">Simpan Aset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 3. DETAIL MODAL --}}
    <div id="detailModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('detailModal')"></div>

            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
                <div class="relative h-56 bg-gray-200 group">
                    <img id="detailImage" src="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex items-end p-6">
                        <div>
                            <span id="detailStatus" class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider mb-2 inline-block shadow-sm">-</span>
                            <h2 class="text-3xl font-bold text-white drop-shadow-md" id="detailName">-</h2>
                            <p class="text-white/90 text-sm font-medium flex items-center gap-1 mt-1">
                                <i class="fa-solid fa-tag text-xs"></i> <span id="detailCategory">-</span>
                            </p>
                        </div>
                    </div>
                    <button onclick="closeModal('detailModal')" class="absolute top-4 right-4 bg-black/30 hover:bg-black/50 text-white rounded-full p-2 w-9 h-9 flex items-center justify-center transition backdrop-blur-sm">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 gap-x-8 gap-y-6 mb-6">
                        <div>
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Serial Number</p>
                            <p class="text-gray-800 font-mono font-medium text-sm border-b border-gray-100 pb-1" id="detailSN">-</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Lokasi</p>
                            <p class="text-gray-800 font-medium text-sm border-b border-gray-100 pb-1">
                                <i class="fa-solid fa-location-dot text-blue-500 mr-1"></i> <span id="detailLoc">-</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Tanggal Pembelian</p>
                            <p class="text-gray-800 font-medium text-sm border-b border-gray-100 pb-1" id="detailDate">-</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-3 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-list-check text-blue-500"></i> Spesifikasi Teknis
                        </h3>
                        <div id="detailSpecs" class="space-y-2 text-sm text-gray-600"></div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button onclick="closeModal('detailModal')" class="px-5 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition shadow-sm">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- JAVASCRIPT LOGIC --}}
    {{-- ========================================== --}}
    <script>
        // GLOBAL VARS
        let currentLocId = null;
        let currentLocName = '';
        let currentLocCode = ''; // Store location code for QR
        let currentPage = 1;

        let lastPage = 1;
        const storageUrl = "{{ asset('storage') }}";

        // Custom Toast Mixin
        function showToast(icon, title) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => fetchLocations());

        // HELPERS
        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');

            // Reset loading state on buttons when modal closes
            const btnSaveLoc = document.getElementById('btnSaveLoc');
            if(btnSaveLoc) { btnSaveLoc.disabled = false; btnSaveLoc.innerHTML = 'Simpan'; }

            const btnSaveAsset = document.getElementById('btnSaveAsset');
            if(btnSaveAsset) { btnSaveAsset.disabled = false; btnSaveAsset.innerHTML = 'Simpan Aset'; }
        }

        function previewFile() {
            const preview = document.getElementById('previewImage');
            const file = document.getElementById('assetImage').files[0];
            const reader = new FileReader();
            reader.onload = () => { preview.src = reader.result; preview.classList.remove('hidden'); };
            if (file) reader.readAsDataURL(file);
        }

        function addSpecRow(key = '', value = '') {
            const container = document.getElementById('specsContainer');
            const emptyMsg = document.getElementById('emptySpecMsg');
            if(emptyMsg) emptyMsg.remove(); // Remove "empty" message

            const rowId = 'spec-' + Date.now();
            container.innerHTML += `
                <div class="flex gap-2 items-center animate-fadeIn" id="${rowId}">
                    <input type="text" name="specs_key[]" value="${key}" placeholder="Label (ex: Warna)" class="w-1/3 border-gray-300 rounded-lg text-xs p-2 focus:ring-blue-500 focus:border-blue-500">
                    <input type="text" name="specs_value[]" value="${value}" placeholder="Value (ex: Merah)" class="w-full border-gray-300 rounded-lg text-xs p-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" onclick="document.getElementById('${rowId}').remove()" class="text-red-400 p-2 hover:bg-red-50 rounded transition"><i class="fa-solid fa-trash-can"></i></button>
                </div>`;
        }

        // --- TREE LOGIC ---
        async function fetchLocations() {
            const container = document.getElementById('locationTreeContainer');
            try {
                const res = await fetch("{{ route('admin.locations.tree') }}", { headers: {'Accept':'application/json'} });
                const json = await res.json();
                container.innerHTML = '';
                if(json.data.length === 0) {
                    container.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-40 text-gray-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                            <span class="text-xs">Belum ada lokasi.</span>
                        </div>`;
                } else {
                    json.data.forEach(loc => container.appendChild(createTreeNode(loc)));
                }
            } catch (e) {
                container.innerHTML = '<div class="text-center text-red-500 text-xs mt-4">Gagal memuat struktur.</div>';
            }
        }

        // HELPER: Escape quotes untuk onclick attributes
        function escapeQuotes(str) {
            if (!str) return '';
            return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
        }

        function createTreeNode(loc) {
            const hasChildren = loc.children && loc.children.length > 0;
            const isParent = hasChildren;
            
            const node = document.createElement('div');
            node.className = "mb-1";
            
            const header = document.createElement('div');
            // [NEW] Added ID for manual selection
            header.id = `node-header-${loc.id}`; 
            header.className = "flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-blue-50 cursor-pointer transition group text-gray-600";
            
            // Escape data untuk keamanan
            const safeName = escapeQuotes(loc.name);
            const safeDesc = escapeQuotes(loc.description || '');
            const safeCode = escapeQuotes(loc.code || '');

            
            header.innerHTML = `
                <div class="flex gap-2.5 items-center flex-1 overflow-hidden" onclick="selectLocation('${loc.id}', '${safeName}', '${safeDesc}', '${safeCode}')">
                    ${hasChildren ? `
                        <button onclick="event.stopPropagation(); toggleNode(this)" class="w-5 h-5 flex items-center justify-center text-gray-400 hover:text-gray-600 transition shrink-0">
                            <i class="fa-solid fa-chevron-right text-xs chevron-icon transition-transform duration-200"></i>
                        </button>
                    ` : '<div class="w-5 shrink-0"></div>'}
                    
                    <div class="w-6 h-6 flex items-center justify-center rounded text-gray-400 shrink-0 node-icon-container">
                        <i class="fa-solid ${loc.type === 'building' ? 'fa-building' : 'fa-location-dot text-xs'}"></i>
                    </div>
                    
                    <span class="truncate text-sm node-text">${loc.name}</span>
                </div>
                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                    <button onclick="event.stopPropagation(); openLocationModal(null, '${loc.id}', '${safeName}', '${safeDesc}')" class="text-gray-500 w-7 h-7 hover:bg-white hover:text-blue-600 hover:shadow-sm rounded transition border border-transparent hover:border-gray-200"><i class="fa-solid fa-pen text-[10px]"></i></button>
                    <button onclick="event.stopPropagation(); openLocationModal('${loc.id}', null, null, null, '${safeName}')" class="text-gray-500 w-7 h-7 hover:bg-white hover:text-green-600 hover:shadow-sm rounded transition border border-transparent hover:border-gray-200"><i class="fa-solid fa-plus text-[10px]"></i></button>
                </div>`;
            node.appendChild(header);

            if (hasChildren) {
                const childContainer = document.createElement('div');
                // [NEW] Updated class for visual hierarchy
                childContainer.className = "children-container space-y-0.5 hidden"; 
                loc.children.forEach(c => childContainer.appendChild(createTreeNode(c)));
                node.appendChild(childContainer);
            }
            return node;
        }

        // FUNGSI TOGGLE NODE (EXPAND/COLLAPSE)
        function toggleNode(button) {
            const treeNode = button.closest('.mb-1');
            const childContainer = treeNode.querySelector('.children-container');
            const chevron = button.querySelector('.chevron-icon');
            
            if (childContainer) {
                const isHidden = childContainer.classList.contains('hidden');
                
                if (isHidden) {
                    // Expand
                    childContainer.classList.remove('hidden');
                    chevron.style.transform = 'rotate(90deg)';
                } else {
                    // Collapse
                    childContainer.classList.add('hidden');
                    chevron.style.transform = 'rotate(0deg)';
                }
            }
        }

        // --- MAIN UI LOGIC ---
        function selectLocation(id, name, desc, code) {
            currentLocId = id; 
            currentLocName = name;
            currentLocCode = code || ''; 
            currentPage = 1;

            document.getElementById('headerTitle').innerText = name;
            document.getElementById('headerId').innerText = code || id; 
            document.getElementById('headerIdBadge').classList.remove('hidden');
            document.getElementById('headerBreadcrumb').innerText = desc || 'Tidak ada deskripsi tambahan.';

            // Set QR Code
            const qrImg = document.getElementById('headerQr');
            const qrData = code || `ID-${id}`; 
            qrImg.classList.add('opacity-30'); 
            setTimeout(() => {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${qrData}`;
                qrImg.onload = () => qrImg.classList.remove('opacity-30'); 
            }, 200);

            // Enable Buttons & Tooltips
            ['btnAddAsset', 'btnPrintQr'].forEach(i => {
                const btn = document.getElementById(i);
                btn.disabled = false;
                // Update title to be standard
                btn.title = i === 'btnAddAsset' ? 'Tambah Aset di sini' : 'Print QR Code';
            });

            // [NEW] ACTIVE STATE LOGIC (No Fetch)
            // 1. Remove active class from previous
            document.querySelectorAll('.tree-node-active').forEach(el => {
                el.classList.remove('tree-node-active', 'bg-blue-50', 'text-blue-600');
                el.classList.add('text-gray-600');
                
                // Reset Icon Container
                const iconContainer = el.querySelector('.node-icon-container');
                if(iconContainer) {
                    iconContainer.classList.remove('text-blue-600', 'bg-blue-100');
                    iconContainer.classList.add('text-gray-400');
                }
            });

            // 2. Add active to current
            const activeNode = document.getElementById(`node-header-${id}`);
            if(activeNode) {
                activeNode.classList.add('tree-node-active', 'bg-blue-50', 'text-blue-600');
                activeNode.classList.remove('text-gray-600');

                const iconContainer = activeNode.querySelector('.node-icon-container');
                if(iconContainer) {
                    iconContainer.classList.remove('text-gray-400');
                    iconContainer.classList.add('text-blue-600', 'bg-blue-100');
                }
            }

            // DO NOT FETCH TREE AGAIN implies Sidebar stays open on mobile

            loadAssetsByLocation(id, 1);
        }

        async function loadAssetsByLocation(id, page) {
            const tbody = document.getElementById('assetTableBody');
            const empty = document.getElementById('emptyState');
            const pagination = document.getElementById('paginationContainer');

            // Skeleton Loading
            tbody.innerHTML = `
                <tr><td colspan="6" class="p-4"><div class="animate-pulse flex space-x-4"><div class="h-4 bg-gray-200 rounded w-full"></div></div></td></tr>
                <tr><td colspan="6" class="p-4"><div class="animate-pulse flex space-x-4"><div class="h-4 bg-gray-200 rounded w-full"></div></div></td></tr>
                <tr><td colspan="6" class="p-4"><div class="animate-pulse flex space-x-4"><div class="h-4 bg-gray-200 rounded w-full"></div></div></td></tr>`;

            empty.classList.add('hidden');
            pagination.classList.add('hidden');

            try {
                const res = await fetch(`/admin/assets/by-location/${id}?page=${page}`);
                const json = await res.json();
                const paginatedData = json.data;
                const assets = paginatedData.data;

                tbody.innerHTML = '';

                if(assets.length === 0) {
                    empty.classList.remove('hidden');
                } else {
                    currentPage = paginatedData.current_page;
                    lastPage = paginatedData.last_page;
                    const perPage = paginatedData.per_page;

                    assets.forEach((asset, index) => {
                        const rowNumber = (currentPage - 1) * perPage + index + 1;
                        let statusClass = 'bg-gray-100 text-gray-600';
                        if(asset.status === 'normal') statusClass = 'bg-green-100 text-green-700 border border-green-200';
                        else if(asset.status === 'rusak') statusClass = 'bg-red-100 text-red-700 border border-red-200';
                        else if(asset.status === 'maintenance') statusClass = 'bg-yellow-100 text-yellow-700 border border-yellow-200';



                        let imgUrl = 'https://via.placeholder.com/150?text=No+Img';
                        if (asset.image_url) imgUrl = asset.image_url;
                        else if (asset.image) imgUrl = `${storageUrl}/${asset.image}`;

                        const catName = asset.category ? asset.category.name : '<span class="text-gray-400 italic">Tanpa Kategori</span>';

                        const row = `
                            <tr class="hover:bg-blue-50/50 border-b border-gray-50 transition group">
                                <td class="px-6 py-4 text-center text-xs font-bold text-gray-400">${rowNumber}</td>
                                <td class="px-6 py-3">
                                    <div class="relative w-10 h-10 rounded-lg overflow-hidden border border-gray-200 group-hover:border-blue-200 transition">
                                        <img src="${imgUrl}" class="w-full h-full object-cover cursor-pointer hover:scale-110 transition duration-300" onclick="showAssetDetail(${asset.id})">
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="font-bold text-gray-800 text-sm">${asset.name}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">${asset.serial_number || 'No SN'}</div>
                                </td>
                                <td class="px-6 py-3 text-xs text-gray-600">${catName}</td>
                                <td class="px-6 py-3"><span class="${statusClass} px-2.5 py-1 rounded-full text-[10px] uppercase font-bold tracking-wide shadow-sm">${asset.status}</span></td>
                                <td class="px-6 py-3 text-center">
                                    <div class="flex justify-center gap-1 opacity-60 group-hover:opacity-100 transition">
                                        <button onclick="showAssetDetail(${asset.id})" class="w-8 h-8 rounded-full hover:bg-white hover:shadow-sm text-gray-500 hover:text-blue-600 transition flex items-center justify-center border border-transparent hover:border-gray-200" title="Detail"><i class="fa-solid fa-circle-info"></i></button>
                                        <button onclick="editAsset(${asset.id})" class="w-8 h-8 rounded-full hover:bg-white hover:shadow-sm text-gray-500 hover:text-orange-500 transition flex items-center justify-center border border-transparent hover:border-gray-200" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button onclick="deleteAsset(${asset.id})" class="w-8 h-8 rounded-full hover:bg-white hover:shadow-sm text-gray-500 hover:text-red-500 transition flex items-center justify-center border border-transparent hover:border-gray-200" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>`;
                        tbody.innerHTML += row;
                    });
                    updatePaginationUI();
                }
            } catch (e) { tbody.innerHTML = ''; }
        }

        function updatePaginationUI() {
            const pagination = document.getElementById('paginationContainer');
            pagination.classList.remove('hidden');
            document.getElementById('pageCurrent').innerText = currentPage;
            document.getElementById('pageTotal').innerText = lastPage;
            document.getElementById('btnPrev').disabled = currentPage <= 1;
            document.getElementById('btnNext').disabled = currentPage >= lastPage;
        }

        function changePage(direction) {
            if(direction === 'prev' && currentPage > 1) loadAssetsByLocation(currentLocId, currentPage - 1);
            else if (direction === 'next' && currentPage < lastPage) loadAssetsByLocation(currentLocId, currentPage + 1);
        }

        // --- DETAIL MODAL ---
        async function showAssetDetail(id) {
            try {
                const res = await fetch(`/admin/assets/${id}`);
                const json = await res.json();
                if(json.status === 'success') {
                    const asset = json.data;
                    document.getElementById('detailImage').src = asset.image_url || 'https://via.placeholder.com/600x300?text=No+Image';
                    document.getElementById('detailName').innerText = asset.name;
                    document.getElementById('detailCategory').innerText = asset.category ? asset.category.name : '-';
                    document.getElementById('detailSN').innerText = asset.serial_number || '-';
                    document.getElementById('detailStatus').innerText = asset.status;

                    let statusClass = 'bg-gray-500';
                    if(asset.status === 'normal') statusClass = 'bg-green-500';
                    else if(asset.status === 'rusak') statusClass = 'bg-red-500';
                    else if(asset.status === 'maintenance') statusClass = 'bg-yellow-500';

                    document.getElementById('detailStatus').className = `px-3 py-1 text-white rounded shadow-sm text-[10px] font-bold uppercase tracking-wider mb-2 inline-block ${statusClass}`;

                    document.getElementById('detailLoc').innerText = asset.location ? asset.location.name : '-';
                    document.getElementById('detailDate').innerText = asset.purchase_date ? new Date(asset.purchase_date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-';

                    const specBox = document.getElementById('detailSpecs');
                    specBox.innerHTML = '';
                    if (asset.specifications && Object.keys(asset.specifications).length > 0) {
                        const specs = typeof asset.specifications === 'string' ? JSON.parse(asset.specifications) : asset.specifications;
                        Object.entries(specs).forEach(([k, v]) => {
                            specBox.innerHTML += `
                                <div class="flex justify-between border-b border-gray-200 pb-2 last:border-0 hover:bg-white p-1 rounded transition">
                                    <span class="font-medium text-gray-700">${k}</span>
                                    <span class="font-bold text-gray-900">${v}</span>
                                </div>`;
                        });
                    } else {
                        specBox.innerHTML = '<p class="text-gray-400 italic text-center py-4">Tidak ada spesifikasi khusus.</p>';
                    }
                    document.getElementById('detailModal').classList.remove('hidden');
                }
            } catch (e) { Swal.fire('Error', 'Gagal memuat detail data.', 'error'); }
        }

        // --- ASSET FORM (Edit, Submit, Delete) ---
        function openAssetModal() {
            if(!currentLocId) {
                Swal.fire({ icon: 'info', title: 'Pilih Lokasi Dulu', text: 'Silakan klik salah satu lokasi di menu kiri.' });
                return;
            }
            document.getElementById('assetForm').reset();
            document.getElementById('assetId').value = '';
            document.getElementById('modalLocationId').value = currentLocId;
            document.getElementById('modalLocationName').innerText = currentLocName;
            document.getElementById('assetModalTitle').innerText = 'Tambah Aset Baru';
            document.getElementById('previewImage').classList.add('hidden');

            const specContainer = document.getElementById('specsContainer');
            specContainer.innerHTML = '<p class="text-xs text-gray-400 text-center italic" id="emptySpecMsg">Belum ada spesifikasi.</p>';

            document.getElementById('assetModal').classList.remove('hidden');
        }

        async function editAsset(id) {
            try {
                const res = await fetch(`/admin/assets/${id}`);
                const json = await res.json();
                if(json.status === 'success') {
                    const asset = json.data;
                    document.getElementById('assetId').value = asset.id;
                    document.getElementById('modalLocationId').value = asset.location_id;
                    document.getElementById('modalLocationName').innerText = currentLocName;

                    document.getElementById('assetName').value = asset.name;
                    document.getElementById('assetCategory').value = asset.category_id;
                    document.getElementById('assetStatus').value = asset.status;
                    document.getElementById('assetSN').value = asset.serial_number || '';
                    document.getElementById('assetDate').value = asset.purchase_date ? asset.purchase_date.split('T')[0] : '';

                    const img = document.getElementById('previewImage');
                    if(asset.image_url) { img.src = asset.image_url; img.classList.remove('hidden'); }
                    else img.classList.add('hidden');

                    const container = document.getElementById('specsContainer');
                    container.innerHTML = '';
                    if(asset.specifications) {
                        const specs = typeof asset.specifications === 'string' ? JSON.parse(asset.specifications) : asset.specifications;
                        Object.entries(specs).forEach(([k, v]) => addSpecRow(k, v));
                    }
                    if(container.innerHTML === '') container.innerHTML = '<p class="text-xs text-gray-400 text-center italic" id="emptySpecMsg">Belum ada spesifikasi.</p>';

                    document.getElementById('assetModalTitle').innerText = 'Edit Aset';
                    document.getElementById('assetModal').classList.remove('hidden');
                }
            } catch (e) { Swal.fire('Error', 'Gagal memuat data aset.', 'error'); }
        }

        async function submitAssetForm(e) {
            e.preventDefault();

            // Loading State
            const btnSave = document.getElementById('btnSaveAsset');
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...';

            const id = document.getElementById('assetId').value;
            const url = id ? `/admin/assets/${id}` : "{{ route('admin.assets.store') }}";
            const formData = new FormData(document.getElementById('assetForm'));
            if(id) formData.append('_method', 'PUT');

            try {
                const res = await fetch(url, { method: 'POST', headers: {'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: formData });
                const json = await res.json();
                if(!res.ok) throw new Error(json.message);

                closeModal('assetModal');
                loadAssetsByLocation(currentLocId, currentPage);

                // Toast.fire({ icon: 'success', title: 'Aset berhasil disimpan' });
                showToast('success', 'Aset berhasil disimpan');

            } catch (e) {
                Swal.fire('Gagal!', e.message, 'error');
            } finally {
                btnSave.disabled = false;
                btnSave.innerHTML = 'Simpan Aset';
            }
        }

        async function deleteAsset(id) {
            Swal.fire({
                title: 'Hapus aset ini?',
                text: "Aset akan dipindahkan ke sampah (Soft Delete).",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`/admin/assets/${id}`, { method: 'DELETE', headers: {'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'} });
                        if(!res.ok) throw new Error('Gagal menghapus');

                        loadAssetsByLocation(currentLocId, currentPage);
                        // Toast.fire({ icon: 'success', title: 'Aset berhasil dihapus' });
                        showToast('success', 'Aset berhasil dihapus');
                    } catch (e) {
                        Swal.fire('Error', 'Gagal menghapus aset.', 'error');
                    }
                }
            });
        }

        // --- LOCATION FORM ---
        function openLocationModal(pid, eid, ename, edesc, pname) {
            document.getElementById('locationForm').reset();
            document.getElementById('locParentId').value = pid||'';
            document.getElementById('locId').value = eid||'';
            const delBtn = document.getElementById('btnDeleteLoc');
            const pInfo = document.getElementById('parentInfoBox');

            if(eid) {
                document.getElementById('locModalTitle').innerText = 'Edit Lokasi';
                document.getElementById('locName').value = ename;
                document.getElementById('locDesc').value = edesc==='null'?'':edesc;
                delBtn.classList.remove('hidden'); pInfo.classList.add('hidden');
            } else {
                document.getElementById('locModalTitle').innerText = pid ? 'Tambah Sub-Lokasi' : 'Tambah Lokasi Utama';
                delBtn.classList.add('hidden');
                if(pid && pname) {
                    pInfo.classList.remove('hidden');
                    document.getElementById('parentNameDisplay').innerText = pname;
                } else {
                    pInfo.classList.add('hidden');
                }
            }
            document.getElementById('locationModal').classList.remove('hidden');
        }

        async function submitLocationForm(e) {
            e.preventDefault();

            const btnSave = document.getElementById('btnSaveLoc');
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

            const id = document.getElementById('locId').value;
            const url = id ? `/admin/api/locations/${id}` : "{{ route('admin.locations.store') }}";
            const body = JSON.stringify(Object.fromEntries(new FormData(e.target)));

            try {
                const res = await fetch(url, { method: id?'PUT':'POST', headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: body });
                const json = await res.json();

                if(!res.ok) throw new Error(json.message);

                closeModal('locationModal');
                fetchLocations();
                if(id && currentLocId==id) selectLocation(id, JSON.parse(body).name, JSON.parse(body).description);

                showToast('success', 'Data lokasi tersimpan');

            } catch (error) {
                Swal.fire('Gagal!', error.message, 'error');
            } finally {
                btnSave.disabled = false;
                btnSave.innerHTML = 'Simpan';
            }
        }

        async function deleteLocation() {
            Swal.fire({
                title: 'Hapus Lokasi?',
                text: "Semua sub-lokasi di dalamnya juga akan terhapus.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {

                if (result.isConfirmed) {
                    try {
                        const id = document.getElementById('locId').value;
                        const res = await fetch(`/admin/api/locations/${id}`, { method: 'DELETE', headers: {'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'} });
                        const json = await res.json();

                        if (!res.ok) throw new Error(json.message);

                        closeModal('locationModal');
                        fetchLocations();

                        if(currentLocId == id) window.location.reload();
                        else showToast('success', 'Lokasi berhasil dihapus');

                    } catch (error) {
                        Swal.fire('Gagal!', error.message, 'error');
                    }
                }
            });
        }

        function printLocationQr() {
            if(!currentLocId) return;
            
            // Use location code if available, otherwise fallback to ID
            const qrData = currentLocCode || `ID-${currentLocId}`;
            const displayCode = currentLocCode || `ID: ${currentLocId}`;
            
            const url = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${qrData}`;
            const win = window.open('', '_blank');
            win.document.write(`
                <html>
                    <head><title>Print QR - ${currentLocName}</title></head>
                    <body style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100vh; font-family:sans-serif;">
                        <h2 style="margin-bottom:10px;">${currentLocName}</h2>
                        <img src="${url}" style="width:300px; height:300px; border:1px solid #ccc;">
                        <p style="margin-top:10px; font-size:18px; font-weight:bold; color:#059669;">${displayCode}</p>
                        <p style="margin-top:5px; font-size:12px; color:#666;">Scan QR code ini dari aplikasi teknisi</p>
                        <script>
                            window.onload = function() { window.print(); window.close(); }
                        <\/script>
                    </body>
                </html>
            `);
            win.document.close();
        }

        // FUNGSI TOGGLE SIDEBAR
        function toggleSidebar() {
            const sidebar = document.getElementById('locationSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isOpen = !sidebar.classList.contains('-translate-x-full');
            
            if (isOpen) {
                // Tutup sidebar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            } else {
                // Buka sidebar
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
    </style>
@endsection
