<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MaintenanceSchedule;
use Illuminate\Support\Facades\Log;

class AutoGenerateMaintenanceSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:auto-generate {--dry-run : Show what would be generated without actually creating schedules}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate maintenance schedules for assets with frequent damage reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic maintenance schedule generation...');
        
        try {
            if ($this->option('dry-run')) {
                $this->info('DRY RUN MODE - No schedules will be created');
                $this->showAssetsNeedingMaintenance();
                return Command::SUCCESS;
            }
            
            $generatedCount = MaintenanceSchedule::autoGenerateFromDamageReports();
            
            if ($generatedCount > 0) {
                $this->info("✅ Successfully generated {$generatedCount} maintenance schedule(s)");
                Log::info("Auto-generated {$generatedCount} maintenance schedules via command");
            } else {
                $this->info("ℹ️  No assets currently need automatic maintenance scheduling");
            }
            
            // Show summary
            $this->showSummary();
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to generate maintenance schedules: " . $e->getMessage());
            Log::error('Auto-generate maintenance command failed: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Show assets that need maintenance attention
     */
    private function showAssetsNeedingMaintenance()
    {
        $twoMonthsAgo = now()->subMonths(2);
        
        $assets = \App\Models\DamagedAsset::where('tanggal_pelaporan', '>=', $twoMonthsAgo)
            ->groupBy('asset_id')
            ->havingRaw('COUNT(*) > 3')
            ->with('asset')
            ->select('asset_id', \DB::raw('COUNT(*) as damage_count'))
            ->get();
            
        if ($assets->count() > 0) {
            $this->info("\nAssets that meet auto-generation criteria (>3 damages in 2 months):");
            $this->table(
                ['Asset ID', 'Asset Name', 'Damage Count', 'Has Scheduled Maintenance'],
                $assets->map(function($damage) {
                    $hasScheduled = MaintenanceSchedule::where('asset_id', $damage->asset_id)
                        ->where('status', 'Dijadwalkan')
                        ->where('tanggal_pemeliharaan', '>=', now())
                        ->exists();
                        
                    return [
                        $damage->asset_id,
                        $damage->asset->nama_asset ?? 'Unknown',
                        $damage->damage_count,
                        $hasScheduled ? 'Yes' : 'No'
                    ];
                })
            );
        } else {
            $this->info("No assets currently meet the auto-generation criteria.");
        }
    }
    
    /**
     * Show maintenance schedule summary
     */
    private function showSummary()
    {
        $stats = [
            'Total Scheduled' => MaintenanceSchedule::where('status', 'Dijadwalkan')->count(),
            'Auto-Generated' => MaintenanceSchedule::where('auto_generated', true)->count(),
            'Overdue' => MaintenanceSchedule::where('status', 'Dijadwalkan')
                ->where('tanggal_pemeliharaan', '<', now())->count(),
            'This Month' => MaintenanceSchedule::whereMonth('tanggal_pemeliharaan', now()->month)
                ->whereYear('tanggal_pemeliharaan', now()->year)->count()
        ];
        
        $this->info("\nMaintenance Schedule Summary:");
        foreach ($stats as $label => $count) {
            $this->line("  {$label}: {$count}");
        }
    }
}