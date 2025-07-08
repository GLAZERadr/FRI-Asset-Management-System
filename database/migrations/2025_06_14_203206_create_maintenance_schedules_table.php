<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_id')->unique();
            $table->string('asset_id'); // ID Aset
            $table->date('tanggal_pemeliharaan'); // Tanggal Pemeliharaan
            $table->enum('jenis_pemeliharaan', ['Rutin', 'Tambahan', 'Khusus'])->default('Rutin'); // Jenis Pemeliharaan
            $table->text('alasan_penjadwalan')->nullable(); // Alasan Penjadwalan
            $table->enum('status', ['Dijadwalkan', 'Selesai', 'Dibatalkan'])->default('Dijadwalkan'); // Status Jadwal
            $table->string('penanggung_jawab')->nullable(); // Penanggung Jawab
            $table->text('catatan_tambahan')->nullable(); // Catatan Tambahan
            $table->boolean('auto_generated')->default(false); // Flag untuk jadwal otomatis
            $table->text('deskripsi_pemeliharaan')->nullable(); // Deskripsi setelah pemeliharaan
            $table->text('catatan_tindak_lanjut')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('asset_id')->references('asset_id')->on('assets')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index(['asset_id', 'tanggal_pemeliharaan']);
            $table->index(['status', 'tanggal_pemeliharaan']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};