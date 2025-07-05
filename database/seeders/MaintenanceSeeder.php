<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\DamagedAsset;
use App\Models\MaintenanceAsset;
use App\Models\AssetMonitoring;
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
            $this->command->info('🚀 Starting MaintenanceSeeder...');
            
            // Create Assets first
            $this->command->info('📦 Creating Assets...');
            $assets = $this->createAssets();
            $this->command->info('✅ Created ' . count($assets) . ' assets');
            
            // Create Damaged Assets based on the created assets
            $this->command->info('🔧 Creating Damaged Assets...');
            $damagedAssets = $this->createDamagedAssets($assets);
            $this->command->info('✅ Created ' . count($damagedAssets) . ' damage reports');
            
            // Create Maintenance Assets for only SOME damaged assets (not all)
            $this->command->info('🛠️ Creating Maintenance Assets...');
            $this->createMaintenanceAssets($assets, array_slice($damagedAssets, 0, 4)); // Only first 4
            $this->command->info('✅ Created 4 maintenance requests');
            
            // Create additional standalone damaged assets (without maintenance requests)
            $this->command->info('📋 Creating standalone damaged assets...');
            $this->createStandaloneDamagedAssets($assets);
            
            // Get users for monitoring and unvalidated damaged assets
            $this->command->info('👥 Getting users...');
            $users = $this->getUsers();
            $this->command->info('Found users: ' . json_encode(array_map(function($user) {
                return $user ? $user->name : 'not found';
            }, $users)));
            
            // Create unvalidated damaged assets with verified = 'No' and validated = 'No'
            $this->command->info('🚨 Creating unvalidated damaged assets...');
            $unvalidatedDamagedAssets = $this->createUnvalidatedDamagedAssets($assets);
            $this->command->info("✅ Created " . count($unvalidatedDamagedAssets) . " unvalidated damaged assets");
            
            // Verify what was actually created
            $allDamagedAssets = DamagedAsset::all();
            $validatedCount = $allDamagedAssets->where('validated', 'Yes')->count();
            $unvalidatedCount = $allDamagedAssets->where('validated', 'No')->count();
            $this->command->info("📊 Database verification:");
            $this->command->info("  - Total damaged assets: " . $allDamagedAssets->count());
            $this->command->info("  - Validated (Yes): " . $validatedCount);
            $this->command->info("  - Unvalidated (No): " . $unvalidatedCount);
            
            // Create Asset Monitoring reports with validated = 'not_validated'
            $this->command->info('📊 Creating Asset Monitoring reports...');
            $monitoringReports = $this->createAssetMonitoringReports($assets, $users);
            
            $this->command->info('✅ MaintenanceSeeder completed successfully!');
            $this->command->info('📊 Summary:');
            $this->command->info('  - Assets: ' . count($assets));
            $this->command->info('  - Validated damage reports: ' . count($damagedAssets));
            $this->command->info('  - Maintenance requests: 4');
            $this->command->info('  - Unvalidated damage reports: ' . count($unvalidatedDamagedAssets));
            $this->command->info('  - Asset monitoring reports: ' . count($monitoringReports));
        } else {
            $this->command->warn('⚠️  Data already exists. Use --force to override or clear tables manually.');
        }
    }

    /**
     * Get users for different roles
     */
    private function getUsers(): array
    {
        return [
            'staff_laboratorium' => User::where('email', 'like', '%laboratorium%')->orWhere('username', 'like', '%lab%')->first() ?? User::first(),
            'staff_logistik' => User::where('email', 'like', '%logistik%')->orWhere('username', 'like', '%logistik%')->first() ?? User::first(),
            'kaur_laboratorium' => User::where('email', 'like', '%kaur%')->orWhere('username', 'like', '%kaur%')->first() ?? User::first(),
            'kaur_keuangan' => User::where('email', 'like', '%keuangan%')->orWhere('username', 'like', '%keuangan%')->first() ?? User::first(),
        ];
    }

    /**
     * Create Asset Monitoring reports with validated = 'not_validated'
     */
    private function createAssetMonitoringReports($assets, array $users): array
    {
        $this->command->info('🔍 Starting to create Asset Monitoring reports...');
        
        $monitoringReports = [];
        $roomGroups = collect($assets)->groupBy('kode_ruangan');
        
        $reportCounter = 1;
        
        foreach ($roomGroups as $kodeRuangan => $roomAssets) {
            $this->command->info("📍 Processing room: {$kodeRuangan} with " . $roomAssets->count() . " assets");
            
            // Create 2-3 monitoring reports per room
            $reportsPerRoom = rand(2, 3);
            
            for ($i = 0; $i < $reportsPerRoom; $i++) {
                // Determine which user creates this report
                $isLabRoom = str_contains($kodeRuangan, 'TULT') || str_contains($kodeRuangan, 'LAB');
                $user = $isLabRoom ? $users['staff_laboratorium'] : $users['staff_logistik'];
                
                if (!$user) {
                    $this->command->warn("⚠️  No user found for room {$kodeRuangan}, using first available user");
                    $user = User::first();
                }
                
                if (!$user) {
                    $this->command->error("❌ No users found in database, skipping monitoring reports");
                    continue;
                }
                
                // Generate report ID
                $idLaporan = $this->generateMonitoringReportId($user, $reportCounter);
                $reportCounter++;
                
                // Create monitoring data for assets in this room
                $monitoringData = $this->generateMonitoringData($roomAssets, $idLaporan);
                
                // Create monitoring report
                $reportDate = Carbon::now()->subDays(rand(1, 45));
                
                $this->command->info("📝 Creating monitoring report: {$idLaporan}");
                
                $monitoring = AssetMonitoring::firstOrCreate([
                    'id_laporan' => $idLaporan,
                    'kode_ruangan' => $kodeRuangan,
                    'nama_pelapor' => $user->name ?? 'System User',
                    'reviewer' => $user->name ?? 'System User',
                    'tanggal_laporan' => $reportDate,
                    'validated' => 'not_validated', // Not validated yet
                    'validated_at' => null,
                    'catatan' => $this->generateReportNotes(),
                    'monitoring_data' => $monitoringData,
                    'user_id' => $user->id
                ]);
                
                $monitoringReports[] = $monitoring;
            }
        }
        
        $this->command->info('✅ Asset Monitoring reports creation completed. Created: ' . count($monitoringReports) . ' reports');
        return $monitoringReports;
    }

    /**
     * Generate monitoring report ID based on user role
     */
    private function generateMonitoringReportId(User $user, int $counter): string
    {
        $monthYear = date('mY');
        
        $userRoles = $user->getRoleNames();
        $primaryRole = $userRoles->first();
        
        $roleCode = match($primaryRole) {
            'staff_laboratorium' => 'LAB',
            'staff_logistik' => 'LOG',
            'kaur_laboratorium' => 'LAB',
            'kaur_keuangan_logistik_sdm' => 'LOG',
            default => 'GEN'
        };
        
        $sequenceFormatted = str_pad($counter, 3, '0', STR_PAD_LEFT);
        return "LAP-{$monthYear}-{$roleCode}-{$sequenceFormatted}";
    }

    /**
     * Generate monitoring data for assets
     */
    private function generateMonitoringData($assets, string $idLaporan): array
    {
        $monitoringData = [];
        
        foreach ($assets as $asset) {
            // 70% chance asset is in good condition, 30% needs maintenance
            $needsMaintenance = rand(1, 100) <= 30;
            
            $status = $needsMaintenance ? 'butuh_perawatan' : 'baik';
            $verification = 'not_verified'; // All assets start as not verified
            
            $deskripsi = null;
            if ($needsMaintenance) {
                $deskripsi = $this->generateMaintenanceDescription($asset->kategori);
            }
            
            $monitoringData[] = [
                'asset_id' => $asset->asset_id,
                'status' => $status,
                'verification' => $verification,
                'deskripsi' => $deskripsi,
                'foto_path' => null, // No photos in seeder
                'id_laporan' => $idLaporan
            ];
        }
        
        return $monitoringData;
    }

    /**
     * Generate maintenance descriptions based on asset category
     */
    private function generateMaintenanceDescription(string $category): string
    {
        $descriptions = [
            'Elektronik' => [
                'Layar berkedip-kedip',
                'Suara bising dari kipas',
                'Port USB tidak berfungsi',
                'Kabel power longgar',
                'Software perlu update',
                'Overheating saat digunakan',
                'Koneksi jaringan tidak stabil',
                'Battery backup lemah'
            ],
            'Furnitur' => [
                'Permukaan meja tergores',
                'Laci susah dibuka',
                'Roda kursi macet',
                'Cat mulai mengelupas',
                'Handle pintu lemari rusak',
                'Kunci tidak berfungsi',
                'Permukaan tidak rata',
                'Sambungan kayu longgar'
            ],
            'Mesin' => [
                'Suara mesin tidak normal',
                'Getaran berlebihan',
                'Konsumsi listrik tinggi',
                'Filter perlu diganti',
                'Oli perlu service',
                'Belt kendor',
                'Sensor error',
                'Coolant system warning'
            ]
        ];
        
        $categoryDescriptions = $descriptions[$category] ?? $descriptions['Elektronik'];
        return $categoryDescriptions[array_rand($categoryDescriptions)];
    }

    /**
     * Generate report notes
     */
    private function generateReportNotes(): ?string
    {
        $notes = [
            'Pemantauan rutin bulanan',
            'Inspeksi kondisi aset setelah pembersihan',
            'Pengecekan berkala sesuai jadwal',
            'Monitoring pasca maintenance',
            'Evaluasi kondisi aset semester ini',
            null, // Some reports don't have notes
            null,
            'Pengecekan sebelum periode ujian',
            'Monitoring kondisi lab praktikum'
        ];
        
        return $notes[array_rand($notes)];
    }

    /**
     * Create unvalidated damaged assets with verified = 'No' and validated = 'No'
     */
    private function createUnvalidatedDamagedAssets(array $assets): array
    {
        $this->command->info('🚨 Starting to create unvalidated damaged assets...');
        
        $damagedAssets = [];
        $damageReports = [
            'Screen flickering terus menerus',
            'Hardware malfunction terdeteksi sistem',
            'Software tidak kompatibel dengan sistem',
            'Physical damage pada casing luar',
            'Power supply tidak stabil saat operasi',
            'Network connectivity bermasalah',
            'Printer sering paper jam',
            'Keyboard beberapa tombol tidak responsif',
            'Mouse scroll wheel macet',
            'Audio output suara pecah',
            'Port USB tidak terdeteksi device',
            'Cooling system tidak berfungsi optimal',
            'Memory error saat startup',
            'Hard drive mengeluarkan bunyi aneh',
            'Battery tidak dapat di-charge',
            'Monitor tidak menampilkan output',
            'Webcam image buram dan tidak fokus',
            'Microphone pickup suara sangat rendah',
            'Ethernet port koneksi terputus-putus',
            'Bluetooth pairing gagal terus'
        ];
        
        $damageLevels = ['Ringan', 'Sedang', 'Berat'];
        $pelapors = ['Laboratorium', 'Logistik dan SDM', 'Mahasiswa', 'Dosen', 'Staff IT'];
        
        // Create 10-15 unvalidated damage reports
        $numberOfReports = rand(10, 15);
        $selectedAssets = collect($assets)->random(min($numberOfReports, count($assets)));
        
        $this->command->info("📊 Creating {$numberOfReports} unvalidated damaged assets...");
        
        foreach ($selectedAssets as $index => $asset) {
            $damageId = 'UDM' . str_pad(200 + $index, 3, '0', STR_PAD_LEFT); // Start from UDM200 to avoid conflicts
            
            // Check if damage report already exists
            $existingDamage = DamagedAsset::where('damage_id', $damageId)->first();
            if ($existingDamage) {
                $this->command->warn("⚠️  Damage ID {$damageId} already exists, skipping...");
                continue;
            }
            
            $reportingDate = Carbon::now()->subDays(rand(1, 30));
            $damageLevel = $damageLevels[array_rand($damageLevels)];
            $pelapor = $pelapors[array_rand($pelapors)];
            
            // Determine reporter roles based on asset location
            $reporterRoles = ['mahasiswa', 'dosen', 'staff'];
            if (str_contains($asset->lokasi, 'Laboratorium')) {
                $reporterRoles[] = 'asisten'; // Add asisten for lab assets
                $pelapor = 'Laboratorium'; // Also update pelapor for consistency
            } else {
                $pelapor = 'Logistik dan SDM';
            }
            
            $reporterRole = $reporterRoles[array_rand($reporterRoles)];
            
            // Generate unique IDs using model methods
            $verificationId = DamagedAsset::generateVerId();
            $validationId = DamagedAsset::generateValId();
            
            // Calculate estimated cost based on damage level and asset importance
            $baseCost = intval($asset->tingkat_kepentingan_asset) * 125000;
            
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
            
            // Calculate estimated completion time
            $estimatedCompletionTime = $this->calculateEstimatedCompletionTime($reportingDate, $damageLevel);
            
            // Some damaged assets should require vendor
            $needsVendor = rand(1, 100) <= 25; // 25% chance needs vendor
            $vendor = null;
            $technician = 'Staf';
            
            if ($needsVendor) {
                $vendors = ['PT Teknologi Maju', 'CV Solusi Digital', 'PT Mitra Komputer', 'UD Teknik Jaya', 'PT Elektronik Prima'];
                $vendor = $vendors[array_rand($vendors)];
                $technician = 'Vendor';
            }
            
            // Generate reporter names based on role
            $reporterNames = [
                'mahasiswa' => ['Ahmad Fadli', 'Siti Nurhaliza', 'Budi Santoso', 'Rani Widiastuti', 'Farhan Maulana'],
                'dosen' => ['Dr. Bambang Wijaya', 'Prof. Siti Aminah', 'Dr. Ir. Agus Salim', 'Dr. Maya Sari'],
                'staff' => ['Pak Joko Widodo', 'Bu Sri Mulyani', 'Pak Andi Susanto', 'Bu Dewi Kartika'],
                'asisten' => ['Asisten Lab Andi', 'Asisten Lab Sari', 'Asisten Lab Budi', 'Asisten Lab Devi'],
                'admin' => ['Admin Laboratorium', 'Admin Logistik', 'Admin Keuangan']
            ];
            
            $reporterName = $reporterNames[$reporterRole][array_rand($reporterNames[$reporterRole])];
            
            $this->command->info("🔧 Creating unvalidated damage report: {$damageId} for asset {$asset->asset_id}");
            
            // Create unvalidated damaged asset with all required fields
            $createData = [
                'damage_id' => $damageId,
                'asset_id' => $asset->asset_id,
                'deskripsi_kerusakan' => $damageReports[array_rand($damageReports)],
                'tanggal_pelaporan' => $reportingDate,
                'pelapor' => $pelapor,
                'status' => 'Baru',
                'reporter_name' => $reporterName,
                'reporter_role' => $reporterRole,
                'damaged_image' => null, // No images in seeder
                'verification_id' => $verificationId,
                'verified' => 'No', // Not verified yet
                'validation_id' => $validationId,
                'validated' => 'No', // Not validated yet
                // Add missing fields that might be required
                'reviewer' => null,
                'verified_at' => null,
                'validated_at' => null,
                'alasan_penolakan' => null,
                'id_laporan' => null
            ];
            
            // Only add vendor if it's in fillable fields (check model)
            // Note: 'vendor' might not be in the fillable array, so we skip it for now
            
            try {
                $this->command->info("🔧 Creating unvalidated damage: {$damageId} with data: " . json_encode([
                    'damage_id' => $damageId,
                    'asset_id' => $asset->asset_id,
                    'verified' => 'No',
                    'validated' => 'No',
                    'verification_id' => $verificationId,
                    'validation_id' => $validationId
                ]));
                
                $damagedAsset = DamagedAsset::firstOrCreate($createData);
                $damagedAssets[] = $damagedAsset;
                $this->command->info("✅ Successfully created unvalidated damage: {$damageId}");
            } catch (\Exception $e) {
                $this->command->error("❌ Failed to create damage {$damageId}: " . $e->getMessage());
                $this->command->info("🔍 Debug data: " . json_encode($createData));
                $this->command->info("🔍 Error trace: " . $e->getTraceAsString());
            }
        }
        
        $this->command->info('✅ Unvalidated damaged assets creation completed. Created: ' . count($damagedAssets) . ' records');
        return $damagedAssets;
    }

    /**
     * Create additional standalone damaged assets without maintenance requests
     */
    private function createStandaloneDamagedAssets(array $assets): void
    {
        $additionalDamageReports = [
            'Monitor tidak menampilkan gambar',
            'Keyboard sticky keys',
            'Webcam tidak terdeteksi',
            'Bluetooth tidak berfungsi',
            'Printer paper jam terus menerus',
            'Scanner tidak dapat membaca',
            'Microphone tidak ada suara',
            'Ethernet port rusak',
            'Baterai tidak mengisi',
            'Casing retak di bagian samping',
            'LED indicator tidak menyala',
            'Software license expired',
            'Driver tidak kompatibel',
            'HDMI port tidak output',
            'CD/DVD drive tidak terbaca'
        ];

        $damageLevels = ['Ringan', 'Sedang', 'Berat'];
        $pelapors = ['Laboratorium', 'Logistik dan SDM'];
        
        // Get remaining assets that don't have damage reports yet
        $usedAssetIds = DamagedAsset::pluck('asset_id')->toArray();
        $availableAssets = collect($assets)->filter(function($asset) use ($usedAssetIds) {
            return !in_array($asset->asset_id, $usedAssetIds);
        });

        // Also add some assets that might have multiple damage reports
        if ($availableAssets->count() < 5) {
            $availableAssets = $availableAssets->merge(collect($assets)->random(min(3, count($assets))));
        }

        $damageCounter = DamagedAsset::count() + 1; // Continue numbering from existing

        foreach ($availableAssets->take(8) as $index => $asset) { // Create 8 additional damaged assets
            $damageId = 'DMG' . str_pad($damageCounter + $index, 3, '0', STR_PAD_LEFT);
            
            // Check if damage report already exists
            $existingDamage = DamagedAsset::where('damage_id', $damageId)->first();
            if ($existingDamage) {
                continue;
            }

            $reportingDate = Carbon::now()->subDays(rand(1, 30)); // More recent reports

            // Determine reporter based on asset location
            $pelapor = 'Logistik dan SDM';
            if (str_contains($asset->lokasi, 'Laboratorium')) {
                $pelapor = 'Laboratorium';
            }

            // Random damage level
            $damageLevel = $damageLevels[array_rand($damageLevels)];
            
            // Calculate cost based on damage level and asset importance
            $baseCost = intval($asset->tingkat_kepentingan_asset) * 150000; // Slightly higher base cost
            
            switch ($damageLevel) {
                case 'Ringan':
                    $estimatedCost = $baseCost * rand(1, 4);
                    break;
                case 'Sedang':
                    $estimatedCost = $baseCost * rand(4, 10);
                    break;
                case 'Berat':
                    $estimatedCost = $baseCost * rand(10, 20);
                    break;
                default:
                    $estimatedCost = $baseCost * rand(3, 7);
            }

            // Calculate estimated completion time based on damage level
            $estimatedCompletionTime = $this->calculateEstimatedCompletionTime($reportingDate, $damageLevel);

            // Some damaged assets should have vendor recommendations
            $needsVendor = rand(1, 100) <= 30; // 30% chance needs vendor
            $vendor = null;
            $technician = 'Staf';
            
            if ($needsVendor) {
                $vendors = ['PT Teknologi Maju', 'CV Solusi Digital', 'PT Mitra Komputer', 'UD Teknik Jaya', 'PT Elektronik Prima'];
                $vendor = $vendors[array_rand($vendors)];
                $technician = 'Vendor';
            }

            // Create the standalone damaged asset
            DamagedAsset::firstOrCreate([
                'damage_id' => $damageId,
                'asset_id' => $asset->asset_id,
                'tingkat_kerusakan' => $damageLevel,
                'estimasi_biaya' => $estimatedCost,
                'estimasi_waktu_perbaikan' => $estimatedCompletionTime,
                'deskripsi_kerusakan' => $additionalDamageReports[array_rand($additionalDamageReports)],
                'tanggal_pelaporan' => $reportingDate,
                'pelapor' => $pelapor,
                'petugas' => $technician,
                'validated' => 'Yes', // Pre-validated so they appear in staff view
            ]);
        }

        $this->command->info('📝 Created 8 additional standalone damaged assets for staff submission');
    }

    /**
     * Calculate estimated completion time based on damage level
     */
    private function calculateEstimatedCompletionTime(Carbon $reportingDate, string $damageLevel): Carbon
    {
        switch ($damageLevel) {
            case 'Ringan':
                // 2-8 hours from reporting
                return $reportingDate->copy()->addHours(rand(2, 8));
                
            case 'Sedang':
                // 3-10 days from reporting
                return $reportingDate->copy()->addDays(rand(3, 10));
                
            case 'Berat':
                // 2-6 weeks from reporting
                return $reportingDate->copy()->addWeeks(rand(2, 6));
                
            default:
                // Default to 2-5 days
                return $reportingDate->copy()->addDays(rand(2, 5));
        }
    }

    /**
     * Generate realistic cause descriptions
     */
    private function generateCauseDescription($damageLevel): string
    {
        $causes = [
            'Ringan' => [
                'Penggunaan normal sehari-hari',
                'Debu menumpuk di dalam perangkat',
                'Kabel longgar atau tidak terpasang dengan baik',
                'Driver software perlu update',
                'Pengaturan konfigurasi berubah'
            ],
            'Sedang' => [
                'Komponen mulai aus karena pemakaian intensif',
                'Overheating akibat ventilasi kurang baik',
                'Tegangan listrik tidak stabil',
                'Komponen elektronik mengalami degradasi',
                'Kerusakan akibat penanganan yang kurang hati-hati'
            ],
            'Berat' => [
                'Komponen utama mengalami kerusakan fatal',
                'Korsleting akibat masalah kelistrikan',
                'Kerusakan fisik akibat terjatuh atau benturan',
                'Kerusakan akibat cairan masuk ke dalam perangkat',
                'Umur pakai sudah mencapai batas maksimum'
            ]
        ];

        return $causes[$damageLevel][array_rand($causes[$damageLevel])];
    }

    /**
     * Generate repair recommendations
     */
    private function generateRepairRecommendation($damageLevel, $needsVendor): string
    {
        if ($needsVendor) {
            return 'Memerlukan penanganan khusus oleh vendor resmi dengan spare part original dan garansi perbaikan.';
        }

        $recommendations = [
            'Ringan' => [
                'Pembersihan menyeluruh dan pengecekan koneksi',
                'Update driver dan software terkait',
                'Kalibrasi ulang pengaturan sistem',
                'Penggantian kabel atau connector yang rusak'
            ],
            'Sedang' => [
                'Penggantian komponen yang rusak dengan yang kompatibel',
                'Perbaikan sistem pendingin dan ventilasi',
                'Pengecekan dan stabilisasi sumber daya listrik',
                'Maintenance preventif untuk mencegah kerusakan lanjutan'
            ],
            'Berat' => [
                'Evaluasi kelayakan ekonomis untuk perbaikan vs penggantian',
                'Perbaikan komprehensif dengan penggantian komponen utama',
                'Rekondisi menyeluruh dengan pengujian kualitas',
                'Pertimbangan untuk upgrade atau replacement unit'
            ]
        ];

        return $recommendations[$damageLevel][array_rand($recommendations[$damageLevel])];
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
                'tingkat_kepentingan_asset' => '3',
                'vendor' => 'none'
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
                'tingkat_kepentingan_asset' => '3',
                'vendor' => 'none'
                
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
                'tingkat_kepentingan_asset' => '3',
                'vendor' => 'none'
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
                'tingkat_kepentingan_asset' => '2',
                'vendor' => 'none'
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
                'tingkat_kepentingan_asset' => '2',
                'vendor' => 'none'
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
                'tingkat_kepentingan_asset' => '2',
                'vendor' => 'none'
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
                'tingkat_kepentingan_asset' => '1',
                'vendor' => 'none'
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
                'tingkat_kepentingan_asset' => '1',
                'vendor' => 'none'
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
                'tingkat_kepentingan_asset' => '3',
                'vendor' => 'none'
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
                'tingkat_kepentingan_asset' => '2',
                'vendor' => 'none'
            ],
        ];

        $assets = [];
        foreach ($assetData as $data) {
            // Check if asset already exists
            $existingAsset = Asset::where('asset_id', $data['asset_id'])->first();
            if (!$existingAsset) {
                $assets[] = Asset::firstOrCreate($data);
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
            if (str_contains($asset->lokasi, 'Laboratorium')) {
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

            // Calculate estimated completion time
            $estimatedCompletionTime = $this->calculateEstimatedCompletionTime($reportingDate, $damageLevel);

            $damagedAsset = DamagedAsset::firstOrCreate([
                'damage_id' => $damageId,
                'asset_id' => $asset->asset_id,
                'tingkat_kerusakan' => $damageLevel,
                'estimasi_biaya' => $estimatedCost,
                'estimasi_waktu_perbaikan' => $estimatedCompletionTime,
                'deskripsi_kerusakan' => $damageReports[array_rand($damageReports)],
                'tanggal_pelaporan' => $reportingDate,
                'pelapor' => $pelapor,
                'validated' => 'Yes'
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
                'status' => $status,
                'tanggal_pengajuan' => $submissionDate,
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
            $maintenanceAsset = MaintenanceAsset::firstOrCreate($maintenanceData);
            
            // Create approval logs
            if ($requester) {
                ApprovalLog::firstOrCreate([
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
                ApprovalLog::firstOrCreate([
                    'maintenance_asset_id' => $maintenanceAsset->id,
                    'action' => 'approved',
                    'performed_by' => $kaurLab->username,
                    'role' => 'kaur_laboratorium',
                    'notes' => 'Disetujui oleh Kaur Laboratorium',
                    'created_at' => $maintenanceData['kaur_lab_approved_at']
                ]);
            }
            
            if ($maintenanceData['kaur_keuangan_approved_at'] ?? false) {
                ApprovalLog::firstOrCreate([
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