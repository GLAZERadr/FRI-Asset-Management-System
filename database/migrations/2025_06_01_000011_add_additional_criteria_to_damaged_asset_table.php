<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('damaged_assets', function (Blueprint $table) {
            $table->json('additional_criteria')->nullable()->after('pelapor');
            $table->index(['asset_id', 'tingkat_kerusakan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damaged_assets', function (Blueprint $table) {
            $table->dropIndex(['asset_id', 'tingkat_kerusakan']);
            $table->dropColumn('additional_criteria');
        });
    }
};