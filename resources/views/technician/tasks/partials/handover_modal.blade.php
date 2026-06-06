{{--
    KOMPONEN 2: Dropdown Hybrid Pool untuk Form Buat Work Order (Admin)

    Cara pakai: Taruh di dalam form pembuatan tiket manual di admin.
    Variabel yang dibutuhkan: $groups (Collection TechnicianGroup)
--}}

<div class="form-group">
    <label for="assigned_group_id" class="block text-sm font-medium text-gray-700 mb-1">
        Tugaskan ke Grup
        <span class="ml-1 text-xs font-normal text-gray-400">(opsional)</span>
    </label>

    <select
        id="assigned_group_id"
        name="assigned_group_id"
        class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    >
        {{-- Opsi pertama: Pool Umum (null) — siapapun bisa klaim --}}
        <option value="">
            🌐 Pool Umum — semua grup dapat melihat & mengklaim
        </option>

        {{-- Daftar grup yang tersedia --}}
        @foreach ($groups as $group)
            <option
                value="{{ $group->id }}"
                {{ old('assigned_group_id') == $group->id ? 'selected' : '' }}
            >
                {{ $group->name }}
                @if ($group->members_count ?? false)
                    ({{ $group->members_count }} anggota)
                @endif
            </option>
        @endforeach
    </select>

    <p class="mt-1 text-xs text-gray-400">
        Pilih grup spesifik agar hanya anggota grup tersebut yang bisa melihat dan mengambil tiket ini.
        Jika dikosongkan, tiket masuk ke <strong>Pool Umum</strong> dan semua grup dapat mengklaimnya.
    </p>
</div>


{{--
    KOMPONEN 3: Modal Handover Antar-Grup (Teknisi)

    Cara pakai: Include di halaman detail task (technician/tasks/show.blade.php).
    Variabel yang dibutuhkan: $groups (Collection TechnicianGroup, kecuali grup user saat ini)

    Trigger tombol di luar modal:
    <button x-on:click="$dispatch('open-handover-modal')">Handover ke Grup Lain</button>
--}}

<div
    x-data="{ open: false }"
    x-on:open-handover-modal.window="open = true"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        x-on:click="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
    ></div>

    {{-- Panel Modal --}}
    <div
        class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl z-10"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8"
        x-transition:enter-end="opacity-100 translate-y-0"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900">Handover ke Grup Lain</h3>
                <p class="text-xs text-gray-400 mt-0.5">Tiket akan dipindah ke antrean grup tujuan</p>
            </div>
            <button
                type="button"
                x-on:click="open = false"
                class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('technician.tasks.handover', $task->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="px-6 py-5 space-y-4">

                {{-- Pilih Grup Tujuan --}}
                <div>
                    <label for="to_group_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Grup Tujuan <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="to_group_id"
                        name="to_group_id"
                        required
                        class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">-- Pilih grup penerima --</option>
                        @foreach ($groups as $group)
                            {{-- Exclude grup sendiri (sudah difilter di controller, tapi tambah di UI juga) --}}
                            @if ($group->id !== auth()->user()->technician_group_id)
                                <option value="{{ $group->id }}">
                                    {{ $group->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('to_group_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Catatan Progres / Alasan Handover --}}
                <div>
                    <label for="handover_notes" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Catatan & Status Progres <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="handover_notes"
                        name="notes"
                        rows="4"
                        required
                        minlength="10"
                        placeholder="Jelaskan kondisi terkini dan alasan handover..."
                        class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-none"
                    ></textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Upload Foto Progres (opsional) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Foto Progres
                        <span class="text-xs font-normal text-gray-400">(opsional, maks. 5 file)</span>
                    </label>
                    <input
                        type="file"
                        name="photos[]"
                        accept="image/jpeg,image/png,image/jpg"
                        multiple
                        class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors"
                    >
                </div>

            </div>

            {{-- Footer Tombol --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
                <button
                    type="button"
                    x-on:click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-sm"
                >
                    Konfirmasi Handover
                </button>
            </div>
        </form>
    </div>
</div>
