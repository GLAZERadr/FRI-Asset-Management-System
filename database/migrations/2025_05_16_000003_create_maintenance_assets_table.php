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
        Schema::create('maintenance_assets', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_id')->unique();
            $table->string('damage_id');
            $table->string('asset_id');
            $table->enum('status', ['Menunggu Persetujuan', 'Diterima', 'Dikerjakan', 'Selesai', 'Ditolak'])->default('Menunggu Persetujuan'); // Updated enum values
            $table->timestamp('tanggal_pengajuan')->nullable();
            $table->timestamp('tanggal_perbaikan')->nullable();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->string('teknisi')->nullable();
            $table->string('requested_by')->nullable();
            $table->string('requested_by_role')->nullable();
            $table->timestamp('kaur_lab_approved_at')->nullable();
            $table->string('kaur_lab_approved_by')->nullable();
            $table->timestamp('kaur_keuangan_approved_at')->nullable();
            $table->string('kaur_keuangan_approved_by')->nullable();
            $table->decimal('priority_score', 8, 4)->nullable();
            $table->string('penyebab_kerusakan')->nullable();
            $table->string('deskripsi_perbaikan')->nullable();
            $table->enum('hasil_perbaikan', ['Sukses', 'Perlu Tindak Lanjut'])->nullable();
            $table->string('rekomendasi')->nullable();
            $table->string('catatan')->nullable();
            $table->timestamps();
            
            $table->foreign('damage_id')->references('damage_id')->on('damaged_assets')->onDelete('cascade');
            $table->foreign('asset_id')->references('asset_id')->on('assets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_assets');
    }
};