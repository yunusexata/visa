<?php

use App\Http\Controllers\Other\CaptchaController;
use App\Http\Controllers\Other\MenuNotificationController;
use App\Livewire\Client\ExampleFormSubmission;
use App\Livewire\Form\UploadDocument;
use App\Livewire\Form\VisaFormSubmission;
use App\Livewire\Form\VisaFormSuccess;
use Illuminate\Support\Facades\Route;

Route::get('/reload-captcha', [CaptchaController::class, 'reload'])->name('reload_captcha');

// Dummy static form submission preview
Route::get('/example-form', ExampleFormSubmission::class)->name('example-form');
Route::get('/example/form', function () {
    return redirect()->route('example-form');
})->name('example.form');

// Document Upload UI (Parameter Langsung {hashid} dari VisaForm)
Route::get('/form/upload-document/{hashid}', UploadDocument::class)->name('form.upload-document');
Route::get('/example/upload', function () {
    return redirect()->route('example-form');
})->name('example.upload');

// Success Page Pengajuan Visa
Route::get('/form/success/{hashid}', VisaFormSuccess::class)->name('form.success');

// Client Visa Form Submission via Magic Link
Route::get('/form/{hashid}', VisaFormSubmission::class)->name('form.show');

Route::middleware('auth')->group(function () {
    Route::get('/menu-notification', [MenuNotificationController::class, 'index'])->name('menu_notification');
});
