<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmergingHotelController;
use App\Http\Controllers\EmergingRegionController;
use App\Http\Controllers\EmergingDescTransHotelController;
use App\Http\Controllers\EmergingTestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// emerging
Route::get('/v1/EmergingHotelStatic', [EmergingHotelController::class, 'fetchHotelStatic']);
Route::get('/v1/EmergingRegionList', [EmergingRegionController::class, 'fetchRegionStatic']);
Route::get('/v1/EmergingDescTransHotel', [EmergingDescTransHotelController::class, 'fetchDescTranslationData']);
Route::get('/v1/EmergingTest', [EmergingTestController::class, 'fetchTest']);