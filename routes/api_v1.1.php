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

Route::prefix('')->group(function(){
    //Route::post('/register', [\App\Http\Controllers\API\V1\AuthController::class, 'register'])->name('register');
    Route::post('/auth', [\App\Http\Controllers\API\V1\AuthController::class, 'login'])->name('login');
});

Route::prefix('')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::get('/hotels', [\App\Http\Controllers\API\V1_1\HotelController::class, 'HotelStatic'])->name('HotelStatic');
    Route::get('/meals', [\App\Http\Controllers\API\V1_1\MealController::class, 'meals'])->name('Meals');

    Route::post('/search', [\App\Http\Controllers\API\V1_1\SearchController::class, 'search'])->name('search');
    Route::post('/searchHotel/{hotel_id}', [\App\Http\Controllers\API\V1_1\SearchController::class, 'show'])->name('searchHotel');
    Route::post('/rateDetails/{hotel_id}/{rate_id}', [\App\Http\Controllers\API\V1_1\SearchController::class, 'ratedetails'])->name('rateDetails');

    Route::post('/book', [\App\Http\Controllers\API\V1_1\BookingController::class, 'book'])->name('book');
    Route::post('/status', [\App\Http\Controllers\API\V1_1\BookingController::class, 'status'])->name('status');
    Route::post('/cancel', [\App\Http\Controllers\API\V1_1\BookingController::class, 'cancel'])->name('cancel');

    Route::fallback(function () {
        return response()->json(['Not found'], 404);
    });
});
