
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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); // Kolom ID unik untuk setiap catatan absensi
            
            // Kolom yang menghubungkan ke tabel users dan offices
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->constrained()->onDelete('cascade');
            
            // Data saat Check-in
            $table->timestamp('check_in_time');
            $table->string('check_in_latitude');
            $table->string('check_in_longitude');
            $table->string('check_in_photo'); // Path ke file foto selfie
            
            // Data saat Check-out (boleh kosong/nullable karena diisi nanti)
            $table->timestamp('check_out_time')->nullable();
            $table->string('check_out_latitude')->nullable();
            $table->string('check_out_longitude')->nullable();
            $table->string('check_out_photo')->nullable();
            
            // Status absensi: 'On Time', 'Late', 'Absent'
            $table->enum('status', ['On Time', 'Late', 'Absent'])->default('On Time');
            
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
