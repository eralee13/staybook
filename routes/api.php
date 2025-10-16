<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\{
    HotelController, MealController, SearchController, BookingController
};
// routes/api.php
Route::prefix('v1')->withoutMiddleware('throttle:api')  ->group(function () {
    Route::middleware(['api.token:catalog.read', 'throttle:partner-api'])->group(function () {
        Route::get('/getHotels', [HotelController::class, 'index']);
        Route::get('/getHotels/{hotel}', [HotelController::class, 'show'])->whereNumber('hotel');
        Route::get('/getMeals', [MealController::class, 'index']);
    });

    Route::middleware(['api.token:availability.read', 'throttle:partner-api'])->group(function () {
        Route::post('/searchHotel', [SearchController::class, 'index']);
        Route::post('/searchHotel/{hotel}', [SearchController::class, 'show'])->whereNumber('hotel');
    });

    Route::middleware(['throttle:partner-api'])->group(function () {
        Route::get('/getBooks/{book}', [BookingController::class, 'show'])
            ->middleware('api.token:booking.read')->whereNumber('book');
        Route::post('/storeBook', [BookingController::class, 'store'])->middleware('api.token:booking.write');
        Route::post('/cancelBook', [BookingController::class, 'cancel'])->middleware('api.token:booking.cancel');
    });
});
