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
            $table->string('id_laporan')->nullable()->after('damage_id');
            
            // Add index for better performance
            $table->index('id_laporan');
            
            // Optional: Add foreign key constraint if you want strict referential integrity
            // $table->foreign('id_laporan')->references('id_laporan')->on('asset_monitorings')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damaged_assets', function (Blueprint $table) {
            // Drop foreign key constraint if it was added
            // $table->dropForeign(['id_laporan']);
            
            // Drop the column
            $table->dropColumn('id_laporan');
        });
    }
};