<?php

namespace App\Models;

use App\Enums\DamageReportStatus;
use App\Enums\CustomerLiabilityStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DamageReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'car_id',
        'reservation_id',
        'pickup_inspection_id',
        'return_inspection_id',
        'reported_by',
        'damage_description',
        'estimated_cost',
        'customer_liability_status',
        'admin_decision',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => DamageReportStatus::class,
            'customer_liability_status' => CustomerLiabilityStatus::class,
            'estimated_cost' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function pickupInspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'pickup_inspection_id');
    }

    public function returnInspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'return_inspection_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }
}
