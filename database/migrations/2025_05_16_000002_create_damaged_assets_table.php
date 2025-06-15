<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damaged_assets', function (Blueprint $table) {
            $table->id();
            $table->string('damage_id')->unique();
            $table->string('asset_id');
            $table->foreign('asset_id')->references('asset_id')->on('assets');
            $table->enum('tingkat_kerusakan', ['Ringan', 'Sedang', 'Berat'])->nullable();
            $table->integer('estimasi_biaya')->default(0);
            $table->dateTime('estimasi_waktu_perbaikan')->nullable();
            $table->string('deskripsi_kerusakan')->nullable();
            $table->dateTime('tanggal_pelaporan')->nullable();
            $table->string('pelapor')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_role')->nullable();
            $table->string('vendor')->nullable();
            $table->enum('status', ['Baru', 'Ditolak', 'Menunggu Persetujuan Kaur', 'Diterima'])->nullable();
            $table->string('damaged_image')->nullable();
            $table->string('reviewer')->nullable();
            $table->string('verification_id')->nullable();
            $table->enum('verified', ['Yes', 'No'])->nullable();
            $table->enum('validated', ['Yes', 'No', 'Reject'])->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->string('validation_id')->nullable();
            $table->dateTime('validated_at')->nullable();
            $table->string('alasan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damaged_assets');
    }
};