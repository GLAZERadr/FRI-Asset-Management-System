<?php
// Create this file: database/migrations/2025_01_XX_fix_existing_criteria_department.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Or if you prefer to duplicate for both departments:
        $existingCriteria = DB::table('criterias')->whereNull('department')->get();
        
        foreach ($existingCriteria as $criteria) {
            // Update original to be for keuangan_logistik
            DB::table('criterias')
                ->where('id', $criteria->id)
                ->update([
                    'department' => 'keuangan_logistik',
                    'created_by' => 1,
                    'updated_at' => now()
                ]);
            
            // Create duplicate for laboratorium with different kriteria_id
            DB::table('criterias')->insert([
                'kriteria_id' => 'L' . substr($criteria->kriteria_id, 1), // Change C001 to L001
                'nama_kriteria' => $criteria->nama_kriteria,
                'tipe_kriteria' => $criteria->tipe_kriteria,
                'department' => 'laboratorium',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Set department back to null for criteria that were updated
        DB::table('criterias')
            ->whereIn('department', ['keuangan_logistik', 'laboratorium'])
            ->whereIn('kriteria_id', ['C001', 'C002', 'C003'])
            ->update(['department' => null]);
    }
};