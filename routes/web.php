<?php

use App\Http\Controllers\Admin\AllBillsController;
use App\Http\Controllers\Admin\AllBookingController;
use App\Http\Controllers\Admin\BookingCalendarController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\ListbookController;
use App\Http\Controllers\Admin\PDFController;
use App\Http\Controllers\Admin\UserBookController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Livewire\BookingForm;
use App\Livewire\HotelResults;
use App\Livewire\HotelRooms;
use App\Livewire\HotelWizard;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

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

Scramble::registerJsonSpecificationRoute(path: 'docs/v1.0.json', api: 'v1.0');
Scramble::registerJsonSpecificationRoute(path: 'docs/v1.1.json', api: 'v1.1');

Route::middleware('set_locale')->group(function () {
    Route::group(["prefix" => "auth"], function () {
        Route::resource("hotels", "App\Http\Controllers\Admin\HotelController");
        Route::resource("amenities", "App\Http\Controllers\Admin\AmenityController");
        //Route::resource("payments", "App\Http\Controllers\Admin\PaymentController");
        //Route::resource("listbooks", "App\Http\Controllers\Admin\ListbookController");
        //Route::resource("bookings", "App\Http\Controllers\Admin\BookingController");
        Route::prefix('bookcalendar')->group(function () {
            Route::get('/books', [BookingCalendarController::class, 'index'])->name('bookcalendar.index');
            Route::get('/books/events', [BookingCalendarController::class, 'getEvents'])->name('bookcalendar.events');
            Route::post('/books/create', [BookingCalendarController::class, 'store'])->name('bookcalendar.create');
            Route::put('/books/update/{id}', [BookingCalendarController::class, 'update'])->name('bookcalendar.update');
            Route::delete('/books/delete/{id}', [BookingCalendarController::class, 'destroy'])->name('bookcalendar.delete');
        });

        Route::resource("prices", "App\Http\Controllers\Admin\PriceController");
        Route::resource("rooms", "App\Http\Controllers\Admin\RoomController");
        Route::resource("rates", "App\Http\Controllers\Admin\RateController");
        Route::resource("meals", "App\Http\Controllers\Admin\MealController");
        Route::resource("cancellations", "App\Http\Controllers\Admin\CancelRuleController");
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
        Route::get('/allbooks/excel', [AllBookingController::class, 'exportExcel'])->name('excel-books');

        Route::get('generate-pdf/{id}', [PDFController::class, 'generatePDF'])->name('pdf');
        Route::post('/books/store', [BookingController::class, 'store'])->name('listbooks.store');
        Route::get('/items/create', HotelWizard::class)->name('hotel.create');

        Route::get('/userbooks', [UserBookController::class, 'index'])->name('userbooks.index');
        Route::get('/userbooks/show/{book}', [UserBookController::class, 'showBook'])->name('userbooks.show');
        Route::post('/userbooks/cancel/{book}', [UserBookController::class, 'cancel_calculate'])->name('userbooks.cancel_calculate');
        Route::post('/userbooks/cancel_confirm', [UserBookController::class, 'cancel_confirm'])->name('userbooks.cancel_confirm');
        //exely
        Route::post('/userbooks/cancel_exely/{book}', [UserBookController::class, 'cancel_calculate_exely'])->name('userbooks.cancel_calculate_exely');
        Route::get('/userbooks/cancel_confirm', [UserBookController::class, 'cancel_confirm_exely'])->name('userbooks.cancel_confirm_exely');


    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    require __DIR__ . '/auth.php';

    Route::get('/', [PageController::class, 'index'])->name('index');

    Route::get('currency/switch/{currency}', function ($currency) {
        $allowed = ['USD','KGS','RUB'];
        $currency = strtoupper($currency);
        if (in_array($currency, $allowed, true)) {
            Session::put('currency', $currency);
        }
        return back();
    })->name('currency.switch');

    //-----search
    //local
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
    Route::get('/hotel/{hotel}', [\App\Http\Controllers\SearchController::class, 'hotel'])->name('hotel');
    //exely
    Route::get('/hotelex', [\App\Http\Controllers\SearchController::class, 'hotel_exely'])->name('hotel_exely');

    //-----booking
    //local
    Route::get('/book/order', [\App\Http\Controllers\BookingController::class, 'order'])->name('order');
    Route::get('/book/verify', [\App\Http\Controllers\BookingController::class, 'book_verify'])->name('book_verify');
    Route::get('/book/reserve', [\App\Http\Controllers\BookingController::class, 'book_reserve'])->name('book_reserve');
    Route::get('/book/cancel/calculate', [\App\Http\Controllers\BookingController::class, 'cancel_calculate'])->name('cancel_calculate');
    Route::get('/book/cancel/confirm', [\App\Http\Controllers\BookingController::class, 'cancel_confirm'])->name('cancel_confirm');

    //exely
    Route::get('/book/order/ex', [\App\Http\Controllers\BookingController::class, 'order_exely'])->name('order_exely');
    Route::get('/book/verify/ex', [\App\Http\Controllers\BookingController::class, 'book_verify_exely'])->name('book_verify_exely');
    Route::get('/book/reserve/ex', [\App\Http\Controllers\BookingController::class, 'book_reserve_exely'])->name('book_reserve_exely');
    Route::get('/book/cancel/calculate/ex', [\App\Http\Controllers\BookingController::class, 'cancel_calculate_exely'])->name('cancel_calculate_exely');
    Route::get('/book/cancel/confirm/ex', [\App\Http\Controllers\BookingController::class, 'cancel_confirm_exely'])->name('cancel_confirm_exely');

    Route::get('/hotels', [PageController::class, 'hotels'])->name('hotels');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/contactspage', [PageController::class, 'contactspage'])->name('contactspage');


    //TourMind
    Route::get('/hotel-results', HotelResults::class)->name('hotel.results');
    Route::get('/hotel-rooms', HotelRooms::class)->name('hotel.rooms');
    Route::get('/bookingform', BookingForm::class)->name('bookingform');
    //Route::get('/allhotels', [PageController::class, 'hotels'])->name('hotels');
    //Route::get('/order/{order}', [PageController::class, 'order'])->name('order');
    Route::get('/testsearch', [PageController::class, 'testsearch'])->name('testsearch');

    //email
    Route::post('contact_mail', [MainController::class, 'contact_mail'])->name('contact_mail');
    Route::post('book_mail', [MainController::class, 'book_mail'])->name('book_mail');
});

