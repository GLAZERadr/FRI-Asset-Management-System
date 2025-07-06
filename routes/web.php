<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\FixVerificationController;
use App\Http\Controllers\FixValidationController;
use App\Http\Controllers\MaintenanceScheduleController;
use App\Http\Controllers\FixStatusController;
use App\Http\Controllers\MonitoringValidationController;
use App\Http\Controllers\PublicMonitoringController;
use App\Http\Controllers\DamagedAssetController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::redirect('/home', '/dashboard');

Route::middleware('guest')->group(function () {
    // Registration
    Route::get('register', [RegistrationController::class, 'create'])
        ->name('register');
    Route::post('register', [RegistrationController::class, 'store']);
    
    // Login
    Route::get('login', [LoginController::class, 'create'])
        ->name('login');
    Route::post('login', [LoginController::class, 'store']);
    
    // Password Reset
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.update');
});

Route::prefix('public')->name('public.')->group(function () {
    // Public QR Scanner Landing Page
    Route::get('/', [PublicMonitoringController::class, 'index'])->name('index');
    
    // QR Code processing for public access
    Route::post('/qr/process', [PublicMonitoringController::class, 'processQR'])->name('qr.process');
    
    Route::get('/debug/room/{kodeRuangan}', function($kodeRuangan) {
        $assets = App\Models\Asset::where('kode_ruangan', $kodeRuangan)->get();
        return response()->json([
            'kode_ruangan' => $kodeRuangan,
            'asset_count' => $assets->count(),
            'assets' => $assets->pluck(['asset_id', 'nama_asset'])->toArray(),
            'monitoring_url' => route('public.monitoring.form', ['kodeRuangan' => $kodeRuangan])
        ]);
    })->name('debug.room');
    // Public monitoring form
    Route::get('/monitoring/{kodeRuangan}', [PublicMonitoringController::class, 'showMonitoring'])->name('monitoring.form');
    Route::post('/monitoring/store', [PublicMonitoringController::class, 'storeMonitoring'])->name('monitoring.store');
    
    // Success page
    Route::get('/monitoring/success/{id}', [PublicMonitoringController::class, 'monitoringSuccess'])->name('monitoring.success');
});

