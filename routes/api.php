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
        Route::post('/searchHotels', [SearchController::class, 'index']);
        Route::post('/searchHotel/{hotel}', [SearchController::class, 'show'])->whereNumber('hotel');
        Route::post('/actualize', [SearchController::class, 'actualize']);
    });

    Route::middleware(['throttle:partner-api'])->group(function () {
        Route::post('/verifyBook', [BookingController::class, 'verify'])
            ->middleware(['api.token:booking.verify', 'throttle:partner-api'])
            ->name('booking.verify');
        Route::post('/storeBook', [BookingController::class, 'store'])
            ->middleware(['api.token:booking.write', 'throttle:partner-api'])
            ->name('booking.store');
        Route::get('/getBooks/{book}', [BookingController::class, 'show'])
            ->middleware('api.token:booking.read')->whereNumber('book');
        Route::post('/cancelBook', [BookingController::class, 'cancel'])->middleware('api.token:booking.cancel');
    });
});
