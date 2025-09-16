<?php

use App\Http\Middleware\EtgBasicAuth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1_1\AuthController;
use App\Http\Controllers\API\V1_1\SearchController;
use App\Http\Controllers\API\V1_1\HotelController;
use App\Http\Controllers\API\V1_1\MealController;
use App\Http\Controllers\API\V1_1\BookingController;

Route::prefix('')->middleware([EtgBasicAuth::class])->group(function () {
    Route::get('/auth', [AuthController::class, 'signin'])->name('api.v1.1.auth');

    Route::post('/search', [SearchController::class, 'search'])->name('api.v1.1.search');
    Route::post('/search/{hotel_id}', [SearchController::class, 'show'])->name('api.v1.1.searchOne');

    Route::get('/hotels', [HotelController::class, 'index'])->name('api.v1.1.hotels')
        ->withoutMiddleware([
            \App\Http\Middleware\ForceJsonContentType::class,
            \App\Http\Middleware\ForceJsonResponse::class,
            \App\Http\Middleware\ForceJsonCharset::class,
        ]);
    Route::get('/meals', [MealController::class, 'index'])->name('api.v1.1.meals');

    Route::post('/rateDetails', [SearchController::class, 'rateDetails'])->name('api.v1.1.rateDetails');

    Route::post('/book', [BookingController::class, 'book'])->name('api.v1.1.book');
    Route::post('/booking-check', [BookingController::class, 'bookingCheck'])->name('api.v1.1.bookingCheck');
    Route::post('/status', [BookingController::class, 'status'])->name('api.v1.1.status');
    Route::post('/cancel', [BookingController::class, 'cancel'])->name('api.v1.1.cancel');
});