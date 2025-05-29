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
            $table->enum('tingkat_kerusakan', ['Ringan', 'Sedang', 'Berat']);
            $table->integer('estimasi_biaya')->default(0);
            $table->string('deskripsi_kerusakan')->nullable();
            $table->dateTime('tanggal_pelaporan')->nullable();
            $table->string('pelapor')->nullable();
            $table->string('vendor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damaged_assets');
    }
};