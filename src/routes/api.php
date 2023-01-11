<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('subscribe', [\App\Http\Controllers\Api\WABInteractionController::class, 'subscribe']);
Route::post('subscribe', [\App\Http\Controllers\Api\WABInteractionController::class, 'interact']);

Route::apiResource('services', \App\Http\Controllers\Api\ServiceController::class);

Route::match(['get', 'post'], 'payment-status/{id}', [\App\Http\Controllers\Api\PaymentController::class, 'callback']);
