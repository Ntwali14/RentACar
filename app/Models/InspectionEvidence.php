<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionEvidence extends Model
{
    use SoftDeletes;

    protected $table = 'inspection_evidence';

    protected $fillable = [
        'inspection_id',
        'file_path',
        'file_type',
        'caption',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(VehicleInspection::class, 'inspection_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
