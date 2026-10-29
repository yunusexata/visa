<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'login']);
    Route::get("/login", [AuthController::class, "login"])->name("login");
    Route::get("/logout", [AuthController::class, "logout"])->name('logout');
    // Route::get("/register", [AuthController::class, "register"])->name("register");
    // Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    // Route::get('/reset-password/{token}',  [AuthController::class, 'resetPassword'])->name('password.reset');
    // Route::get("/email_verification", [AuthController::class, "emailVerification"])->name("verification.index");
    // Route::get('/email/verify/{id}/{hash}', [AuthController::class, "emailVerificationVerify"])->middleware('signed')->name('verification.verify');
});

Route::middleware('auth')->group(function () {
    Route::get("/logout", [AuthController::class, "logout"])->name('logout');
    Route::get('/profile', [AuthController::class, "profile"])->name('profile');
});
