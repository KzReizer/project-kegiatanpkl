<?php

use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\PklJournalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return auth()->user()->isAdmin()
        ? redirect()->route('admin.users.index')
        : redirect()->route('journals.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/jurnal', [PklJournalController::class, 'index'])->name('journals.index');
    Route::get('/laporan', [PklJournalController::class, 'print'])->name('journals.print');
    Route::get('/export', [PklJournalController::class, 'export'])->name('journals.export');
    Route::post('/jurnal', [PklJournalController::class, 'store'])->name('journals.store');
    Route::post('/jurnal/{journal}/duplicate', [PklJournalController::class, 'duplicate'])->name('journals.duplicate');
    Route::patch('/jurnal/{journal}/archive', [PklJournalController::class, 'archive'])->name('journals.archive');
    Route::get('/jurnal/{journal}/edit', [PklJournalController::class, 'edit'])->name('journals.edit');
    Route::put('/jurnal/{journal}', [PklJournalController::class, 'update'])->name('journals.update');
    Route::delete('/jurnal/{journal}', [PklJournalController::class, 'destroy'])->name('journals.destroy');

    Route::get('/admin', [AdminReportController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [AdminReportController::class, 'show'])->name('admin.users.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
