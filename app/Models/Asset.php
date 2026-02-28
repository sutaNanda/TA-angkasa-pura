<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str; // Import Str

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', // Tambahkan ini
        'name',
        'serial_number',
        'category_id',
        'location_id',
        'status',
        'purchase_date',
        'image',
        'specifications',
    ];

    protected $casts = [
        'specifications' => 'array',
        'purchase_date' => 'date',
    ];

    // Otomatis generate UUID saat create
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function maintenances() { return $this->hasMany(Maintenance::class); }
    public function maintenancePlans() { return $this->belongsToMany(MaintenancePlan::class, 'maintenance_plan_assets'); }
}
