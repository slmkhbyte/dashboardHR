<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hgu_marker_photos', function (Blueprint $table) {
            $table->binary('photo_blob')->nullable()->after('photo_path');
            $table->string('photo_mime_type')->nullable()->after('photo_blob');
            $table->string('original_filename')->nullable()->after('photo_mime_type');
            $table->unsignedInteger('photo_size_bytes')->nullable()->after('original_filename');
        });
    }

    public function down(): void
    {
        Schema::table('hgu_marker_photos', function (Blueprint $table) {
            $table->dropColumn([
                'photo_blob',
                'photo_mime_type',
                'original_filename',
                'photo_size_bytes',
            ]);
        });
    }
};
