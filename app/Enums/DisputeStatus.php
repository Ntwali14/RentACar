<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case SUBMITTED = 'submitted';
    case REVIEWING = 'reviewing';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';

    public static function getMeta(): array
    {
        return array_map(function ($case) {
            return [
                'value' => $case->value,
                'label' => ucfirst($case->value),
            ];
        }, self::cases());
    }
}
