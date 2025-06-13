<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_monitorings', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->string('id_laporan')->unique(); // Your custom report ID
            $table->string('kode_ruangan');
            $table->string('nama_pelapor');
            $table->date('tanggal_laporan');
            $table->json('monitoring_data');
            $table->string('reviewer')->nullable();
            $table->string('validated')->nullable();
            $table->date('validated_at')->nullable();
            $table->string('catatan')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('kode_ruangan');
            $table->index('tanggal_laporan');
            $table->index('id_laporan');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_monitorings');
    }
};