<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Criteria;

class CriteriaSeeder extends Seeder
{
    public function run()
    {
        // Base criteria that both departments might use
        $baseCriteria = [
            [
                'nama_kriteria' => 'Tingkat Kerusakan',
                'tipe_kriteria' => 'benefit'
            ],
            [
                'nama_kriteria' => 'Kepentingan Aset', 
                'tipe_kriteria' => 'benefit'
            ],
            [
                'nama_kriteria' => 'Estimasi Biaya',
                'tipe_kriteria' => 'cost'
            ]
        ];

        // Laboratorium-specific criteria
        $laboratoriuCriteria = [
            [
                'nama_kriteria' => 'Dampak Pembelajaran',
                'tipe_kriteria' => 'benefit'
            ],
            [
                'nama_kriteria' => 'Frekuensi Penggunaan Lab',
                'tipe_kriteria' => 'benefit'
            ]
        ];

        // Keuangan Logistik-specific criteria  
        $keuanganLogistikCriteria = [
            [
                'nama_kriteria' => 'Dampak Operasional',
                'tipe_kriteria' => 'benefit'
            ],
            [
                'nama_kriteria' => 'Urgensi Perbaikan',
                'tipe_kriteria' => 'benefit'
            ],
            [
                'nama_kriteria' => 'Kompleksitas Perbaikan',
                'tipe_kriteria' => 'cost'
            ]
        ];

        // Create criteria for laboratorium department
        $labCounter = 1;
        foreach (array_merge($baseCriteria, $laboratoriuCriteria) as $criterion) {
            Criteria::firstOrCreate([
                'kriteria_id' => 'L' . str_pad($labCounter, 3, '0', STR_PAD_LEFT),
                'nama_kriteria' => $criterion['nama_kriteria'],
                'department' => 'laboratorium'
            ], [
                'tipe_kriteria' => $criterion['tipe_kriteria'],
                'created_by' => 1 // Assuming admin user has ID 1
            ]);
            $labCounter++;
        }

        // Create criteria for keuangan_logistik department
        $keuanganCounter = 1;
        foreach (array_merge($baseCriteria, $keuanganLogistikCriteria) as $criterion) {
            Criteria::firstOrCreate([
                'kriteria_id' => 'K' . str_pad($keuanganCounter, 3, '0', STR_PAD_LEFT),
                'nama_kriteria' => $criterion['nama_kriteria'],
                'department' => 'keuangan_logistik'
            ], [
                'tipe_kriteria' => $criterion['tipe_kriteria'],
                'created_by' => 1 // Assuming admin user has ID 1
            ]);
            $keuanganCounter++;
        }

        // Optional: Create some shared criteria (if you want some criteria available to all)
        $sharedCriteria = [
            [
                'kriteria_id' => 'S001',
                'nama_kriteria' => 'Estimasi Waktu Perbaikan',
                'tipe_kriteria' => 'cost',
                'department' => 'all' // Special department for shared criteria
            ]
        ];

        foreach ($sharedCriteria as $criterion) {
            Criteria::firstOrCreate([
                'kriteria_id' => $criterion['kriteria_id'],
                'department' => $criterion['department']
            ], [
                'nama_kriteria' => $criterion['nama_kriteria'],
                'tipe_kriteria' => $criterion['tipe_kriteria'],
                'created_by' => 1
            ]);
        }
    }
}