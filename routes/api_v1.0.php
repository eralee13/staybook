<?php

use Illuminate\Http\Request;
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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::prefix('v1.0')->group(function(){
//    //Route::post('/register', [\App\Http\Controllers\API\V1\AuthController::class, 'register'])->name('register');
//    Route::post('/login', [\App\Http\Controllers\API\V1\AuthController::class, 'login'])->name('login');
//});

Route::prefix('')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::get('/getHotels', [\App\Http\Controllers\API\V1\HotelController::class, 'index'])->name('getHotelList');
    Route::get('/getHotels/{hotel}', [\App\Http\Controllers\API\V1\HotelController::class, 'show'])->name('showHotel');

    Route::get('/getMeals', [\App\Http\Controllers\API\V1\MealController::class, 'index'])->name('getMealList');

    Route::post('/searchHotel', [\App\Http\Controllers\API\V1\SearchController::class, 'index'])->name('searchHotels');
    Route::post('/searchHotel/{hotel}', [\App\Http\Controllers\API\V1\SearchController::class, 'show'])->name('searchHotel');

    Route::get('/getBooks/{book}', [\App\Http\Controllers\API\V1\BookingController::class, 'show'])->name('showBook');
    Route::post('/storeBook', [\App\Http\Controllers\API\V1\BookingController::class, 'store'])->name('storeBook');
    Route::post('/cancelBook', [\App\Http\Controllers\API\V1\BookingController::class, 'cancel'])->name('cancelBook');

    //Route::get('/getAmenities', [\App\Http\Controllers\API\V1\AmenityController::class, 'index'])->name('getAmenityList');
    //Route::get('/getAmenities/{amenity}', [\App\Http\Controllers\API\V1\AmenityController::class, 'show'])->name('showAmenity');


    Route::fallback(function () {
        return response()->json(['Not found'], 404);
    });
});