<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $appends = ['photo_before', 'photo_after'];
    
    // Columns:
    // ticket_number, asset_id, reporter_id, technician_id, issue_description, priority, status, initial_photo,
    // source (patrol, manual_ticket), maintenance_id (optional link to patrol log)

    // ==========================================
    // LOGIKA AUTO-NUMBER (BOOT)
    // ==========================================
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Jika tiket dibuat manual dan user tidak isi nomor, kita generate otomatis
            if (empty($model->ticket_number)) {
                $date = now()->format('Ymd');
                
                // Cari tiket terakhir hari ini
                $lastOrder = self::where('ticket_number', 'like', 'WO-' . $date . '-%')
                                 ->latest('id')
                                 ->first();
                
                if ($lastOrder) {
                    // Ambil 4 digit terakhir (0001) lalu tambah 1
                    $lastNumber = intval(substr($lastOrder->ticket_number, -4));
                    $newNumber = $lastNumber + 1;
                } else {
                    $newNumber = 1;
                }

                // Format: WO-20260206-0001
                $model->ticket_number = 'WO-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ==========================================
    // RELASI DATABASE
    // ==========================================
    
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // Relasi ke User yang melaporkan (Admin/Koordinator)
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    // Relasi ke Logbook Rutin (Jika tiket ini hasil dari cek harian yg Fail)
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    // Relasi ke History / Logbook Aktivitas (Vertical Timeline)
    public function histories()
    {
        return $this->hasMany(WorkOrderHistory::class)->orderBy('created_at', 'asc');
    }

    // ==========================================
    // ACCESSORS (Untuk URL Gambar)
    // ==========================================

    public function getPhotoBeforeAttribute($value)
    {
        // Cek kolom photo_before, jika kosong cek initial_photo
        $path = $value ?? $this->attributes['initial_photo'] ?? null;
        return $path ? asset('storage/' . $path) : null;
    }

    public function getPhotoAfterAttribute($value)
    {
        // Cek kolom photo_after, jika kosong cek last_progress_photo (jika ada)
        // Kita gunakan $this->attributes karena ini accessor
        $path = $value ?? ($this->attributes['last_progress_photo'] ?? null);
        
        // Fallback: Cek history 'completed' terakhir jika null
        if (!$path) {
            $history = $this->histories()->where('action', 'completed')->latest()->first();
            $path = $history ? $history->photo : null;
        }

        return $path ? asset('storage/' . $path) : null;
    }
}