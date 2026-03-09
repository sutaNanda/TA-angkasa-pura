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
        'uuid',
        'name',
        'serial_number',
        'category_id',
        'location_id',
        'parent_asset_id', // Self-referencing: Software → Hardware induk
        'status',
        'purchase_date',
        'image',
        'images',
        'specifications',
    ];

    protected $casts = [
        'specifications' => 'array',
        'purchase_date' => 'date',
        'images' => 'array',
    ];
    
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!empty($this->images) && is_array($this->images)) {
            $firstImage = $this->images[0];
            return str_starts_with($firstImage, 'http') ? $firstImage : asset('storage/' . $firstImage);
        }
        
        if (!empty($this->image)) {
            return str_starts_with($this->image, 'http') ? $this->image : asset('storage/' . $this->image);
        }

        return null;
    }

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
    public function parentAsset() { return $this->belongsTo(Asset::class, 'parent_asset_id'); }
    public function childAssets() { return $this->hasMany(Asset::class, 'parent_asset_id'); }
    public function maintenances() { return $this->hasMany(Maintenance::class); }
    public function workOrders() { return $this->hasMany(WorkOrder::class); }
    public function maintenancePlans() { return $this->belongsToMany(MaintenancePlan::class, 'maintenance_plan_assets'); }
}
