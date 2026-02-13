<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'parent_id', 'description', 'code', 'type', 'path', 'level'];

    // Auto-generate unique code & Path saat create/update
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($location) {
            if (empty($location->code)) {
                do {
                    $code = 'LOC-' . strtoupper(Str::random(6));
                } while (self::where('code', $code)->exists());
                $location->code = $code;
            }
        });

        // Event saat Created (ID sudah ada) untuk set Path Awal
        static::created(function ($location) {
            $location->updatePathAndLevel();
        });

        // Event saat Updating (Jika parent berubah, update path anak-anak)
        static::updating(function ($location) {
            if ($location->isDirty('parent_id')) {
                $location->updatePathAndLevel();
            }
        });

        // Event saat Updated (Untuk Recursive Update ke Children)
        static::updated(function ($location) {
            if ($location->isDirty('path')) {
                foreach ($location->children as $child) {
                    $child->updatePathAndLevel(); // Triggre recursive
                }
            }
        });
    }

    // Helper: Update Path & Level diri sendiri (tanpa save, kecuali dipanggil explicit)
    public function updatePathAndLevel()
    {
        $parent = $this->parent;
        
        $this->level = $parent ? $parent->level + 1 : 0;
        $this->path  = $parent ? $parent->path . '/' . $this->id : (string)$this->id;

        // Smart Type Detection (Heuristic sederhana saat update parent)
        if ($this->level == 0) {
            $this->type = 'building';
        } elseif (stripos($this->name, 'Lantai') !== false || stripos($this->name, 'Floor') !== false) {
            $this->type = 'floor';
        } else {
            // Default fallback, bisa diadjust user nanti
             $this->type = $this->children()->count() == 0 ? 'room' : 'area';
        }

        $this->saveQuietly(); // Hindari infinite loop
    }

    // Accessor: Breadcrumb (Lokasi Lengkap)
    // Contoh output: "Gedung A > Lantai 1 > Ruang Server"
    public function getFullAddressAttribute()
    {
        if (!$this->path) return $this->name;

        $ids = explode('/', $this->path);
        
        // Optimasi: Jika path pendek, query langsung. Jika panjang, cache disarankan.
        // Disini kita query whereIn untuk performa lebih baik drpd N+1
        $names = self::whereIn('id', $ids)
                    ->orderByRaw("FIELD(id, " . implode(',', $ids) . ")")
                    ->pluck('name')
                    ->toArray();

        return implode(' > ', $names);
    }

    // Scope: Filter Type
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Relasi: Recursive Children (Mengambil semua anak cucu)
    public function childrenRecursive()
    {
        return $this->hasMany(Location::class, 'parent_id')->with('childrenRecursive');
    }

    // Relasi Parent
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    // Relasi Children (Langsung di bawahnya)
    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    // Relasi Aset
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