// Damaged Assets
Route::prefix('damage-report')->name('damage-report.')->group(function () {
    // QR processing for guest damage reports
    Route::post('/qr-process', [DamagedAssetController::class, 'processQRForDamage'])->name('qr.process');
    
    // Damage report form (guest access)
    Route::get('/create', [DamagedAssetController::class, 'createDamageReport'])->name('create');
    Route::post('/store', [DamagedAssetController::class, 'storeDamageReport'])->name('store');
    
    // Success page
    Route::get('/success/{damage_id}', [DamagedAssetController::class, 'damageReportSuccess'])->name('success');
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::prefix('mobile')->name('mobile.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/asset-stats', [DashboardController::class, 'getAssetStats'])->name('asset.stats');
        Route::get('/damage-stats', [DashboardController::class, 'getDamageStats'])->name('damage.stats');
    });

    Route::post('/debug-excel', function (Request $request) {
        if ($request->hasFile('excel_file')) {
            $file = $request->file('excel_file');
            
            // Read Excel and show raw data
            $data = Excel::toArray([], $file);
            
            // Show first sheet data
            $firstSheet = $data[0] ?? [];
            
            Log::info('Excel Debug - Raw Data', [
                'total_sheets' => count($data),
                'first_sheet_rows' => count($firstSheet),
                'headers' => $firstSheet[0] ?? [],
                'first_data_row' => $firstSheet[1] ?? [],
                'all_data' => $firstSheet
            ]);
            
            return response()->json([
                'success' => true,
                'total_sheets' => count($data),
                'first_sheet_rows' => count($firstSheet),
                'headers' => $firstSheet[0] ?? [],
                'first_data_row' => $firstSheet[1] ?? [],
                'sample_data' => array_slice($firstSheet, 0, 5) // First 5 rows
            ]);
        }
        
        return response()->json(['error' => 'No file uploaded']);
    });

    Route::post('/qr/process', [AssetController::class, 'processQR'])->name('qr.process');

    // Force mobile view for testing (optional)
    Route::get('/dashboard/mobile', function() {
        request()->merge(['mobile' => true]);
        return app(DashboardController::class)->index(request());
    })->name('dashboard.mobile.force');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data/expenditure', [DashboardController::class, 'getMonthlyExpenditure'])->name('dashboard.expenditure');
    Route::get('/dashboard/data/damage-by-location', [DashboardController::class, 'getDamageByLocation'])->name('dashboard.damage.by.location');
    Route::get('/dashboard/data/completion-time', [DashboardController::class, 'getCompletionTime'])->name('dashboard.completion.time');
    
    // Asset Management
    Route::prefix('pemantauan')->name('pemantauan.')->group(function () {
        // Basic CRUD routes
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/export-pdf', [AssetController::class, 'exportPdf'])->name('export-pdf');
        
        // Monitoring routes - MUST be before the {id} routes
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/print', [MonitoringController::class, 'printLaporan'])->name('monitoring.printLaporan');
        Route::get('/monitoring/verify', [MonitoringController::class, 'verify'])->name('monitoring.verify');
        Route::get('/monitoring/verification/{id_laporan}/{asset_id}', [MonitoringController::class, 'verifying'])->name('monitoring.verifying');
        Route::put('/monitoring/verification/{id_laporan}/{asset_id}', [MonitoringController::class, 'updateVerification'])->name('monitoring.updateVerification');
        Route::get('/monitoring/{kodeRuangan}', [MonitoringController::class, 'showMonitoring'])->name('monitoring.form');
        Route::post('/monitoring/store', [MonitoringController::class, 'storeMonitoring'])->name('monitoring.store');
        Route::get('/monitoring-history', [MonitoringController::class, 'index'])->name('monitoring.history');
        Route::get('/monitoring-report/{id}', [MonitoringController::class, 'show'])->name('monitoring.show');
        Route::delete('/monitoring/verification/report/{id_laporan}', [MonitoringController::class, 'destroyVerificationReport'])->name('monitoring.destroy-report');
        Route::delete('/monitoring/verification/{reportId}/asset/{assetId}', [MonitoringController::class, 'destroyAssetVerification'])->name('monitoring.destroy-asset-verification');
        Route::delete('/monitoring/verification/bulk-delete', [MonitoringController::class, 'bulkDeleteVerified'])->name('monitoring.bulk-delete-verified');
        
        // Asset-specific routes - MUST be at the end
        Route::get('/{id}', [AssetController::class, 'show'])->name('show');
        Route::get('/{asset_id}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{asset_id}', [AssetController::class, 'update'])->name('update');
        Route::get('/qr-download/{asset_id}', [AssetController::class, 'downloadQrCode'])->name('qr-download');
        Route::get('/qr-download-location/{kode_ruangan}', [AssetController::class, 'downloadLocationQrCode'])->name('qr-download-location');
        Route::delete('/{id}', [AssetController::class, 'destroy'])->name('destroy'); 
    });

    Route::prefix('perbaikan')->name('perbaikan.')->group(function () {
        // Validation routes
        Route::get('/validation', [FixValidationController::class, 'index'])->name('validation.index');
        Route::get('/validation/show/{validation_id}', [FixValidationController::class, 'show'])->name('validation.show');
        Route::get('/validation/action/{validation_id}', [FixValidationController::class, 'action'])->name('validation.action');
        Route::post('/validation/update/{validation_id}', [FixValidationController::class, 'update'])->name('validation.update');
        Route::get('/validation/history', [FixValidationController::class, 'history'])->name('validation.history');
        Route::get('/validation/download-pdf/{validation_id}', [FixValidationController::class, 'downloadPdf'])->name('validation.download-pdf');
    
        // Updated Maintenance Schedule routes
        Route::prefix('pemeliharaan-berkala')->name('pemeliharaan-berkala.')->group(function () {
            // Main CRUD routes
            Route::get('/', [MaintenanceScheduleController::class, 'index'])->name('index');
            Route::post('/', [MaintenanceScheduleController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [MaintenanceScheduleController::class, 'edit'])->name('edit');
            Route::put('/{id}', [MaintenanceScheduleController::class, 'update'])->name('update');
            Route::delete('/{id}', [MaintenanceScheduleController::class, 'destroy'])->name('destroy');
            
            // Additional functionality routes
            Route::get('/{id}/details', [MaintenanceScheduleController::class, 'getDetails'])->name('details');
            Route::post('/auto-generate', [MaintenanceScheduleController::class, 'autoGenerate'])->name('auto-generate');
            
            // Report routes
            Route::get('/report', [MaintenanceScheduleController::class, 'report'])->name('report');
            Route::get('/{id}/show-report', [MaintenanceScheduleController::class, 'showReport'])->name('show-report');
            Route::get('/{id}/download-pdf', [MaintenanceScheduleController::class, 'downloadReportPdf'])->name('download-pdf');
            
            // API routes for statistics and data
            Route::get('/api/stats', [MaintenanceScheduleController::class, 'getMaintenanceStats'])->name('api.stats');
            Route::get('/api/asset/{assetId}/history', [MaintenanceScheduleController::class, 'getAssetHistory'])->name('api.asset-history');
            Route::get('/api/assets-needing-maintenance', [MaintenanceScheduleController::class, 'getAssetsNeedingMaintenance'])->name('api.assets-needing-maintenance');
        });
    
        // Status routes - FIXED CONFLICTS
        Route::get('/status', [FixStatusController::class, 'index'])->name('status.index');
        Route::get('/status/selesai', [FixStatusController::class, 'fixHasDone'])->name('status.done');
        Route::get('/status/report', [FixStatusController::class, 'report'])->name('status.report');
        
        // Individual maintenance routes
        Route::get('/status/show/{maintenance_id}', [FixStatusController::class, 'show'])->name('status.show');
        Route::get('/status/show-done/{maintenance_id}', [FixStatusController::class, 'showDone'])->name('status.show-done');
        Route::put('/status/update/{maintenance_id}', [FixStatusController::class, 'update'])->name('status.update');
        
        // Recommendation routes - FIXED CONFLICTS
        Route::get('/status/recommendation/{maintenance_id}', [FixStatusController::class, 'showRecommendation'])->name('status.recommendation.show');
        Route::put('/status/recommendation/{maintenance_id}', [FixStatusController::class, 'updateRecommendation'])->name('status.recommendation.update');
        
        // Report routes - FIXED CONFLICTS
        Route::get('/status/report/show/{maintenance_id}', [FixStatusController::class, 'showReport'])->name('status.report.show');
        Route::put('/status/report/{maintenance_id}', [FixStatusController::class, 'updateCatatan'])->name('status.report.update-catatan');
        
        // PDF download routes
        Route::get('/status/download-pdf/{maintenance_id}', [FixStatusController::class, 'downloadPdf'])->name('status.download-pdf');
        Route::get('/status/download-report-pdf/{maintenance_id}', [FixStatusController::class, 'downloadReportPdf'])->name('status.download-report-pdf');
    });
    
    // Maintenance Requests
    Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
        Route::get('/', [PengajuanController::class, 'index'])->name('index');
        Route::get('/daftar', [PengajuanController::class, 'index'])->name('daftar');
        
        // Static routes MUST come before dynamic ones
        Route::get('/detailed', [PengajuanController::class, 'detailed'])->name('detailed');
        Route::get('/create', [PengajuanController::class, 'create'])->name('create');
        Route::get('/baru', [PengajuanController::class, 'create'])->name('baru');
        
        // Excel template routes (static)
        Route::get('/template/download', [PengajuanController::class, 'downloadTemplate'])->name('template.download');
        Route::post('/template/download-selected', [PengajuanController::class, 'downloadSelectedTemplate'])->name('template.download-selected'); 
        Route::post('/template/upload', [PengajuanController::class, 'uploadTemplate'])->name('template.upload');
        
        Route::post('/topsis/calculate', [PengajuanController::class, 'triggerTopsisCalculation'])->name('topsis.calculate');
        Route::get('/topsis/status', [PengajuanController::class, 'getTopsisStatus'])->name('topsis.status');
        Route::get('/topsis/results', [PengajuanController::class, 'getTopsisResults'])->name('topsis.results');
        Route::post('/ensure-priority-scores', [PengajuanController::class, 'ensurePriorityScores'])->name('ensure-priority-scores');

        // POST routes
        Route::post('/', [PengajuanController::class, 'store'])->name('store');
        Route::post('/selected', [PengajuanController::class, 'storeSelected'])->name('store.selected');
        Route::post('/bulk-approve', [PengajuanController::class, 'bulkApprove'])->name('bulk-approve');

        
        // Dynamic routes MUST come last
        Route::get('/{id}', [PengajuanController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PengajuanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PengajuanController::class, 'update'])->name('update');
        Route::delete('/{id}', [PengajuanController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [PengajuanController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/update-photos', [PengajuanController::class, 'updatePhotos'])->name('update-photos');
        Route::get('/{id}/photo/{photoIndex}/download', [PengajuanController::class, 'downloadPhoto'])->name('photo.download');
        Route::get('/{id}/photos/download-all', [PengajuanController::class, 'downloadAllPhotos'])->name('photos.download-all');
        
        // Approval routes (dynamic)
        Route::post('/{id}/approve', [PengajuanController::class, 'approve'])->name('approve');
    });

    Route::prefix('fix-verification')->name('fix-verification.')->group(function () {
        Route::get('/', [FixVerificationController::class, 'index'])->name('index');
        Route::get('/create/{damage_id}', [FixVerificationController::class, 'create'])->name('create');
        Route::get('/history', [FixVerificationController::class, 'history'])->name('history');
        Route::get('/show/{damage_id}', [FixVerificationController::class, 'show'])->name('show');        
        Route::post('/update/{damage_id}', [FixVerificationController::class, 'update'])->name('update');
        Route::get('/download-pdf', [FixVerificationController::class, 'downloadPdf'])->name('download-pdf');
    });

    Route::prefix('fix-validation')->name('fix-validation.')->group(function () {
        Route::get('/', [MonitoringValidationController::class, 'index'])->name('index');
        Route::get('/create/{id_laporan}', [MonitoringValidationController::class, 'create'])->name('create');
        Route::get('/show/{id_laporan}', [MonitoringValidationController::class, 'show'])->name('show');  
        Route::post('/{id_laporan}/store', [MonitoringValidationController::class, 'store'])->name('store');      
        Route::post('/update/{id_laporan}', [MonitoringValidationController::class, 'approve'])->name('approve');
        Route::get('/print', [MonitoringValidationController::class, 'printValidated'])->name('print');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/get', [NotificationController::class, 'getNotifications'])->name('get');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    // Criteria
    Route::prefix('kriteria')->name('kriteria.')->group(function () {
        Route::get('/', [CriteriaController::class, 'index'])->name('index');
        Route::get('/create', [CriteriaController::class, 'create'])->name('create');
        Route::post('/', [CriteriaController::class, 'store'])->name('store');
        Route::post('/calculate', [CriteriaController::class, 'calculate'])->name('calculate');
        
        // IMPORTANT: This route must exist for storing AHP weights
        Route::post('/store-weights', [CriteriaController::class, 'storeWeights'])->name('store-weights');
        
        Route::delete('/{id}', [CriteriaController::class, 'destroy'])->name('destroy');
    });

    // Payments
    Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/create', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('edit');
        Route::put('/{payment}', [PaymentController::class, 'update'])->name('update');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
        
        // Additional payment actions
        Route::patch('/{payment}/mark-paid', [PaymentController::class, 'markAsPaid'])->name('mark-paid');
        Route::patch('/{payment}/cancel', [PaymentController::class, 'cancel'])->name('cancel');
        Route::get('/{payment}/download-invoice', [PaymentController::class, 'downloadInvoice'])->name('download-invoice');
        
        // NEW ROUTES - Add these lines to your existing routes
        Route::patch('/{payment}/update-status', [PaymentController::class, 'updateStatus'])->name('update-status');
        Route::post('/{payment}/update-photo', [PaymentController::class, 'updatePhoto'])->name('update-photo');
        Route::get('/{payment}/download-payment-photo', [PaymentController::class, 'downloadPaymentPhoto'])->name('download-payment-photo');
        
        // Dashboard and export
        Route::get('/export', [PaymentController::class, 'export'])->name('export');
    });
    
    // Email Verification
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    // Password Confirmation
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    
    // Logout
    Route::post('logout', [LoginController::class, 'destroy'])
        ->name('logout');
});