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
        Schema::table('employee_families', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('relationship');
            $table->string('birth_place')->nullable()->after('gender');
            $table->string('last_education')->nullable()->after('birth_date');
            $table->string('religion')->nullable()->after('last_education');
            $table->string('ethnicity')->nullable()->after('religion');
            $table->text('address')->nullable()->after('ethnicity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_families', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'birth_place',
                'last_education',
                'religion',
                'ethnicity',
                'address',
            ]);
        });
    }
};
