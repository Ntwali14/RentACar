<?php

namespace App\Enums;

enum ConditionStatus: string
{
    case GOOD = 'good';
    case SCRATCHED = 'scratched';
    case DENTED = 'dented';
    case CRACKED = 'cracked';
    case MISSING = 'missing';
    case DIRTY = 'dirty';
    case DAMAGED = 'damaged';

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
