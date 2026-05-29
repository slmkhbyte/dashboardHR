<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

class ImportDateParser
{
    private const EXCEL_MIN_SERIAL = 1;

    private const EXCEL_MAX_SERIAL = 2958465;

    public static function parse(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmedValue = self::normalizeString($value);

            if ($trimmedValue === '') {
                return null;
            }

            if (self::isExcelSerial($trimmedValue)) {
                return self::parseExcelSerial((float) $trimmedValue) ?? $value;
            }

            if ($parsedValue = self::parseLocalizedDate($trimmedValue)) {
                return $parsedValue;
            }

            if ($parsedValue = self::parseWithCarbon($trimmedValue)) {
                return $parsedValue;
            }

            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return self::parseExcelSerial((float) $value) ?? $value;
        }

        return $value;
    }

    public static function helperText(): string
    {
        return 'Gunakan format YYYY-MM-DD. Format Excel umum seperti DD/MM/YYYY, DD-MM-YYYY, DD.MM.YYYY, tanggal pendek Excel, dan serial Excel juga diterima.';
    }

    private static function isExcelSerial(string $value): bool
    {
        return preg_match('/^\d+(?:\.\d+)?$/', $value) === 1;
    }

    private static function normalizeString(string $value): string
    {
        $value = trim($value);
        $value = preg_replace("/^[\"']+|[\"']+$/", '', $value) ?? $value;

        return strtr($value, [
            "\u{2010}" => '-',
            "\u{2011}" => '-',
            "\u{2012}" => '-',
            "\u{2013}" => '-',
            "\u{2014}" => '-',
            "\u{2212}" => '-',
        ]);
    }

    private static function parseExcelSerial(float $value): ?string
    {
        if (($value < self::EXCEL_MIN_SERIAL) || ($value > self::EXCEL_MAX_SERIAL)) {
            return null;
        }

        $wholeDays = (int) floor($value);
        $seconds = (int) round(($value - $wholeDays) * 86400);

        return CarbonImmutable::create(1899, 12, 30, 0, 0, 0, 'UTC')
            ->addDays($wholeDays)
            ->addSeconds($seconds)
            ->format('Y-m-d');
    }

    private static function parseLocalizedDate(string $value): ?string
    {
        $matches = self::matchSeparatedDate($value);

        if ($matches === null) {
            return null;
        }

        $first = (int) $matches['first'];
        $second = (int) $matches['second'];
        $year = self::normalizeYear((int) $matches['year']);

        [$day, $month] = self::resolveDayMonthOrder(
            first: $first,
            second: $second,
            rawYear: $matches['year'],
        );

        if (($day === null) || ($month === null)) {
            return null;
        }

        if (! checkdate($month, $day, $year)) {
            return null;
        }

        if (isset($matches['hour']) && ($matches['hour'] !== '')) {
            $hour = (int) $matches['hour'];
            $minute = (int) $matches['minute'];
            $second = isset($matches['second']) ? (int) $matches['second'] : 0;

            if (($hour > 23) || ($minute > 59) || ($second > 59)) {
                return null;
            }
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    /**
     * @return array<string, string> | null
     */
    private static function matchSeparatedDate(string $value): ?array
    {
        if (
            preg_match(
                '/^(?<first>\d{1,2})(?<separator>[\/\.-])(?<second>\d{1,2})\k<separator>(?<year>\d{2}|\d{4})(?:\s+(?<hour>\d{1,2}):(?<minute>\d{2})(?::(?<second_time>\d{2}))?)?$/',
                $value,
                $matches,
            ) !== 1
        ) {
            return null;
        }

        return $matches;
    }

    private static function normalizeYear(int $year): int
    {
        if ($year >= 100) {
            return $year;
        }

        return $year >= 70 ? 1900 + $year : 2000 + $year;
    }

    /**
     * @return array{0: int | null, 1: int | null}
     */
    private static function resolveDayMonthOrder(int $first, int $second, string $rawYear): array
    {
        $isShortYear = strlen($rawYear) === 2;

        if ($first > 12) {
            return [$first, $second];
        }

        if ($second > 12) {
            return [$second, $first];
        }

        if ($isShortYear) {
            // Excel often rewrites dates into a short M-D-YY representation.
            return [$second, $first];
        }

        return [$first, $second];
    }

    private static function parseWithCarbon(string $value): ?string
    {
        try {
            return CarbonImmutable::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
