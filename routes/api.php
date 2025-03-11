<?php

use App\Http\Controllers\API\V1\AuthController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function(){
    //Route::post('/register', [\App\Http\Controllers\API\V1\AuthController::class, 'register'])->name('register');
    Route::post('/login', [\App\Http\Controllers\API\V1\AuthController::class, 'login'])->name('login');
});

Route::prefix('v1')->middleware(['throttle:api', 'auth:sanctum'])->group(function () {
    Route::get('/getHotels', [\App\Http\Controllers\API\V1\HotelController::class, 'index'])->name('getHotelList');
    Route::get('/getHotels/{hotel}', [\App\Http\Controllers\API\V1\HotelController::class, 'show'])->name('showHotel');

    Route::get('/getRooms', [\App\Http\Controllers\API\V1\RoomController::class, 'index'])->name('getRoomList');
    Route::get('/getRooms/{room}', [\App\Http\Controllers\API\V1\RoomController::class, 'show'])->name('showRoom');

    Route::get('/getRates', [\App\Http\Controllers\API\V1\RateController::class, 'index'])->name('getRateList');
    Route::get('/getRates/{category}', [\App\Http\Controllers\API\V1\RateController::class, 'show'])->name('showRate');

    Route::get('/getRules', [\App\Http\Controllers\API\V1\RuleController::class, 'index'])->name('getRuleList');
    Route::get('/getRules/{rule}', [\App\Http\Controllers\API\V1\RuleController::class, 'show'])->name('showRule');

    Route::get('/getMeals', [\App\Http\Controllers\API\V1\MealController::class, 'index'])->name('getMealList');
    Route::get('/getMeals/{meal}', [\App\Http\Controllers\API\V1\MealController::class, 'show'])->name('showMeal');

    Route::get('/getAccommodations', [\App\Http\Controllers\API\V1\AccommodationController::class, 'index'])->name('getAccommodationList');
    Route::get('/getAccommodations/{accommodation}', [\App\Http\Controllers\API\V1\AccommodationController::class, 'show'])->name('showAccommodation');
    //Route::apiResource('books', BookingController::class);

    //Route::get('/getBooks', [\App\Http\Controllers\API\V1\BookingController::class, 'index'])->name('getBookList');
    Route::get('/getBooks/{book}', [\App\Http\Controllers\API\V1\BookingController::class, 'show'])->name('showBook');
    Route::post('/storeBook', [\App\Http\Controllers\API\V1\BookingController::class, 'store'])->name('storeBook');
    Route::post('/updateBook/{book}', [\App\Http\Controllers\API\V1\BookingController::class, 'update'])->name('updateBook');
    Route::post('/cancelBook', [\App\Http\Controllers\API\V1\BookingController::class, 'cancel'])->name('cancelBook');

    //Route::get('/getAmenities', [\App\Http\Controllers\API\V1\AmenityController::class, 'index'])->name('getAmenityList');
    //Route::get('/getAmenities/{amenity}', [\App\Http\Controllers\API\V1\AmenityController::class, 'show'])->name('showAmenity');

    Route::get('/getCategoryRooms', [\App\Http\Controllers\API\V1\CategoryRoomController::class, 'index'])->name('getCategoryRoomList');
    Route::get('/getCategoryRooms/{categoryRoom}', [\App\Http\Controllers\API\V1\CategoryRoomController::class, 'show'])->name('showCategoryRoom');

    Route::fallback(function () {
        return response()->json(['Not found'], 404);
    });
});
