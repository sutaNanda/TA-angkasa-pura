<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'maintenance_plan_id',
        'location_id',
        'target_asset_ids',
        'scheduled_date',
        'type',
        'checklist_id',
        'asset_id',
        'technician_id',
        'schedule_date',
        'status',
        'notes',
        'result_data',
        'checklist_template_id',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'scheduled_date' => 'date',
        'result_data' => 'array',
        'target_asset_ids' => 'array',
    ];

    /**
     * Relationships
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    public function checklistTemplate()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function maintenancePlan()
    {
        return $this->belongsTo(MaintenancePlan::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Scopes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', now()->toDateString());
    }

    public function scopePreventive($query)
    {
        return $query->where('type', 'preventive');
    }

    public function scopeCorrective($query)
    {
        return $query->where('type', 'corrective');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['OPEN', 'IN_PROGRESS']);
    }
}
