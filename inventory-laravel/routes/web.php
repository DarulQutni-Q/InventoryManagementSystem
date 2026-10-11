<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Publik)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

// Protected Routes (Wajib Login)
Route::middleware('auth')->group(function () {
    // Dashboard Eksekutif & Statistik
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Transaksi Mutasi Stok
    Route::get('/stock/out', [StockMovementController::class, 'createOut'])->name('stock.createOut');
    Route::resource('stock', StockMovementController::class)->only(['index', 'create', 'store']);

    // Master Data Produk, Kategori, Supplier
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('suppliers', SupplierController::class);

    // Manajemen Pengguna (Khusus Administrator)
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'create', 'store']);
    });
});
