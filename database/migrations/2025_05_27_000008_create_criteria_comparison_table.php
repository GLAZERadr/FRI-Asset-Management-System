<?php
// File: database/migrations/2025_05_16_000006_create_criteria_comparisons_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('criteria_comparisons', function (Blueprint $table) {
            $table->id();
            $table->string('criteria_1');
            $table->string('criteria_2');
            $table->decimal('comparison_value', 8, 3);
            $table->timestamps();
            
            $table->foreign('criteria_1')->references('kriteria_id')->on('criteria')->onDelete('cascade');
            $table->foreign('criteria_2')->references('kriteria_id')->on('criteria')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('criteria_comparisons');
    }
};