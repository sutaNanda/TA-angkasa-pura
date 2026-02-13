<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    use HasFactory;

    // Arahkan ke tabel yang tadi kita rename lewat migrasi
    protected $table = 'checklist_items';

    protected $fillable = [
        'checklist_template_id',
        'question',
        'type',
        'unit',
        'order'
    ];

    // Relasi kebalikannya: Item milik satu Template
    public function template()
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }
}
