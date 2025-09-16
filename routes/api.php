<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\V1\Tourmind\HotelDetailController;
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
