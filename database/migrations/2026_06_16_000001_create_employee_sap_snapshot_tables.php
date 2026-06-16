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
            $table->timestamp('imported_at')->nullable();
            $table->string('source_file_name')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->foreignId('import_id')->nullable()->constrained('imports')->nullOnDelete();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['period_year', 'period_month']);
        });

        Schema::create('employee_sap_snapshot_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('employee_sap_snapshots')->cascadeOnDelete();
            $table->string('nik_sap');
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->string('work_unit')->nullable();
            $table->unsignedTinyInteger('lvl_bod')->nullable();
            $table->string('employee_grade')->nullable();
            $table->string('employment_status')->nullable();
            $table->string('company')->nullable();
            $table->string('department')->nullable();
            $table->string('division')->nullable();
            $table->string('unit')->nullable();
            $table->string('location')->nullable();
            $table->string('superior')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->unique(['snapshot_id', 'nik_sap']);
            $table->index('nik_sap');
        });

        Schema::create('employee_sap_snapshot_differences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('employee_sap_snapshots')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('nik_sap');
            $table->string('name')->nullable();
            $table->unsignedInteger('difference_count')->default(0);
            $table->timestamp('detected_at')->nullable();
            $table->timestamps();

            $table->unique(['snapshot_id', 'nik_sap']);
            $table->index('nik_sap');
        });

        Schema::create('employee_sap_snapshot_difference_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('difference_id')->constrained('employee_sap_snapshot_differences')->cascadeOnDelete();
            $table->string('field_name');
            $table->string('field_label');
            $table->text('sap_value')->nullable();
            $table->text('local_value')->nullable();
            $table->timestamp('local_changed_at')->nullable();
            $table->boolean('is_recorded_in_sap')->default(false);
            $table->timestamp('recorded_in_sap_at')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->unique(['difference_id', 'field_name']);
            $table->index(['field_name', 'is_recorded_in_sap']);
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
