<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requested_assets', function (Blueprint $table) {
            $table->id();
            $table->string('requested_id')->unique();
            $table->string('asset_id');
            $table->foreign('asset_id')->references('asset_id')->on('assets');
            $table->integer('nilai_prioritas')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requested_assets');
    }
};