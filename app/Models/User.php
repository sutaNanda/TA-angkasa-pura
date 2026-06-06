<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
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
        'department_id',
        'requires_password_reset',
        'avatar',
        'technician_group_id', // Ganti shift_id dengan grup
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'requires_password_reset' => 'boolean',
    ];

    public function getRememberTokenName()
    {
        return null; // Disable remember_token
    }

    // --- CUSTOM NOTIFICATION ---
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }

    // --- RELASI ---

    /**
     * Grup teknisi tempat user ini bertugas (nullable untuk Admin & Manajer).
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(TechnicianGroup::class, 'technician_group_id');
    }

    // Relasi User belongsTo Department (untuk user/pelapor)
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }


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

    // --- HELPER FUNCTION ---
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isTeknisi(): bool { return $this->role === 'teknisi'; }
    public function isManajer(): bool { return $this->role === 'manajer'; }

    /** Cek apakah user ini memiliki grup aktif. */
    public function hasGroup(): bool
    {
        return $this->technician_group_id !== null;
    }
}
