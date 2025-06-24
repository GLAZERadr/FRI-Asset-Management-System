<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ahp_weights', function (Blueprint $table) {
            $table->id();
            $table->string('calculation_id'); // Remove unique constraint - multiple records can have same calculation_id
            $table->string('criteria_id'); // Foreign key to criteria table
            $table->string('nama_kriteria');
            $table->string('tipe_kriteria');
            $table->decimal('weight', 10, 6); // Weight value with 6 decimal places
            $table->decimal('consistency_ratio', 5, 4); // CR value
            $table->decimal('consistency_index', 10, 6); // CI value
            $table->decimal('lambda_max', 10, 6); // Lambda max value
            $table->decimal('random_index', 5, 4); // RI value
            $table->integer('criteria_count'); // Number of criteria in this calculation
            $table->string('calculated_by'); // User who performed the calculation
            $table->string('department', 50)->default('general');
            $table->boolean('is_active')->default(true); // Mark current active weights
            $table->json('matrix_data')->nullable(); // Store comparison and normalized matrices
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('criteria_id')->references('kriteria_id')->on('criteria')->onDelete('cascade');
            
            // Composite unique constraint - each calculation can have each criteria only once
            $table->unique(['calculation_id', 'criteria_id'], 'ahp_weights_calc_criteria_unique');
            
            // Index for faster queries
            $table->index(['calculation_id', 'is_active']);
            $table->index('criteria_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ahp_weights');
    }
};