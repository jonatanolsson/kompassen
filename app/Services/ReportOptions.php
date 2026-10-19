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
        'sample_scope' => true,
        'methodologies' => true,
        'clean_pages' => true,
        'wcag_index' => true,
    ];

    public static function resolve(?array $options): array
    {
        $resolved = self::DEFAULTS;
        $options ??= [];

        if (array_key_exists('scope', $options) && ! array_key_exists('sample_scope', $options)) {
            $options['sample_scope'] = $options['scope'];
        }

        foreach (array_intersect_key($options, self::DEFAULTS) as $key => $value) {
            $resolved[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $resolved;
    }
}
