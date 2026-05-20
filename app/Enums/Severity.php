<?php

namespace App\Enums;

enum Severity: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

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
