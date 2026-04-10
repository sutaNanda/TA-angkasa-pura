<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'color',
    ];

    // --- Relationships ---

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function maintenancePlans(): HasMany
    {
        return $this->hasMany(MaintenancePlan::class);
    }

    public function patrolLogs(): HasMany
    {
        return $this->hasMany(PatrolLog::class);
    }

    // --- Helpers ---

    /**
     * Get Tailwind CSS badge classes based on shift color.
     * Pagi=yellow, Siang=orange, Sore=blue, Malam=purple
     */
    public function getBadgeClassAttribute(): string
    {
        return match($this->color) {
            'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            'orange' => 'bg-orange-100 text-orange-700 border-orange-200',
            'blue'   => 'bg-blue-100 text-blue-700 border-blue-200',
            'purple' => 'bg-purple-100 text-purple-700 border-purple-200',
            default  => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    /**
     * Get icon class for shift badge.
     */
    public function getIconClassAttribute(): string
    {
        return match($this->color) {
            'yellow' => 'fa-solid fa-sun',
            'orange' => 'fa-solid fa-cloud-sun',
            'blue'   => 'fa-solid fa-cloud-moon',
            'purple' => 'fa-solid fa-moon',
            default  => 'fa-solid fa-clock',
        };
    }
}
