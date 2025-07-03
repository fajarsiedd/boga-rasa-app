<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/')->middleware('redirect.auth.guest');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Resources
    Route::resource('tipe-konsumen', CustomerTypeController::class);
    Route::resource('konsumen', CustomerController::class);
    Route::resource('pemasok', SupplierController::class);
    Route::resource('produk', ProductController::class);
    Route::resource('bahan-baku', MaterialController::class);
    Route::resource('penjualan', SaleController::class);
    Route::resource('pembelian', PurchaseController::class);
    Route::resource('pesanan', OrderController::class);
    Route::resource('piutang', ReceivableController::class);
    Route::resource('produksi', ProductionController::class);
    Route::resource('laporan', ReportController::class);

    // API
    Route::prefix('api')->group(function () {
        Route::post('/konsumen', [CustomerController::class, 'quickStore'])->name('konsumen.quickStore');
        Route::post('/pemasok', [SupplierController::class, 'quickStore'])->name('pemasok.quickStore');
    });
});

require __DIR__ . '/auth.php';
