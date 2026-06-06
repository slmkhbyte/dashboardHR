<?php

namespace App\Support;

use RuntimeException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EmployeeDocumentImageStorage
{
    public static function tempDisk(): string
    {
        return 'local';
    }

    public static function tempDirectory(): string
    {
        return 'tmp/employee-document-images';
    }

    public static function generateStoredFilename(TemporaryUploadedFile $file): string
    {
        return Str::uuid() . '__' . $file->getClientOriginalName();
    }

    /**
     * @return array{image_blob: string, image_mime_type: string, image_original_filename: string, image_size_bytes: int}
     */
    public static function buildDatabasePayload(string $path, ?string $disk = null): array
    {
        $disk ??= static::tempDisk();

        $storage = Storage::disk($disk);

        if (! $storage->exists($path)) {
            throw new RuntimeException("Temporary employee document image file not found: {$path}");
        }

        $filename = basename($path);
        $contents = $storage->get($path);

        return [
            'image_blob' => HguMarkerPhotoStorage::encodeBlobForDatabase($contents),
            'image_mime_type' => $storage->mimeType($path) ?? 'application/octet-stream',
            'image_original_filename' => static::extractOriginalFilename($filename),
            'image_size_bytes' => $storage->size($path),
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

    /**
     * @param  resource|string|null  $contents
     */
    public static function decodeBlobFromDatabase(mixed $contents, ?string $driver = null): ?string
    {
        return HguMarkerPhotoStorage::decodeBlobFromDatabase($contents, $driver);
    }
}
