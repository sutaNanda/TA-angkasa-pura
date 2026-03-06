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
        'images',
        'specifications',
    ];

    protected $casts = [
        'specifications' => 'array',
        'purchase_date' => 'date',
        'images' => 'array', // Menyimpan multiple image paths
    ];
    
    // Virtual Attribute untuk kompatibilitas frontend lama
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
    public function maintenances() { return $this->hasMany(Maintenance::class); }
    public function maintenancePlans() { return $this->belongsToMany(MaintenancePlan::class, 'maintenance_plan_assets'); }
}
