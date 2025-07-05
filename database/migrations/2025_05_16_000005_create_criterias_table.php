<?php
// File: database/migrations/2025_05_16_000005_create_criteria_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->string('kriteria_id')->unique();
            $table->string('nama_kriteria');
            $table->enum('tipe_kriteria', ['benefit', 'cost']);
            $table->string('department')->default('laboratorium');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('criteria');
    }
};