<?php

namespace App\Modules\Imports;

use App\Models\Asset;
use App\Models\DamagedAsset;
use App\Models\Criteria;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetDamageImport implements ToModel, WithHeadingRow, WithValidation
{
    private $criteria;
    
    public function __construct()
    {
        $this->criteria = Criteria::all();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Check if the asset exists
        $asset = Asset::where('asset_id', $row['id_aset'])->first();
        
        if (!$asset) {
            // Create the asset if it doesn't exist
            $asset = Asset::create([
                'asset_id' => $row['id_aset'],
                'nama_asset' => $row['nama_aset'],
                'lokasi' => $row['lokasi'],
                'tingkat_kepentingan_asset' => $this->extractKepentinganFromRow($row),
                'kategori' => 'Elektronik', // Default category
            ]);
        }
        
        // Generate a unique damage ID with thread-safe approach
        $damageId = DB::transaction(function() {
            $latestDamage = DamagedAsset::latest('id')->lockForUpdate()->first();
            $damageNumber = $latestDamage ? intval(substr($latestDamage->damage_id, 4)) + 1 : 1;
            return 'DMG-' . str_pad($damageNumber, 5, '0', STR_PAD_LEFT);
        });
        
        // Create the damaged asset record with dynamic criteria data
        $damagedAssetData = [
            'damage_id' => $damageId,
            'asset_id' => $asset->asset_id,
            'tingkat_kerusakan' => $this->extractKerusakanFromRow($row),
            'estimasi_biaya' => $this->extractBiayaFromRow($row),
            'deskripsi_kerusakan' => $row['deskripsi_kerusakan'] ?? 'Imported from Excel',
            'tanggal_pelaporan' => now(),
            'pelapor' => Auth::user()->name,
        ];
        
        // Add any additional criteria data as JSON or in separate fields
        $additionalCriteriaData = $this->extractAdditionalCriteriaData($row);
        if (!empty($additionalCriteriaData)) {
            $damagedAssetData['additional_criteria'] = json_encode($additionalCriteriaData);
        }
        
        return new DamagedAsset($damagedAssetData);
    }
    
    /**
     * Extract kerusakan value from dynamic criteria
     */
    private function extractKerusakanFromRow($row)
    {
        // Look for kerusakan in any criteria
        foreach ($this->criteria as $criterion) {
            $criteriaKey = strtolower(str_replace(' ', '_', $criterion->nama_kriteria));
            if (str_contains(strtolower($criterion->nama_kriteria), 'kerusakan') && isset($row[$criteriaKey])) {
                return $row[$criteriaKey];
            }
        }
        
        // Fallback to standard field
        return $row['tingkat_kerusakan'] ?? 'Sedang';
    }
    
    /**
     * Extract biaya value from dynamic criteria
     */
    private function extractBiayaFromRow($row)
    {
        // Look for biaya in any criteria
        foreach ($this->criteria as $criterion) {
            $criteriaKey = strtolower(str_replace(' ', '_', $criterion->nama_kriteria));
            if (str_contains(strtolower($criterion->nama_kriteria), 'biaya') && isset($row[$criteriaKey])) {
                return $row[$criteriaKey];
            }
        }
        
        // Fallback to standard field
        return $row['estimasi_biaya'] ?? 0;
    }
    
    /**
     * Extract kepentingan value from dynamic criteria
     */
    private function extractKepentinganFromRow($row)
    {
        // Look for kepentingan in any criteria
        foreach ($this->criteria as $criterion) {
            $criteriaKey = strtolower(str_replace(' ', '_', $criterion->nama_kriteria));
            if (str_contains(strtolower($criterion->nama_kriteria), 'kepentingan') && isset($row[$criteriaKey])) {
                return $row[$criteriaKey];
            }
        }
        
        // Fallback to standard field
        return $row['tingkat_kepentingan_asset'] ?? 0;
    }
    
    /**
     * Extract additional criteria data that doesn't map to standard fields
     */
    private function extractAdditionalCriteriaData($row)
    {
        $additionalData = [];
        
        foreach ($this->criteria as $criterion) {
            $criteriaKey = strtolower(str_replace(' ', '_', $criterion->nama_kriteria));
            $isStandardCriteria = str_contains(strtolower($criterion->nama_kriteria), 'kerusakan') ||
                                 str_contains(strtolower($criterion->nama_kriteria), 'biaya') ||
                                 str_contains(strtolower($criterion->nama_kriteria), 'kepentingan');
            
            // If it's not a standard criteria and exists in row, store it
            if (!$isStandardCriteria && isset($row[$criteriaKey])) {
                $additionalData[$criterion->kriteria_id] = [
                    'nama_kriteria' => $criterion->nama_kriteria,
                    'value' => $row[$criteriaKey],
                    'tipe_kriteria' => $criterion->tipe_kriteria
                ];
            }
        }
        
        return $additionalData;
    }
    
    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'id_aset' => 'required|string',
            'nama_aset' => 'required|string',
            'lokasi' => 'required|string',
        ];
        
        // Add dynamic validation rules based on criteria
        foreach ($this->criteria as $criterion) {
            $criteriaKey = strtolower(str_replace(' ', '_', $criterion->nama_kriteria));
            
            if (str_contains(strtolower($criterion->nama_kriteria), 'kerusakan')) {
                $rules[$criteriaKey] = 'required|in:Ringan,Sedang,Berat';
            } elseif (str_contains(strtolower($criterion->nama_kriteria), 'biaya')) {
                $rules[$criteriaKey] = 'required|numeric|min:0';
            } elseif (str_contains(strtolower($criterion->nama_kriteria), 'kepentingan')) {
                $rules[$criteriaKey] = 'required|numeric|min:0';
            } elseif ($criterion->tipe_kriteria === 'cost') {
                $rules[$criteriaKey] = 'nullable|numeric|min:0';
            } else {
                $rules[$criteriaKey] = 'nullable|string';
            }
        }
        
        return $rules;
    }
    
    /**
     * Custom error messages
     */
    public function customValidationMessages()
    {
        $messages = [
            'id_aset.required' => 'ID Aset harus diisi',
            'nama_aset.required' => 'Nama Aset harus diisi',
            'lokasi.required' => 'Lokasi harus diisi',
        ];
        
        // Add dynamic validation messages
        foreach ($this->criteria as $criterion) {
            $criteriaKey = strtolower(str_replace(' ', '_', $criterion->nama_kriteria));
            $criteriaName = $criterion->nama_kriteria;
            
            if (str_contains(strtolower($criterion->nama_kriteria), 'kerusakan')) {
                $messages[$criteriaKey . '.required'] = $criteriaName . ' harus diisi';
                $messages[$criteriaKey . '.in'] = $criteriaName . ' harus salah satu dari: Ringan, Sedang, Berat';
            } elseif (str_contains(strtolower($criterion->nama_kriteria), 'biaya')) {
                $messages[$criteriaKey . '.required'] = $criteriaName . ' harus diisi';
                $messages[$criteriaKey . '.numeric'] = $criteriaName . ' harus berupa angka';
                $messages[$criteriaKey . '.min'] = $criteriaName . ' tidak boleh negatif';
            } elseif (str_contains(strtolower($criterion->nama_kriteria), 'kepentingan')) {
                $messages[$criteriaKey . '.required'] = $criteriaName . ' harus diisi';
                $messages[$criteriaKey . '.in'] = $criteriaName . ' harus salah satu dari: Rendah, Sedang, Tinggi';
            }
        }
        
        return $messages;
    }
}