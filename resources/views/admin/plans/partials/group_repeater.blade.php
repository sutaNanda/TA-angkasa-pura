{{--
    KOMPONEN 1: Form Repeater Grup untuk halaman Create/Edit Maintenance Plan
    
    Cara pakai: Include partial ini di dalam form plan.
    Variabel yang dibutuhkan dari controller: $groups (Collection TechnicianGroup), $plan (jika halaman edit)
    
    Data yang dikirim ke controller (via form POST):
    groups[0][group_id] = 1, groups[0][start_time] = 08:00
    groups[1][group_id] = 2, groups[1][start_time] = 20:00
--}}

<div
    x-data="{
        rows: {{ $plan?->groups->map(fn($g) => ['group_id' => $g->id, 'start_time' => $g->pivot->start_time ?? '']) ?? '[]' }},

        addRow() {
            this.rows.push({ group_id: '', start_time: '' });
        },

        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        }
    }"
    class="space-y-3"
>
    <div class="flex items-center justify-between">
        <label class="block text-sm font-semibold text-gray-700">
            Penugasan Grup & Jam Mulai
            <span class="text-xs font-normal text-gray-400 ml-1">(setiap grup dapat memiliki jam mulai berbeda)</span>
        </label>
        {{-- Tombol tambah baris baru --}}
        <button
            type="button"
            x-on:click="addRow()"
            class="flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Grup
        </button>
    </div>

    {{-- Pesan jika belum ada baris --}}
    <p x-show="rows.length === 0" class="text-sm text-gray-400 italic py-2">
        Belum ada grup ditambahkan. Klik "+ Tambah Grup" di atas.
    </p>

    {{-- Daftar baris repeater --}}
    <template x-for="(row, index) in rows" :key="index">
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 transition-all">

            {{-- Dropdown Pilih Grup --}}
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Grup Teknisi</label>
                <select
                    :name="`groups[${index}][group_id]`"
                    x-model="row.group_id"
                    required
                    class="w-full text-sm rounded-md border-2 border-gray-200 bg-white py-2 "
                >
                    <option value=""> Pilih Grup </option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Input Jam Mulai --}}
            <div class="w-40">
                <label class="block text-xs text-gray-500 mb-1">Jam Mulai</label>
                <input
                    type="time"
                    :name="`groups[${index}][start_time]`"
                    x-model="row.start_time"
                    class="w-full text-sm rounded-md border-2 py-2 border-gray-200 bg-white pl-3"
                    placeholder="08:00"
                >
            </div>

            {{-- Tombol Hapus Baris --}}
            <div class="pt-5">
                <button
                    type="button"
                    x-on:click="removeRow(index)"
                    x-show="rows.length > 1"
                    class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                    title="Hapus baris ini"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </template>

    {{-- Validasi: minimal satu grup harus dipilih (JavaScript side) --}}
    <p class="text-xs text-gray-400">
        * Jika tanpa jam mulai, tugas akan tampil sebagai "Fleksibel" di dasbor teknisi.
    </p>
</div>
