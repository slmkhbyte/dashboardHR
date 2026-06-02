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
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('nik', 'nik_sap');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('nik_karyawan')->nullable()->unique()->after('nik_sap');
            $table->string('employee_grade')->nullable()->after('employment_status_id');
            $table->string('marital_status')->nullable()->after('employee_grade');
            $table->unsignedTinyInteger('dependent_count')->default(0)->after('marital_status');
            $table->unsignedTinyInteger('afdeling')->nullable()->after('dependent_count');
            $table->string('last_education')->nullable()->after('afdeling');
            $table->string('birth_place')->nullable()->after('last_education');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'nik_karyawan',
                'employee_grade',
                'marital_status',
                'dependent_count',
                'afdeling',
                'last_education',
                'birth_place',
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('nik_sap', 'nik');
        });
    }
};
