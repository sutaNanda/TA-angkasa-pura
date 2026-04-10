<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $appends = ['photo_before', 'photo_after', 'photos_before_urls', 'photos_after_urls', 'initial_photo_url'];
    
    protected $casts = [
        'photos_before' => 'array',
        'photos_after' => 'array',
    ];
    
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

    public function location()
    {
        return $this->belongsTo(Location::class);
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
        // Cek kolom photos_before array dulu, lalu fallback ke singular
        $photos = array_key_exists('photos_before', $this->attributes) ? $this->attributes['photos_before'] : null;
        if ($photos) {
            $arr = is_string($photos) ? json_decode($photos, true) : $photos;
            if (is_array($arr) && count($arr) > 0) {
                return asset('storage/' . $arr[0]);
            }
        }
        $path = $value ?? (array_key_exists('initial_photo', $this->attributes) ? $this->attributes['initial_photo'] : null);
        return $path ? asset('storage/' . $path) : null;
    }

    public function getPhotoAfterAttribute($value)
    {
        // Cek kolom photos_after array dulu
        $photos = array_key_exists('photos_after', $this->attributes) ? $this->attributes['photos_after'] : null;
        if ($photos) {
            $arr = is_string($photos) ? json_decode($photos, true) : $photos;
            if (is_array($arr) && count($arr) > 0) {
                return asset('storage/' . $arr[0]);
            }
        }
        $path = $value ?? (array_key_exists('last_progress_photo', $this->attributes) ? $this->attributes['last_progress_photo'] : null);
        
        if (!$path) {
            $history = $this->histories->where('action', 'completed')->sortByDesc('created_at')->first();
            $path = $history ? $history->photo : null;
        }

        return $path ? asset('storage/' . $path) : null;
    }

    public function getPhotosBeforeUrlsAttribute()
    {
        $photos = $this->photos_before;
        if (!is_array($photos) || empty($photos)) {
            // No fallback since we now distinguish between reported (initial) and before
            return [];
        }
        return array_map(fn($p) => asset('storage/' . $p), $photos);
    }
    
    public function getInitialPhotoUrlAttribute()
    {
        $path = array_key_exists('initial_photo', $this->attributes) ? $this->attributes['initial_photo'] : null;
        return $path ? asset('storage/' . $path) : null;
    }

    public function getPhotosAfterUrlsAttribute()
    {
        $photos = $this->photos_after;
        if (!is_array($photos) || empty($photos)) {
            // Fallback: check history photos
            $history = $this->histories->where('action', 'completed')->sortByDesc('created_at')->first();
            if ($history) {
                $histPhotos = $history->photos ?? null;
                if (is_array($histPhotos) && count($histPhotos) > 0) {
                    return array_map(fn($p) => asset('storage/' . $p), $histPhotos);
                }
                $histPhoto = $history->photo ?? null;
                if ($histPhoto) {
                    return [asset('storage/' . $histPhoto)];
                }
            }
            return [];
        }
        return array_map(fn($p) => asset('storage/' . $p), $photos);
    }

    public function getActualReporterNameAttribute()
    {
        if ($this->reported_by && $this->reporter) {
            return $this->reporter->name;
        }

        // Jika Work Order degenerate secara otomatis dari inspeksi rutin/patroli,
        // maka pelapor adalah teknisi yang melakukan inspeksi tersebut.
        if ($this->maintenance_id && $this->maintenance && $this->maintenance->technician) {
            return $this->maintenance->technician->name;
        }

        return 'Sistem Otomatis';
    }
}