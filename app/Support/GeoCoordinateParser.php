<?php

namespace App\Support;

class GeoCoordinateParser
{
    public static function parseLatitudeOrLongitude(mixed $value): ?float
    {
        if (blank($value)) {
            return null;
        }

        $value = (string) $value;
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        // Normalize Unicode quote/apostrophe variants
        $search = ["'", '′', '″', '"', '"', '„', '‟', '˚', 'º', '˝'];
        $replace = ["'", "'", '"', '"', '"', '"', '"', '°', '°', '"'];
        $value = str_replace($search, $replace, $value);
        
        // Clean up multiple commas
        $value = str_replace(',,', ',', $value);
        $value = trim($value);
        
        // Remove any backslashes that might be escaping quotes
        $value = str_replace('\"', '"', $value);
        $value = str_replace("\'", "'", $value);

        if ($value === '?' || $value === '-') {
            return null;
        }

        if (preg_match('/^\s*(?<hem>[NSEW])\s*(?<rest>.+)$/i', $value, $matches)) {
            $hemisphere = strtoupper($matches['hem']);
            $value = trim($matches['rest']);
        } elseif (preg_match('/^(?<rest>.+?)\s*(?<hem>[NSEW])\s*$/i', $value, $matches)) {
            $hemisphere = strtoupper($matches['hem']);
            $value = trim($matches['rest']);
        } else {
            $hemisphere = null;
        }

        $value = str_replace('""', '"', $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        // Try to extract DMS components by splitting on non-numeric characters
        // This handles any combination of separators (°, ', ", spaces, etc.)
        $dmsPattern = '/^([+-]?)\s*(\d+(?:\.\d+)?)\D+(\d+(?:\.\d+)?)\D+(\d+(?:\.\d+)?)/';
        
        if (preg_match($dmsPattern, $value, $matches)) {
            $degrees = (float) $matches[2];
            $minutes = (float) $matches[3];
            $seconds = (float) $matches[4];

            $decimal = abs($degrees) + ($minutes / 60.0) + ($seconds / 3600.0);

            if ($degrees < 0 || $matches[1] === '-') {
                $decimal = -$decimal;
            }

            if ($hemisphere !== null) {
                if (in_array($hemisphere, ['S', 'W'], true)) {
                    $decimal = -abs($decimal);
                }

                if (in_array($hemisphere, ['N', 'E'], true)) {
                    $decimal = abs($decimal);
                }
            }

            return $decimal;
        }

        $normalized = trim(str_replace(' ', '', $value));
        $normalized = str_replace(',', '.', $normalized);

        if (is_numeric($normalized)) {
            return (float) $normalized;
        }

        return null;
    }

    public static function composeDecimalFromDms(mixed $degrees, mixed $minutes, mixed $seconds, ?string $hemisphere): ?float
    {
        if (blank($degrees) || blank($hemisphere)) {
            return null;
        }

        $degrees = (int) $degrees;
        $minutes = is_numeric($minutes) ? (float) $minutes : 0.0;
        $seconds = is_numeric($seconds) ? (float) $seconds : 0.0;
        $hemisphere = strtoupper(trim((string) $hemisphere));

        if ($minutes < 0 || $minutes >= 60 || $seconds < 0 || $seconds >= 60) {
            return null;
        }

        $decimal = abs($degrees) + ($minutes / 60.0) + ($seconds / 3600.0);

        if (in_array($hemisphere, ['S', 'W'], true)) {
            $decimal = -$decimal;
        }

        return $decimal;
    }

    public static function formatLatitudeDms(?float $latitude): ?string
    {
        return self::formatDms($latitude, 'N', 'S');
    }

    public static function formatLongitudeDms(?float $longitude): ?string
    {
        return self::formatDms($longitude, 'E', 'W');
    }

    public static function getLatitudeDmsParts(?float $latitude): ?array
    {
        return self::getDmsParts($latitude, 'N', 'S');
    }

    public static function getLongitudeDmsParts(?float $longitude): ?array
    {
        return self::getDmsParts($longitude, 'E', 'W');
    }

    private static function formatDms(?float $value, string $positiveHemisphere, string $negativeHemisphere): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $hemisphere = $value < 0 ? $negativeHemisphere : $positiveHemisphere;
        $absolute = abs($value);

        $degrees = (int) floor($absolute);
        $minutesFloat = ($absolute - $degrees) * 60;
        $minutes = (int) floor($minutesFloat);
        $seconds = round(($minutesFloat - $minutes) * 60, 3);

        if ($seconds >= 60) {
            $seconds = 0;
            $minutes++;
        }

        if ($minutes >= 60) {
            $minutes = 0;
            $degrees++;
        }

        $secondsString = rtrim(rtrim(number_format($seconds, 3, ',', ''), '0'), ',');
        if ($secondsString === '') {
            $secondsString = '0';
        }

        return sprintf('%d° %d\' %s %s', $degrees, $minutes, $secondsString, $hemisphere);
    }

    private static function getDmsParts(?float $value, string $positiveHemisphere, string $negativeHemisphere): ?array
    {
        if (is_null($value)) {
            return null;
        }

        $hemisphere = $value < 0 ? $negativeHemisphere : $positiveHemisphere;
        $absolute = abs($value);

        $degrees = (int) floor($absolute);
        $minutesFloat = ($absolute - $degrees) * 60;
        $minutes = (int) floor($minutesFloat);
        $seconds = round(($minutesFloat - $minutes) * 60, 3);

        if ($seconds >= 60) {
            $seconds = 0;
            $minutes++;
        }

        if ($minutes >= 60) {
            $minutes = 0;
            $degrees++;
        }

        return [
            'degrees' => $degrees,
            'minutes' => $minutes,
            'seconds' => $seconds,
            'hemisphere' => $hemisphere,
        ];
    }

    public static function normalizeUtmCoordinates(?string $utmCoordinates, ?string $utmX = null, ?string $utmY = null, string $zone = '49M'): ?string
    {
        // If complete UTM string is provided (e.g., "49M 420553 9987669"), use it
        if (filled($utmCoordinates)) {
            return trim($utmCoordinates);
        }

        // If separate X and Y are provided, combine them
        if (filled($utmX) && filled($utmY)) {
            return trim($zone) . ' ' . trim($utmX) . ' ' . trim($utmY);
        }

        // If only X or only Y is provided, we can't construct valid UTM
        return null;
    }
}
