<?php

use App\Http\Controllers\Admin\RplController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/rpl', [RplController::class, 'index'])->name('rpl.index');
    Route::post('/rpl/{pengajuan}/assessment', [RplController::class, 'markAsAssessment'])->name('rpl.assessment');
    Route::post('/rpl/{pengajuan}/konversi', [RplController::class, 'storeKonversi'])->name('rpl.konversi.store');
    Route::post('/rpl/{pengajuan}/approve', [RplController::class, 'approve'])->name('rpl.approve');
    Route::post('/rpl/{pengajuan}/reject', [RplController::class, 'reject'])->name('rpl.reject');
});
