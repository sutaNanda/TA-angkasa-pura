<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes; // Tambahkan ini

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes; // Tambahkan SoftDeletes

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Pastikan role masuk sini
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- RELASI ---

    // Teknisi bisa punya banyak riwayat pengecekan rutin
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    // Teknisi bisa punya banyak tiket perbaikan yang ditugaskan
    public function assignedWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'technician_id');
    }

    // --- HELPER FUNCTION (Opsional, biar gampang di Blade nanti) ---
    public function isAdmin() { return $this->role === 'admin'; }
    public function isTeknisi() { return $this->role === 'teknisi'; }
    public function isManajer() { return $this->role === 'manajer'; }
}
