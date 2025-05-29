<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Criteria;

class CriteriaSeeder extends Seeder
{
    public function run()
    {
        $criteria = [
            [
                'kriteria_id' => 'C001',
                'nama_kriteria' => 'Tingkat Kerusakan',
                'tipe_kriteria' => 'benefit'
            ],
            [
                'kriteria_id' => 'C002',
                'nama_kriteria' => 'Kepentingan Aset',
                'tipe_kriteria' => 'benefit'
            ],
            [
                'kriteria_id' => 'C003',
                'nama_kriteria' => 'Estimasi Biaya',
                'tipe_kriteria' => 'cost'
            ]
        ];

        foreach ($criteria as $criterion) {
            Criteria::create($criterion);
        }
    }
}