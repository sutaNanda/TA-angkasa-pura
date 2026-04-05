@extends('layouts.admin')

@section('title', 'Template Checklist')
@section('page-title', 'Manajemen Template SOP')

@section('content')
    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto items-center">
            <div class="relative w-full md:w-80">
                <input type="text" id="searchInput" placeholder="Cari SOP (misal: Genset)..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition w-full" onkeyup="filterTable()">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400"></i>
            </div>
            
            <select id="filterCategory" onchange="filterTable()" class="py-2 px-3 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm transition w-full md:w-48">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
                <option value="umum">Berlaku Umum</option>
            </select>
        </div>

        <div class="flex gap-2 w-full md:w-auto">
            @if(!auth()->user()->isManajer())
            <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition shadow-sm w-full md:w-auto">
                <i class="fa-solid fa-plus"></i> Buat SOP Baru
            </button>
            @endif
        </div>
    </div>

    {{-- Tabel Data --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="bg-gray-50 text-gray-700 uppercase font-bold text-xs border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 w-12 text-center">No</th> {{-- Kolom Index --}}
                        <th class="px-6 py-4">Nama SOP</th>
                        <th class="px-6 py-4">Target Aset</th>
                        <th class="px-6 py-4 text-center">Isi Checklist</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50 transition group template-row" 
                            data-name="{{ strtolower($template->name) }}" 
                            data-category="{{ $template->category_id ?? 'umum' }}">
                            {{-- LOGIKA NOMOR URUT (Support Pagination) --}}
                            <td class="px-6 py-4 text-center font-bold text-gray-400 text-xs">
                                {{ ($templates->currentPage() - 1) * $templates->perPage() + $loop->iteration }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 text-base">{{ $template->name }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ Str::limit($template->description, 50) ?: 'Tidak ada deskripsi' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($template->category)
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                                        <i class="{{ $template->category->icon ?? 'fa-solid fa-box' }} text-gray-400"></i>
                                        {{ $template->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Berlaku Umum</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 font-bold text-xs border border-gray-200">
                                    {{ $template->items->count() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    {{-- TOMBOL DETAIL BARU --}}
                                    <button onclick="showDetailSOP({{ $template->id }})" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-600 hover:text-white transition flex items-center justify-center" title="Lihat Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button onclick="editTemplate({{ $template->id }})" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition flex items-center justify-center" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button onclick="deleteTemplate({{ $template->id }})" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition flex items-center justify-center" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fa-regular fa-clipboard text-4xl mb-3"></i>
                                    <p class="text-sm">Belum ada template SOP.</p>
                                    @if(!auth()->user()->isManajer())
                                    <button onclick="openModal()" class="mt-2 text-blue-600 hover:underline text-sm font-bold">Buat Sekarang</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templates->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $templates->links() }}
            </div>
        @endif
    </div>

    {{-- ================================================= --}}
    {{-- MODAL 1: FORM INPUT (CREATE / EDIT) --}}
    {{-- ================================================= --}}
    <div id="checklistModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeModal()"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 ">
                <form id="checklistForm" onsubmit="saveChecklist(event)">
                    <input type="hidden" id="templateId">

                    <div class="bg-white px-8 py-5 border-b border-gray-100 flex justify-between items-center sticky top-0 z-20 shadow-sm">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2 " id="modalTitle">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                Buat SOP Baru
                            </h3>
                            <p class="text-md text-gray-500 mt-2">Atur standar pertanyaan checklist untuk teknisi.</p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row max-h-[75vh]">
                        {{-- Kiri: Info Dasar --}}
                        <div class="w-full md:w-1/3 bg-gray-50 px-6 py-6 border-r border-gray-100 overflow-y-auto space-y-5">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">1. Informasi Dasar</h4>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nama SOP <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" class="w-full border-gray-300 rounded-lg text-sm border-2 border-gray-300 pl-2 py-2 bg-white" placeholder="Cek Harian Genset" required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Target Kategori Aset <span class="text-red-500">*</span></label>
                                <select id="category_id" name="category_id" class="w-full border-gray-300 rounded-lg text-sm bg-white border-2 border-gray-300 pl-2 py-2" required>
                                    <option value="" disabled selected>-- Pilih Kategori Aset --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" class="text-gray-500">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Deskripsi</label>
                                <textarea id="description" name="description" rows="2" class="w-full border-gray-300 rounded-lg text-sm border-2 border-gray-300 pl-2 py-2 bg-white" placeholder="Deskripsi SOP"></textarea>
                            </div>
                        </div>

                        {{-- Kanan: Daftar Pertanyaan --}}
                        <div class="w-full md:w-2/3 px-8 py-6 overflow-y-auto custom-scrollbar bg-white">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">2. Butir Pertanyaan Checklist</h4>
                                <button type="button" onclick="addItemRow()" class="text-xs bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 hover:shadow-lg cursor-pointer transition font-bold flex items-center gap-2">
                                    <i class="fa-solid fa-plus"></i> Tambah Item
                                </button>
                            </div>
                            <div id="itemsContainer" class="space-y-3 pb-10"></div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-8 py-4 flex justify-end gap-3 border-t border-gray-100 sticky bottom-0 z-20">
                        <button type="button" onclick="closeModal()" class="px-6 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">Batal</button>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-md hover:shadow-lg transition transform active:scale-95 flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Simpan SOP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================= --}}
    {{-- MODAL 2: VIEW DETAIL (READ ONLY) - FITUR BARU --}}
    {{-- ================================================= --}}
    <div id="detailSOPModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/30 bg-opacity-75 transition-opacity" onclick="closeDetailModal()"></div>
            <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200">

                {{-- Header Detail --}}
                <div class="bg-gray-50 px-8 py-6 border-b border-gray-100">
                    <div class="flex justify-between items-start">
                        <div>
                            <span id="detailBadge" class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-700 px-2.5 py-1 rounded-md text-xs font-bold border border-blue-200 mb-2">
                                <i class="fa-regular fa-sun"></i> Harian
                            </span>
                            <h3 class="text-2xl font-bold text-gray-900" id="detailNameSOP">Nama SOP</h3>
                            <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
                                <i class="fa-solid fa-layer-group text-gray-400"></i>
                                <span id="detailCatSOP">Kategori</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-4 bg-white p-3 rounded-lg border border-gray-200" id="detailDescSOP">Deskripsi SOP...</p>
                </div>

                {{-- List Pertanyaan --}}
                <div class="px-8 py-6 bg-white max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b pb-2">Daftar Pengecekan</h4>
                    <ul class="space-y-0 divider-y divider-gray-100" id="detailItemsList">
                        {{-- Items akan diinject via JS --}}
                    </ul>
                </div>

                <div class="bg-gray-50 px-8 py-4 flex justify-end">
                    <button onclick="closeDetailModal()" class="px-6 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-100 transition shadow-sm">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
    <script>
        // --- MODAL UTAMA (Form) ---
        function openModal() {
            document.getElementById('checklistForm').reset();
            document.getElementById('templateId').value = '';
            document.getElementById('itemsContainer').innerHTML = '';
            document.getElementById('modalTitle').innerHTML = '<div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-list-check"></i></div> Buat SOP Baru';
            addItemRow();
            document.getElementById('checklistModal').classList.remove('hidden');
        }

        function closeModal() { document.getElementById('checklistModal').classList.add('hidden'); }

        // --- MODAL DETAIL (Read Only) ---
        function closeDetailModal() { document.getElementById('detailSOPModal').classList.add('hidden'); }

        async function showDetailSOP(id) {
            try {
                // Fetch data
                const response = await fetch(`/admin/checklists/${id}`);
                const result = await response.json();

                if(result.status === 'success') {
                    const data = result.data;

                    // Populate Header
                    document.getElementById('detailNameSOP').innerText = data.name;
                    document.getElementById('detailDescSOP').innerText = data.description || 'Tidak ada deskripsi tambahan.';
                    document.getElementById('detailCatSOP').innerText = data.category ? data.category.name : 'Umum / Semua Aset';



                    // Populate List Items
                    const listContainer = document.getElementById('detailItemsList');
                    listContainer.innerHTML = '';

                    if(data.items.length > 0) {
                        data.items.forEach((item, index) => {
                            let typeLabel = '';
                            if(item.type === 'pass_fail') typeLabel = '<span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded border">Pilihan: OK/Rusak</span>';
                            else if(item.type === 'number') typeLabel = `<span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded border border-blue-100">Angka (${item.unit || '-'})</span>`;
                            else if(item.type === 'checkbox') typeLabel = '<span class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded border border-green-100">Centang (Ya/Tidak)</span>';
                            else if(item.type === 'header') typeLabel = '<span class="text-xs bg-gray-600 text-white px-2 py-0.5 rounded border border-gray-700 font-bold uppercase tracking-wider">Header / Judul Bagian</span>';
                            else typeLabel = '<span class="text-xs bg-yellow-50 text-yellow-600 px-2 py-0.5 rounded border border-yellow-100">Teks Catatan</span>';

                            const li = document.createElement('li');
                            li.className = 'py-3 flex gap-3 items-start border-b border-gray-50 last:border-0';
                            li.innerHTML = `
                                <div class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 flex flex-shrink-0 items-center justify-center text-xs font-bold mt-0.5">${index + 1}</div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800">${item.question}</p>
                                    <div class="mt-1">${typeLabel}</div>
                                </div>
                            `;
                            listContainer.appendChild(li);
                        });
                    } else {
                        listContainer.innerHTML = '<div class="text-center py-4 text-gray-400 text-sm">Belum ada item checklist.</div>';
                    }

                    document.getElementById('detailSOPModal').classList.remove('hidden');
                }
            } catch (error) {
                Swal.fire('Gagal!', 'Gagal memuat detail SOP.', 'error');
            }
        }

        // --- ROW BUILDER ---
        function addItemRow(question = '', type = 'pass_fail', unit = '') {
            const container = document.getElementById('itemsContainer');
            const div = document.createElement('div');
            div.className = 'flex gap-3 items-start bg-white p-4 rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition duration-200 group animate-fade-in item-row relative';
            div.innerHTML = `

                <div class="flex-1 space-y-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pertanyaan / Instruksi</label>
                        <input type="text" name="questions[]" value="${question}" class="w-full border-gray-300 rounded-lg text-sm border-2 border-gray-500 pl-2 py-2 mt-4" placeholder="Cek Suhu Radiator" required>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-2/3">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tipe Jawaban</label>
                            <select name="types[]" class="w-full rounded-lg text-xs bg-white text-gray-700 border-2 border-gray-300 h-9 font-medium" onchange="toggleUnitInput(this)">
                                <option value="pass_fail" ${type === 'pass_fail' ? 'selected' : ''}>🔘 Pilihan: Bagus / Rusak</option>
                                <option value="number" ${type === 'number' ? 'selected' : ''}>🔢 Isian Angka (Suhu, Volt)</option>
                                <option value="text" ${type === 'text' ? 'selected' : ''}>📝 Catatan Teks</option>
                                <option value="checkbox" ${type === 'checkbox' ? 'selected' : ''}>✅ Ya / Tidak (Centang)</option>
                                <option value="header" ${type === 'header' ? 'selected' : ''}>📑 Header / Judul Bagian</option>
                            </select>
                        </div>
                        <div class="w-1/3 ${type !== 'number' ? 'hidden' : ''} unit-wrapper">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Satuan</label>
                            <input class="pl-2 border-2 border-gray-300 rounded-lg text-xs py-2" type="text" name="units[]" value="${unit || ''}" placeholder="Cth: °C">
                        </div>
                        <input type="hidden" name="units[]" value="" class="unit-hidden ${type === 'number' ? 'hidden' : ''}">
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-gray-300 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition absolute top-2 right-2"><i class="fa-solid fa-trash-can text-lg"></i></button>
            `;
            container.appendChild(div);
            div.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function toggleUnitInput(select) {
            const row = select.closest('.flex-1').querySelector('.flex');
            const unitWrapper = row.querySelector('.unit-wrapper');
            const unitHidden = row.querySelector('.unit-hidden');
            if (select.value === 'number') {
                unitWrapper.classList.remove('hidden'); unitHidden.classList.add('hidden'); unitHidden.disabled = true; unitWrapper.querySelector('input').disabled = false;
            } else {
                unitWrapper.classList.add('hidden'); unitHidden.classList.remove('hidden'); unitHidden.disabled = false; unitWrapper.querySelector('input').disabled = true;
            }
        }

        // --- EDIT & SAVE ---
        async function editTemplate(id) {
            try {
                const response = await fetch(`/admin/checklists/${id}`);
                const result = await response.json();
                if(result.status === 'success') {
                    const data = result.data;
                    document.getElementById('templateId').value = data.id;
                    document.getElementById('name').value = data.name;
                    document.getElementById('description').value = data.description;
                    document.getElementById('category_id').value = data.category_id || '';
                    const container = document.getElementById('itemsContainer');
                    container.innerHTML = '';
                    if(data.items.length > 0) { data.items.forEach(item => addItemRow(item.question, item.type, item.unit)); } else { addItemRow(); }
                    document.getElementById('modalTitle').innerHTML = '<div class="w-8 h-8 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center"><i class="fa-solid fa-pen-to-square"></i></div> Edit SOP';
                    document.getElementById('checklistModal').classList.remove('hidden');
                }
            } catch (error) { Swal.fire('Gagal!', 'Gagal mengambil data.', 'error'); }
        }

        async function saveChecklist(e) {
            e.preventDefault();
            const id = document.getElementById('templateId').value;
            const url = id ? `/admin/checklists/${id}` : "{{ route('admin.checklists.store') }}";
            const method = id ? 'PUT' : 'POST';
            const form = document.getElementById('checklistForm');
            const formData = new FormData(form);
            const questions = [], types = [], units = [];
            const rows = document.querySelectorAll('#itemsContainer .item-row');
            rows.forEach(row => {
                questions.push(row.querySelector('input[name="questions[]"]').value);
                const typeSelect = row.querySelector('select[name="types[]"]');
                types.push(typeSelect.value);
                if(typeSelect.value === 'number') units.push(row.querySelector('.unit-wrapper input').value); else units.push(null);
            });
            const dataPayload = {
                name: formData.get('name'), category_id: formData.get('category_id'), description: formData.get('description'),
                questions: questions, types: types, units: units
            };
            try {
                const response = await fetch(url, {
                    method: method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(dataPayload)
                });
                const result = await response.json();
                if(!response.ok) throw new Error(result.message);
                
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                location.reload();

            } catch (error) { 
                Swal.fire('Error!', error.message, 'error');
            }
        }

        function deleteTemplate(id) {
            Swal.fire({
                title: 'Hapus SOP ini?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch(`/admin/checklists/${id}`, { method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} });
                        if(response.ok) {
                            await Swal.fire('Terhapus!', 'SOP berhasil dihapus.', 'success');
                            location.reload();
                        } else {
                            throw new Error('Gagal menghapus data.');
                        }
                    } catch (error) {
                        Swal.fire('Gagal!', error.message, 'error');
                    }
                }
            });
        }

        // FUNGSI FILTER TABLE
        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const categoryValue = document.getElementById('filterCategory').value;
            
            const rows = document.querySelectorAll('.template-row');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const category = row.getAttribute('data-category');
                
                // Check all filters
                const matchSearch = name.includes(searchValue);
                const matchCategory = !categoryValue || category === categoryValue;
                
                // Show/hide row
                if (matchSearch && matchCategory) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide empty state
            const tbody = document.querySelector('tbody');
            const emptyRow = tbody.querySelector('.empty-state-row');
            
            if (visibleCount === 0 && !emptyRow) {
                const tr = document.createElement('tr');
                tr.className = 'empty-state-row';
                tr.innerHTML = `
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fa-solid fa-search text-5xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600 font-bold text-lg mb-1">Tidak ada data yang sesuai</p>
                            <p class="text-gray-400 text-sm mb-4">Coba ubah filter atau kata kunci pencarian</p>
                            <button onclick="resetFilters()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition shadow-sm">
                                <i class="fa-solid fa-rotate-right mr-2"></i> Reset
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            } else if (visibleCount > 0 && emptyRow) {
                emptyRow.remove();
            }
        }

        // FUNGSI RESET FILTERS
        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterCategory').value = '';
            filterTable();
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    </style>
@endsection
