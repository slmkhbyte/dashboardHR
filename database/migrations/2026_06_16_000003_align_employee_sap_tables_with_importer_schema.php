<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_sap_snapshot_rows', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_sap_snapshot_rows', 'snapshot_id') && ! Schema::hasColumn('employee_sap_snapshot_rows', 'employee_sap_snapshot_id')) {
                $table->renameColumn('snapshot_id', 'employee_sap_snapshot_id');
            }

            if (Schema::hasColumn('employee_sap_snapshot_rows', 'name') && ! Schema::hasColumn('employee_sap_snapshot_rows', 'full_name')) {
                $table->renameColumn('name', 'full_name');
            }

            if (! Schema::hasColumn('employee_sap_snapshot_rows', 'employee_id')) {
                $table->foreignId('employee_id')->nullable()->after('employee_sap_snapshot_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('employee_sap_snapshot_rows', 'nik_karyawan')) {
                $table->string('nik_karyawan')->nullable()->after('nik_sap');
            }

            if (! Schema::hasColumn('employee_sap_snapshot_rows', 'hire_date')) {
                $table->date('hire_date')->nullable()->after('lvl_bod');
            }

            if (! Schema::hasColumn('employee_sap_snapshot_rows', 'is_active')) {
                $table->boolean('is_active')->nullable()->after('hire_date');
            }
        });

        Schema::table('employee_sap_snapshot_differences', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_sap_snapshot_differences', 'snapshot_id') && ! Schema::hasColumn('employee_sap_snapshot_differences', 'employee_sap_snapshot_id')) {
                $table->renameColumn('snapshot_id', 'employee_sap_snapshot_id');
            }

            if (Schema::hasColumn('employee_sap_snapshot_differences', 'name') && ! Schema::hasColumn('employee_sap_snapshot_differences', 'employee_name')) {
                $table->renameColumn('name', 'employee_name');
            }
        });

        Schema::table('employee_sap_snapshot_difference_items', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_sap_snapshot_difference_items', 'difference_id') && ! Schema::hasColumn('employee_sap_snapshot_difference_items', 'employee_sap_snapshot_difference_id')) {
                $table->renameColumn('difference_id', 'employee_sap_snapshot_difference_id');
            }

            if (! Schema::hasColumn('employee_sap_snapshot_difference_items', 'recorded_in_sap_by')) {
                $table->foreignId('recorded_in_sap_by')->nullable()->after('recorded_in_sap_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_sap_snapshot_difference_items', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_sap_snapshot_difference_items', 'recorded_in_sap_by')) {
                $table->dropConstrainedForeignId('recorded_in_sap_by');
            }

            if (Schema::hasColumn('employee_sap_snapshot_difference_items', 'employee_sap_snapshot_difference_id') && ! Schema::hasColumn('employee_sap_snapshot_difference_items', 'difference_id')) {
                $table->renameColumn('employee_sap_snapshot_difference_id', 'difference_id');
            }
        });

        Schema::table('employee_sap_snapshot_differences', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_sap_snapshot_differences', 'employee_name') && ! Schema::hasColumn('employee_sap_snapshot_differences', 'name')) {
                $table->renameColumn('employee_name', 'name');
            }

            if (Schema::hasColumn('employee_sap_snapshot_differences', 'employee_sap_snapshot_id') && ! Schema::hasColumn('employee_sap_snapshot_differences', 'snapshot_id')) {
                $table->renameColumn('employee_sap_snapshot_id', 'snapshot_id');
            }
        });

        Schema::table('employee_sap_snapshot_rows', function (Blueprint $table): void {
            if (Schema::hasColumn('employee_sap_snapshot_rows', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('employee_sap_snapshot_rows', 'hire_date')) {
                $table->dropColumn('hire_date');
            }

            if (Schema::hasColumn('employee_sap_snapshot_rows', 'nik_karyawan')) {
                $table->dropColumn('nik_karyawan');
            }

            if (Schema::hasColumn('employee_sap_snapshot_rows', 'employee_id')) {
                $table->dropConstrainedForeignId('employee_id');
            }

            if (Schema::hasColumn('employee_sap_snapshot_rows', 'full_name') && ! Schema::hasColumn('employee_sap_snapshot_rows', 'name')) {
                $table->renameColumn('full_name', 'name');
            }

            if (Schema::hasColumn('employee_sap_snapshot_rows', 'employee_sap_snapshot_id') && ! Schema::hasColumn('employee_sap_snapshot_rows', 'snapshot_id')) {
                $table->renameColumn('employee_sap_snapshot_id', 'snapshot_id');
            }
        });
    }
};
