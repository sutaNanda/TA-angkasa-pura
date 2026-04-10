@extends('layouts.technician')

@section('title', 'Inventaris Aset')
@section('page-title', 'Struktur & Denah Lokasi')

@section('content')
<div class="flex flex-col lg:flex-row h-[calc(100vh-140px)] md:h-[calc(100vh-140px)] gap-6 relative" x-data="{ showSidebar: false }">

    {{-- TOGGLE BUTTON (Mobile & Desktop) --}}
    <div class="lg:hidden flex justify-start mb-2">
        <button @click="showSidebar = !showSidebar" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-blue-700 transition flex items-center justify-center gap-2 text-sm font-bold w-full"
                id="sidebarToggleBtn flex gap-2">
            <i class="fa-solid fa-folder-tree"></i> Pilih Lokasi / Ruangan
        </button>
    </div>

    {{-- SIDEBAR: LOCATION TREE --}}
    <div :class="showSidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
         id="locationSidebar" 
         class="fixed lg:relative inset-y-0 left-0 z-50 w-80 lg:w-1/3 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-[calc(100vh-100px)] lg:h-full transform transition-transform duration-300">
        
        {{-- Overlay untuk mobile --}}
        <div x-show="showSidebar" @click="showSidebar = false" x-transition.opacity
             class="fixed inset-[-100px] w-[200vw] h-[200vh] bg-black/50 lg:hidden -z-10" 
             style="display: none;"></div>
        
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-xl relative z-10">
            <h3 class="font-bold text-gray-800 text-sm">Struktur Lokasi</h3>
            <div class="flex gap-2">
                <button @click="showSidebar = false" class="lg:hidden text-gray-600 hover:bg-gray-100 w-8 h-8 rounded transition flex items-center justify-center">
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
        <div class="p-4 md:p-6 border-b border-gray-100 bg-gray-50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex gap-4 items-center">
                <div class="w-12 h-12 md:w-16 md:h-16 bg-white p-1 rounded-lg shadow-sm border border-gray-200 shrink-0">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=DEMO" alt="QR" class="w-full h-full object-contain opacity-30 transition-opacity duration-300" id="headerQr">
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="text-lg md:text-xl font-bold text-gray-800 truncate max-w-[150px] sm:max-w-xs md:max-w-md" id="headerTitle">Pilih Lokasi</h2>
                        <span class="bg-gray-200 text-gray-600 text-[10px] px-2 py-0.5 rounded border border-gray-300 font-bold uppercase hidden" id="headerIdBadge">ID: <span id="headerId">-</span></span>
                    </div>
                    <p class="text-xs md:text-sm text-gray-500 mb-0 line-clamp-1" id="headerBreadcrumb">Silakan pilih lokasi di atas terlebih dahulu.</p>
                </div>
            </div>
            
            {{-- Tombol Detail Lokasi dipindahkan ke sini sebagai ganti Tambah Aset --}}
            <div class="flex gap-2 w-full md:w-auto shrink-0 justify-end">
                <button type="button" @click="showScanOptions = true" class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 shadow-sm transition flex items-center gap-2 flex-1 md:flex-none justify-center">
                    <i class="fa-solid fa-qrcode"></i> <span class="md:hidden">Scan QR</span><span class="hidden md:inline">Scan QR Ruangan</span>
                </button>
                <a href="#" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 shadow-sm disabled:opacity-50 transition flex items-center gap-2 pointer-events-none opacity-50 flex-1 md:flex-none justify-center" id="btnDetailLoc">
                    <i class="fa-solid fa-folder-open"></i> Detail <span class="hidden md:inline">Ruangan</span>
                </a>
            </div>
        </div>

        {{-- Table/Card Area --}}
        <div class="flex-1 overflow-y-auto relative flex flex-col bg-gray-50 md:bg-white">
            <div class="flex-1 overflow-y-auto custom-scrollbar p-0 md:p-0" id="assetListContainer">
                
                {{-- Desktop Table View --}}
                <table class="w-full text-sm text-left text-gray-600 hidden md:table">
                    <thead class="bg-white text-gray-500 uppercase font-bold text-xs sticky top-0 z-10 border-b shadow-sm">
                        <tr>
                            <th class="px-6 py-4 w-12 text-center bg-gray-50">No</th>
                            <th class="px-6 py-4 w-16 bg-gray-50">Foto</th>
                            <th class="px-6 py-4 bg-gray-50">Nama Aset</th>
                            <th class="px-6 py-4 bg-gray-50">Kategori</th>
                            <th class="px-6 py-4 bg-gray-50 text-center">Status</th>
                            <th class="px-6 py-4 text-center w-32 bg-gray-50">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="assetTableBody"></tbody>
                </table>

                {{-- Mobile Card View --}}
                <div class="md:hidden flex flex-col gap-3 p-4" id="assetCardContainer"></div>

                {{-- Empty State --}}
                <div id="emptyState" class="flex flex-col items-center justify-center h-64 text-gray-400 hidden">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-map-location-dot text-3xl text-gray-300"></i>
                    </div>
                    <p class="font-medium text-gray-600">Lokasi ini belum memiliki aset.</p>
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

