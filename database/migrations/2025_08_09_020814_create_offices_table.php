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
        // Perintah ini akan membuat tabel 'offices'
        Schema::create('offices', function (Blueprint $table) {
            $table->id(); // Kolom ID unik untuk setiap kantor
            $table->string('name'); // Kolom untuk nama kantor, contoh: "Kantor Pusat"
            $table->text('address'); // Kolom untuk alamat lengkap kantor
            $table->string('latitude'); // Kolom untuk koordinat latitude
            $table->string('longitude'); // Kolom untuk koordinat longitude
            $table->unsignedInteger('radius')->default(50); // Kolom untuk radius toleransi absensi dalam meter
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
