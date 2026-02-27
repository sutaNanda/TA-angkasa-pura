<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic Observer - Gunakan satu observer ini untuk semua model.
 * Daftarkan di AppServiceProvider.
 */
class AuditableObserver
{
    /**
     * Map nama class model ke nama modul yang tampil di log.
     */
    protected array $moduleMap = [
        'Asset'             => 'Aset',
        'Location'          => 'Lokasi',
        'Category'          => 'Kategori Aset',
        'User'              => 'Pengguna',
        'WorkOrder'         => 'Work Order',
        'MaintenancePlan'   => 'Rencana Perawatan',
        'Maintenance'       => 'Maintenance',
        'PatrolLog'         => 'Log Patroli',
        'ChecklistTemplate' => 'Template Checklist',
    ];

    /**
     * Kolom yang tidak perlu direkam untuk menghindari noise.
     */
    protected array $hiddenColumns = [
        'password', 'remember_token', 'updated_at', 'created_at',
    ];

    // ────────────────────────────────────────────────
    // HOOKS
    // ────────────────────────────────────────────────

    public function created(Model $model): void
    {
        $this->log('create', $model, null, $this->getClean($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $dirty    = $model->getDirty();
        $original = array_intersect_key($model->getOriginal(), $dirty);

        // Jangan log kalau yang berubah hanya updated_at atau kolom tersembunyi
        $filtered = array_diff_key($dirty, array_flip($this->hiddenColumns));
        if (empty($filtered)) return;

        $this->log(
            'update',
            $model,
            $this->getClean($original),
            $this->getClean($dirty)
        );
    }

    public function deleted(Model $model): void
    {
        $this->log('delete', $model, $this->getClean($model->getAttributes()), null);
    }

    // ────────────────────────────────────────────────
    // HELPERS
    // ────────────────────────────────────────────────

    private function log(string $action, Model $model, ?array $oldData, ?array $newData): void
    {
        // Jangan log jika tidak ada user yang login (misal: seeder, cron job)
        if (!auth()->check()) return;

        $className  = class_basename($model);
        $moduleName = $this->moduleMap[$className] ?? $className;
        $modelName  = $model->name ?? $model->title ?? $model->code ?? ('#' . $model->getKey());

        $descriptions = [
            'create' => "Menambah {$moduleName}: {$modelName}",
            'update' => "Memperbarui {$moduleName}: {$modelName}",
            'delete' => "Menghapus {$moduleName}: {$modelName}",
        ];

        AuditLog::record(
            action:      $action,
            module:      $moduleName,
            description: $descriptions[$action] ?? "{$action} {$moduleName}",
            oldData:     $oldData,
            newData:     $newData,
        );
    }

    /**
     * Bersihkan kolom sensitif sebelum disimpan ke log.
     */
    private function getClean(array $data): array
    {
        return collect($data)
            ->except($this->hiddenColumns)
            ->toArray();
    }
}
