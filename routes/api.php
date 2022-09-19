<?php

use App\Http\Controllers\Api\UserEmployeeController;
use App\Http\Controllers\Api\UserHrdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('employee')->group(function () {
    Route::get('/', [UserEmployeeController::class, 'index']);
    Route::get('/{id}', [UserEmployeeController::class, 'show']);

    Route::post('/', [UserEmployeeController::class, 'create']);

    Route::put('/', [UserEmployeeController::class, 'edit']);

    Route::delete('/{id}', [UserEmployeeController::class, 'delete']);
});

Route::prefix('hrd')->group(function () {
    Route::get('/', [UserHrdController::class, 'index']);
    Route::get('/{id}', [UserHrdController::class, 'show']);

    Route::post('/', [UserHrdController::class, 'create']);

    Route::put('/', [UserHrdController::class, 'edit']);
    Route::put('/password', [UserHrdController::class, 'editPassword']);

    Route::delete('/{id}', [UserHrdController::class, 'delete']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
