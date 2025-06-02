<?php

namespace App\Modules\Exports;

use App\Models\Criteria;
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
        // Get dynamic criteria for example values
        $criteria = Criteria::all();
        
        // Build example row with base fields and dynamic criteria
        $exampleRow = [
            'ID Aset' => 'AST101',
            'Nama Aset' => 'Computer 101',
            'Lokasi' => 'Laboratorium R3',
            'Deskripsi Kerusakan' => 'Kerusakan pada layar monitor',
        ];
        
        // Add dynamic criteria columns with example values
        foreach ($criteria as $criterion) {
            $exampleValue = $this->getExampleValueForCriteria($criterion);
            $exampleRow[$criterion->nama_kriteria] = $exampleValue;
        }
        
        return collect([$exampleRow]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Get dynamic criteria from database
        $criteria = Criteria::all();
        
        // Base required columns
        $headings = [
            'ID Aset',
            'Nama Aset', 
            'Lokasi',
            'Deskripsi Kerusakan',
        ];
        
        // Add dynamic criteria columns
        foreach ($criteria as $criterion) {
            $headings[] = $criterion->nama_kriteria;
        }
        
        return $headings;
    }

    /**
     * Get example value based on criteria name and type
     */
    private function getExampleValueForCriteria($criterion)
    {
        // Map common criteria names to appropriate example values
        $criteriaNameLower = strtolower($criterion->nama_kriteria);
        
        if (str_contains($criteriaNameLower, 'kerusakan')) {
            return 'Berat'; // or 'Ringan', 'Sedang'
        }
        
        if (str_contains($criteriaNameLower, 'biaya')) {
            return 500000; // Example cost
        }
        
        if (str_contains($criteriaNameLower, 'kepentingan') || str_contains($criteriaNameLower, 'prioritas')) {
            return '9'; 
        }
        
        if (str_contains($criteriaNameLower, 'waktu')) {
            return '2 hari'; // Example time
        }
        
        if (str_contains($criteriaNameLower, 'kompleksitas')) {
            return 'Sedang'; // or 'Rendah', 'Tinggi'
        }
        
        // Default based on criteria type
        if ($criterion->tipe_kriteria === 'cost') {
            return 100000; // Default cost value
        } else {
            return 'Sedang'; // Default benefit value
        }
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        $criteria = Criteria::all();
        $totalColumns = 4 + $criteria->count(); // Base columns + dynamic criteria
        
        return [
            // Style the first row (heading row)
            1 => ['font' => ['bold' => true]],
            // Add some color coding for different column types
            'A1:D1' => ['fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E3F2FD']]], // Base columns in light blue
        ];
    }
}