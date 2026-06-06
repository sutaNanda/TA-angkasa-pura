<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TechnicianGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    /**
     * Anggota grup (One-to-Many: satu teknisi hanya di satu grup).
     */
    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'technician_group_id');
    }

    /**
     * Rencana pemeliharaan yang menggunakan grup ini.
     * Membawa pivot 'start_time' untuk jam mulai spesifik per-grup.
     */
    public function maintenancePlans(): BelongsToMany
    {
        return $this->belongsToMany(
            MaintenancePlan::class,
            'maintenance_plan_group',
            'technician_group_id',
            'maintenance_plan_id'
        )->withPivot('start_time')->withTimestamps();
    }

    /**
     * Tugas harian yang dijadwalkan untuk grup ini.
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'technician_group_id');
    }

    /**
     * Work order yang di-assign ke grup ini.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'assigned_group_id');
    }

    /**
     * Log handover yang dikirim DARI grup ini.
     */
    public function outgoingHandovers(): HasMany
    {
        return $this->hasMany(WorkOrderHandover::class, 'from_group_id');
    }

    /**
     * Log handover yang diterima OLEH grup ini.
     */
    public function incomingHandovers(): HasMany
    {
        return $this->hasMany(WorkOrderHandover::class, 'to_group_id');
    }

    // =========================================================================
    // HELPER
    // =========================================================================

    /**
     * Mendapatkan warna badge Tailwind berdasarkan properti 'color'.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->color) {
            'blue'   => 'bg-blue-100 text-blue-800',
            'green'  => 'bg-green-100 text-green-800',
            'red'    => 'bg-red-100 text-red-800',
            'yellow' => 'bg-yellow-100 text-yellow-800',
            'purple' => 'bg-purple-100 text-purple-800',
            'orange' => 'bg-orange-100 text-orange-800',
            default  => 'bg-gray-100 text-gray-800',
        };
    }
}
