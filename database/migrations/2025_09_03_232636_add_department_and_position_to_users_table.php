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
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('department_id')->nullable()->after('office_id')
              ->constrained('departments')->onDelete('set null');

        $table->foreignId('position_id')->nullable()->after('department_id')
              ->constrained('positions')->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['department_id']);
        $table->dropForeign(['position_id']);
        $table->dropColumn(['department_id', 'position_id']);
    });
}
};
