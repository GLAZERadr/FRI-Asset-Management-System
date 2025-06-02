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
            $table->text('spesifikasi')->nullable();
            $table->string('kode_ruangan')->nullable();
            $table->dateTime('tgl_perolehan')->nullable();
            $table->dateTime('masa_pakai_maksimum')->nullable();
            $table->integer('masa_pakai_duration')->nullable()->comment('Duration value for masa pakai');
            $table->enum('masa_pakai_unit', ['hari', 'bulan', 'tahun'])->nullable()->comment('Unit for masa pakai (days, months, years)');
            $table->decimal('nilai_perolehan', 15, 2)->nullable()->comment('Nilai perolehan amount');
            $table->string('sumber_perolehan')->nullable();
            $table->enum('status_kelayakan', ['Layak', 'Tidak Layak'])->nullable();
            $table->string('foto_asset')->nullable()->comment('Foto aset file path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};