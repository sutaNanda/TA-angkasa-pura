<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'checklist_template_id',
        'frequency',
        'day_of_week',
        'day_of_month',
        'preferred_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
        'day_of_month' => 'integer',
    ];

    /**
     * Relationships
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function checklistTemplate()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Check if this schedule should run today
     */
    public function shouldRunToday(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return match($this->frequency) {
            'daily' => true,
            'weekly' => now()->format('N') == $this->day_of_week,
            'monthly' => now()->day == $this->day_of_month,
            default => false,
        };
    }

    /**
     * Get human-readable schedule description
     */
    public function getScheduleDescriptionAttribute(): string
    {
        return match($this->frequency) {
            'daily' => 'Setiap Hari',
            'weekly' => 'Setiap ' . $this->getDayName($this->day_of_week),
            'monthly' => 'Setiap Tanggal ' . $this->day_of_month,
            default => 'Tidak Terjadwal',
        };
    }

    /**
     * Helper: Get day name in Indonesian
     */
    private function getDayName(int $day): string
    {
        return match($day) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
            default => 'Unknown',
        };
    }
}
