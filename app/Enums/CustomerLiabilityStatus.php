<?php

namespace App\Enums;

enum CustomerLiabilityStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DISPUTED = 'disputed';
    case WAIVED = 'waived';

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
