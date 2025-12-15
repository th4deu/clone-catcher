<?php

use App\Http\Controllers\Api\CollectorController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/collect', [CollectorController::class, 'store'])->name('api.collect');
Route::get('/stats', [DashboardController::class, 'api'])->name('api.stats');
