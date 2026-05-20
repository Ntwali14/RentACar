<?php

namespace App\Models;

use App\Enums\ConditionStatus;
use App\Enums\Severity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleConditionItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'inspection_id',
        'area_name',
        'condition_status',
        'severity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'condition_status' => ConditionStatus::class,
            'severity' => Severity::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'inspection_id');
    }
}
