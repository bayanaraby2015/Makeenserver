<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class VisitAppointmentFormatter
{
    public static function dateTime(mixed $value): string
    {
        $date = self::parse($value);

        return $date ? $date->format('Y-m-d h:i A') : '-';
    }

    public static function isSelected(mixed $optionValue, mixed $scheduledValue): bool
    {
        $option = self::parse($optionValue);
        $scheduled = self::parse($scheduledValue);

        if ($option === null || $scheduled === null) {
            return false;
        }

        return $option->timestamp === $scheduled->timestamp;
    }

    public static function storeValue(mixed $value): ?string
    {
        $date = self::parse($value);

        return $date?->utc()->format('Y-m-d H:i:s');
    }

    private static function parse(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->copy()->timezone(config('app.timezone'));
        }

        try {
            return Carbon::parse($value)->timezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
