<?php

use App\Models\Form\VisaForm;
use App\Repositories\Form\VisaFormRepository;
use App\Services\Form\VisaFromFillerService;
use App\Services\Form\VisaJapanPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group([], __DIR__ . '/web/Auth.php');
Route::group([], __DIR__ . '/web/Dashboard.php');
Route::group([], __DIR__ . '/web/Other.php');
Route::group([], __DIR__ . '/web/Account.php');
Route::group([], __DIR__ . '/web/VisaFormLink.php');
Route::group([], __DIR__ . '/web/VisaForm.php');

Route::post('/lw-test', function (Request $request) {
    logger()->info('LW TEST', [
        'headers' => $request->headers->all(),
        'cookies' => $request->cookies->all(),
    ]);

    return response()->json(['ok' => true]);
});
