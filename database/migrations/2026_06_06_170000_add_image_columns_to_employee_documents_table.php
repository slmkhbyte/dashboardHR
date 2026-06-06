<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->binary('image_blob')->nullable()->after('notes');
            $table->string('image_mime_type')->nullable()->after('image_blob');
            $table->string('image_original_filename')->nullable()->after('image_mime_type');
            $table->unsignedInteger('image_size_bytes')->nullable()->after('image_original_filename');
        });
    }

    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropColumn([
                'image_blob',
                'image_mime_type',
                'image_original_filename',
                'image_size_bytes',
            ]);
        });
    }
};
