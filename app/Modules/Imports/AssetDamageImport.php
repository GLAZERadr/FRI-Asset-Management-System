<?php

namespace App\Modules\Imports;

use App\Models\Asset;
use App\Models\DamagedAsset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;

class AssetDamageImport implements ToModel, WithHeadingRow, WithValidation
{
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
                'tingkat_kepentingan_asset' => $row['tingkat_kepentingan_asset'],
                'kategori' => 'Elektronik', // Default category
            ]);
        }
        
        // Generate a unique damage ID
        $latestDamage = DamagedAsset::latest()->first();
        $damageNumber = $latestDamage ? intval(substr($latestDamage->damage_id, 4)) + 1 : 1;
        $damageId = 'DMG-' . str_pad($damageNumber, 5, '0', STR_PAD_LEFT);
        
        // Create the damaged asset record
        return new DamagedAsset([
            'damage_id' => $damageId,
            'asset_id' => $asset->asset_id,
            'tingkat_kerusakan' => $row['tingkat_kerusakan'],
            'estimasi_biaya' => $row['estimasi_biaya'],
            'tanggal_pelaporan' => now(),
            'pelapor' => Auth::user()->name,
        ]);
    }
    
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'id_aset' => 'required|string',
            'nama_aset' => 'required|string',
            'lokasi' => 'required|string',
            'tingkat_kerusakan' => 'required|in:Ringan,Sedang,Berat',
            'estimasi_biaya' => 'required|numeric|min:0',
            'tingkat_kepentingan_asset' => 'required|in:Ringan,Sedang,Berat',
        ];
    }
}