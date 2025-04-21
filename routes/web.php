<?php

use App\Http\Controllers\Admin\AllBillsController;
use App\Http\Controllers\Admin\AllBookingController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\ListbookController;
use App\Http\Controllers\Admin\UserBookController;
use App\Http\Controllers\API\Exely\ContentController;
use App\Http\Controllers\API\Exely\ReservationController;
use App\Http\Controllers\API\Exely\SearchController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProfileController;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;
use App\Livewire\HotelResults;
use App\Livewire\HotelRooms;
use App\Livewire\BookingForm;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('locale/{locale}', 'App\Http\Controllers\MainController@changeLocale')->name('locale');
Route::get('/logout', 'App\Http\Controllers\ProfileController@logout')->name('get-logout');

Route::middleware('set_locale')->group(function () {
    Route::group(["prefix" => "auth"], function () {
        Route::resource("hotels", "App\Http\Controllers\Admin\HotelController");
        Route::resource("amenities", "App\Http\Controllers\Admin\AmenityController");
        Route::resource("payments", "App\Http\Controllers\Admin\PaymentController");
        //Route::resource("listbooks", "App\Http\Controllers\Admin\ListbookController");
        Route::resource("bookings", "App\Http\Controllers\Admin\BookingController");
        Route::resource("prices", "App\Http\Controllers\Admin\PriceController");
        Route::resource("rooms", "App\Http\Controllers\Admin\RoomController");
        Route::resource("categoryRooms", "App\Http\Controllers\Admin\CategoryRoomController");
        Route::resource("rates", "App\Http\Controllers\Admin\RateController");
        Route::resource("meals", "App\Http\Controllers\Admin\MealController");
        Route::resource("rules", "App\Http\Controllers\Admin\RuleController");
        Route::resource("accommodations", "App\Http\Controllers\Admin\AccommodationController");
        Route::resource("pages", "App\Http\Controllers\Admin\PageController");
        Route::resource("images", "App\Http\Controllers\Admin\ImageController");
        Route::resource("bills", "App\Http\Controllers\Admin\BillController");
        Route::resource("users", "App\Http\Controllers\Admin\UserController");
        Route::resource("roles", "App\Http\Controllers\Admin\RoleController");
        Route::resource("permissions", "App\Http\Controllers\Admin\PermissionController");
        Route::resource("contacts", "App\Http\Controllers\Admin\ContactController");

        Route::get("search", [HotelController::class, 'search']);
        Route::get("searchbook", [ListBookController::class, 'searchbook']);
        //Route::get("/book/exelyshow/{book}", [ListBookController::class, 'exelyshow'])->name('book.exelyshow');
        Route::get("/listbooks", [ListBookController::class, 'index'])->name('listbooks.index');
        Route::get("/listbooks/show/{book}", [ListBookController::class, 'show'])->name('listbooks.show');

        //finance role
        Route::get("/allbooks", [AllBookingController::class, 'index'])->name('allbooks.index');
        Route::get("/allbills", [AllBillsController::class, 'index'])->name('allbills.index');


//                Route::get('/hotels/{status?}/{show_result?}/{s_query?}', [HHotelController::class, 'index'])->name
//                ('hotels.index');
//                Route::get('/rooms/{status?}/{show_result?}/{s_query?}', [RoomController::class, 'index'])->name
//                ('rooms.index');

        Route::get('generate-pdf/{id}', [PDFController::class, 'generatePDF'])->name('pdf');

        //Route::get('/books/index', [BookingController::class, 'index'])->name('books.index');
        //Route::get('/books/create', [BookingController::class, 'create'])->name('listbooks.create');
        Route::post('/books/store', [BookingController::class, 'store'])->name('listbooks.store');
        //Route::put('/books/edit/{id}', [BookingController::class, 'edit'])->name('listbooks.edit');
        //Route::patch('/books/update/{id}', [BookingController::class, 'update'])->name('listbooks.update');
        //Route::delete('/books/destroy/{id}', [BookingController::class, 'destroy'])->name('listbooks
        //.destroy');

        Route::get('/userbooks', [UserBookController::class, 'index'])->name('userbooks.index');
        Route::get('/userbooks/show/{book}', [UserBookController::class, 'showBook'])->name('userbooks.show');
        Route::post('/userbooks/cancel/{book}', [UserBookController::class, 'cancelBook'])->name('userbooks.cancel');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    require __DIR__ . '/auth.php';

    //Scramble::registerJsonSpecificationRoute(path: 'docs/v1.0.json', api: 'v1.0');
    //Scramble::registerJsonSpecificationRoute(path: 'docs/v1.1.json', api: 'v1.1');


    Route::get('/', [PageController::class, 'index'])->name('index');
    Route::get("/searchtest", [PageController::class, 'searchtest'])->name('searchtest');
    Route::get('/allrooms', [PageController::class, 'allrooms'])->name('allrooms');
    Route::get('/products/create-step-one', [PageController::class, 'createStepOne'])->name('createStepOne');
    Route::post('/products/create-step-one', [PageController::class, 'postCreateStepOne'])->name('postCreateStepOne');
    Route::get('/products/create-step-two', [PageController::class, 'createStepTwo'])->name('createStepTwo');
    Route::post('/products/create-step-two', [PageController::class, 'postCreateStepTwo'])->name('postCreateStepTwo');
    Route::get('/products/create-step-three', [PageController::class, 'createStepThree'])->name('createStepThree');
    Route::post('/products/create-step-three', [PageController::class, 'postCreateStepThree'])->name('postCreateStepThree');

    //Exely API
    //Content
    Route::get('/v1/properties', [ContentController::class, 'properties'])->name('properties');
    Route::get('/v1/properties/{property}', [ContentController::class, 'property'])->name('property');
    Route::get('/v1/meals', [ContentController::class, 'meals'])->name('meals');
    Route::get('/v1/roomtypes', [ContentController::class, 'roomtypes'])->name('roomtypes');
    Route::get('/v1/amenities', [ContentController::class, 'amenities'])->name('amenities');
    Route::get('/v1/extrarules', [ContentController::class, 'extrarules'])->name('extrarules');

    //Search API
    Route::get('/v1/search_property', [SearchController::class, 'search_property'])->name('search_property');
    Route::get('/v1/search_roomstays', [SearchController::class, 'search_roomstays'])->name('search_roomstays');
    Route::get('/v1/search_services', [SearchController::class, 'search_services'])->name('search_services');
    Route::get('/v1/search_extrastays', [SearchController::class, 'search_extrastays'])->name('search_extrastays');

    //Reservation API
    Route::get('/v1/bookings', [ReservationController::class, 'res_bookings'])->name('res_bookings');
    Route::get('/v1/booking', [ReservationController::class, 'res_booking'])->name('res_booking');
    Route::get('/v1/booking/modify', [ReservationController::class, 'res_modify'])->name('res_modify');
    Route::get('/v1/booking/verify', [ReservationController::class, 'res_verify'])->name('res_verify');
    Route::get('/v1/booking/cancel', [ReservationController::class, 'res_cancel'])->name('res_cancel');
    Route::get('/v1/booking/calculate', [ReservationController::class, 'res_calculate'])->name('res_calculate');
    Route::get('/v1/bookings/verify', [ReservationController::class, 'res_verify_bookings'])->name('res_bookings_verify');
    Route::get('/orderexely/{order}', [ReservationController::class, 'orderexely'])->name('orderexely');

    Route::get('/allhotels', [PageController::class, 'hotels'])->name('hotels');
    Route::get('/order/{order}', [PageController::class, 'order'])->name('order');

    Route::get('/testsearch', [PageController::class, 'testsearch'])->name('testsearch');


    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/contactspage', [PageController::class, 'contactspage'])->name('contactspage');
    Route::get('/search', [PageController::class, 'search'])->name('search');
    Route::get('/hotel/{hotel}', [PageController::class, 'hotel'])->name('hotel');
    Route::get('/hotel/{hotel}/{rooms}', [PageController::class, 'room'])->name('room');

    //email
    Route::post('contact_mail', [MainController::class, 'contact_mail'])->name('contact_mail');
    Route::post('book_mail', [PageController::class, 'book_mail'])->name('book_mail');

    //Hotel list
    Route::get('/hotel-results', HotelResults::class)->name('hotel.results');
    Route::get('/hotel-rooms', HotelRooms::class)->name('hotel.rooms');
    Route::get('/bookingform', BookingForm::class)->name('bookingform');

    Route::get('/books/export', [ListbookController::class, 'export'])->name('books.list');


});

