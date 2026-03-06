@extends('layouts.admin')

@section('title', 'Kategori Aset')
@section('page-title', 'Klasifikasi & Audit Aset')

@section('content')
    <div class="flex flex-col lg:flex-row h-[calc(100vh-140px)] gap-6">

        <div class="w-full lg:w-1/3 flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gray-50">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-gray-800 text-sm">Daftar Kategori</h3>
                    @if(!auth()->user()->isManajer())
                    <button onclick="openModal('addCategoryModal')" class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition shadow-sm font-bold flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> Baru
                    </button>
                    @endif
                </div>

                {{-- Sidebar Search --}}
                <div class="relative">
                    <input type="text" 
                           id="categorySearch" 
                           placeholder="Cari kategori..." 
                           onkeyup="filterCategories()" 
                           class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                </div>
            </div>


            <div class="flex-1 overflow-y-auto p-3 space-y-2" id="categoryListContainer">
                @if(isset($categories) && count($categories) > 0)
                    @foreach($categories as $cat)
                        <div class="w-full flex items-center justify-between p-3 rounded-xl border border-transparent hover:border-gray-200 transition group category-item"
                                id="cat-item-{{ $cat->id }}">
                            <button onclick="loadAssetsByCategory({{ $cat->id }}, '{{ $cat->name }}', {{ $cat->assets_count ?? 0 }}, '{{ $cat->description }}')"
                                    class="flex-1 flex items-center gap-3 text-left hover:bg-gray-50 rounded-lg p-2 -m-2">
                                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-sm">
                                    <i class="{{ $cat->icon ?? 'fa-solid fa-box' }}"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-800 text-sm">{{ $cat->name }}</h4>
                                    <p class="text-[10px] text-gray-500">Total: {{ $cat->assets_count ?? 0 }} Unit</p>
                                </div>
                            </button>
                            @if(!auth()->user()->isManajer())
                            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                <button onclick="openEditCategoryModal({{ $cat->id }}, '{{ $cat->name }}', '{{ $cat->description }}', '{{ $cat->icon }}')" 
                                        class="w-7 h-7 rounded flex items-center justify-center bg-yellow-100 text-yellow-600 hover:bg-yellow-200 transition"
                                        title="Edit Kategori">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button onclick="deleteCategory({{ $cat->id }}, '{{ $cat->name }}')" 
                                        class="w-7 h-7 rounded flex items-center justify-center bg-red-100 text-red-600 hover:bg-red-200 transition"
                                        title="Hapus Kategori">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="p-4 text-center text-gray-400 text-sm">Belum ada kategori.</div>
                @endif
            </div>
        </div>

        <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden relative">

            <div class="p-6 border-b border-gray-100 flex justify-between items-start bg-gray-50">
                <div>
                    <!-- <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Audit Aset Kategori</p> -->
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2" id="detailTitle">Pilih Kategori</h2>
                    <p class="text-sm text-gray-500 mt-1" id="detailDesc">Klik salah satu kategori di kiri untuk melihat detail.</p>
                </div>
                <div class="flex gap-2 hidden" id="categoryActions"></div>
            </div>

            <div class="flex-1 overflow-y-auto relative flex flex-col">

                {{-- ELEMENT PENTING YANG HILANG SEBELUMNYA --}}
                <div id="loadingSpinner" class="hidden absolute inset-0 bg-white bg-opacity-80 z-20 flex items-center justify-center">
                    <div class="text-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl text-blue-600 mb-2"></i>
                        <p class="text-xs text-gray-500 font-bold">Memuat Data...</p>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-sm text-left text-gray-600 sticky top-0">
                        <thead class="bg-white text-gray-500 uppercase font-bold text-xs sticky top-0 z-10 border-b shadow-sm">
                            <tr>
                                <th class="px-6 py-4 w-12 text-center">No</th>
                                <th class="px-6 py-4 w-16">Foto</th>
                                <th class="px-6 py-4">Nama Aset</th>
                                <th class="px-6 py-4">Lokasi</th>
                                <th class="px-6 py-4">Status</th>
                                @if(!auth()->user()->isManajer())
                                <th class="px-6 py-4 text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="assetTableBody"></tbody>
                    </table>

                    <div id="emptyState" class="flex flex-col items-center justify-center h-48 text-gray-400 mt-10">
                        <i class="fa-solid fa-arrow-left text-3xl mb-2 animate-bounce-x"></i>
                        <p class="text-sm">Pilih kategori di menu kiri</p>
                    </div>
                </div>

                <div id="paginationContainer" class="p-3 border-t border-gray-100 bg-gray-50 flex justify-between items-center hidden">
                    <span class="text-xs text-gray-500">
                        Hal <span id="pageCurrent" class="font-bold">1</span> dari <span id="pageTotal" class="font-bold">1</span>
                    </span>
                    <div class="flex gap-1">
                        <button onclick="changePage('prev')" id="btnPrev" class="px-3 py-1 bg-white border rounded text-xs font-medium text-gray-600 hover:bg-gray-100 disabled:opacity-50">Prev</button>
                        <button onclick="changePage('next')" id="btnNext" class="px-3 py-1 bg-white border rounded text-xs font-medium text-gray-600 hover:bg-gray-100 disabled:opacity-50">Next</button>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50 text-right">
                <span class="text-xs text-gray-500">Total Aset Terdata: <strong class="text-gray-800 text-sm" id="detailCount">0</strong> Unit</span>
            </div>
        </div>
    </div>

    <div id="addCategoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('addCategoryModal')"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                <form action="{{ route('admin.categories.store') }}" method="POST">
                    @csrf
                    <div class="bg-gray-50 px-4 py-3 border-b">
                        <h3 class="text-lg font-bold text-gray-900">Tambah Kategori Baru</h3>
                    </div>
                    <div class="bg-white px-6 py-6 space-y-4">

                        {{-- Error banner --}}
                        @if($errors->any())
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <p class="text-xs font-bold text-red-700 mb-1 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-exclamation"></i> Gagal menyimpan:
                                </p>
                                <ul class="list-disc list-inside space-y-0.5">
                                    @foreach($errors->all() as $error)
                                        <li class="text-xs text-red-600">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Kategori</label>
                            <input type="text" name="name"
                                value="{{ old('name') }}"
                                class="w-full rounded-lg text-sm focus:ring-blue-500 {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }} border-2 border-gray-300 py-2 pl-2"
                                required placeholder="Kategori">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" rows="2" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 py-2 pl-2 border-2 border-gray-300" placeholder="Deskripsi">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Ikon Kategori</label>
                            <input type="hidden" name="icon" id="add_icon_value" value="fa-solid fa-box">
                            <div id="add_icon_grid" class="grid grid-cols-8 gap-2 max-h-64 overflow-y-auto p-2 border border-gray-200 rounded-lg bg-gray-50">
                                <!-- Icons will be rendered by JavaScript -->
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fa-solid fa-info-circle"></i> Pilih salah satu icon untuk kategori aset Anda
                            </p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Simpan</button>
                        <button type="button" onclick="closeModal('addCategoryModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- EDIT CATEGORY MODAL --}}
    <div id="editCategoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('editCategoryModal')"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200">
                <form id="editCategoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-yellow-50 px-4 py-3 border-b border-yellow-100">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-pen-to-square text-yellow-600"></i>
                            Edit Kategori
                        </h3>
                    </div>
                    <div class="bg-white px-6 py-6 space-y-4">
                        <input type="hidden" id="edit_category_id">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Kategori</label>
                            <input type="text" name="name" id="edit_category_name" class="w-full border-gray-300 rounded-lg text-sm focus:ring-yellow-500 border-2 border-yellow-500 bg-yellow-50 pl-2 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" id="edit_category_description" rows="2" class="w-full border-gray-300 rounded-lg text-sm focus:ring-yellow-500 border-2 border-yellow-500 bg-yellow-50 pl-2 py-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Ikon Kategori</label>
                            <input type="hidden" name="icon" id="edit_icon_value" value="fa-solid fa-box">
                            <div id="edit_icon_grid" class="grid grid-cols-8 gap-2 max-h-64 overflow-y-auto p-2 border border-gray-200 rounded-lg bg-yellow-50">
                                <!-- Icons will be rendered by JavaScript -->
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fa-solid fa-info-circle"></i> Pilih salah satu icon untuk kategori aset Anda
                            </p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 sm:ml-3 sm:w-auto sm:text-sm">Update</button>
                        <button type="button" onclick="closeModal('editCategoryModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal('detailModal')"></div>
            <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden border border-gray-200">
                <div class="relative h-48 bg-gray-200">
                    <img id="detailImage" src="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-6">
                        <div>
                            <h2 class="text-2xl font-bold text-white" id="detailName">-</h2>
                            <p class="text-white/80 text-sm" id="detailCategory">-</p>
                        </div>
                    </div>
                    <button onclick="closeModal('detailModal')" class="absolute top-4 right-4 bg-black/30 hover:bg-black/50 text-white rounded-full p-2 w-8 h-8 flex items-center justify-center transition"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div><p class="text-xs text-gray-500 uppercase font-bold mb-1">Serial Number</p><p class="text-gray-800 font-mono text-sm" id="detailSN">-</p></div>
                        <div><p class="text-xs text-gray-500 uppercase font-bold mb-1">Status</p><span id="detailStatus" class="px-2 py-1 rounded text-xs font-bold uppercase">-</span></div>
                        <div><p class="text-xs text-gray-500 uppercase font-bold mb-1">Lokasi</p><p class="text-gray-800 text-sm"><i class="fa-solid fa-location-dot text-blue-500 mr-1"></i> <span id="detailLoc">-</span></p></div>
                        <div><p class="text-xs text-gray-500 uppercase font-bold mb-1">Tanggal Beli</p><p class="text-gray-800 text-sm" id="detailDate">-</p></div>
                    </div>
                    <hr class="border-gray-100 mb-4">
                    <h3 class="font-bold text-gray-700 mb-3">Spesifikasi Teknis</h3>
                    <div id="detailSpecs" class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600 space-y-2"></div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button onclick="closeModal('detailModal')" class="px-4 py-2 bg-white border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-100">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT AJAX / API REAL --}}
    <script>
        // GLOBAL VARS
        let currentCatId = null;
        let currentPage = 1;
        let lastPage = 1;

        function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

        // FUNGSI UTAMA: Load Aset via API (RESET HALAMAN KE 1)
        function loadAssetsByCategory(categoryId, categoryName, assetCount, categoryDesc) {
            currentCatId = categoryId;
            currentPage = 1;

            // 1. Update UI Header
            document.getElementById('detailTitle').innerText = categoryName;
            document.getElementById('detailCount').innerText = assetCount;
            document.getElementById('detailDesc').innerText = categoryDesc || 'Tidak ada deskripsi untuk kategori ini.';
            document.getElementById('categoryActions').classList.remove('hidden');
            document.getElementById('emptyState').classList.add('hidden');

            // Highlight Tombol Aktif
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('bg-blue-50', 'border-blue-200');
                btn.classList.add('border-transparent');
            });
            const activeBtn = document.getElementById('btn-cat-' + categoryId);
            if(activeBtn) {
                activeBtn.classList.add('bg-blue-50', 'border-blue-200');
                activeBtn.classList.remove('border-transparent');
            }

            // Load Data
            fetchAssets(categoryId, 1);
        }

        // FUNGSI FETCH DENGAN PAGINATION
        async function fetchAssets(categoryId, page) {
            const tableBody = document.getElementById('assetTableBody');
            const spinner = document.getElementById('loadingSpinner'); // SEKARANG ID INI SUDAH ADA
            const emptyState = document.getElementById('emptyState');
            const pagination = document.getElementById('paginationContainer');

            tableBody.innerHTML = '';

            // Cek apakah spinner ada sebelum akses classList
            if(spinner) spinner.classList.remove('hidden');

            pagination.classList.add('hidden');

            try {
                // Fetch data dengan parameter ?page=
                const response = await fetch(`/admin/assets/by-category/${categoryId}?page=${page}`);
                const result = await response.json();
                const paginatedData = result.data; // Object pagination Laravel
                const assets = paginatedData.data; // Array data asetnya

                if(spinner) spinner.classList.add('hidden');

                if (assets.length === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    emptyState.classList.add('hidden'); // Sembunyikan empty state jika ada data

                    // Update Pagination State
                    currentPage = paginatedData.current_page;
                    lastPage = paginatedData.last_page;
                    const perPage = paginatedData.per_page;

                    // Render Tabel
                    assets.forEach((asset, index) => {
                        // LOGIKA INDEX: (Halaman - 1) * PerPage + IndexLoop + 1
                        const rowNumber = (currentPage - 1) * perPage + index + 1;

                        // Badge Status
                        let statusBadge = '';
                        if(asset.status === 'normal') statusBadge = '<span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-[10px] font-bold">Normal</span>';
                        else if(asset.status === 'rusak') statusBadge = '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-[10px] font-bold">Rusak</span>';
                        else statusBadge = '<span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-[10px] font-bold">Maintenance</span>';

                        // Handle Gambar & Lokasi
                        let locName = asset.location ? asset.location.name : '-';
                        const imgUrl = asset.image ? `/storage/${asset.image}` : 'https://via.placeholder.com/150?text=No+Img';

                        const isManajer = {{ auth()->user()->isManajer() ? 'true' : 'false' }};
                        let row = `
                            <tr class="hover:bg-gray-50 transition border-b border-gray-50">
                                <td class="px-6 py-4 text-center text-xs font-bold text-gray-500">${rowNumber}</td>
                                <td class="px-6 py-3">
                                    <img src="${imgUrl}" class="w-10 h-10 rounded object-cover border bg-gray-100 cursor-pointer" onclick="showAssetDetail(${asset.id})">
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-800 block">${asset.name}</span>
                                    <span class="text-[10px] text-gray-400 font-mono">SN: ${asset.serial_number ?? '-'}</span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <i class="fa-solid fa-location-dot text-gray-400 mr-1"></i> ${locName}
                                </td>
                                <td class="px-6 py-4">${statusBadge}</td>
                                ` + (!isManajer ? `
                                <td class="px-6 py-4 text-center">
                                    <button onclick="showAssetDetail(${asset.id})" class="text-gray-500 hover:text-blue-600" title="Lihat Detail"><i class="fa-solid fa-circle-info"></i></button>
                                </td>` : '') + `
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });

                    // Update UI Pagination
                    updatePaginationUI();
                }

            } catch (error) {
                console.error('Error fetching assets:', error);
                if(spinner) spinner.classList.add('hidden');
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center text-red-500 py-4">Gagal mengambil data.</td></tr>`;
            }
        }

        // FUNGSI NAVIGASI PAGINATION
        function updatePaginationUI() {
            const pagination = document.getElementById('paginationContainer');
            pagination.classList.remove('hidden');

            document.getElementById('pageCurrent').innerText = currentPage;
            document.getElementById('pageTotal').innerText = lastPage;

            document.getElementById('btnPrev').disabled = currentPage <= 1;
            document.getElementById('btnNext').disabled = currentPage >= lastPage;
        }

        function changePage(direction) {
            if(direction === 'prev' && currentPage > 1) {
                fetchAssets(currentCatId, currentPage - 1);
            } else if (direction === 'next' && currentPage < lastPage) {
                fetchAssets(currentCatId, currentPage + 1);
            }
        }

        // FUNGSI TAMPIL DETAIL (Modal)
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
                    document.getElementById('detailStatus').className = `px-2 py-1 rounded text-xs font-bold uppercase ${asset.status === 'normal' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
                    document.getElementById('detailLoc').innerText = asset.location ? asset.location.name : '-';
                    document.getElementById('detailDate').innerText = asset.purchase_date ? new Date(asset.purchase_date).toLocaleDateString() : '-';

                    // Specs
                    const specBox = document.getElementById('detailSpecs');
                    specBox.innerHTML = '';
                    if (asset.specifications && Object.keys(asset.specifications).length > 0) {
                        const specs = typeof asset.specifications === 'string' ? JSON.parse(asset.specifications) : asset.specifications;
                        Object.entries(specs).forEach(([k, v]) => {
                            specBox.innerHTML += `<div class="flex justify-between border-b border-gray-200 pb-1 last:border-0"><span class="font-medium">${k}</span><span>${v}</span></div>`;
                        });
                    } else {
                        specBox.innerHTML = '<p class="text-gray-400 italic">Tidak ada spesifikasi khusus.</p>';
                    }

                    document.getElementById('detailModal').classList.remove('hidden');
                }
            } catch (e) { Swal.fire('Gagal!', 'Gagal memuat detail.', 'error'); }
        }

        // ========================================
        // CURATED ICON GRID SYSTEM
        // ========================================
        
        // Curated list of 30+ common icons for IT & Facility Asset Management
        const ASSET_ICONS = [
            // IT Equipment
            'fa-solid fa-laptop',
            'fa-solid fa-desktop',
            'fa-solid fa-server',
            'fa-solid fa-network-wired',
            'fa-solid fa-router',
            'fa-solid fa-wifi',
            'fa-solid fa-ethernet',
            'fa-solid fa-hard-drive',
            
            // Peripherals
            'fa-solid fa-keyboard',
            'fa-solid fa-computer-mouse',
            'fa-solid fa-print',
            'fa-solid fa-scanner',
            'fa-solid fa-headphones',
            'fa-solid fa-microphone',
            'fa-solid fa-video',
            'fa-solid fa-camera',
            
            // Facility & Infrastructure
            'fa-solid fa-building',
            'fa-solid fa-door-open',
            'fa-solid fa-lightbulb',
            'fa-solid fa-bolt',
            'fa-solid fa-plug',
            'fa-solid fa-temperature-half',
            'fa-solid fa-fan',
            'fa-solid fa-fire-extinguisher',
            
            // Furniture & Office
            'fa-solid fa-chair',
            'fa-solid fa-couch',
            'fa-solid fa-table',
            'fa-solid fa-bed',
            'fa-solid fa-door-closed',
            
            // Vehicles & Transport
            'fa-solid fa-car',
            'fa-solid fa-truck',
            'fa-solid fa-van-shuttle',
            'fa-solid fa-motorcycle',
            
            // Tools & Equipment
            'fa-solid fa-toolbox',
            'fa-solid fa-wrench',
            'fa-solid fa-screwdriver',
            'fa-solid fa-hammer',
            
            // Security & Safety
            'fa-solid fa-shield-halved',
            'fa-solid fa-lock',
            'fa-solid fa-key',
            'fa-solid fa-bell',
            
            // Miscellaneous
            'fa-solid fa-box',
            'fa-solid fa-boxes-stacked',
            'fa-solid fa-warehouse',
            'fa-solid fa-clipboard',
            'fa-solid fa-phone',
            'fa-solid fa-mobile-screen',
        ];

        // Render icon grid for a specific modal
        function renderIconGrid(gridId, hiddenInputId, selectedIcon = 'fa-solid fa-box') {
            const grid = document.getElementById(gridId);
            grid.innerHTML = '';
            
            ASSET_ICONS.forEach(iconClass => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `w-10 h-10 rounded-lg border-2 flex items-center justify-center transition-all hover:scale-110 ${
                    iconClass === selectedIcon 
                        ? 'border-blue-500 bg-blue-100 text-blue-600 shadow-md' 
                        : 'border-gray-300 bg-white text-gray-600 hover:border-blue-300'
                }`;
                button.innerHTML = `<i class="${iconClass} text-lg"></i>`;
                button.onclick = () => selectIcon(iconClass, gridId, hiddenInputId);
                grid.appendChild(button);
            });
        }

        // Handle icon selection
        function selectIcon(iconClass, gridId, hiddenInputId) {
            // Update hidden input
            document.getElementById(hiddenInputId).value = iconClass;
            
            // Re-render grid with new selection
            renderIconGrid(gridId, hiddenInputId, iconClass);
        }

        // FUNGSI FILTER KATEGORI (SIDEBAR SEARCH)
        function filterCategories() {
            const searchValue = document.getElementById('categorySearch').value.toLowerCase();
            const categoryItems = document.querySelectorAll('.category-item');
            
            categoryItems.forEach(item => {
                const categoryName = item.querySelector('h4').textContent.toLowerCase();
                if (categoryName.includes(searchValue)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // FUNGSI EDIT KATEGORI (UPDATED FOR ICON GRID)
        function openEditCategoryModal(id, name, description, icon) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('edit_category_description').value = description || '';
            
            // Set icon value and render grid
            const selectedIcon = icon || 'fa-solid fa-box';
            document.getElementById('edit_icon_value').value = selectedIcon;
            renderIconGrid('edit_icon_grid', 'edit_icon_value', selectedIcon);
            
            // Set form action
            document.getElementById('editCategoryForm').action = `/admin/categories/${id}`;
            
            openModal('editCategoryModal');
        }

        // Initialize icon grids when modals are opened
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
            
            // Render icon grids on modal open
            if (id === 'addCategoryModal') {
                renderIconGrid('add_icon_grid', 'add_icon_value', 'fa-solid fa-box');
            }
        }

        // FUNGSI DELETE KATEGORI
        async function deleteCategory(id, name) {
            const result = await Swal.fire({
                title: 'Hapus Kategori?',
                html: `Kategori <strong>${name}</strong> akan dihapus.<br><small class="text-gray-500">Aset di kategori ini tidak akan terhapus.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/admin/categories/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    const json = await response.json();

                    if (response.ok) {
                        await Swal.fire('Terhapus!', 'Kategori berhasil dihapus.', 'success');
                        window.location.reload();
                    } else {
                        throw new Error(json.message || 'Gagal menghapus kategori');
                    }
                } catch (error) {
                    Swal.fire('Gagal!', error.message, 'error');
                }
            }
        }

        // HANDLE EDIT FORM SUBMIT
        document.getElementById('editCategoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const id = document.getElementById('edit_category_id').value;
            
            try {
                const response = await fetch(`/admin/categories/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const json = await response.json();

                if (response.ok) {
                    closeModal('editCategoryModal');
                    await Swal.fire('Berhasil!', 'Kategori berhasil diupdate.', 'success');
                    window.location.reload();
                } else if (response.status === 422) {
                    // Tampilkan pesan validasi field-by-field
                    const messages = Object.values(json.errors).flat().join('\n');
                    Swal.fire({
                        icon: 'error',
                        title: 'Data tidak valid',
                        text: messages,
                    });
                } else {
                    throw new Error(json.message || 'Gagal mengupdate kategori');
                }
            } catch (error) {
                Swal.fire('Gagal!', error.message, 'error');
            }
        });

        @if($errors->any())
            // Auto-buka modal tambah jika ada error validasi dari server
            document.addEventListener('DOMContentLoaded', function() {
                openModal('addCategoryModal');
            });
        @endif
    </script>
@endsection
