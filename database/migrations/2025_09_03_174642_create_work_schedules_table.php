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
    Schema::create('work_schedules', function (Blueprint $table) {
        $table->id();
        $table->string('name')->comment('Contoh: Jam Kantor Normal, Shift Pagi');
        $table->time('start_time')->comment('Jam Masuk Kerja');
        $table->time('end_time')->comment('Jam Pulang Kerja');
        $table->integer('late_tolerance_minutes')->default(0)->comment('Toleransi keterlambatan dalam menit');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
