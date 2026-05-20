<?php

namespace App\Models;

use App\Enums\InspectionType;
use App\Enums\InspectionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleInspection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'car_id',
        'reservation_id',
        'inspected_by',
        'inspection_type',
        'mileage',
        'fuel_level',
        'overall_condition',
        'notes',
        'inspection_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'inspection_type' => InspectionType::class,
            'status' => InspectionStatus::class,
            'inspection_date' => 'datetime',
            'mileage' => 'integer',
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

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function conditionItems(): HasMany
    {
        return $this->hasMany(VehicleConditionItem::class, 'inspection_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(InspectionEvidence::class, 'inspection_id');
    }
}
