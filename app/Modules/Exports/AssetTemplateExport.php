<?php
namespace App\Modules\Exports;

use App\Models\Criteria;
use App\Models\DamagedAsset;
use App\Models\MaintenanceAsset;
use App\Models\AhpWeight;
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
        // Get criteria specifically defined for keuangan_logistik department
        $criteria = $this->getKeuanganLogistikCriteria();
        
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
     * Get criteria specifically for Kaur Keuangan Logistik SDM
     */
    private function getKeuanganLogistikCriteria()
    {
        // First, try to get criteria from active AHP weights for keuangan_logistik department
        $ahpCriteria = AhpWeight::where('department', 'keuangan_logistik')
            ->where('is_active', true)
            ->with('criteria')
            ->get();

        if ($ahpCriteria->isNotEmpty()) {
            // Extract unique criteria from AHP weights
            $criteriaIds = $ahpCriteria->pluck('criteria_id')->unique();
            $criteria = Criteria::whereIn('kriteria_id', $criteriaIds)->get();
            
            \Log::info('Using AHP-based criteria for keuangan_logistik', [
                'criteria_count' => $criteria->count(),
                'criteria_ids' => $criteriaIds->toArray()
            ]);
            
            return $criteria;
        }

        // Fallback: Get criteria that are commonly used for financial/logistic decisions
        $criteria = Criteria::where(function($query) {
            $query->where('department', 'keuangan_logistik')
                  ->orWhere('department', 'all')
                  ->orWhereNull('department');
        })->get();

        // If still empty, get default financial criteria
        if ($criteria->isEmpty()) {
            $criteria = Criteria::whereIn('nama_kriteria', [
                'Tingkat Kerusakan',
                'Estimasi Biaya Perbaikan', 
                'Tingkat Kepentingan Asset',
                'Estimasi Waktu Perbaikan',
                'Kompleksitas Perbaikan',
                'Dampak Operasional',
                'Urgensi Perbaikan'
            ])->get();
        }

        \Log::info('Using fallback criteria for keuangan_logistik', [
            'criteria_count' => $criteria->count()
        ]);

        return $criteria;
    }

    /**
     * Generate sample data when no assets are selected
     */
    private function generateSampleData($criteria)
    {
        $sampleRows = collect();
        
        // Create 2-3 sample rows to show the format for Kaur Keuangan Logistik SDM
        for ($i = 1; $i <= 3; $i++) {
            $row = [
                'ID Aset' => "SAMPLE-KLS-00{$i}",
                'Nama Aset' => "Contoh Aset Keuangan/Logistik {$i}",
                'Lokasi' => $i == 1 ? "Ruang Keuangan" : ($i == 2 ? "Ruang Logistik" : "Ruang SDM"),
                'Damage ID' => '', // Empty for new entries
                'Deskripsi Kerusakan' => "Contoh kerusakan yang memerlukan persetujuan keuangan {$i}",
            ];

            // Add sample values for Kaur Keuangan Logistik criteria
            foreach ($criteria as $criterion) {
                $row[$criterion->nama_kriteria] = $this->getSampleValueForKeuanganLogistikCriteria($criterion);
            }

            $sampleRows->push($row);
        }

        return $sampleRows;
    }

    /**
     * Get sample value specifically for Kaur Keuangan Logistik criteria
     */
    private function getSampleValueForKeuanganLogistikCriteria($criterion)
    {
        $criteriaNameLower = strtolower($criterion->nama_kriteria);
        
        // Financial/Cost related criteria
        if (str_contains($criteriaNameLower, 'biaya') || str_contains($criteriaNameLower, 'cost')) {
            return ['500000', '1000000', '250000'][rand(0, 2)];
        }
        
        // Damage level criteria
        if (str_contains($criteriaNameLower, 'kerusakan')) {
            return ['Ringan', 'Sedang', 'Berat'][rand(0, 2)];
        }
        
        // Asset importance criteria
        if (str_contains($criteriaNameLower, 'kepentingan') || str_contains($criteriaNameLower, 'prioritas')) {
            return ['Rendah', 'Sedang', 'Tinggi'][rand(0, 2)];
        }
        
        // Time estimation criteria
        if (str_contains($criteriaNameLower, 'waktu')) {
            return ['1 hari', '3 hari', '1 minggu'][rand(0, 2)];
        }
        
        // Complexity criteria for financial approval
        if (str_contains($criteriaNameLower, 'kompleksitas')) {
            return ['Rendah', 'Sedang', 'Tinggi'][rand(0, 2)];
        }
        
        // Operational impact criteria
        if (str_contains($criteriaNameLower, 'dampak')) {
            return ['Minimal', 'Sedang', 'Signifikan'][rand(0, 2)];
        }
        
        // Urgency criteria for financial decision
        if (str_contains($criteriaNameLower, 'urgensi')) {
            return ['Normal', 'Mendesak', 'Sangat Mendesak'][rand(0, 2)];
        }
        
        // Budget impact criteria
        if (str_contains($criteriaNameLower, 'anggaran') || str_contains($criteriaNameLower, 'budget')) {
            return ['Rendah', 'Sedang', 'Tinggi'][rand(0, 2)];
        }
        
        // Default based on criteria type for financial context
        if ($criterion->tipe_kriteria === 'cost') {
            return '750000';
        } else {
            return ['Rendah', 'Sedang', 'Tinggi'][rand(0, 2)];
        }
    }

    /**
     * Build a row for a specific asset with Kaur Keuangan Logistik context
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

        // Build base row data with financial context
        $row = [
            'ID Aset' => $asset->asset_id,
            'Nama Aset' => $asset->nama_asset,
            'Lokasi' => $asset->lokasi,
            'Damage ID' => $damagedAsset ? $damagedAsset->damage_id : '',
            'Deskripsi Kerusakan' => $damagedAsset ? $damagedAsset->deskripsi_kerusakan : '',
        ];

        // Add Kaur Keuangan Logistik specific criteria columns with actual values
        foreach ($criteria as $criterion) {
            $value = $this->getActualValueForKeuanganLogistikCriteria($criterion, $asset, $damagedAsset);
            $row[$criterion->nama_kriteria] = $value;
        }

        return $row;
    }

    /**
     * Get actual value for Kaur Keuangan Logistik criteria
     */
    private function getActualValueForKeuanganLogistikCriteria($criterion, $asset, $damagedAsset)
    {
        $criteriaNameLower = strtolower($criterion->nama_kriteria);
        
        // Priority mapping for financial approval context
        if (str_contains($criteriaNameLower, 'kerusakan') && $damagedAsset) {
            return $damagedAsset->tingkat_kerusakan ?? 'Sedang';
        }
        
        if (str_contains($criteriaNameLower, 'biaya') && $damagedAsset) {
            return $damagedAsset->estimasi_biaya ?? 0;
        }
        
        if (str_contains($criteriaNameLower, 'kepentingan') || str_contains($criteriaNameLower, 'prioritas')) {
            return $asset->tingkat_kepentingan_asset ?? 'Sedang';
        }
        
        if (str_contains($criteriaNameLower, 'waktu') && $damagedAsset) {
            return $damagedAsset->estimasi_waktu_perbaikan ?? '3 hari';
        }
        
        if (str_contains($criteriaNameLower, 'vendor') && $damagedAsset) {
            return $damagedAsset->vendor ? 'Ya' : 'Tidak';
        }

        // Financial-specific criteria mappings
        if (str_contains($criteriaNameLower, 'dampak')) {
            // Map operational impact based on asset importance
            $importance = $asset->tingkat_kepentingan_asset ?? '';
            switch ($importance) {
                case 'Tinggi': return 'Signifikan';
                case 'Sedang': return 'Sedang';
                case 'Rendah': return 'Minimal';
                default: return 'Sedang';
            }
        }

        if (str_contains($criteriaNameLower, 'urgensi')) {
            // Map urgency based on damage level
            $damageLevel = $damagedAsset->tingkat_kerusakan ?? '';
            switch ($damageLevel) {
                case 'Berat': return 'Sangat Mendesak';
                case 'Sedang': return 'Mendesak';
                case 'Ringan': return 'Normal';
                default: return 'Normal';
            }
        }

        if (str_contains($criteriaNameLower, 'kompleksitas')) {
            // Map complexity based on damage level and cost
            $damageLevel = $damagedAsset->tingkat_kerusakan ?? '';
            $cost = $damagedAsset->estimasi_biaya ?? 0;
            
            if ($damageLevel === 'Berat' || $cost > 1000000) {
                return 'Tinggi';
            } elseif ($damageLevel === 'Sedang' || $cost > 500000) {
                return 'Sedang';
            } else {
                return 'Rendah';
            }
        }

        // Check additional criteria data (stored as JSON)
        if ($damagedAsset && $damagedAsset->additional_criteria) {
            $additionalData = json_decode($damagedAsset->additional_criteria, true);
            if (is_array($additionalData) && isset($additionalData[$criterion->kriteria_id])) {
                $criteriaData = $additionalData[$criterion->kriteria_id];
                return $criteriaData['value'] ?? $criteriaData['raw_value'] ?? '';
            }
        }

        // Extended property mapping for financial context
        $financialKeys = [
            'tingkat_kepentingan_asset', 'kategori', 'merk', 'tahun_perolehan', 
            'kondisi', 'status_operasional', 'departemen', 'nilai_perolehan',
            'lokasi_detail', 'penanggung_jawab'
        ];
        
        foreach ($financialKeys as $key) {
            if (str_contains($criteriaNameLower, str_replace('_', '', $key))) {
                if (is_object($asset) && property_exists($asset, $key)) {
                    return $asset->{$key};
                } elseif (is_array($asset) && isset($asset[$key])) {
                    return $asset[$key];
                }
            }
        }

        // Financial-specific damaged asset properties
        if ($damagedAsset) {
            $financialDamagedKeys = [
                'petugas', 'status', 'reporter_name', 'reporter_role',
                'complexity', 'urgency', 'impact', 'frequency', 'budget_impact',
                'approval_level_required', 'cost_center'
            ];
            
            foreach ($financialDamagedKeys as $key) {
                if (str_contains($criteriaNameLower, str_replace('_', '', $key))) {
                    if (is_object($damagedAsset) && property_exists($damagedAsset, $key)) {
                        return $damagedAsset->{$key};
                    } elseif (is_array($damagedAsset) && isset($damagedAsset[$key])) {
                        return $damagedAsset[$key];
                    }
                }
            }
        }

        // Direct property name matching for financial criteria
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
        
        // Default value for financial approval context
        return '';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Get Kaur Keuangan Logistik specific criteria from database
        $criteria = $this->getKeuanganLogistikCriteria();
        
        // Base required columns for financial approval
        $headings = [
            'ID Aset',
            'Nama Aset', 
            'Lokasi',
            'Damage ID',
            'Deskripsi Kerusakan',
        ];
        
        // Add Kaur Keuangan Logistik specific criteria columns
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
        $criteria = $this->getKeuanganLogistikCriteria();
        $totalColumns = 5 + $criteria->count(); // 5 base columns
        $lastColumn = chr(64 + $totalColumns); // Convert to column letter
        
        $styles = [
            // Style the first row (heading row) - bold with financial theme
            1 => ['font' => ['bold' => true, 'size' => 11]],
            
            // Base columns styling with financial color scheme
            'A1:E1' => [
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E8F5E8']], // Light green for financial
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

        // Style Kaur Keuangan Logistik criteria columns with distinctive color
        if ($criteria->count() > 0) {
            $startCol = 'F'; // After base columns (A-E)
            $endCol = chr(69 + $criteria->count()); // Calculate end column
            
            $styles["{$startCol}1:{$endCol}1"] = [
                'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'FFF3E0']], // Light orange for financial criteria
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
        }

        // Add financial-specific comments/notes
        $sheet->getComment('A1')->getText()->createTextRun('ID Aset untuk persetujuan keuangan/logistik');
        $sheet->getComment('D1')->getText()->createTextRun('Kosongkan untuk pengajuan baru. Isi untuk update data existing.');
        $sheet->getComment('E1')->getText()->createTextRun('Deskripsi detail untuk justifikasi persetujuan keuangan');
        
        // Set column widths optimized for financial data
        $sheet->getColumnDimension('A')->setWidth(15); // ID Aset
        $sheet->getColumnDimension('B')->setWidth(30); // Nama Aset
        $sheet->getColumnDimension('C')->setWidth(25); // Lokasi
        $sheet->getColumnDimension('D')->setWidth(15); // Damage ID
        $sheet->getColumnDimension('E')->setWidth(35); // Deskripsi

        // Set Kaur Keuangan Logistik criteria columns width and add validation hints
        foreach ($criteria as $index => $criterion) {
            $colIndex = chr(70 + $index); // Start from F
            $sheet->getColumnDimension($colIndex)->setWidth(20);
            
            // Add validation hints specific to financial approval
            $criteriaNameLower = strtolower($criterion->nama_kriteria);
            
            if (str_contains($criteriaNameLower, 'kerusakan')) {
                $sheet->getComment($colIndex . '1')->getText()
                    ->createTextRun('Pilih: Ringan, Sedang, atau Berat (untuk justifikasi biaya)');
            } elseif (str_contains($criteriaNameLower, 'biaya') || str_contains($criteriaNameLower, 'cost')) {
                $sheet->getComment($colIndex . '1')->getText()
                    ->createTextRun('Masukkan angka tanpa titik/koma. Contoh: 500000 (untuk persetujuan budget)');
            } elseif (str_contains($criteriaNameLower, 'kepentingan') || str_contains($criteriaNameLower, 'prioritas')) {
                $sheet->getComment($colIndex . '1')->getText()
                    ->createTextRun('Pilih: Rendah, Sedang, atau Tinggi (untuk prioritas budget)');
            } elseif (str_contains($criteriaNameLower, 'urgensi')) {
                $sheet->getComment($colIndex . '1')->getText()
                    ->createTextRun('Pilih: Normal, Mendesak, atau Sangat Mendesak (untuk justifikasi keuangan)');
            } elseif (str_contains($criteriaNameLower, 'dampak')) {
                $sheet->getComment($colIndex . '1')->getText()
                    ->createTextRun('Pilih: Minimal, Sedang, atau Signifikan (untuk analisis risiko keuangan)');
            }
        }

        return $styles;
    }

    /**
     * Custom value binder to handle financial data types properly
     */
    public function bindValue(Cell $cell, $value)
    {
        // Handle financial/cost values as numeric
        if (is_numeric($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);
            return true;
        }
        
        // Handle financial text values (like currency descriptions)
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        
        // Default behavior for other types
        return parent::bindValue($cell, $value);
    }
}