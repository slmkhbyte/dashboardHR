<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_sap_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->string('source_file_name')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('import_id')->nullable()->constrained('imports')->nullOnDelete();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['period_month', 'period_year']);
        });

        Schema::create('employee_sap_snapshot_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_sap_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nik_sap');
            $table->string('nik_karyawan')->nullable();
            $table->string('full_name')->nullable();
            $table->string('position')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('employee_grade')->nullable();
            $table->string('work_unit')->nullable();
            $table->unsignedTinyInteger('lvl_bod')->nullable();
            $table->date('hire_date')->nullable();
            $table->boolean('is_active')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['employee_sap_snapshot_id', 'nik_sap'], 'sap_snapshot_rows_snapshot_nik_unique');
        });

        Schema::create('employee_sap_snapshot_differences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_sap_snapshot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nik_sap');
            $table->string('employee_name')->nullable();
            $table->unsignedSmallInteger('difference_count')->default(0);
            $table->timestamp('detected_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_sap_snapshot_id', 'nik_sap'], 'sap_snapshot_diffs_snapshot_nik_unique');
        });

        Schema::create('employee_sap_snapshot_difference_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_sap_snapshot_difference_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('field_name');
            $table->string('field_label');
            $table->text('sap_value')->nullable();
            $table->text('local_value')->nullable();
            $table->timestamp('local_changed_at')->nullable();
            $table->boolean('is_recorded_in_sap')->default(false);
            $table->timestamp('recorded_in_sap_at')->nullable();
            $table->foreignId('recorded_in_sap_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->unique(['employee_sap_snapshot_difference_id', 'field_name'], 'sap_snapshot_diff_items_diff_field_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_sap_snapshot_difference_items');
        Schema::dropIfExists('employee_sap_snapshot_differences');
        Schema::dropIfExists('employee_sap_snapshot_rows');
        Schema::dropIfExists('employee_sap_snapshots');
    }
};