{{-- JAVASCRIPT LOGIC --}}
<script>
    // GLOBAL VARS
    let currentLocId = null;
    let currentLocName = '';
    let currentLocCode = ''; 
    let currentPage = 1;
    let lastPage = 1;

    const storageUrl = "{{ asset('storage') }}";

    document.addEventListener('DOMContentLoaded', () => fetchLocations());

    // --- TREE LOGIC ---
    async function fetchLocations() {
        const container = document.getElementById('locationTreeContainer');
        try {
            const res = await fetch("{{ route('technician.locations.tree') }}", { headers: {'Accept':'application/json'} });
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

    function escapeQuotes(str) {
        if (!str) return '';
        return String(str).replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ');
    }

    function createTreeNode(loc) {
        const childrenArray = loc.children_recursive || loc.children || [];
        const hasChildren = childrenArray.length > 0;
        
        const node = document.createElement('div');
        node.className = "mb-1";
        
        const header = document.createElement('div');
        header.id = `node-header-${loc.id}`; 
        header.className = "flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-blue-50 cursor-pointer transition group text-gray-600";
        
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
            `;
            // Removed modal action buttons (Edit/Add Location) for Technician
            
        node.appendChild(header);

        if (hasChildren) {
            const childContainer = document.createElement('div');
            childContainer.className = "children-container space-y-0.5 hidden"; 
            childrenArray.forEach(c => childContainer.appendChild(createTreeNode(c)));
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
                childContainer.classList.remove('hidden');
                chevron.style.transform = 'rotate(90deg)';
            } else {
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

        // Update Detail Button Route
        const detailBtn = document.getElementById('btnDetailLoc');
        detailBtn.href = `/technician/locations/${id}`;
        detailBtn.classList.remove('pointer-events-none', 'opacity-50');

        // 1. Remove active class from previous
        document.querySelectorAll('.tree-node-active').forEach(el => {
            el.classList.remove('tree-node-active', 'bg-blue-50', 'text-blue-600');
            el.classList.add('text-gray-600');
            
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

        // Auto Close Sidebar on Mobile
        if(window.innerWidth < 1024) { 
            const toggleBtn = document.querySelector('[x-data]');
            if(toggleBtn && toggleBtn.__x) {
                toggleBtn.__x.$data.showSidebar = false;
            } else {
                // Fallback direct DOM manipulation if alpine instance not found easily
                document.getElementById('locationSidebar').__x.$data.showSidebar = false;
            }
        }

        loadAssetsByLocation(id, 1);
    }

    async function loadAssetsByLocation(id, page) {
        const tbody = document.getElementById('assetTableBody');
        const cardBody = document.getElementById('assetCardContainer');
        const empty = document.getElementById('emptyState');
        const pagination = document.getElementById('paginationContainer');

        // Skeleton Loading
        const skeletonRow = `<tr><td colspan="6" class="p-4"><div class="animate-pulse flex space-x-4"><div class="h-4 bg-gray-200 rounded w-full"></div></div></td></tr>`;
        const skeletonCard = `<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 animate-pulse"><div class="flex gap-4"><div class="w-16 h-16 bg-gray-200 rounded-lg shrink-0"></div><div class="flex-1 space-y-2"><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="h-3 bg-gray-200 rounded w-1/2"></div></div></div></div>`;
        
        tbody.innerHTML = skeletonRow.repeat(3);
        cardBody.innerHTML = skeletonCard.repeat(3);

        empty.classList.add('hidden');
        pagination.classList.add('hidden');

        try {
            const res = await fetch(`/technician/assets/by-location/${id}?page=${page}`);
            const json = await res.json();
            const paginatedData = json.data;
            const assets = paginatedData.data;

            tbody.innerHTML = '';
            cardBody.innerHTML = '';

            if(assets.length === 0) {
                empty.classList.remove('hidden');
            } else {
                currentPage = paginatedData.current_page;
                lastPage = paginatedData.last_page;
                const perPage = paginatedData.per_page;

                assets.forEach((asset, index) => {
                    const rowNumber = (currentPage - 1) * perPage + index + 1;
                    
                    let statusColor = 'gray';
                    let statusClass = 'bg-gray-100 text-gray-600 border-gray-200';
                    switch(asset.status) {
                        case 'normal': statusColor = 'green'; statusClass = 'bg-green-100 text-green-700 border-green-200'; break;
                        case 'rusak': statusColor = 'red'; statusClass = 'bg-red-100 text-red-700 border-red-200'; break;
                        case 'maintenance': statusColor = 'yellow'; statusClass = 'bg-yellow-100 text-yellow-700 border-yellow-200'; break;
                    }

                    let imgUrl = 'https://via.placeholder.com/150?text=No+Img';
                    if (asset.images && Array.isArray(asset.images) && asset.images.length > 0) {
                        imgUrl = `${storageUrl}/${asset.images[0]}`;
                    } else if (asset.image) {
                        imgUrl = `${storageUrl}/${asset.image}`;
                    }

                    const catName = asset.category ? asset.category.name : '<span class="text-gray-400 italic">Tanpa Kategori</span>';

                    // DESKTOP ROW
                    const row = `
                        <tr class="hover:bg-blue-50/50 border-b border-gray-50 transition group">
                            <td class="px-6 py-4 text-center text-xs font-bold text-gray-400">${rowNumber}</td>
                            <td class="px-6 py-3">
                                <div class="relative w-10 h-10 rounded-lg overflow-hidden border border-gray-200 group-hover:border-blue-200 transition">
                                    <img src="${imgUrl}" class="w-full h-full object-cover">
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-gray-800 text-sm">${asset.name}</div>
                                <div class="text-[10px] text-gray-400 font-mono">${asset.serial_number || 'No SN'}</div>
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-600">${catName}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="${statusClass} px-2.5 py-1 rounded-md border shadow-sm text-[10px] uppercase font-bold tracking-wide break-words text-center truncate">${asset.status}</span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <a href="/technician/assets/${asset.id}" class="w-8 h-8 mx-auto rounded-full hover:bg-white hover:shadow-sm text-gray-500 hover:text-blue-600 transition flex items-center justify-center border border-transparent hover:border-gray-200" title="Detail">
                                    <i class="fa-solid fa-folder-open"></i>
                                </a>
                            </td>
                        </tr>`;
                    tbody.innerHTML += row;

                    // MOBILE CARD
                    const card = `
                        <a href="/technician/assets/${asset.id}" class="block bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:border-blue-300 transition-colors active:scale-[0.98] transform">
                            <div class="flex gap-4 items-start">
                                <div class="w-16 h-16 rounded-xl overflow-hidden border border-gray-200 shrink-0 shadow-sm">
                                    <img src="${imgUrl}" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="font-bold text-gray-800 text-sm truncate pr-2">${asset.name}</h4>
                                        <div class="w-2 h-2 rounded-full shrink-0 mt-1.5 bg-${statusColor}-500 shadow-sm shadow-${statusColor}-500/50"></div>
                                    </div>
                                    <p class="text-[10px] font-mono text-gray-500 mb-2 truncate">${asset.serial_number || 'No Serial Number'}</p>
                                    
                                    <div class="flex justify-between items-end mt-2">
                                        <span class="text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded border border-gray-100">${catName}</span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-${statusColor}-600">${asset.status}</span>
                                    </div>
                                </div>
                            </div>
                        </a>`;
                    cardBody.innerHTML += card;
                });
                updatePaginationUI();
            }
        } catch (e) { tbody.innerHTML = ''; cardBody.innerHTML = ''; }
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

</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>
@endsection
