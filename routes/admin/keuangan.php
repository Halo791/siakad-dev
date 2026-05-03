<?php

use App\Http\Controllers\Admin\KeuanganController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan.index');
    Route::post('/keuangan/pembayaran/{pembayaran}/approve', [KeuanganController::class, 'approvePembayaran'])->name('keuangan.pembayaran.approve');
    Route::post('/keuangan/pembayaran/{pembayaran}/reject', [KeuanganController::class, 'rejectPembayaran'])->name('keuangan.pembayaran.reject');
});
