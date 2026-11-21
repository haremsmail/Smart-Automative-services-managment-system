<?php

namespace App\Support;

/**
 * یەکسانکردنی داتا پێش هەڵگرتن، بۆ ئەوەی گەڕان و دۆزینەوەی دووبارە کار بکات.
 */
class Normalize
{
    /**
     * ژمارەی تەلەفۆن: +964 750 111 2233 ، 00964..., 7501112233 → 07501112233
     */
    public static function phone(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // ژمارەی عێراق: 964 7XX XXX XXXX
        if (str_starts_with($digits, '964') && strlen($digits) === 13) {
            return '0'.substr($digits, 3);
        }

        if (str_starts_with($digits, '7') && strlen($digits) === 10) {
            return '0'.$digits;
        }

        return $digits;
    }

    /**
     * ژمارەی تابلۆ: بۆشایی زیادە لادەبرێت و پیتی لاتینی گەورە دەکرێت.
     */
    public static function plate(?string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) $value));

        return mb_strtoupper($value);
    }

    /**
     * تابلۆ بەبێ بۆشایی، تەنها بۆ بەراوردکردن و گەڕان.
     */
    public static function plateKey(?string $value): string
    {
        return mb_strtoupper(preg_replace('/[\s\-]+/u', '', (string) $value));
    }

    /**
     * VIN: پیتی گەورە، بەبێ بۆشایی و هێڵ.
     */
    public static function vin(?string $value): ?string
    {
        $value = strtoupper(preg_replace('/[\s\-]+/', '', (string) $value));

        return $value === '' ? null : $value;
    }
}
