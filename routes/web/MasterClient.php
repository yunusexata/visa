<?php

use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterClient\ClientController;
use App\Http\Controllers\MasterClient\MasterClientController;
use App\Http\Controllers\MasterClient\MasterClientRegisteredController;
use App\Http\Controllers\MasterClient\MonitoringBuktiTransferController;
use App\Http\Controllers\MasterClient\TestPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(['controller' => MasterClientController::class, 'prefix' => 'master_client', 'as' => 'master_client.'], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::get('{id}/detail', 'detail')->name('detail');
    });
    Route::group(['controller' => MasterClientRegisteredController::class, 'prefix' => 'master_client_registered', 'as' => 'master_client_registered.'], function () {
        Route::get('/', 'index')->name('index');
        Route::get('{id}/edit', 'edit')->name('edit');
        Route::get('{id}/detail', 'detail')->name('detail');
    });
    Route::group(['controller' => DashboardController::class, 'prefix' => 'pov_client', 'as' => 'pov_client.'], function () {
        Route::get('/{id}', 'povClient')->name('index');
    });

    // Route::group(["controller" => TestPageController::class, "prefix" => "test_page", "as" => "test_page."], function () {
    //     Route::get('/', 'index')->name('index');
    //     Route::get('{id}/edit', 'edit')->name('edit');
    //     Route::get('{id}/detail', 'detail')->name('detail');
    // });

    Route::group(['controller' => ClientController::class, 'prefix' => 'client_form_confirmation', 'as' => 'client_form_confirmation.'], function () {
        Route::get('/', 'form_confirmation')->name('index');
    });
    Route::group(['controller' => ClientController::class, 'prefix' => 'client_document', 'as' => 'client_document.'], function () {
        Route::get('/', 'document')->name('index');
    });
    Route::group(['controller' => ClientController::class, 'prefix' => 'client_agreement', 'as' => 'client_agreement.'], function () {
        Route::get('/', 'agreement')->name('index');
    });
    Route::group(['controller' => ReviewController::class, 'prefix' => 'admin/reviews', 'as' => 'admin_review.'], function () {
        Route::get('/', 'index')->name('index');
    });
    Route::group([
        'controller' => MonitoringBuktiTransferController::class,
        'prefix' => 'monitoring-bukti-transfer',
        'as' => 'monitoring_bukti_transfer.',
    ], function () {
        Route::get('/', 'index')->name('index');
    });
});

Route::group(['controller' => ClientController::class, 'prefix' => 'client', 'as' => 'client.'], function () {
    Route::get('{token}/create', 'create')->name('create');
});
