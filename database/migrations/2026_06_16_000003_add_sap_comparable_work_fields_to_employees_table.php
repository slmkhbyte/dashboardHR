<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('company')->nullable()->after('work_unit');
            $table->string('department')->nullable()->after('company');
            $table->string('division')->nullable()->after('department');
            $table->string('unit')->nullable()->after('division');
            $table->string('location')->nullable()->after('unit');
            $table->string('superior')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn([
                'company',
                'department',
                'division',
                'unit',
                'location',
                'superior',
            ]);
        });
    }
};
