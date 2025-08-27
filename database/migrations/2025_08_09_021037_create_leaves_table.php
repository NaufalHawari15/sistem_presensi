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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id(); // Kolom ID unik untuk setiap pengajuan
            
            // Kolom ini menghubungkan pengajuan dengan user yang membuatnya
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->date('start_date'); // Tanggal mulai izin
            $table->date('end_date'); // Tanggal selesai izin
            $table->text('reason'); // Alasan pengajuan izin
            $table->string('attachment')->nullable(); // Lampiran (misal: surat dokter), boleh kosong
            
            // Status pengajuan: 'Pending', 'Approved', 'Rejected'
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
