<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Account\RoleController;
use App\Http\Controllers\Account\UserController;
use App\Http\Controllers\Account\PermissionController;


Route::middleware(['auth', 'access_permission'])->group(function () {
    Route::group(["controller" => UserController::class, "prefix" => "user", "as" => "user."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });

    Route::group(["controller" => RoleController::class, "prefix" => "role", "as" => "role."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });

    Route::group(["controller" => PermissionController::class, "prefix" => "permission", "as" => "permission."], function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{id}/edit', 'edit')->name('edit');
    });
});
