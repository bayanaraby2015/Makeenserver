<?php

namespace App\Support;

class InitiativeSpecializations
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(config('makeen.initiative_specializations', []))
            ->mapWithKeys(fn (string $label, string $key): array => [$key => __('initiatives.specializations.'.$key)])
            ->all();
    }

    /**
     * @param  array<int, string>|null  $values
     * @return array<int, string>
     */
    public static function labels(?array $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $options = static::options();

        return array_values(array_map(
            fn (string $value): string => $options[$value] ?? $value,
            array_values(array_filter($values, fn (mixed $value): bool => is_string($value) && $value !== '')),
        ));
    }
}
