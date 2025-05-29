<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('nama_asset');
            $table->string('lokasi');
            $table->string('tingkat_kepentingan_asset')->nullable();
            $table->string('kategori')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};