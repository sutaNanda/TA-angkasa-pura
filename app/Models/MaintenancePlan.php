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
        'target_type',
        'template_configs',
        'frequency',
        'start_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'is_active' => 'boolean',
        'template_configs' => 'array',
    ];

    /**
     * Relationships
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Get unique categories from template_configs
     */
    public function getCategoriesAttribute()
    {
        if (empty($this->template_configs)) return collect();
        $ids = collect($this->template_configs)->pluck('category_id')->unique();
        return Category::whereIn('id', $ids)->get();
    }

    /**
     * Get unique templates from template_configs
     */
    public function getTemplatesAttribute()
    {
        if (empty($this->template_configs)) return collect();
        $ids = collect($this->template_configs)->pluck('template_id')->unique();
        return ChecklistTemplate::whereIn('id', $ids)->get();
    }

    public function assets(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'maintenance_plan_assets');
    }

    public function locations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'maintenance_plan_locations');
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
        // 1. Jika target_type == 'location', hitung jumlah aset di lokasi-lokasi tersebut (dan childnya jika area-centric)
        // Disini kita approach sederhana dulu, count per location
        if ($this->target_type === 'location' && $this->locations()->exists()) {
            $locationIds = $this->locations()->pluck('locations.id');
            // Menghitung jumlah aset hardware yang ada di lokasi-lokasi tersebut (boleh beserta anak lokasinya)
            // Untuk sementara kita count direct assets di lokasi tersebut
            return Asset::whereIn('location_id', $locationIds)->count();
        }

        // 2. Prioritaskan jika user memilih aset secara spesifik
        if ($this->target_type === 'asset' && $this->assets()->exists()) {
            return $this->assets()->count();
        }

        // 3. Fallback: Hitung semua aset dalam kategori yang dipilih
        if (empty($this->template_configs)) return 0;
        
        $categoryIds = collect($this->template_configs)->pluck('category_id')->unique()->toArray();
        return Asset::whereIn('category_id', $categoryIds)->count();
    }
}
