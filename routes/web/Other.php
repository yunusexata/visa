<?php

use App\Http\Controllers\Other\CaptchaController;
use App\Http\Controllers\Other\MenuNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/reload-captcha', [CaptchaController::class, 'reload'])->name('reload_captcha');

// Dummy static form submission preview
Route::get('/example-form', \App\Livewire\Client\ExampleFormSubmission::class)->name('example-form');
Route::get('/example/form', function () {
    return redirect()->route('example-form');
});

Route::middleware('auth')->group(function () {
    Route::get("/menu-notification", [MenuNotificationController::class, "index"])->name('menu_notification');
});