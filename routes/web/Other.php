<?php

use App\Http\Controllers\Other\CaptchaController;
use App\Http\Controllers\Other\MenuNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/reload-captcha', [CaptchaController::class, 'reload'])->name('reload_captcha');

// Dummy static form submission preview
Route::get('/example-form', \App\Livewire\Client\ExampleFormSubmission::class)->name('example-form');
Route::get('/example/form', function () {
    return redirect()->route('example-form');
})->name('example.form');

// Document Upload UI
Route::get('/form/upload-document', \App\Livewire\Form\UploadDocument::class)->name('form.upload-document');
Route::get('/example/upload', function () {
    return redirect()->route('form.upload-document');
})->name('example.upload');

// Client Visa Form Submission via Magic Link
Route::get('/form/{hashid}', \App\Livewire\Form\VisaFormSubmission::class)->name('form.show');

Route::middleware('auth')->group(function () {
    Route::get("/menu-notification", [MenuNotificationController::class, "index"])->name('menu_notification');
});