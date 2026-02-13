<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenance_details';

    protected $fillable = [
        'maintenance_id',
        'checklist_item_id', // Pertanyaannya apa
        'answer',            // Jawabannya apa (Bersih, 20 Derajat, dll)
        'is_abnormal'        // Apakah jawaban ini memicu masalah? (true/false)
    ];

    // Relasi ke Header Maintenance
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    // Relasi ke Butir Pertanyaan (Agar tau ini jawaban untuk pertanyaan apa)
    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
    }
}