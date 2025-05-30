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
        Schema::table('maintenance_assets', function (Blueprint $table) {
            $table->timestamp('priority_calculated_at')->nullable()->after('priority_score');
            $table->string('priority_method', 50)->nullable()->after('priority_calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_assets', function (Blueprint $table) {
            $table->dropColumn(['priority_calculated_at', 'priority_method']);
        });
    }
};