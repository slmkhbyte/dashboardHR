<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_document_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_document_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('event');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->binary('old_image_blob')->nullable();
            $table->string('old_image_mime_type')->nullable();
            $table->string('old_image_original_filename')->nullable();
            $table->unsignedInteger('old_image_size_bytes')->nullable();
            $table->binary('new_image_blob')->nullable();
            $table->string('new_image_mime_type')->nullable();
            $table->string('new_image_original_filename')->nullable();
            $table->unsignedInteger('new_image_size_bytes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_document_histories');
    }
};
