

<div
    x-data="{
        rows: <?php echo e($plan?->groups->map(fn($g) => ['group_id' => $g->id, 'start_time' => $g->pivot->start_time ?? '']) ?? '[]'); ?>,

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
        
        <button
            type="button"
            x-on:click="addRow()"
            class="flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Grup
        </button>
    </div>

    
    <p x-show="rows.length === 0" class="text-sm text-gray-400 italic py-2">
        Belum ada grup ditambahkan. Klik "+ Tambah Grup" di atas.
    </p>

    
    <template x-for="(row, index) in rows" :key="index">
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 transition-all">

            
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Grup Teknisi</label>
                <select
                    :name="`groups[${index}][group_id]`"
                    x-model="row.group_id"
                    required
                    class="w-full text-sm rounded-md border-2 border-gray-200 bg-white py-2 "
                >
                    <option value=""> Pilih Grup </option>
                    <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($group->id); ?>"><?php echo e($group->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
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

    
    <p class="text-xs text-gray-400">
        * Jika tanpa jam mulai, tugas akan tampil sebagai "Fleksibel" di dasbor teknisi.
    </p>
</div>
<?php /**PATH C:\Users\User\Documents\tugas kuliah\TA\asset-monitoring\resources\views/admin/plans/partials/group_repeater.blade.php ENDPATH**/ ?>