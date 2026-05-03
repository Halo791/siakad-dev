<?php

use App\Http\Controllers\Mahasiswa\KeuanganController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan.index');
    Route::post('/keuangan/pembayaran', [KeuanganController::class, 'storePembayaran'])->name('keuangan.pembayaran.store');
});
