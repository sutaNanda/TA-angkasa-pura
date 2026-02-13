<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class MaintenancePlan extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'checklist_template_id',
        'frequency',
        'start_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Get human-readable schedule description
     */
    public function getScheduleDescriptionAttribute(): string
    {
        $startDate = Carbon::parse($this->start_date);
        
        return match($this->frequency) {
            'daily' => 'Setiap hari',
            'weekly' => 'Setiap ' . $startDate->locale('id')->dayName,
            'monthly' => 'Setiap tanggal ' . $startDate->day,
            'yearly' => 'Setiap ' . $startDate->locale('id')->translatedFormat('d F'),
            default => '-',
        };
    }

    /**
     * Check if this plan should run today
     */
    public function shouldRunToday(?Carbon $date = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = $date ?? now();
        $startDate = Carbon::parse($this->start_date);

        return match($this->frequency) {
            'daily' => true,
            'weekly' => $today->dayOfWeek === $startDate->dayOfWeek,
            'monthly' => $this->isMonthlyMatch($today, $startDate),
            'yearly' => $today->month === $startDate->month && $today->day === $startDate->day,
            default => false,
        };
    }

    /**
     * Handle monthly date overflow (e.g., Jan 31 → Feb 28)
     */
    private function isMonthlyMatch(Carbon $today, Carbon $startDate): bool
    {
        $targetDay = $startDate->day;
        $daysInMonth = $today->daysInMonth;
        
        // If target day exceeds days in current month, run on last day
        $effectiveDay = min($targetDay, $daysInMonth);
        
        return $today->day === $effectiveDay;
    }

    /**
     * Get count of assets affected by this plan
     */
    public function getAffectedAssetsCountAttribute(): int
    {
        return Asset::where('category_id', $this->category_id)->count();
    }
}
