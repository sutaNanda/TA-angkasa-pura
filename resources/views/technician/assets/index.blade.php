@extends('layouts.technician')

@section('title', 'Inventaris Aset')
@section('page-title', 'Inventaris Aset')

@section('content')

<style>
    /* ===== LAYOUT ===== */
    .asset-page-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        height: calc(100dvh - 130px);
        min-height: 500px;
        position: relative;
    }

    @media (min-width: 1024px) {
        .asset-page-wrapper {
            flex-direction: row;
            gap: 1.25rem;
        }
    }

    /* ===== SIDEBAR ===== */
    .location-sidebar {
        background: #ffffff;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px -2px rgba(0,0,0,.05);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        /* Mobile: fixed slide-in */
        position: fixed;
        inset: 0 auto 0 0;
        width: min(85vw, 340px);
        z-index: 60;
        transform: translateX(-110%);
        transition: transform .3s cubic-bezier(.4,0,.2,1), box-shadow .3s;
        border-radius: 0 1.25rem 1.25rem 0;
    }

    .location-sidebar.open {
        transform: translateX(0);
        box-shadow: 0 8px 40px rgba(30,41,59,.18);
    }

    @media (min-width: 1024px) {
        .location-sidebar {
            position: relative;
            inset: auto;
            width: 300px;
            min-width: 260px;
            flex-shrink: 0;
            transform: none !important;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }
    }

    @media (min-width: 1280px) {
        .location-sidebar { width: 320px; }
    }

    /* ===== OVERLAY ===== */
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,.42);
        backdrop-filter: blur(3px);
        z-index: 59;
    }

    .sidebar-overlay.active { display: block; }

    @media (min-width: 1024px) {
        .sidebar-overlay { display: none !important; }
    }

    /* ===== TREE ===== */
    .children-container {
        position: relative;
        margin-left: 1rem;
        padding-left: .75rem;
        border-left: 1.5px dashed #cbd5e1;
    }

    .tree-node-btn {
        display: flex;
        align-items: center;
        gap: .625rem;
        width: 100%;
        padding: .5rem .625rem;
        border-radius: .75rem;
        border: 1px solid transparent;
        background: transparent;
        cursor: pointer;
        text-align: left;
        transition: all .2s ease;
        color: #475569;
        font-size: .8125rem;
        font-weight: 500;
    }

    .tree-node-btn:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
        transform: translateX(2px);
    }

    .tree-node-btn.active {
        background: linear-gradient(to right, #eff6ff, #e0e7ff);
        border-color: #bfdbfe;
        color: #1d4ed8;
        font-weight: 700;
        box-shadow: inset 3px 0 0 #3b82f6;
    }

    .tree-node-btn.active .node-icon {
        background: #3b82f6;
        border-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(59,130,246,.3);
    }

    .node-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .6rem;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #64748b;
        flex-shrink: 0;
        transition: all .2s;
        font-size: .75rem;
    }

    .chevron-btn {
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .375rem;
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        flex-shrink: 0;
        font-size: .65rem;
        transition: all .2s;
    }

    .chevron-btn:hover { background: #e2e8f0; color: #334155; }
    .chevron-btn.expanded { transform: rotate(90deg); color: #3b82f6; }

    /* ===== MAIN CONTENT ===== */
    .main-content {
        flex: 1;
        min-width: 0;
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px -2px rgba(0,0,0,.05);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* ===== TABLE ===== */
    .asset-table { width: 100%; font-size: .875rem; text-align: left; color: #4b5563; }

    .asset-table thead th {
        padding: 1rem 1.5rem;
        background: #f8fafc;
        color: #475569;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        border-bottom: 2px solid #f1f5f9;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .asset-table tbody tr {
        border-bottom: 1px solid #f8fafc;
        transition: all .15s;
    }

    .asset-table tbody tr:hover { 
        background: #f8fafc; 
        box-shadow: inset 2px 0 0 #3b82f6;
    }

    .asset-table td {
        padding: .75rem 1.5rem;
        vertical-align: middle;
    }

    /* ===== STATUS BADGE ===== */
    .st-normal      { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; box-shadow: 0 2px 4px rgba(16,185,129,.1); }
    .st-rusak       { background: #fef2f2; color: #e11d48; border: 1px solid #fecdd3; box-shadow: 0 2px 4px rgba(225,29,72,.1); }
    .st-maintenance { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; box-shadow: 0 2px 4px rgba(217,119,6,.1); }
    .st-default     { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: .25rem .75rem;
        border-radius: 9999px;
        font-size: .625rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        white-space: nowrap;
    }
    
    .status-pill::before {
        content: '';
        display: block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: currentColor;
    }

    /* ===== ASSET CARD GRID ===== */
    .asset-card {
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        padding: 0;
        text-decoration: none;
        color: inherit;
        transition: all .3s cubic-bezier(.4,0,.2,1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);
    }

    .asset-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 20px 25px -5px rgba(59,130,246,.15), 0 10px 10px -5px rgba(59,130,246,.04);
        transform: translateY(-4px);
    }

    .asset-card:active { transform: scale(.98); }

    .card-image-box {
        position: relative;
        width: 100%;
        height: 180px;
        background: #f8fafc;
        overflow: hidden;
        border-bottom: 1px solid #f1f5f9;
    }

    .card-image-box img {
        width: 100%;
        height: 100%;
        object-cover: cover;
        transition: transform .5s ease;
    }

    .asset-card:hover .card-image-box img {
        transform: scale(1.1);
    }

    .card-content {
        padding: 1.25rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .card-badge-loc {
        display: inline-flex;
        align-items: center;
        gap: .375rem;
        padding: .25rem .625rem;
        background: #eff6ff;
        color: #3b82f6;
        border-radius: .5rem;
        font-size: .625rem;
        font-weight: 700;
        margin-bottom: .75rem;
        width: fit-content;
        max-width: 100%;
    }

    /* ===== MODAL DETAIL ===== */
    .custom-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15,23,42,.6);
        backdrop-filter: blur(6px);
    }
    .custom-modal.active { display: flex; }
    .modal-content {
        background: #fff;
        width: 100%;
        max-width: 650px;
        max-height: 90vh;
        border-radius: 1.5rem;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1);
    }

    /* ===== TOGGLE SUB-LOKASI ===== */
    .toggle-bar {
        padding: .75rem 1.5rem;
        background: linear-gradient(to right, #f8fafc, #ffffff);
        border-bottom: 1px solid #e2e8f0;
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    .toggle-switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }
    .toggle-switch input { display: none; }
    .toggle-slider {
        width: 42px;
        height: 24px;
        background: #cbd5e1;
        border-radius: 99px;
        position: relative;
        transition: .3s cubic-bezier(.4,0,.2,1);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }
    .toggle-slider::before {
        content: "";
        position: absolute;
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background: white;
        border-radius: 50%;
        transition: .3s cubic-bezier(.4,0,.2,1);
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    input:checked + .toggle-slider { background: linear-gradient(to right, #3b82f6, #6366f1); }
    input:checked + .toggle-slider::before { transform: translateX(18px); }

    .badge-info {
        font-size: .65rem;
        font-weight: 700;
        padding: .35rem .85rem;
        border-radius: 99px;
        border: 1px solid transparent;
        transition: .3s;
    }
    .badge-on  { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .badge-off { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }

    /* ===== SCROLLBAR ===== */
    .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

{{-- OVERLAY --}}
<div id="sidebarOverlay" class="sidebar-overlay" onclick="closeSidebar()"></div>

<div class="asset-page-wrapper">

    {{-- TOGGLE (Mobile Only) --}}
    <div class="lg:hidden">
        <button onclick="openSidebar()"
                class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-white to-slate-50 border border-slate-200 text-slate-700 px-4 py-3 rounded-xl shadow-sm hover:border-blue-300 transition text-sm font-bold active:scale-[.98]">
            <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-folder-tree text-sm"></i>
            </div>
            <span>Pilih Lokasi / Ruangan</span>
            <i class="fa-solid fa-chevron-right text-xs text-slate-400 ml-auto"></i>
        </button>
    </div>

    {{-- SIDEBAR --}}
    <div class="location-sidebar" id="locationSidebar">
        {{-- Header --}}
        <div class="px-4 py-4 border-b border-slate-100 flex items-center justify-between gap-3 shrink-0 bg-transparent">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-500/30 flex items-center justify-center text-white shrink-0">
                    <i class="fa-solid fa-sitemap text-sm"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-800 text-sm leading-tight tracking-wide">Area Kerja</h3>
                    <p class="text-[11px] text-slate-500 leading-tight font-medium mt-0.5">Struktur Lokasi Aset</p>
                </div>
            </div>
            <button onclick="closeSidebar()"
                    class="lg:hidden w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition">
                <i class="fa-solid fa-times text-sm"></i>
            </button>
        </div>

        {{-- Search --}}
        <div class="px-3 pt-4 pb-2 shrink-0">
            <div class="relative group">
                <i class="fa-solid fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none group-focus-within:text-blue-500 transition-colors"></i>
                <input type="text" id="locationSearch" placeholder="Cari gedung, ruangan..."
                       oninput="filterTree(this.value)"
                       class="w-full pl-9 pr-3 py-2.5 text-xs font-medium rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm placeholder-slate-400">
            </div>
        </div>

        {{-- Tree --}}
        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scroll" id="locationTreeContainer">
            <div class="empty-state min-h-[160px] flex flex-col items-center justify-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-circle-notch fa-spin text-blue-500 text-xl"></i>
                </div>
                <span class="text-xs font-medium text-slate-500">Memuat struktur tata letak...</span>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content" id="mainContent">

        {{-- Header --}}
        <div class="px-4 py-4 md:px-6 md:py-5 border-b border-slate-100 shrink-0 relative overflow-hidden bg-white">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-50 to-transparent rounded-full opacity-60 pointer-events-none -mr-10 -mt-10"></div>
            
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 relative z-10">
                {{-- Info --}}
                <div class="flex items-center gap-4 flex-1 min-w-0">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl overflow-hidden shrink-0 shadow-inner  flex items-center justify-center p-1" id="headerQrWrapper">
                        <i class="fa-solid fa-qrcode text-indigo-200/60 text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-lg md:text-xl font-extrabold text-slate-800 truncate tracking-tight" id="headerTitle">Pilih Lokasi</h2>
                            <span class="hidden text-[10px] px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 border border-indigo-200 font-bold uppercase tracking-wider shadow-sm" id="headerIdBadge">
                                ID: <span id="headerId">-</span>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-1 font-medium flex items-center gap-1" id="headerBreadcrumb">
                            <i class="fa-solid fa-info-circle text-slate-300"></i> Silakan pilih lokasi di panel sebelah kiri.
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 shrink-0">
                    <a href="#" id="btnDetailLoc"
                       class="inline-flex items-center justify-center bg-blue-600 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition-all shadow-md hover:shadow-lg hover:shadow-blue-500/40 hover:-translate-y-0.5 pointer-events-none  active:scale-[.97]">
                        <i class="fa-solid fa-folder-open text-sm mr-2"></i>
                        <span>Detail Ruangan</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats Bar --}}
        <div id="statsBar" class="hidden px-4 md:px-6 py-3 border-b border-slate-100 bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 overflow-x-auto custom-scroll pb-1">
                <div class="bg-white border border-slate-200 rounded-lg px-3 py-1.5 flex items-center gap-2 shadow-sm shrink-0">
                    <i class="fa-solid fa-boxes-stacked text-slate-400"></i>
                    <span class="text-[11px] text-slate-500 font-medium whitespace-nowrap">Total: <strong class="text-slate-800 text-xs ml-0.5" id="statTotal">0</strong></span>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-1.5 flex items-center gap-2 shadow-sm shrink-0">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[11px] text-emerald-700 font-bold whitespace-nowrap">Normal: <span id="statNormal">0</span></span>
                </div>
                <div class="bg-amber-50 border border-amber-100 rounded-lg px-3 py-1.5 flex items-center gap-2 shadow-sm shrink-0">
                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                    <span class="text-[11px] text-amber-700 font-bold whitespace-nowrap">Maintenance: <span id="statMaintenance">0</span></span>
                </div>
                <div class="bg-rose-50 border border-rose-100 rounded-lg px-3 py-1.5 flex items-center gap-2 shadow-sm shrink-0">
                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                    <span class="text-[11px] text-rose-700 font-bold whitespace-nowrap">Rusak: <span id="statRusak">0</span></span>
                </div>
            </div>
        </div>

        {{-- Toggle Sub-Lokasi --}}
        <div id="toggleSubLocationBar" class="toggle-bar">
            <div class="flex items-center gap-3">
                <label class="toggle-switch">
                    <input type="checkbox" id="toggleIncludeSub" checked>
                    <div class="toggle-slider"></div>
                </label>
                <span class="text-sm font-extrabold text-slate-700">Tampilkan Aset Turunan</span>
            </div>
            <span id="toggleSubLabel" class="badge-info badge-on shadow-sm">
                <i class="fa-solid fa-layer-group mr-1 opacity-80"></i> Menampilkan aset di lokasi ini & turunannya
            </span>
        </div>

        {{-- List Area --}}
        <div class="flex-1 overflow-y-auto custom-scroll bg-slate-50/30 pb-4" id="assetListContainer" style="min-height: 200px;">

            {{-- Initial State --}}
            <div id="initialState" class="flex flex-col items-center justify-center h-full min-h-[300px] text-center px-4">
                <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-blue-100 to-indigo-50 flex items-center justify-center mb-4 shadow-inner border border-blue-100/50">
                    <i class="fa-solid fa-map-location-dot text-blue-500 text-3xl"></i>
                </div>
                <h4 class="font-extrabold text-slate-800 text-base mb-1.5 tracking-tight">Eksplorasi Aset</h4>
                <p class="text-sm text-slate-500 max-w-sm">Pilih area, gedung, atau ruangan di panel samping untuk melihat daftar aset yang dikelola.</p>
                <button onclick="openSidebar()"
                        class="lg:hidden mt-6 inline-flex items-center gap-2 bg-slate-800 text-white text-xs font-bold px-5 py-2.5 rounded-xl hover:bg-slate-700 transition shadow-lg shadow-slate-800/20">
                    <i class="fa-solid fa-list-ul"></i> Buka Struktur Lokasi
                </button>
            </div>

            {{-- Empty State --}}
            <div id="emptyState" class="hidden flex flex-col items-center justify-center h-full min-h-[300px] text-center px-4">
                <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-slate-100 to-slate-50 flex items-center justify-center mb-4 shadow-inner border border-slate-200">
                    <i class="fa-solid fa-box-open text-slate-400 text-3xl"></i>
                </div>
                <h4 class="font-extrabold text-slate-700 text-base mb-1.5 tracking-tight">Tidak Ada Aset</h4>
                <p class="text-sm text-slate-500 max-w-sm">Lokasi yang dipilih belum memiliki data inventaris aset apapun.</p>
            </div>

            {{-- Asset Card Grid (Responsive for all devices) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6 p-6" id="assetCardContainer"></div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL ASET --}}
<div id="detailModal" class="custom-modal">
    <div class="modal-content animate-fadeIn">
        {{-- Header Gallery --}}
        <div class="relative h-56 md:h-72 bg-gradient-to-br from-slate-800 to-slate-900 group shrink-0 overflow-hidden">
            <img id="detailImage" src="" class="w-full h-full object-cover mix-blend-overlay opacity-80">
            
            {{-- Navigation Arrows --}}
            <button id="modalPrevBtn" onclick="navigateGallery(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/30 text-white rounded-full backdrop-blur-md transition border border-white/20 hidden">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </button>
            <button id="modalNextBtn" onclick="navigateGallery(1)" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/30 text-white rounded-full backdrop-blur-md transition border border-white/20 hidden">
                <i class="fa-solid fa-chevron-right text-sm"></i>
            </button>

            {{-- Dots --}}
            <div id="modalDots" class="absolute bottom-20 left-0 right-0 flex justify-center gap-2"></div>

            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent flex items-end p-6 md:p-8">
                <div class="min-w-0 flex-1 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                    <span id="detailStatusPill" class="status-pill mb-3 shadow-lg border-white/10 bg-white/10 backdrop-blur-md text-white">-</span>
                    <h2 id="detailAssetName" class="text-2xl md:text-3xl font-extrabold text-white truncate tracking-tight drop-shadow-md mb-1">-</h2>
                    <p class="text-blue-200 text-xs font-bold uppercase tracking-widest flex items-center gap-1.5 opacity-90">
                        <i class="fa-solid fa-layer-group"></i> <span id="detailCategoryName">-</span>
                    </p>
                </div>
            </div>

            <button onclick="closeDetailModal()" class="absolute top-5 right-5 w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-rose-500 hover:text-white hover:border-transparent text-white/80 rounded-full backdrop-blur-md transition border border-white/20">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6 custom-scroll bg-slate-50/50">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                        <i class="fa-solid fa-barcode text-slate-300"></i> Serial Number
                    </div>
                    <p id="detailSN" class="font-mono text-sm font-bold text-slate-800 break-words">-</p>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">
                        <i class="fa-solid fa-map-pin text-slate-300"></i> Lokasi
                    </div>
                    <p id="detailLocation" class="text-sm font-bold text-blue-600 line-clamp-2">
                        <span id="detailLocName">-</span>
                    </p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 md:p-6 border border-slate-200 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full opacity-50 -z-10"></div>
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-sliders text-[10px]"></i></div>
                    Spesifikasi Teknis
                </h3>
                <div id="detailSpecs" class="space-y-0 divide-y divide-slate-100/80"></div>
            </div>

            <div id="detailChildSection" class="hidden">
                 <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-md bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-diagram-project text-[10px]"></i></div>
                    Komponen Terkait
                </h3>
                <div id="detailChildren" class="space-y-2.5"></div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="p-5 border-t border-slate-200 bg-white flex justify-end items-center gap-3 shrink-0">
            <button onclick="closeDetailModal()" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">Batal / Tutup</button>
            <a id="detailPageLink" href="#" class="px-6 py-2.5 bg-slate-800 text-white text-xs font-bold rounded-xl shadow-lg shadow-slate-800/20 hover:bg-slate-900 transition flex items-center gap-2">
                <span>Kelola Aset</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>

<script>
// ===== STATE =====
let currentLocId   = null;
let currentLocName = '';
let totalItems     = 0;
let allLocations   = []; // Cached tree for search filtering
let currentLocHasChildren = false;

// Gallery state
let galleryImages = [];
let galleryIdx    = 0;

const storageUrl = "{{ asset('storage') }}";
const treeUrl    = "{{ route('technician.locations.tree') }}";

// ===== SIDEBAR =====
function openSidebar() {
    document.getElementById('locationSidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    document.getElementById('locationSidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
    document.body.style.overflow = '';
}

// ===== INIT =====
document.addEventListener('DOMContentLoaded', () => {
    fetchLocations();
    
    const toggleSub = document.getElementById('toggleIncludeSub');
    if (toggleSub) {
        toggleSub.addEventListener('change', () => {
            updateToggleUI();
            if (currentLocId) loadAssets(currentLocId);
        });
    }
});

function updateToggleUI() {
    const isChecked = document.getElementById('toggleIncludeSub').checked;
    const label     = document.getElementById('toggleSubLabel');
    if (isChecked) {
        label.innerHTML = '<i class="fa-solid fa-layer-group mr-1 opacity-80"></i> Menampilkan aset di lokasi ini & turunannya';
        label.className   = 'badge-info badge-on shadow-sm';
    } else {
        label.innerHTML = '<i class="fa-solid fa-bullseye mr-1 opacity-80"></i> Hanya aset di lokasi utama';
        label.className   = 'badge-info badge-off';
    }
}

// ===== FETCH TREE =====
async function fetchLocations() {
    const container = document.getElementById('locationTreeContainer');
    try {
        const res  = await fetch(treeUrl, { headers: { Accept: 'application/json' } });
        const json = await res.json();
        allLocations = json.data || [];

        container.innerHTML = '';
        if (allLocations.length === 0) {
            container.innerHTML = '<div class="empty-state min-h-[120px] text-xs"><i class="fa-solid fa-folder-open text-2xl text-slate-200 mb-2"></i><br>Belum ada lokasi.</div>';
        } else {
            allLocations.forEach(loc => container.appendChild(createTreeNode(loc)));
        }
    } catch (e) {
        container.innerHTML = '<div class="text-center text-rose-500 text-xs p-4 font-medium bg-rose-50 rounded-xl border border-rose-100">Gagal memuat struktur lokasi.<br><button onclick="fetchLocations()" class="mt-2 font-bold underline">Coba lagi</button></div>';
    }
}

// ===== RENDER TREE NODE =====
function createTreeNode(loc) {
    const children = loc.children_recursive ?? loc.childrenRecursive ?? loc.children ?? [];
    const hasChildren = Array.isArray(children) && children.length > 0;

    const wrapper = document.createElement('div');
    wrapper.dataset.locId = loc.id;

    const row = document.createElement('div');
    row.className = 'flex items-center gap-1 mb-1';

    let childContainer = null;

    if (hasChildren) {
        const chevron = document.createElement('button');
        chevron.className = 'chevron-btn';
        chevron.type = 'button';
        chevron.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
        chevron.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (childContainer) toggleNode(chevron, childContainer);
        });
        row.appendChild(chevron);
    } else {
        const spacer = document.createElement('div');
        spacer.style.cssText = 'width:22px;flex-shrink:0;';
        row.appendChild(spacer);
    }

    // Dynamic coloring based on location type
    let iconClass = 'fa-location-dot';
    let colorStyles = '';
    
    if (loc.type === 'building') {
        iconClass = 'fa-building';
        colorStyles = 'color: #d97706; background-color: #fef3c7; border-color: #fde68a;'; // Amber
    } else if (loc.type === 'floor') {
        iconClass = 'fa-layer-group';
        colorStyles = 'color: #6366f1; background-color: #e0e7ff; border-color: #c7d2fe;'; // Indigo
    } else if (loc.type === 'virtual') {
        iconClass = 'fa-cloud';
        colorStyles = 'color: #0ea5e9; background-color: #e0f2fe; border-color: #bae6fd;'; // Sky
    } else {
        colorStyles = 'color: #059669; background-color: #d1fae5; border-color: #a7f3d0;'; // Emerald
    }

    const btn = document.createElement('button');
    btn.id    = `node-${loc.id}`;
    btn.type  = 'button';
    btn.className = 'tree-node-btn flex-1';

    btn.dataset.locId   = loc.id;
    btn.dataset.locName = loc.name || '';
    btn.dataset.locDesc = loc.description || '';
    btn.dataset.locCode = loc.code || '';

    btn.innerHTML = `
        <div class="node-icon" style="${colorStyles}"><i class="fa-solid ${iconClass}"></i></div>
        <span class="truncate flex-1 node-label text-[13px] tracking-tight">${loc.name ?? ''}</span>
        ${hasChildren ? `<span class="shrink-0 text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md">${children.length}</span>` : ''}
    `;

    btn.addEventListener('click', function(e) {
        e.preventDefault();
        selectLocation(this.dataset.locId, this.dataset.locName, this.dataset.locDesc, this.dataset.locCode);
    });

    row.appendChild(btn);
    wrapper.appendChild(row);

    if (hasChildren) {
        childContainer = document.createElement('div');
        childContainer.className = 'children-container space-y-1 py-1 hidden';
        children.forEach(child => childContainer.appendChild(createTreeNode(child)));
        wrapper.appendChild(childContainer);
    }

    return wrapper;
}

function toggleNode(chevronBtn, childContainer) {
    const isHidden = childContainer.classList.contains('hidden');
    childContainer.classList.toggle('hidden', !isHidden);
    chevronBtn.classList.toggle('expanded', isHidden);
}

// ===== FILTER TREE =====
function filterTree(query) {
    const q = query.toLowerCase().trim();
    const container = document.getElementById('locationTreeContainer');
    if (!q) {
        container.innerHTML = '';
        allLocations.forEach(loc => container.appendChild(createTreeNode(loc)));
        return;
    }
    const results = flatFilterLocations(allLocations, q);
    container.innerHTML = '';
    if (results.length === 0) {
        container.innerHTML = '<div class="text-center text-slate-400 text-xs p-3">Tidak ditemukan kecocokan.</div>';
    } else {
        results.forEach(loc => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tree-node-btn w-full mb-1 border border-slate-100 bg-white shadow-sm';
            btn.dataset.locId   = loc.id;
            btn.dataset.locName = loc.name || '';
            btn.dataset.locDesc = loc.description || '';
            btn.dataset.locCode = loc.code || '';
            btn.innerHTML = `<div class="node-icon bg-blue-50 text-blue-500 border-blue-100"><i class="fa-solid fa-search"></i></div><span class="truncate flex-1 text-xs font-bold">${loc.name}</span>`;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                selectLocation(this.dataset.locId, this.dataset.locName, this.dataset.locDesc, this.dataset.locCode);
            });
            container.appendChild(btn);
        });
    }
}

function flatFilterLocations(nodes, q, result = []) {
    nodes.forEach(loc => {
        const children = loc.children_recursive ?? loc.childrenRecursive ?? loc.children ?? [];
        if ((loc.name || '').toLowerCase().includes(q) || (loc.code || '').toLowerCase().includes(q)) {
            result.push(loc);
        }
        if (children.length) flatFilterLocations(children, q, result);
    });
    return result;
}

// ===== SELECT LOCATION =====
function selectLocation(id, name, desc, code) {
    currentLocId   = id;
    currentLocName = name;

    document.getElementById('headerTitle').textContent        = name;
    document.getElementById('headerId').textContent           = code || id;
    document.getElementById('headerIdBadge').classList.remove('hidden');
    document.getElementById('headerBreadcrumb').innerHTML     = `<i class="fa-solid fa-info-circle text-blue-300"></i> ${desc || 'Tidak ada deskripsi detail untuk lokasi ini.'}`;

    // QR Code Update with nice fade effect
    const qrWrapper = document.getElementById('headerQrWrapper');
    const qrData    = code || `LOC-${id}`;
    qrWrapper.innerHTML = `
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(qrData)}&color=312e81&format=svg"
             alt="QR ${name}"
             class="w-full h-full object-contain p-0.5 mix-blend-multiply opacity-80"
             onerror="this.style.display='none'">
    `;

    const toggleBar = document.getElementById('toggleSubLocationBar');
    const nodeBtn = document.getElementById(`node-${id}`);
    const wrapper = nodeBtn?.closest('[data-loc-id]');
    currentLocHasChildren = wrapper?.querySelector('.children-container') !== null;
    toggleBar.style.display = currentLocHasChildren ? 'flex' : 'none';

    const detailBtn = document.getElementById('btnDetailLoc');
    detailBtn.href  = `/technician/locations/${id}`;
    detailBtn.classList.remove('pointer-events-none', 'opacity-50');
    detailBtn.classList.add('hover:-translate-y-0.5', 'hover:shadow-lg');

    document.querySelectorAll('.tree-node-btn.active').forEach(el => {
        el.classList.remove('active');
    });
    const activeBtn = document.getElementById(`node-${id}`);
    if (activeBtn) activeBtn.classList.add('active');

    if (window.innerWidth < 1024) closeSidebar();

    loadAssets(id);
}

// ===== LOAD ASSETS =====
async function loadAssets(id) {
    const cardBody   = document.getElementById('assetCardContainer');
    const emptyState = document.getElementById('emptyState');
    const initState  = document.getElementById('initialState');
    const statsBar   = document.getElementById('statsBar');
    const pagination = document.getElementById('paginationContainer');

    const includeSub = document.getElementById('toggleIncludeSub')?.checked ?? true;
    
    initState.classList.add('hidden');
    emptyState.classList.add('hidden');
    statsBar.classList.add('hidden');
    if(pagination) pagination.classList.add('hidden');

    const loaderHtml = `
        <div class="col-span-full py-20 flex flex-col items-center justify-center">
            <div class="w-12 h-12 border-4 border-blue-100 border-t-blue-500 rounded-full animate-spin mb-4"></div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest animate-pulse">Menyiapkan Katalog Aset...</p>
        </div>`;

    cardBody.innerHTML = loaderHtml;

    try {
        const url  = `/technician/assets/by-location/${id}?include_sub=${includeSub}`;
        const res  = await fetch(url);
        const json = await res.json();
        
        const assets = Array.isArray(json.data) ? json.data : (json.data?.data || []);

        cardBody.innerHTML = '';

        if (assets.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        }

        totalItems = json.data?.total || assets.length;

        // Display stats
        statsBar.classList.remove('hidden');
        const stats = assets.reduce((acc, cur) => {
            acc[cur.status] = (acc[cur.status] || 0) + 1;
            return acc;
        }, { normal: 0, maintenance: 0, rusak: 0 });
        
        document.getElementById('statTotal').textContent       = totalItems;
        document.getElementById('statNormal').textContent      = stats.normal || 0;
        document.getElementById('statMaintenance').textContent = stats.maintenance || 0;
        document.getElementById('statRusak').textContent       = stats.rusak || 0;

        assets.forEach((asset, index) => {
            const status = asset.status || 'default';
            const stMap  = {
                normal:      { cls: 'st-normal',      label: 'Normal' },
                rusak:       { cls: 'st-rusak',       label: 'Rusak' },
                maintenance: { cls: 'st-maintenance', label: 'Maintenance' },
            };
            const st = stMap[status] || { cls: 'st-default', label: status };

            let imgUrl = 'https://placehold.co/400x300/f8fafc/94a3b8?text=N/A';
            if (asset.images && Array.isArray(asset.images) && asset.images.length > 0) {
                imgUrl = `${storageUrl}/${asset.images[0]}`;
            } else if (asset.image) {
                imgUrl = `${storageUrl}/${asset.image}`;
            }

            const card = document.createElement('div');
            card.className = 'asset-card group';
            card.onclick   = () => showAssetDetail(asset.id);
            card.innerHTML = `
                <div class="card-image-box">
                    <img src="${imgUrl}" alt="${asset.name}" onerror="this.src='https://placehold.co/400x300/f8fafc/94a3b8?text=Image+Error'">
                    <div class="absolute top-3 right-3">
                         <span class="status-pill ${st.cls} shadow-lg">${st.label}</span>
                    </div>
                </div>
                <div class="card-content">
                    ${includeSub && currentLocHasChildren ? `
                        <div class="card-badge-loc">
                            <i class="fa-solid fa-location-dot text-blue-400"></i>
                            <span class="truncate">${asset.location?.name || 'Unknown'}</span>
                        </div>
                    ` : ''}
                    <h4 class="font-extrabold text-slate-800 text-sm mb-1 line-clamp-2 leading-tight group-hover:text-blue-600 transition-colors">${asset.name}</h4>
                    <p class="text-[10px] text-slate-400 font-mono mb-4">${asset.serial_number || 'No Serial Number'}</p>
                    
                    <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">
                            <i class="fa-solid fa-tag text-slate-300 mr-1"></i> ${asset.category?.name || 'Umum'}
                        </span>
                        <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-500 group-hover:text-white transition-all">
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </div>
                    </div>
                </div>
            `;
            cardBody.appendChild(card);
        });

    } catch (e) {
        console.error(e);
        cardBody.innerHTML = `
            <div class="col-span-full p-20 text-center">
                <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 border border-rose-100 shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <h4 class="font-extrabold text-slate-800 mb-1">Gagal Sinkronisasi</h4>
                <p class="text-xs text-slate-500">Gagal memuat data aset. Mohon periksa koneksi internet Anda.</p>
            </div>`;
    }
}

// ===== MODAL DETAIL FUNCTIONS =====
async function showAssetDetail(id) {
    Swal.fire({ 
        title: 'Membuka Detail', 
        html: 'Menyiapkan informasi aset...',
        didOpen: () => Swal.showLoading(), 
        allowOutsideClick: false,
        backdrop: `rgba(15,23,42,0.6)`
    });

    try {
        const res  = await fetch(`/technician/assets/${id}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }); 
        const json = await res.json();
        
        if (json.status !== 'success') throw new Error('Failed to load asset');
        const asset = json.data;
        Swal.close();

        // Populate Modal Gallery
        galleryImages = asset.image_urls || [];
        galleryIdx    = 0;
        updateModalGallery();

        // Status Map
        const stMap = {
            normal:      { cls: 'st-normal',      label: 'Normal' },
            rusak:       { cls: 'st-rusak',       label: 'Rusak' },
            maintenance: { cls: 'st-maintenance', label: 'Maintenance' },
        };
        const st = stMap[asset.status] || { cls: 'st-default', label: asset.status };
        
        document.getElementById('detailStatusPill').textContent = st.label;
        
        // Modal status pill specific classes
        let pillColor = 'bg-white/20 text-white';
        if(asset.status === 'normal') pillColor = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
        if(asset.status === 'rusak') pillColor = 'bg-rose-500/20 text-rose-300 border-rose-500/30';
        if(asset.status === 'maintenance') pillColor = 'bg-amber-500/20 text-amber-300 border-amber-500/30';

        document.getElementById('detailStatusPill').className   = `status-pill mb-3 shadow-lg backdrop-blur-md border ${pillColor}`;
        document.getElementById('detailAssetName').textContent  = asset.name;
        document.getElementById('detailCategoryName').textContent = asset.category?.name || 'Uncategorized';
        document.getElementById('detailSN').textContent         = asset.serial_number || 'TIDAK TERSEDIA';
        document.getElementById('detailLocName').textContent    = asset.location?.name || (asset.parent_asset?.location?.name ? asset.parent_asset.location.name + ' (Virtual)' : 'Tidak Ditemukan');
        
        // Specs Rendering
        const specCont = document.getElementById('detailSpecs');
        specCont.innerHTML = '';
        if (asset.specifications && typeof asset.specifications === 'object') {
            const entries = Object.entries(asset.specifications);
            if (entries.length > 0) {
                entries.forEach(([k, v]) => {
                    specCont.innerHTML += `
                        <div class="flex justify-between items-center py-2.5">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">${k}</span>
                            <span class="text-sm font-bold text-slate-800 text-right ml-4">${v}</span>
                        </div>`;
                });
            } else {
                specCont.innerHTML = '<div class="py-6 text-center text-xs text-slate-400 italic">Data spesifikasi belum diisi.</div>';
            }
        } else {
            specCont.innerHTML = '<div class="py-6 text-center text-xs text-slate-400 italic">Data spesifikasi belum diisi.</div>';
        }

        // Child Assets
        const childSect = document.getElementById('detailChildSection');
        const childCont = document.getElementById('detailChildren');
        childCont.innerHTML = '';
        if (asset.child_assets && asset.child_assets.length > 0) {
            childSect.classList.remove('hidden');
            asset.child_assets.forEach(c => {
                childCont.innerHTML += `
                    <div class="p-3.5 bg-white rounded-xl border border-slate-200 flex items-center justify-between gap-3 shadow-sm hover:border-indigo-300 transition-colors cursor-pointer" onclick="showAssetDetail(${c.id})">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0 border border-indigo-100">
                                <i class="fa-solid fa-microchip text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate leading-tight">${c.name}</p>
                                <p class="text-[9px] text-slate-400 uppercase font-black tracking-widest mt-1">${c.category?.name || 'Sub Komponen'}</p>
                            </div>
                        </div>
                        <div class="w-7 h-7 flex items-center justify-center rounded-full bg-slate-50 text-slate-400">
                             <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </div>
                    </div>`;
            });
        } else {
            childSect.classList.add('hidden');
        }

        document.getElementById('detailPageLink').href = `/technician/assets/${asset.id}`;
        document.getElementById('detailModal').classList.add('active');
        document.body.style.overflow = 'hidden';

    } catch (e) {
        console.error(e);
        Swal.fire({
            icon: 'error',
            title: 'Gagal Memuat',
            text: 'Terjadi kesalahan saat menarik data detail aset.',
            confirmButtonColor: '#3b82f6'
        });
    }
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.remove('active');
    document.body.style.overflow = '';
}

function updateModalGallery() {
    const img  = document.getElementById('detailImage');
    const prev = document.getElementById('modalPrevBtn');
    const next = document.getElementById('modalNextBtn');
    const dots = document.getElementById('modalDots');

    if (galleryImages.length > 0) {
        img.src = galleryImages[galleryIdx];
        if (galleryImages.length > 1) {
            prev.classList.remove('hidden');
            next.classList.remove('hidden');
            dots.innerHTML = galleryImages.map((_, i) => `
                <span class="w-2 h-2 rounded-full transition-all duration-300 ${i === galleryIdx ? 'bg-white scale-125 shadow-[0_0_8px_rgba(255,255,255,0.8)]' : 'bg-white/30 border border-white/20'}"></span>
            `).join('');
        } else {
            prev.classList.add('hidden');
            next.classList.add('hidden');
            dots.innerHTML = '';
        }
    } else {
        img.src = 'https://placehold.co/600x400/1e293b/475569?text=Tidak+Ada+Foto';
        prev.classList.add('hidden');
        next.classList.add('hidden');
        dots.innerHTML = '';
    }
}

function navigateGallery(dir) {
    galleryIdx += dir;
    if (galleryIdx < 0) galleryIdx = galleryImages.length - 1;
    if (galleryIdx >= galleryImages.length) galleryIdx = 0;
    updateModalGallery();
}
</script>
@endsection