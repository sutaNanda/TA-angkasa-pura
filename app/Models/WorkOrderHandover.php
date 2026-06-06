<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model untuk audit trail setiap handover tiket antar-grup.
 */
class WorkOrderHandover extends Model
{
    protected $fillable = [
        'work_order_id',
        'from_group_id',        // Nullable: null berarti handover dari pool umum
        'to_group_id',
        'handed_over_by_user_id',
        'notes',
    ];

    // =========================================================================
    // RELASI
    // =========================================================================

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /** Grup yang mengirim handover (bisa null jika dari pool umum). */
    public function fromGroup(): BelongsTo
    {
        return $this->belongsTo(TechnicianGroup::class, 'from_group_id');
    }

    /** Grup yang menerima handover. */
    public function toGroup(): BelongsTo
    {
        return $this->belongsTo(TechnicianGroup::class, 'to_group_id');
    }

    /** Teknisi individual yang melakukan aksi handover. */
    public function handedOverBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handed_over_by_user_id');
    }
}
