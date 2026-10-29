<?php

use App\Helpers\ExportHelper;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterClient\ClientController;
use App\Models\Client\Client;
use App\Models\Client\ClientMagicLink;
use App\Models\Gensen\GensenExportImportHistory;
use App\Models\User;
use App\Repositories\Client\ClientRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

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

Route::post('/lw-test', function (\Illuminate\Http\Request $request) {
    logger()->info('LW TEST', [
        'headers' => $request->headers->all(),
        'cookies' => $request->cookies->all(),
    ]);

    return response()->json(['ok' => true]);
});
