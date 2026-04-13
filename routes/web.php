<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kasir\PosController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
// Transaction history - admin only
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::resource('transactions', TransactionController::class)->only(['index', 'show']);
});

// Admin-only routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('products/trashed', [ProductController::class, 'trashed'])->name('products.trashed');
    Route::post('products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::get('products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.forceDelete');
    Route::get('products-export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class)->except(['show']);

    Route::resource('users', UserController::class);
    Route::get('users-export', [UserController::class, 'export'])->name('users.export');
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/export', [ActivityLogController::class, 'export'])->name('activity-logs.export');

    // Stock Adjustments
    Route::get('stock', [StockAdjustmentController::class, 'index'])->name('stock.index');
    Route::get('stock/export', [StockAdjustmentController::class, 'export'])->name('stock.export');
    Route::get('stock/create', [StockAdjustmentController::class, 'create'])->name('stock.create');
    Route::post('stock', [StockAdjustmentController::class, 'store'])->name('stock.store');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Settings
    Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::patch('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    
    // Backups
    Route::get('backups', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backups.index');
    Route::get('backups/create', [App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backups.create');
    Route::get('backups/download/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
    Route::delete('backups/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('backups.delete');
    Route::get('backups/restore/{filename}', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');
});

// Kasir routes - WITH RATE LIMITING
Route::middleware(['auth', 'role:admin,kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [PosController::class, 'searchProducts'])
        ->middleware('throttle:60,1')
        ->name('pos.search');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])
        ->middleware('throttle:30,1')
        ->name('pos.checkout');
    Route::get('/pos/receipt/{id}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::get('/pos/receipt-invoice/{invoice_code}', [PosController::class, 'receiptByInvoice'])->name('pos.receipt-invoice');
});

require __DIR__.'/auth.php';
