<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(['controller' => DashboardController::class, 'prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
        Route::get('/', 'index')->name('index');
    });
});
