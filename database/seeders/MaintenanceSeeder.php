<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\DamagedAsset;
use App\Models\MaintenanceAsset;
use App\Models\User;
use App\Models\ApprovalLog;
use Carbon\Carbon;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create data if tables are empty or specific flag is set
        if (Asset::count() == 0 || $this->command->option('force')) {
            // Create Assets first
            $assets = $this->createAssets();
            
            // Create Damaged Assets based on the created assets
            $damagedAssets = $this->createDamagedAssets($assets);
            
            // Create Maintenance Assets based on damaged assets
            $this->createMaintenanceAssets($assets, $damagedAssets);
            
            $this->command->info('✅ MaintenanceSeeder completed successfully!');
            $this->command->info('📊 Created: ' . count($assets) . ' assets, ' . count($damagedAssets) . ' damage reports, ' . count($damagedAssets) . ' maintenance requests');
        } else {
            $this->command->warn('⚠️  Data already exists. Use --force to override or clear tables manually.');
        }
    }

    /**
     * Create sample assets with proper structure
     */
    private function createAssets(): array
    {
        $assetData = [
            // Computers/Electronics
            [
                'asset_id' => 'T0901-ELE-001',
                'nama_asset' => 'Computer Dell OptiPlex 7070',
                'kategori' => 'Elektronik',
                'spesifikasi' => 'Intel Core i5-9500, 8GB RAM, 256GB SSD, Windows 11 Pro',
                'lokasi' => 'Laboratorium',
                'kode_ruangan' => 'TULT-0901',
                'tgl_perolehan' => '2022-01-15',
                'masa_pakai_maksimum' => Carbon::parse('2022-01-15')->addMonths(48), // 4 years
                'nilai_perolehan' => 8500000,
                'sumber_perolehan' => 'Yayasan Universitas Telkom',
                'status_kelayakan' => 'Layak',
                'tingkat_kepentingan_asset' => '8'
            ],
            [
                'asset_id' => 'T0901-ELE-002',
                'nama_asset' => 'Computer HP EliteDesk 800',
                'kategori' => 'Elektronik',
                'spesifikasi' => 'Intel Core i5-8500, 8GB RAM, 1TB HDD, Windows 10 Pro',
                'lokasi' => 'Laboratorium',
                'kode_ruangan' => 'TULT-0901',
                'tgl_perolehan' => '2021-08-20',
                'masa_pakai_maksimum' => Carbon::parse('2021-08-20')->addMonths(48),
                'nilai_perolehan' => 7500000,
                'sumber_perolehan' => 'Bantuan Pemerintah',
                'status_kelayakan' => 'Layak',
                'tingkat_kepentingan_asset' => '7'
            ],
            [
                'asset_id' => 'T0901-ELE-003',
                'nama_asset' => 'Printer Canon imageCLASS LBP6030',
                'kategori' => 'Elektronik',
                'spesifikasi' => 'Laser Monochrome, USB 2.0, A4 Size',
                'lokasi' => 'Laboratorium',
                'kode_ruangan' => 'TULT-0902',
                'tgl_perolehan' => '2021-03-10',
                'masa_pakai_maksimum' => Carbon::parse('2021-03-10')->addMonths(60), // 5 years
                'nilai_perolehan' => 1200000,
                'sumber_perolehan' => 'Hibah',
                'status_kelayakan' => 'Tidak Layak',
                'tingkat_kepentingan_asset' => '6'
            ],
            [
                'asset_id' => 'T0901-ELE-004',
                'nama_asset' => 'Projector Epson EB-X41',
                'kategori' => 'Elektronik',
                'spesifikasi' => '3600 Lumens, XGA Resolution, HDMI, VGA',
                'lokasi' => 'Laboratorium',
                'kode_ruangan' => 'GACUK-101',
                'tgl_perolehan' => '2020-09-05',
                'masa_pakai_maksimum' => Carbon::parse('2020-09-05')->addMonths(72), // 6 years
                'nilai_perolehan' => 4500000,
                'sumber_perolehan' => 'Yayasan Universitas Telkom',
                'status_kelayakan' => 'Layak',
                'tingkat_kepentingan_asset' => '9'
            ],
            [
                'asset_id' => 'T0901-ELE-005',
                'nama_asset' => 'Router Cisco ISR 4331',
                'kategori' => 'Elektronik',
                'spesifikasi' => 'Integrated Services Router, 4-port Gigabit Ethernet',
                'lokasi' => 'Laboratorium',
                'kode_ruangan' => 'TULT-0903',
                'tgl_perolehan' => '2022-06-12',
                'masa_pakai_maksimum' => Carbon::parse('2022-06-12')->addMonths(84), // 7 years
                'nilai_perolehan' => 25000000,
                'sumber_perolehan' => 'Bantuan Pemerintah',
                'status_kelayakan' => 'Layak',
                'tingkat_kepentingan_asset' => '10'
            ],
            
            // Furniture
            [
                'asset_id' => 'T0901-FUR-001',
                'nama_asset' => 'Meja Kantor',
                'kategori' => 'Furnitur',
                'spesifikasi' => 'Kayu Jati, 120cm x 60cm x 75cm, Finishing Natural',
                'lokasi' => 'Logistik',
                'kode_ruangan' => 'TULT-0901',
                'tgl_perolehan' => '2020-01-15',
                'masa_pakai_maksimum' => Carbon::parse('2020-01-15')->addMonths(120), // 10 years
                'nilai_perolehan' => 1500000,
                'sumber_perolehan' => 'Yayasan Universitas Telkom',
                'status_kelayakan' => 'Layak',
                'tingkat_kepentingan_asset' => '6'
            ],
            [
                'asset_id' => 'T0901-FUR-002',
                'nama_asset' => 'Kursi Kantor',
                'kategori' => 'Furnitur',
                'spesifikasi' => 'Kursi Putar, Busa Memory Foam, Adjustable Height',
                'lokasi' => 'Logistik',
                'kode_ruangan' => 'TULT-0901',
                'tgl_perolehan' => '2020-01-15',
                'masa_pakai_maksimum' => Carbon::parse('2020-01-15')->addMonths(84), // 7 years
                'nilai_perolehan' => 800000,
                'sumber_perolehan' => 'Yayasan Universitas Telkom',
                'status_kelayakan' => 'Layak',
                'tingkat_kepentingan_asset' => '5'
            ],
            [
                'asset_id' => 'T0901-FUR-003',
                'nama_asset' => 'Lemari Arsip',
                'kategori' => 'Furnitur',
                'spesifikasi' => 'Besi, 4 Laci, Sistem Kunci, 180cm x 40cm x 60cm',
                'lokasi' => 'Logistik',
                'kode_ruangan' => 'TULT-0902',
                'tgl_perolehan' => '2019-11-20',
                'masa_pakai_maksimum' => Carbon::parse('2019-11-20')->addMonths(180), // 15 years
                'nilai_perolehan' => 2200000,
                'sumber_perolehan' => 'Hibah',
                'status_kelayakan' => 'Layak',
                'tingkat_kepentingan_asset' => '7'
            ],
            
            // Machines
            [
                'asset_id' => 'T0901-MES-001',
                'nama_asset' => 'AC Daikin Split',
                'kategori' => 'Mesin',
                'spesifikasi' => '1.5 PK, Inverter Technology, R32 Refrigerant',
                'lokasi' => 'Logistik',
                'kode_ruangan' => 'TULT-0901',
                'tgl_perolehan' => '2021-07-25',
                'masa_pakai_maksimum' => Carbon::parse('2021-07-25')->addMonths(120), // 10 years
                'nilai_perolehan' => 4200000,
                'sumber_perolehan' => 'Yayasan Universitas Telkom',
                'status_kelayakan' => 'Layak',
                'tingkat_kepentingan_asset' => '8'
            ],
            [
                'asset_id' => 'T0901-MES-002',
                'nama_asset' => 'Generator Listrik',
                'kategori' => 'Mesin',
                'spesifikasi' => '5000W, Bensin, Portable, Auto Start',
                'lokasi' => 'Logistik',
                'kode_ruangan' => 'GACUK-101',
                'tgl_perolehan' => '2020-12-10',
                'masa_pakai_maksimum' => Carbon::parse('2020-12-10')->addMonths(96), // 8 years
                'nilai_perolehan' => 12000000,
                'sumber_perolehan' => 'Bantuan Pemerintah',
                'status_kelayakan' => 'Tidak Layak',
                'tingkat_kepentingan_asset' => '9'
            ],
        ];

        $assets = [];
        foreach ($assetData as $data) {
            // Check if asset already exists
            $existingAsset = Asset::where('asset_id', $data['asset_id'])->first();
            if (!$existingAsset) {
                $assets[] = Asset::create($data);
            } else {
                $assets[] = $existingAsset;
            }
        }

        return $assets;
    }

    /**
     * Create sample damaged assets
     */
    private function createDamagedAssets(array $assets): array
    {
        $damageReports = [
            'Tidak bisa menyala',
            'Layar berkedip-kedip',
            'Suara bising saat beroperasi', 
            'Koneksi internet terputus-putus',
            'Tombol power tidak berfungsi',
            'Hasil print tidak jelas',
            'Tinta habis tidak terdeteksi',
            'Proyeksi buram dan tidak fokus',
            'Remote control tidak berfungsi',
            'Cooling fan tidak berputar',
            'Temperatur terlalu panas',
            'Koneksi WiFi tidak stabil',
            'Kabel power rusak',
            'Software tidak bisa dijalankan',
            'Hard disk berbunyi aneh',
            'RAM error saat booting',
            'Keyboard beberapa tombol tidak berfungsi',
            'Mouse klik kanan tidak responsif',
            'Speaker mengeluarkan suara pecah',
            'Port USB tidak terdeteksi'
        ];

        // Get user divisions for more realistic data
        $staffLogistik = User::role('staff_logistik')->first();
        $staffLab = User::role('staff_laboratorium')->first();
        
        $vendors = ['PT Teknologi Maju', 'CV Solusi Digital', 'PT Mitra Komputer', 'UD Teknik Jaya', 'PT Elektronik Prima'];
        $damageLevels = ['Ringan', 'Sedang', 'Berat'];
        
        $damagedAssets = [];
        
        // Create damage reports for 6 assets (more manageable number)
        $selectedAssets = collect($assets)->random(min(6, count($assets)));
        
        foreach ($selectedAssets as $index => $asset) {
            $damageId = 'DMG' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            
            // Check if damage report already exists
            $existingDamage = DamagedAsset::where('damage_id', $damageId)->first();
            if ($existingDamage) {
                $damagedAssets[] = $existingDamage;
                continue;
            }
            
            $reportingDate = Carbon::now()->subDays(rand(1, 60));
            
            // Determine reporter based on asset location
            $pelapor = 'Logistik dan SDM';
            if (str_contains($asset->lokasi, 'TULT-090')) {
                $pelapor = 'Laboratorium';
            }
            
            // Determine cost based on damage level and asset importance
            $damageLevel = $damageLevels[array_rand($damageLevels)];
            $baseCost = intval($asset->tingkat_kepentingan_asset) * 100000; // Base cost
            
            switch ($damageLevel) {
                case 'Ringan':
                    $estimatedCost = $baseCost * rand(1, 3);
                    break;
                case 'Sedang':
                    $estimatedCost = $baseCost * rand(3, 8);
                    break;
                case 'Berat':
                    $estimatedCost = $baseCost * rand(8, 15);
                    break;
                default:
                    $estimatedCost = $baseCost * rand(2, 5);
            }

            $damagedAsset = DamagedAsset::create([
                'damage_id' => $damageId,
                'asset_id' => $asset->asset_id,
                'tingkat_kerusakan' => $damageLevel,
                'estimasi_biaya' => $estimatedCost,
                'deskripsi_kerusakan' => $damageReports[array_rand($damageReports)],
                'tanggal_pelaporan' => $reportingDate,
                'pelapor' => $pelapor,
                'vendor' => $vendors[array_rand($vendors)],
            ]);
            
            $damagedAssets[] = $damagedAsset;
        }

        return $damagedAssets;
    }

    /**
     * Create sample maintenance assets with approval workflow
     */
    private function createMaintenanceAssets(array $assets, array $damagedAssets): void
    {
        $technicians = ['Vendor', 'Staf'];
        
        // Get users for workflow - check if they exist
        $staffLogistik = User::whereHas('roles', function($q) {
            $q->where('name', 'staff_logistik');
        })->first();
        
        $staffLab = User::whereHas('roles', function($q) {
            $q->where('name', 'staff_laboratorium');
        })->first();
        
        $kaurLab = User::whereHas('roles', function($q) {
            $q->where('name', 'kaur_laboratorium');
        })->first();
        
        $kaurKeuangan = User::whereHas('roles', function($q) {
            $q->where('name', 'kaur_keuangan_logistik_sdm');
        })->first();
        
        // Create maintenance requests for all damaged assets
        foreach ($damagedAssets as $index => $damagedAsset) {
            $maintenanceId = 'MNT' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            
            // Check if maintenance request already exists
            $existingMaintenance = MaintenanceAsset::where('maintenance_id', $maintenanceId)->first();
            if ($existingMaintenance) {
                continue;
            }
            
            $submissionDate = Carbon::parse($damagedAsset->tanggal_pelaporan)->addDays(rand(1, 3));
            
            // Determine requester based on pelapor
            $requester = null;
            $requesterRole = null;
            
            if ($damagedAsset->pelapor == 'Laboratorium' && $staffLab) {
                $requester = $staffLab;
                $requesterRole = 'staff_laboratorium';
            } elseif ($staffLogistik) {
                $requester = $staffLogistik;
                $requesterRole = 'staff_logistik';
            }
            
            // Create with different approval states
            $approvalScenario = rand(1, 5);
            $status = 'Menunggu Persetujuan';

            $maintenanceData = [
                'maintenance_id' => $maintenanceId,
                'damage_id' => $damagedAsset->damage_id,
                'asset_id' => $damagedAsset->asset_id,
                'status' => $status,
                'tanggal_pengajuan' => $submissionDate,
                'teknisi' => $technicians[array_rand($technicians)],
                'requested_by' => $requester ? $requester->id : null,
                'requested_by_role' => $requesterRole,
            ];
            
            // Apply different approval scenarios
            switch ($approvalScenario) {
                case 1: // Pending approval at kaur lab (for lab staff submissions)
                    if ($requesterRole == 'staff_laboratorium') {
                        // Just submitted, waiting for kaur lab
                    }
                    break;
                    
                case 2: // Approved by kaur lab, pending kaur keuangan
                    if ($requesterRole == 'staff_laboratorium' && $kaurLab) {
                        $maintenanceData['kaur_lab_approved_at'] = $submissionDate->copy()->addDays(1);
                        $maintenanceData['kaur_lab_approved_by'] = $kaurLab->username;
                    }
                    break;
                    
                case 3: // Fully approved and accepted
                    if ($requesterRole == 'staff_laboratorium' && $kaurLab && $kaurKeuangan) {
                        $maintenanceData['kaur_lab_approved_at'] = $submissionDate->copy()->addDays(1);
                        $maintenanceData['kaur_lab_approved_by'] = $kaurLab->username;
                        $maintenanceData['kaur_keuangan_approved_at'] = $submissionDate->copy()->addDays(2);
                        $maintenanceData['kaur_keuangan_approved_by'] = $kaurKeuangan->username;
                        $maintenanceData['status'] = 'Diterima';
                    } elseif ($requesterRole == 'staff_logistik' && $kaurKeuangan) {
                        $maintenanceData['kaur_keuangan_approved_at'] = $submissionDate->copy()->addDays(1);
                        $maintenanceData['kaur_keuangan_approved_by'] = $kaurKeuangan->username;
                        $maintenanceData['status'] = 'Diterima';
                    }
                    break;
                    
                case 4: // Approved and in progress
                    if ($kaurKeuangan) {
                        if ($requesterRole == 'staff_laboratorium' && $kaurLab) {
                            $maintenanceData['kaur_lab_approved_at'] = $submissionDate->copy()->addDays(1);
                            $maintenanceData['kaur_lab_approved_by'] = $kaurLab->username;
                        }
                        $maintenanceData['kaur_keuangan_approved_at'] = $submissionDate->copy()->addDays(2);
                        $maintenanceData['kaur_keuangan_approved_by'] = $kaurKeuangan->username;
                        $maintenanceData['status'] = 'Dikerjakan';
                        $maintenanceData['tanggal_perbaikan'] = $submissionDate->copy()->addDays(3);
                    }
                    break;
                    
                case 5: // Completed
                    if ($kaurKeuangan) {
                        if ($requesterRole == 'staff_laboratorium' && $kaurLab) {
                            $maintenanceData['kaur_lab_approved_at'] = $submissionDate->copy()->addDays(1);
                            $maintenanceData['kaur_lab_approved_by'] = $kaurLab->username;
                        }
                        $maintenanceData['kaur_keuangan_approved_at'] = $submissionDate->copy()->addDays(2);
                        $maintenanceData['kaur_keuangan_approved_by'] = $kaurKeuangan->username;
                        $maintenanceData['status'] = 'Selesai';
                        $maintenanceData['tanggal_perbaikan'] = $submissionDate->copy()->addDays(3);
                        $maintenanceData['tanggal_selesai'] = $submissionDate->copy()->addDays(7);
                    }
                    break;
            }
            
            // Create the maintenance asset
            $maintenanceAsset = MaintenanceAsset::create($maintenanceData);
            
            // Create approval logs
            if ($requester) {
                ApprovalLog::create([
                    'maintenance_asset_id' => $maintenanceAsset->id,
                    'action' => 'submitted',
                    'performed_by' => $requester->username,
                    'role' => $requesterRole,
                    'notes' => 'Pengajuan perbaikan aset diajukan',
                    'created_at' => $submissionDate
                ]);
            }
            
            // Add approval logs based on scenario
            if ($maintenanceData['kaur_lab_approved_at'] ?? false) {
                ApprovalLog::create([
                    'maintenance_asset_id' => $maintenanceAsset->id,
                    'action' => 'approved',
                    'performed_by' => $kaurLab->username,
                    'role' => 'kaur_laboratorium',
                    'notes' => 'Disetujui oleh Kaur Laboratorium',
                    'created_at' => $maintenanceData['kaur_lab_approved_at']
                ]);
            }
            
            if ($maintenanceData['kaur_keuangan_approved_at'] ?? false) {
                ApprovalLog::create([
                    'maintenance_asset_id' => $maintenanceAsset->id,
                    'action' => 'approved',
                    'performed_by' => $kaurKeuangan->username,
                    'role' => 'kaur_keuangan_logistik_sdm',
                    'notes' => 'Disetujui oleh Kaur Keuangan Logistik SDM',
                    'created_at' => $maintenanceData['kaur_keuangan_approved_at']
                ]);
            }
        }
    }
}