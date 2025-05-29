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

// Auth routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data/expenditure', [DashboardController::class, 'getMonthlyExpenditure'])->name('dashboard.expenditure');
    Route::get('/dashboard/data/damage-by-location', [DashboardController::class, 'getDamageByLocation'])->name('dashboard.damage.by.location');
    Route::get('/dashboard/data/completion-time', [DashboardController::class, 'getCompletionTime'])->name('dashboard.completion.time');
    
    // Asset Management
    Route::prefix('pemantauan')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('pemantauan');
        Route::get('/create', [AssetController::class, 'create'])->name('pemantauan.create');
        Route::post('/', [AssetController::class, 'store'])->name('pemantauan.store');
        Route::get('/{id}', [AssetController::class, 'show'])->name('pemantauan.show');
        Route::get('/{id}/edit', [AssetController::class, 'edit'])->name('pemantauan.edit');
        Route::put('/{id}', [AssetController::class, 'update'])->name('pemantauan.update');
    });
    
    // Damaged Assets
    Route::prefix('perbaikan-aset')->group(function () {
        Route::get('/', [DamagedAssetController::class, 'index'])->name('perbaikan.aset');
        Route::get('/create', [DamagedAssetController::class, 'create'])->name('perbaikan.aset.create');
        Route::post('/', [DamagedAssetController::class, 'store'])->name('perbaikan.aset.store');
        Route::get('/{id}', [DamagedAssetController::class, 'show'])->name('perbaikan.aset.show');
        Route::get('/{id}/edit', [DamagedAssetController::class, 'edit'])->name('perbaikan.aset.edit');
        Route::put('/{id}', [DamagedAssetController::class, 'update'])->name('perbaikan.aset.update');
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
        Route::post('/template/upload', [PengajuanController::class, 'uploadTemplate'])->name('template.upload');
        
        // POST routes
        Route::post('/', [PengajuanController::class, 'store'])->name('store');
        Route::post('/selected', [PengajuanController::class, 'storeSelected'])->name('store.selected');
        
        // Dynamic routes MUST come last
        Route::get('/{id}', [PengajuanController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PengajuanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PengajuanController::class, 'update'])->name('update');
        Route::delete('/{id}', [PengajuanController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [PengajuanController::class, 'updateStatus'])->name('update-status');
        
        // Approval routes (dynamic)
        Route::post('/{id}/approve', [PengajuanController::class, 'approve'])->name('approve');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
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