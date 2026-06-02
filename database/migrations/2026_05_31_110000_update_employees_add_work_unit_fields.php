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
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
            $table->dropColumn('afdeling');

            $table->string('religion')->nullable()->after('gender');
            $table->string('work_unit')->nullable()->after('address');
            $table->unsignedTinyInteger('lvl_bod')->nullable()->after('work_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->foreignId('division_id')->constrained()->cascadeOnUpdate()->restrictOnDelete()->after('address');
            $table->unsignedTinyInteger('afdeling')->nullable()->after('dependent_count');
            $table->dropColumn(['work_unit', 'lvl_bod', 'religion']);
        });
    }
};
