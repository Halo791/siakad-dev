<?php

use App\Http\Controllers\Mahasiswa\RplController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/rpl', [RplController::class, 'index'])->name('rpl.index');
    Route::post('/rpl', [RplController::class, 'store'])->name('rpl.store');
});
