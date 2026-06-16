<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_histories', function (Blueprint $table): void {
            $table->boolean('is_job_change')->default(false)->after('event');
            $table->json('changed_fields')->nullable()->after('is_job_change');
        });
    }

    public function down(): void
    {
        Schema::table('employee_histories', function (Blueprint $table): void {
            $table->dropColumn(['is_job_change', 'changed_fields']);
        });
    }
};
