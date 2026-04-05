<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail; // Implement Interface
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail // Implement Interface
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'division',
        'requires_password_reset',
        'avatar',
        'shift_id',
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
        'requires_password_reset' => 'boolean',
    ];

    // --- CUSTOM NOTIFICATION ---
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    // --- RELASI ---

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // Divisi disimpan sebagai string sederhana (tanpa relasi ke tabel divisions)

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

    public function patrolLogs()
    {
        return $this->hasMany(PatrolLog::class, 'technician_id');
    }

    // --- HELPER FUNCTION (Opsional, biar gampang di Blade nanti) ---
    public function isAdmin() { return $this->role === 'admin'; }
    public function isTeknisi() { return $this->role === 'teknisi'; }
    public function isManajer() { return $this->role === 'manajer'; }
}
