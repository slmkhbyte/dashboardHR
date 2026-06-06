<?php

namespace App\Support;

use RuntimeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HguMarkerPhotoStorage
{
    public static function tempDisk(): string
    {
        return 'local';
    }

    public static function tempDirectory(): string
    {
        return 'tmp/hgu-marker-photos';
    }

    public static function generateStoredFilename(TemporaryUploadedFile $file): string
    {
        return Str::uuid() . '__' . $file->getClientOriginalName();
    }

    /**
     * @return array{photo_path: string, photo_blob: string, photo_mime_type: string, original_filename: string, photo_size_bytes: int}
     */
    public static function buildDatabasePayload(string $path, ?string $disk = null): array
    {
        $disk ??= static::tempDisk();

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            throw new RuntimeException("Temporary HGU photo file not found: {$path}");
        }

        $filename = basename($path);
        $contents = $storage->get($path);

        return [
            'photo_path' => 'db://' . ltrim($path, '/'),
            'photo_blob' => static::encodeBlobForDatabase($contents),
            'photo_mime_type' => $storage->mimeType($path) ?? 'application/octet-stream',
            'original_filename' => static::extractOriginalFilename($filename),
            'photo_size_bytes' => $storage->size($path),
        ];
    }

    public static function deleteTempFile(string $path, ?string $disk = null): void
    {
        $disk ??= static::tempDisk();

        $storage = Storage::disk($disk);

        if ($storage->exists($path)) {
            $storage->delete($path);
        }
    }

    public static function extractOriginalFilename(string $filename): string
    {
        if (str_contains($filename, '__')) {
            return Str::after($filename, '__');
        }

        return $filename;
    }

    public static function encodeBlobForDatabase(string $contents, ?string $driver = null): string
    {
        $driver ??= DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return '\\x' . bin2hex($contents);
        }

        return $contents;
    }

    /**
     * @param  resource|string|null  $contents
     */
    public static function decodeBlobFromDatabase(mixed $contents, ?string $driver = null): ?string
    {
        if ($contents === null) {
            return null;
        }

        if (is_resource($contents)) {
            $streamContents = stream_get_contents($contents);

            if ($streamContents === false) {
                return null;
            }

            $contents = $streamContents;
        }

        $driver ??= DB::connection()->getDriverName();

        if ($driver !== 'pgsql' || ! str_starts_with($contents, '\\x')) {
            return $contents;
        }

        $decoded = hex2bin(substr($contents, 2));

        return $decoded === false ? $contents : $decoded;
    }
}
