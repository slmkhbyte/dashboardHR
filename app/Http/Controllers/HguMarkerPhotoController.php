<?php

namespace App\Http\Controllers;

use App\Models\HguMarkerPhoto;
use App\Support\HguMarkerPhotoStorage;
use Illuminate\Http\Response;

class HguMarkerPhotoController extends Controller
{
    public function show(HguMarkerPhoto $photo): Response
    {
        abort_unless(auth()->check(), 403);
        abort_if(blank($photo->photo_blob), 404);

        $binaryContents = HguMarkerPhotoStorage::decodeBlobFromDatabase($photo->photo_blob);

        return response($binaryContents, 200, [
            'Content-Type' => $photo->photo_mime_type ?? 'application/octet-stream',
            'Content-Length' => (string) ($photo->photo_size_bytes ?? strlen($binaryContents ?? '')),
            'Content-Disposition' => 'inline; filename="' . ($photo->original_filename ?? 'photo') . '"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
