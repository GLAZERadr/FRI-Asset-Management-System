<?php

namespace App\Modules\Exports;

use App\Models\Criteria;
use App\Models\DamagedAsset;
use App\Models\MaintenanceAsset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Illuminate\Support\Collection;

class AssetTemplateExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithCustomValueBinder
{
    protected $selectedAssets;
    protected $selectedType;

    public function __construct($selectedAssets = [], $selectedType = 'damaged_assets')
    {
        $this->selectedAssets = $selectedAssets;
        $this->selectedType = $selectedType;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Get dynamic criteria for columns
        $criteria = Criteria::all();
        
        // If no assets selected, return sample/empty data
        if (empty($this->selectedAssets)) {
            return $this->generateSampleData($criteria);
        }

        $rows = collect();

        foreach ($this->selectedAssets as $assetId) {
            $row = $this->buildRowForAsset($assetId, $criteria);
            if ($row) {
                $rows->push($row);
            }
        }

        return $rows;
    }

    /**
     * Generate sample data when no assets are selected
     */
    private function generateSampleData($criteria)
    {
        $sampleRows = collect();
        
        // Create 1-2 sample rows to show the format
        for ($i = 1; $i <= 2; $i++) {
            $row = [
                'ID Aset' => "SAMPLE-00{$i}",
                'Nama Aset' => "Contoh Aset {$i}",
                'Lokasi' => $i == 1 ? "Laboratorium Komputer" : "Ruang Administrasi",
                'Damage ID' => '', // Empty for new entries
                'Deskripsi Kerusakan' => "Contoh deskripsi kerusakan aset {$i}",
            ];

            // Add sample values for dynamic criteria
            foreach ($criteria as $criterion) {
                $row[$criterion->nama_kriteria] = $this->getSampleValueForCriteria($criterion);
            }

            $sampleRows->push($row);
        }

        return $sampleRows;
    }

    /**
     * Get sample value for criteria
     */
    private function getSampleValueForCriteria($criterion)
    {
        $criteriaNameLower = strtolower($criterion->nama_kriteria);
        
        if (str_contains($criteriaNameLower, 'kerusakan')) {
            return 'Sedang';
        }
        
        if (str_contains($criteriaNameLower, 'biaya') || str_contains($criteriaNameLower, 'cost')) {
            return '500000';
        }
        
        if (str_contains($criteriaNameLower, 'kepentingan') || str_contains($criteriaNameLower, 'prioritas')) {
            return 'Tinggi';
        }
        
        if (str_contains($criteriaNameLower, 'waktu')) {
            return '2 hari';
        }
        
        if (str_contains($criteriaNameLower, 'kompleksitas')) {
            return 'Sedang';
        }
        
        if (str_contains($criteriaNameLower, 'urgensi')) {
            return 'Tinggi';
        }
        
        if (str_contains($criteriaNameLower, 'dampak')) {
            return 'Sedang';
        }
        
        // Default based on criteria type
        if ($criterion->tipe_kriteria === 'cost') {
            return '100000';
        } else {
            return 'Sedang';
        }
    }

    /**
     * Build a row for a specific asset
     */
    private function buildRowForAsset($assetId, $criteria)
    {
        $asset = null;
        $damagedAsset = null;

        if ($this->selectedType === 'damaged_assets') {
            // For staff roles - working with damaged assets
            $damagedAsset = DamagedAsset::with('asset')->find($assetId);
            if (!$damagedAsset || !$damagedAsset->asset) {
                return null;
            }
            $asset = $damagedAsset->asset;
        } else {
            // For kaur roles - working with maintenance assets
            $maintenanceAsset = MaintenanceAsset::with(['asset', 'damagedAsset'])->find($assetId);
            if (!$maintenanceAsset || !$maintenanceAsset->asset) {
                return null;
            }
            $asset = $maintenanceAsset->asset;
            $damagedAsset = $maintenanceAsset->damagedAsset;
        }

        // Build base row data
        $row = [
            'ID Aset' => $asset->asset_id,
            'Nama Aset' => $asset->nama_asset,
            'Lokasi' => $asset->lokasi,
            'Damage ID' => $damagedAsset ? $damagedAsset->damage_id : '',
            'Deskripsi Kerusakan' => $damagedAsset ? $damagedAsset->deskripsi_kerusakan : '',
        ];

        // Add dynamic criteria columns with actual values
        foreach ($criteria as $criterion) {
            $value = $this->getActualValueForCriteria($criterion, $asset, $damagedAsset);
            $row[$criterion->nama_kriteria] = $value;
        }

        return $row;
    }

