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
        Schema::table('hgu_marker_moves', function (Blueprint $table) {
            $table->string('moved_by_type')->default('internal')->after('to_longitude');
            $table->string('moved_by_name')->nullable()->after('moved_by_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hgu_marker_moves', function (Blueprint $table) {
            $table->dropColumn([
                'moved_by_type',
                'moved_by_name',
            ]);
        });
    }
};
