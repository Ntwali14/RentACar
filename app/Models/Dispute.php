<?php

namespace App\Models;

use App\Enums\DisputeStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'damage_report_id',
        'customer_id',
        'admin_id',
        'customer_statement',
        'admin_response',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DisputeStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function damageReport(): BelongsTo
    {
        return $this->belongsTo(DamageReport::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
