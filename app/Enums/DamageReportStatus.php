<?php

namespace App\Enums;

enum DamageReportStatus: string
{
    case OPEN = 'open';
    case UNDER_REVIEW = 'under_review';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';

    public static function getMeta(): array
    {
        return array_map(function ($case) {
            return [
                'value' => $case->value,
                'label' => ucfirst(str_replace('_', ' ', $case->value)),
            ];
        }, self::cases());
    }
}
