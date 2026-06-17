<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_sap_snapshots', function (Blueprint $table): void {
            if (! Schema::hasColumn('employee_sap_snapshots', 'notes')) {
                $table->text('notes')->nullable()->after('source_file_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_sap_snapshots', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_sap_snapshots', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
