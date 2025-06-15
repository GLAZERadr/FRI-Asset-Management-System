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
            $table->string('lokasi');
            $table->date('tanggal_pemeliharaan');
            $table->text('deskripsi_pemeliharaan')->nullable();
            $table->text('catatan_tindak_lanjut')->nullable();
            $table->json('photos')->nullable(); // Store multiple photo paths
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};