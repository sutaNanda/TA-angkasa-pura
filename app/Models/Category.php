<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Asset;
use App\Models\ChecklistTemplate;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    // Kita tambahkan 'slug' dan 'icon' agar bisa disimpan
    protected $fillable = ['name', 'slug', 'icon', 'description'];

    // 2. Tambahkan fungsi boot ini
    protected static function boot()
    {
        parent::boot();

        // Saat membuat data baru (creating), otomatis buat slug dari name
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Opsional: Saat update nama, slug ikut berubah
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function checklistTemplates()
    {
        return $this->hasMany(ChecklistTemplate::class);
    }
}
