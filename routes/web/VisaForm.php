<?php

use App\Http\Controllers\Form\VisaFormController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(['controller' => VisaFormController::class, 'prefix' => 'visa_form', 'as' => 'visa_form.'], function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}/attachments', 'attachments')->name('attachments');
        Route::get('/{id}/generate-pdf', 'generatePdf')->name('generate_pdf');
    });
});
