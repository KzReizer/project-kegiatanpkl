<?php

use App\Http\Controllers\PklJournalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PklJournalController::class, 'index'])->name('journals.index');
Route::post('/jurnal', [PklJournalController::class, 'store'])->name('journals.store');
Route::delete('/jurnal/{journal}', [PklJournalController::class, 'destroy'])->name('journals.destroy');
