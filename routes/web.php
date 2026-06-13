<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/pesanan', [DashboardController::class, 'store'])->name('pesanan.store');
Route::patch('/pesanan/{id}/status', [DashboardController::class, 'updateStatus'])->name('pesanan.updateStatus');

// API Dashboard Stats (untuk update real-time tanpa reload)
Route::get('/api/dashboard-stats', [DashboardController::class, 'getStats'])->name('api.dashboard-stats');
Route::get('/api/count-proses', [DashboardController::class, 'countProses'])->name('api.countProses');

// Layanan
Route::get('/layanan', [LayananController::class, 'index'])->name('layanan.index');
Route::post('/layanan', [LayananController::class, 'store'])->name('layanan.store');
Route::put('/layanan/{id}', [LayananController::class, 'update'])->name('layanan.update');
Route::patch('/layanan/{id}/toggle', [LayananController::class, 'toggle'])->name('layanan.toggle');
Route::delete('/layanan/{id}', [LayananController::class, 'destroy'])->name('layanan.destroy');
Route::get('/api/layanan', [LayananController::class, 'getAll'])->name('api.layanan');

// Transaksi
Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
Route::get('/api/transaksi', [TransaksiController::class, 'getData'])->name('api.transaksi');

// Laporan
Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
Route::get('/api/laporan', [LaporanController::class, 'getData'])->name('api.laporan');
