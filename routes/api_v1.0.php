<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Tourmind\SalesTmController;
use App\Http\Controllers\API\V1\Emerging\SalesEtgController;

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

Route::prefix('base')->group(function(){
    //Route::post('/register', [\App\Http\Controllers\API\V1\AuthController::class, 'register'])->name('register');
    Route::post('/login', [\App\Http\Controllers\API\V1\AuthController::class, 'login'])->name('login');
});

Route::prefix('base')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::get('/getHotels', [\App\Http\Controllers\API\V1\HotelController::class, 'index'])->name('getHotelList');
    Route::get('/getHotels/{hotel}', [\App\Http\Controllers\API\V1\HotelController::class, 'show'])->name('showHotel');

    Route::get('/getMeals', [\App\Http\Controllers\API\V1\MealController::class, 'index'])->name('getMealList');

    Route::post('/searchHotel', [\App\Http\Controllers\API\V1\SearchController::class, 'index'])->name('searchHotels');
    Route::post('/searchHotel/{hotel}', [\App\Http\Controllers\API\V1\SearchController::class, 'show'])->name('searchHotel');

    Route::get('/getBooks/{book}', [\App\Http\Controllers\API\V1\BookingController::class, 'show'])->name('showBook');
    Route::post('/storeBook', [\App\Http\Controllers\API\V1\BookingController::class, 'store'])->name('storeBook');
    Route::post('/cancelBook', [\App\Http\Controllers\API\V1\BookingController::class, 'cancel'])->name('cancelBook');
    Route::get('getStatus', ['App\Http\Controllers\BookingController', 'getStatus']);
    //Route::get('/getAmenities', [\App\Http\Controllers\API\V1\AmenityController::class, 'index'])->name('getAmenityList');
    //Route::get('/getAmenities/{amenity}', [\App\Http\Controllers\API\V1\AmenityController::class, 'show'])->name('showAmenity');

    Route::fallback(function () {
        return response()->json(['Not found'], 404);
    });
});

// Tourmind Sales API
Route::post('tm-sales-search', [SalesTmController::class, 'searchHotels']);
Route::post('tm-sales-actualize', [SalesTmController::class, 'actualize']);
Route::post('tm-sales-create-order', [SalesTmController::class, 'createOrder']);
Route::post('tm-sales-search-order', [SalesTmController::class, 'searchOrder']);
Route::post('tm-sales-cancel', [SalesTmController::class, 'cancelOrder']);

// Emerging Sales API
Route::post('etg-sales-search', [SalesEtgController::class, 'searchHotels']);
Route::post('etg-sales-search-rates', [SalesEtgController::class, 'searchRates']);
Route::post('etg-sales-prebook', [SalesEtgController::class, 'preBook']);
Route::post('etg-sales-order-process', [SalesEtgController::class, 'orderProcess']);
Route::post('etg-sales-order-finish', [SalesEtgController::class, 'orderFinish']);
Route::post('etg-sales-order-status', [SalesEtgController::class, 'orderStatus']);
Route::post('etg-sales-search-order', [SalesEtgController::class, 'searchOrder']);
Route::post('etg-sales-cancel', [SalesEtgController::class, 'cancelOrder']);