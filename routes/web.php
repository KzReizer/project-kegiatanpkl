<?php

use App\Http\Controllers\PklJournalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PklJournalController::class, 'index'])->name('journals.index');
Route::get('/laporan', [PklJournalController::class, 'print'])->name('journals.print');
Route::post('/jurnal', [PklJournalController::class, 'store'])->name('journals.store');
Route::get('/jurnal/{journal}/edit', [PklJournalController::class, 'edit'])->name('journals.edit');
Route::put('/jurnal/{journal}', [PklJournalController::class, 'update'])->name('journals.update');
Route::delete('/jurnal/{journal}', [PklJournalController::class, 'destroy'])->name('journals.destroy');
