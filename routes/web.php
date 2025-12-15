<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/log/{id}', [DashboardController::class, 'show'])->name('log.show');
Route::get('/domain/{domain}', [DashboardController::class, 'domain'])->name('domain.show');
Route::get('/export', [DashboardController::class, 'export'])->name('export');
