<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BaseController;
use App\Http\Controllers\API\VehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/unauthenticated', function () {
    return (new BaseController)->sendError('Unauthorized.', ['error'=>'Unauthorized']);
})->name('api.unauthenticated');

Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('signup', [AuthController::class, 'signup']);

Route::middleware('auth:sanctum')->group( function () {
    Route::resource('vehicle', VehicleController::class);

    Route::get('vehicle-get-deleted-data', [VehicleController::class, 'deletedData']);

});
