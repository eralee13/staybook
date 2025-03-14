<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Tourmind\TourmindHotelStaticListController;

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

Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\API\V1'], function () {
    Route::apiResource('hotels', HotelController::class);
    Route::apiResource('rooms', RoomController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('rules', RuleController::class);
    Route::apiResource('meals', MealController::class);
    Route::apiResource('childs', ChildController::class);
    Route::apiResource('books', BookingController::class);
});

Route::post('/v1/tmhotels', [TourmindHotelStaticListController::class, 'fetchHotels']);

