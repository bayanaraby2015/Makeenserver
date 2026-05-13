<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class DisplayNumber
{
    public static function plain(int|float|string|null $value, int $maxDecimals = 0): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $normalized = str_replace([',', ' '], '', (string) $value);

        if (! is_numeric($normalized)) {
            return (string) $value;
        }

        return number_format((float) $normalized, $maxDecimals, '.', ',');
    }

    public static function currency(int|float|string|null $value, string $currency = 'ر.س'): string
    {
        $plain = static::plain($value);

        if ($plain === '-') {
            return $plain;
        }

        return $currency.' '.$plain;
    }

    public static function riyal(int|float|string|null $value): string
    {
        return static::currency($value);
    }

    public static function riyalHtml(int|float|string|null $value): HtmlString
    {
        $plain = e(static::plain($value));

        if ($plain === '-') {
            return new HtmlString('-');
        }

        $src = e(asset('brand/riyal-symbol.svg'));

        return new HtmlString(
            '<span style="display:inline-flex;align-items:center;gap:.35rem;direction:ltr;white-space:nowrap;">'
            .'<img src="'.$src.'" alt="Saudi Riyal" style="width:.9em;height:.9em;display:inline-block;object-fit:contain;">'
            .'<span>'.$plain.'</span>'
            .'</span>'
        );
    }

    public static function percentage(int|float|string|null $value): string
    {
        $plain = static::plain($value);

        if ($plain === '-') {
            return $plain;
        }

        return $plain.'%';
    }

    public static function listText(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            $items = array_filter(array_map(
                fn (mixed $item): string => is_scalar($item) ? (string) $item : '',
                $value,
            ));

            return $items === [] ? '-' : implode(', ', $items);
        }

        return (string) $value;
    }
}
