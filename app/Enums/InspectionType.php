<?php

namespace App\Enums;

enum InspectionType: string
{
    case PICKUP = 'pickup';
    case RETURN = 'return';
    case GENERAL = 'general';
    case MAINTENANCE = 'maintenance';

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
