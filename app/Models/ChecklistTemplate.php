<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistTemplate extends Model
{
    use HasFactory, SoftDeletes;

    // Pastikan nama tabel benar (jika Laravel bingung)
    protected $table = 'checklist_templates';

    protected $fillable = [
        'name',
        'frequency',
        'category_id',
        'description'
    ];

    // Relasi: Satu Template punya BANYAK Item Pertanyaan
    public function items()
    {
        return $this->hasMany(ChecklistItem::class, 'checklist_template_id');
    }

    // Relasi: Template ini milik Kategori apa
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
