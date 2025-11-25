<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EtgBasicAuth; // можно оставить FQCN, чтобы исключить путаницу с алиасами
use App\Http\Controllers\API\V1_1\AuthController;
use App\Http\Controllers\API\V1_1\SearchController;
use App\Http\Controllers\API\V1_1\HotelController;
use App\Http\Controllers\API\V1_1\MealController;
use App\Http\Controllers\API\V1_1\BookingController;

Route::prefix('')
    ->middleware([EtgBasicAuth::class])
    ->group(function () {

        Route::get('/auth', [AuthController::class, 'signin'])->name('api.v1.1.auth');

        // --- статус / отмена (основные и алиасы под /search/ ) ---
        Route::post('/status',        [BookingController::class, 'status'])->name('api.v1.1.status');
        Route::post('/cancel',        [BookingController::class, 'cancel'])->name('api.v1.1.cancel');

        // алиасы, которые дергает валидатор (base URL заканчивается на /search/)
        Route::post('/search/status', [BookingController::class, 'status'])->name('api.v1.1.search.status');
        Route::post('/search/cancel', [BookingController::class, 'cancel'])->name('api.v1.1.search.cancel');

        // --- статика ---
        Route::get('/hotels', [HotelController::class, 'index'])->name('api.v1.1.hotels');
        Route::get('/meals',  [MealController::class, 'index'])->name('api.v1.1.meals');

        Route::get('/search/hotels', [HotelController::class, 'index'])->name('api.v1.1.search.hotels');
        Route::get('/search/meals',  [MealController::class, 'index'])->name('api.v1.1.search.meals');

        // --- search ---
        Route::post('/search/search/{scenario}', [SearchController::class, 'searchByHotelInvalidId'])
            ->name('api.v1.1.searchByHotelInvalidId');

        Route::post('/search/{scenario}', [SearchController::class, 'search'])
            ->name('api.v1.1.search');

        Route::post('/rateDetails', [SearchController::class, 'rateDetails'])->name('api.v1.1.rateDetails');

        // --- book / booking-check ---
        Route::post('/book',          [BookingController::class, 'book'])->name('api.v1.1.book');
        Route::post('/booking-check', [BookingController::class, 'bookingCheck'])->name('api.v1.1.bookingCheck');
    });