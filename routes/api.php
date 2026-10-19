<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// 'web' gives this route session auth so the logged-in browser can poll it
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
});