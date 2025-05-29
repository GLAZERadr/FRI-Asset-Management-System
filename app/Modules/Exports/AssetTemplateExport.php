<?php

namespace App\Modules\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Return an empty collection with one example row
        return collect([
            [
                'ID Aset' => '#AST101',
                'Nama Aset' => 'Computer 101',
                'Lokasi' => 'Laboratorium R3',
                'Tingkat Kerusakan' => 'Ringan',
                'Estimasi Biaya' => 0,
                'Tingkat Kepentingan Aset' => 'Sedang',
            ]
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Aset',
            'Nama Aset',
            'Lokasi',
            'Tingkat Kerusakan',
            'Estimasi Biaya',
            'Tingkat Kepentingan Aset',
            '(Tambah sesuai dengan kriteria)',
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (heading row)
            1 => ['font' => ['bold' => true]],
        ];
    }
}