    /**
     * Get actual value for criteria based on asset and damage data
     */
    private function getActualValueForCriteria($criterion, $asset, $damagedAsset)
    {
        $criteriaNameLower = strtolower($criterion->nama_kriteria);
        
        // Map criteria to actual asset/damage data
        if (str_contains($criteriaNameLower, 'kerusakan') && $damagedAsset) {
            return $damagedAsset->tingkat_kerusakan ?? '';
        }
        
        if (str_contains($criteriaNameLower, 'biaya') && $damagedAsset) {
            return $damagedAsset->estimasi_biaya ?? 0;
        }
        
        if (str_contains($criteriaNameLower, 'kepentingan') || str_contains($criteriaNameLower, 'prioritas')) {
            return $asset->tingkat_kepentingan_asset ?? '';
        }
        
        if (str_contains($criteriaNameLower, 'waktu') && $damagedAsset) {
            return $damagedAsset->estimasi_waktu_perbaikan ?? '';
        }
        
        if (str_contains($criteriaNameLower, 'vendor') && $damagedAsset) {
            return $damagedAsset->vendor ? 'Ya' : 'Tidak';
        }

        if (str_contains($criteriaNameLower, 'kompleksitas') && $damagedAsset) {
            return $damagedAsset->tingkat_kerusakan ?? '';
        }

        // **ENHANCED: Check additional criteria data (stored as JSON)**
        if ($damagedAsset && $damagedAsset->additional_criteria) {
            $additionalData = json_decode($damagedAsset->additional_criteria, true);
            if (is_array($additionalData) && isset($additionalData[$criterion->kriteria_id])) {
                $criteriaData = $additionalData[$criterion->kriteria_id];
                return $criteriaData['value'] ?? $criteriaData['raw_value'] ?? '';
            }
        }

        // Try to map to asset properties using property_exists or array access
        $assetKeys = [
            'tingkat_kepentingan_asset', 'kategori', 'merk', 'tahun_perolehan', 
            'kondisi', 'status_operasional', 'departemen'
        ];
        
        foreach ($assetKeys as $key) {
            if (str_contains($criteriaNameLower, str_replace('_', '', $key))) {
                if (is_object($asset) && property_exists($asset, $key)) {
                    return $asset->{$key};
                } elseif (is_array($asset) && isset($asset[$key])) {
                    return $asset[$key];
                }
            }
        }

        // Try to map to damaged asset properties
        if ($damagedAsset) {
            $damagedKeys = [
                'petugas', 'status', 'reporter_name', 'reporter_role',
                'complexity', 'urgency', 'impact', 'frequency'
            ];
            
            foreach ($damagedKeys as $key) {
                if (str_contains($criteriaNameLower, str_replace('_', '', $key))) {
                    if (is_object($damagedAsset) && property_exists($damagedAsset, $key)) {
                        return $damagedAsset->{$key};
                    } elseif (is_array($damagedAsset) && isset($damagedAsset[$key])) {
                        return $damagedAsset[$key];
                    }
                }
            }
        }

        // **NEW: Try direct property name matching (case-insensitive)**
        $directKey = strtolower(str_replace(' ', '_', $criterion->nama_kriteria));
        
        // Check asset properties directly
        if ($asset) {
            if (is_object($asset) && property_exists($asset, $directKey)) {
                return $asset->{$directKey};
            } elseif (is_array($asset) && isset($asset[$directKey])) {
                return $asset[$directKey];
            }
        }
        
        // Check damaged asset properties directly
        if ($damagedAsset) {
            if (is_object($damagedAsset) && property_exists($damagedAsset, $directKey)) {
                return $damagedAsset->{$directKey};
            } elseif (is_array($damagedAsset) && isset($damagedAsset[$directKey])) {
                return $damagedAsset[$directKey];
            }
        }
        
        // Default empty value
        return '';
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
            'Damage ID',
            'Deskripsi Kerusakan',
        ];
        
        // Add dynamic criteria columns
        foreach ($criteria as $criterion) {
            $headings[] = $criterion->nama_kriteria;
        }
        
        return $headings;
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        $criteria = Criteria::all();
        $totalColumns = 5 + $criteria->count(); // 5 base columns
        $lastColumn = chr(64 + $totalColumns); // Convert to column letter
        
        $styles = [
            // Style the first row (heading row) - bold
            1 => ['font' => ['bold' => true, 'size' => 11]],
            
            // Base columns styling
            'A1:E1' => [
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E3F2FD']], // Light blue
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            
            // Data validation styling for the whole sheet
            "A1:{$lastColumn}1000" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ]
            ]
        ];

        // Style criteria columns differently
        if ($criteria->count() > 0) {
            $startCol = 'F'; // After base columns (A-E)
            $endCol = chr(69 + $criteria->count()); // Calculate end column
            
            $styles["{$startCol}1:{$endCol}1"] = [
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F3E5F5']], // Light purple
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
        }

        // Add comments/notes for important columns
        $sheet->getComment('A1')->getText()->createTextRun('ID Aset harus unik dan sesuai dengan data sistem');
        $sheet->getComment('D1')->getText()->createTextRun('Kosongkan jika membuat laporan baru. Isi jika update data existing.');
        
        // Set column widths for better readability
        $sheet->getColumnDimension('A')->setWidth(15); // ID Aset
        $sheet->getColumnDimension('B')->setWidth(25); // Nama Aset
        $sheet->getColumnDimension('C')->setWidth(20); // Lokasi
        $sheet->getColumnDimension('D')->setWidth(15); // Damage ID
        $sheet->getColumnDimension('E')->setWidth(30); // Deskripsi

        // Set criteria columns width
        foreach ($criteria as $index => $criterion) {
            $colIndex = chr(70 + $index); // Start from F
            $sheet->getColumnDimension($colIndex)->setWidth(18);
            
            // Add validation hints in comments
            if (str_contains(strtolower($criterion->nama_kriteria), 'kerusakan')) {
                $sheet->getComment($colIndex . '1')->getText()
                    ->createTextRun('Pilih: Ringan, Sedang, atau Berat');
            } elseif (str_contains(strtolower($criterion->nama_kriteria), 'biaya')) {
                $sheet->getComment($colIndex . '1')->getText()
                    ->createTextRun('Masukkan angka tanpa titik atau koma (contoh: 500000)');
            } elseif (str_contains(strtolower($criterion->nama_kriteria), 'kepentingan')) {
                $sheet->getComment($colIndex . '1')->getText()
                    ->createTextRun('Pilih: Rendah, Sedang, atau Tinggi');
            }
        }

        return $styles;
    }

    /**
     * Custom value binder to handle different data types properly
     */
    public function bindValue(Cell $cell, $value)
    {
        // Handle numeric values
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
            return true;
        }

        // Handle text values
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        // Default behavior for other types
        return parent::bindValue($cell, $value);
    }
}