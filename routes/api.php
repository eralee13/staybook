<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\V1\Tourmind\HotelDetailController;
use App\Http\Controllers\API\V1\Tourmind\CheckRoomRateController;
use App\Http\Controllers\API\V1\Tourmind\SearchOrderController;
use App\Http\Controllers\API\V1\Tourmind\CreateOrderController;
use App\Http\Controllers\API\V1\Tourmind\CancelOrderController;
use App\Http\Controllers\API\V1\Tourmind\HotelStaticListController;
use App\Http\Controllers\API\V1\Tourmind\RegionListController;
use App\Http\Controllers\API\V1\Tourmind\RoomStaticListController;

use App\Http\Controllers\API\V1\Emerging\EmergingHotelController;
use App\Http\Controllers\API\V1\Emerging\EmergingRegionController;
use App\Http\Controllers\API\V1\Emerging\EmergingDescTransHotelController;
use App\Http\Controllers\API\V1\Emerging\EmergingTestController;
use App\Http\Controllers\API\V1\Emerging\EmergingFormController;

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


Route::prefix('api/v1/')->group(function () {
    // tourmind
    Route::post('TmHotelDetail', [HotelDetailController::class, 'fetchHotelDetail']);
    Route::post('TmCheckRoomRate', [CheckRoomRateController::class, 'fetchCheckRoomRate']);
    Route::post('TmSearchOrder', [SearchOrderController::class, 'fetchSearchOrder']);
    Route::post('TmCreateOrder', [CreateOrderController::class, 'fetchCreateOrder']);
    Route::post('TmCancelOrder', [CancelOrderController::class, 'fetchCancelOrder']);
    Route::post('TmHotels', [HotelStaticListController::class, 'fetchHotels']);
    Route::post('TmRegionList', [RegionListController::class, 'fetchRegions']);
    Route::post('TmRoomType', [RoomStaticListController::class, 'fetchRoomsTypes']);
    
    // emerging
    Route::get('EmergingHotelStatic', [EmergingHotelController::class, 'fetchHotelStatic']);
    Route::get('EmergingRegionList', [EmergingRegionController::class, 'fetchRegionStatic']);
    Route::get('EmergingDescTransHotel', [EmergingDescTransHotelController::class, 'fetchDescTranslationData']);
    Route::get('EmergingTest', [EmergingTestController::class, 'fetchTest']);
    Route::get('EmergingForm', [EmergingFormController::class, 'startProcess']);
});
