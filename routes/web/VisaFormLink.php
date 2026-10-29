<?php

use App\Http\Controllers\Form\VisaFormLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(['controller' => VisaFormLinkController::class, 'prefix' => 'visa_form_link', 'as' => 'visa_form_link.'], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
});
