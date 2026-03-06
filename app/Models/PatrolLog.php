<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatrolLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'technician_id',
        'asset_id',
        'location_id',
        'checklist_template_id',
        'inspection_data',
        'status',
        'notes',
        'photos',
        'work_order_id',
    ];

    protected $casts = [
        'inspection_data' => 'array',
        'photos' => 'array',
    ];

    // Relationships
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function checklistTemplate()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
