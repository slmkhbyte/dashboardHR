<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hgu_markers', function (Blueprint $table) {
            $table->id();
            $table->string('marker_number')->unique();
            $table->unsignedTinyInteger('afdeling')->nullable();
            $table->string('utm_coordinates')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('marker_type')->default('beton');
            $table->string('condition')->default('baik');
            $table->boolean('is_moved')->default(false);
            $table->date('last_checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hgu_marker_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hgu_marker_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('hgu_marker_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hgu_marker_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('from_utm_coordinates')->nullable();
            $table->decimal('from_latitude', 10, 7)->nullable();
            $table->decimal('from_longitude', 10, 7)->nullable();
            $table->string('to_utm_coordinates')->nullable();
            $table->decimal('to_latitude', 10, 7)->nullable();
            $table->decimal('to_longitude', 10, 7)->nullable();
            $table->date('moved_at')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('hgu_marker_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hgu_marker_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('event');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hgu_marker_histories');
        Schema::dropIfExists('hgu_marker_moves');
        Schema::dropIfExists('hgu_marker_photos');
        Schema::dropIfExists('hgu_markers');
    }
};
