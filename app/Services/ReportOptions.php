<?php

namespace App\Services;

class ReportOptions
{
    public const DEFAULTS = [
        'evidence' => true,
        'images' => true,
        'code' => true,
        'diagrams' => true,
        'examples' => true,
        'resolution' => true,
        'scope' => true,
        'methodologies' => true,
        'clean_pages' => true,
        'wcag_index' => true,
    ];

    public static function resolve(?array $options): array
    {
        $resolved = self::DEFAULTS;

        foreach (array_intersect_key($options ?? [], self::DEFAULTS) as $key => $value) {
            $resolved[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $resolved;
    }
}
