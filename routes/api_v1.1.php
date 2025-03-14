<?php

use Illuminate\Support\Facades\Route;

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


Route::prefix('v1.1')->group(function () {
    Route::get('/getHotels', [\App\Http\Controllers\API\V1_1\SearchController::class, 'index'])->name('getHotelList');
    Route::get('/getHotels/{hotel}', [\App\Http\Controllers\API\V1_1\HotelController::class, 'show'])->name('showHotel');
    Route::get('/getMeals', [\App\Http\Controllers\API\V1_1\MealController::class, 'index'])->name('getMealList');
    Route::post('/searchHotel', [\App\Http\Controllers\API\V1_1\SearchController::class, 'index'])->name('searchHotels');
    Route::post('/searchHotel/{hotel}', [\App\Http\Controllers\API\V1_1\SearchController::class, 'show'])->name('searchHotel');

    Route::post('/storeBook', [\App\Http\Controllers\API\V1_1\BookingController::class, 'store'])->name('storeBook');
    Route::post('/getStatus', [\App\Http\Controllers\API\V1_1\BookingController::class, 'getStatus'])->name('getStatus');
    Route::post('/cancelBook', [\App\Http\Controllers\API\V1_1\BookingController::class, 'cancelBook'])->name('cancelBook');

    Route::fallback(function () {
        return response()->json(['Not found'], 404);
    });
});
