<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_monitorings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruangan');
            $table->string('nama_pelapor');
            $table->date('tanggal_laporan');
            $table->json('monitoring_data'); // Store array of asset monitoring data
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_monitorings');
    }
};