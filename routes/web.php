<?php

use App\Http\Controllers\Admin\AllBillsController;
use App\Http\Controllers\Admin\AllBookingController;
use App\Http\Controllers\Admin\BookingCalendarController;
use App\Http\Controllers\Admin\BookingCalendarPriceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\ListbookController;
use App\Http\Controllers\Admin\OfflineController;
use App\Http\Controllers\Admin\PDFController;
use App\Http\Controllers\Admin\UserBookController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Livewire\BookingForm;
use App\Livewire\HotelResults;
use App\Livewire\HotelRooms;
use App\Livewire\HotelWizard;
use App\Livewire\LWTester;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
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
    Route::group(["prefix" => "auth", "middleware" => 'auth'], function () {

        Route::get('imports/exely/start', [ImportController::class, 'exelyStart'])
            ->name('exely.import.start');
        Route::get('/imports/exely/progress', [ImportController::class,'exelyProgress']);

        Route::resource("hotels", "App\Http\Controllers\Admin\HotelController");
        Route::resource("amenities", "App\Http\Controllers\Admin\AmenityController");
        Route::prefix('bookcalendar')->group(function () {
            Route::get('/books/events', [BookingCalendarController::class, 'getEvents'])->name('bookcalendar.events'); // СТАВИМ ВЫШЕ
            Route::get('/books', function () {
                $firstHotel = \App\Models\Hotel::orderBy('title')->first();
                return redirect()->route('bookcalendar.index', ['hotel' => $firstHotel->id ?? 14]);
            });
            Route::get('/books/{hotel?}', [BookingCalendarController::class, 'index'])->name('bookcalendar.index');
            Route::post('/books/create', [BookingCalendarController::class, 'store'])->name('bookcalendar.create');
        });


        Route::prefix('bookcalendarprice')->group(function () {
            Route::get('/books/events', [BookingCalendarPriceController::class, 'getEvents'])->name('bookcalendarprice.events');
            Route::get('/books', function () {
                $firstHotel = \App\Models\Hotel::orderBy('title')->first();
                return redirect()->route('bookcalendarprice.index', ['hotel' => $firstHotel->id ?? 14]);
            });
            Route::get('/books/{hotel?}', [BookingCalendarPriceController::class, 'index'])->name('bookcalendarprice.index');
            Route::post('/books/create', [BookingCalendarPriceController::class, 'store'])->name('bookcalendarprice.create');
        });

        //Route::resource("prices", "App\Http\Controllers\Admin\PriceController");
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
        Route::resource("details", "App\Http\Controllers\Admin\ContactController");

        Route::get("search", [HotelController::class, 'search']);
        Route::get("searchbook", [ListBookController::class, 'searchbook']);
        //Route::get("/book/exelyshow/{book}", [ListBookController::class, 'exelyshow'])->name('book.exelyshow');
        Route::get("/listbooks", [ListBookController::class, 'index'])->name('listbooks.index');
        Route::get("/listbooks/show/{book}", [ListBookController::class, 'show'])->name('listbooks.show');
        Route::post('/listbooks/cancel/{book}', [ListBookController::class, 'cancel_calculate'])->name('listbooks.cancel_calculate');
        Route::post('/listbooks/cancel_confirm', [ListBookController::class, 'cancel_confirm'])->name('listbooks.cancel_confirm');
        //exely
        Route::post('/listbooks/cancel_exely/{book}', [ListBookController::class, 'cancel_calculate_exely'])->name('listbooks.cancel_calculate_exely');
        Route::get('/listbooks/cancel_confirm', [ListBookController::class, 'cancel_confirm_exely'])->name('listbooks.cancel_confirm_exely');

        //finance role
        Route::get("/allbooks", [AllBookingController::class, 'index'])->name('allbooks.index');
        Route::get("/allbills", [AllBillsController::class, 'index'])->name('allbills.index');
        Route::get('/allbooks/excel', [AllBookingController::class, 'exportExcel'])->name('excel-books');

        Route::get('generate-pdf/{id}', [PDFController::class, 'generatePDF'])->name('pdf');
        //Route::post('/books/store', [BookingController::class, 'store'])->name('listbooks.store');
        Route::get('/items/create', HotelWizard::class)->name('hotel.create');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/console', [\App\Http\Controllers\Admin\PageController::class, 'console'])->name('console');

        Route::get('/offlines', [OfflineController::class, 'index'])->name('offlines.index');
        Route::get('/offlines/show/{offline}', [OfflineController::class, 'show'])->name('offlines.show');

        Route::get('/userbooks', [UserBookController::class, 'index'])->name('userbooks.index');
        Route::get('/userbooks/show/{book}', [UserBookController::class, 'showBook'])->name('userbooks.show');
        Route::post('/userbooks/cancel/{book}', [UserBookController::class, 'cancel_calculate'])->name('userbooks.cancel_calculate');
        Route::post('/userbooks/cancel_confirm', [UserBookController::class, 'cancel_confirm'])->name('userbooks.cancel_confirm');
        //exely
        Route::post('/userbooks/cancel_exely/{book}', [UserBookController::class, 'cancel_calculate_exely'])->name('userbooks.cancel_calculate_exely');
        Route::get('/userbooks/cancel_confirm', [UserBookController::class, 'cancel_confirm_exely'])->name('userbooks.cancel_confirm_exely');

        // tourmind
        Route::post('/userbooks/cancel_calculate_tm/{book}', [UserBookController::class, 'cancelCalculateBookingTM'])->name('userbooks.cancel_calculate_tm');
        Route::get('/userbooks/cancel_confirm_tm', [UserBookController::class, 'cancelBookingTM'])->name('userbooks.cancel_confirm_tm');

        // emerging
        Route::post('/userbooks/cancel_calculate_etg/{book}', [UserBookController::class, 'cancelCalculateBookingETG'])->name('userbooks.cancel_calculate_etg');
        Route::get('/userbooks/cancel_confirm_etg', [UserBookController::class, 'cancelBookingETG'])->name('userbooks.cancel_confirm_etg');

        // hotelstar
        Route::post('/userbooks/cancel_calculate_hs/{book}', [UserBookController::class, 'cancelCalculateBookingHS'])->name('userbooks.cancel_calculate_hs');
        Route::get('/userbooks/cancel_confirm_hs', [UserBookController::class, 'cancelBookingHS'])->name('userbooks.cancel_confirm_hs');

        Route::post('/users/store-user-hotel', [\App\Http\Controllers\Admin\UserController::class, 'storeHotel'])->name('users.createHotelUsers');
        Route::get('/list-users-hotel', [\App\Http\Controllers\Admin\UserController::class, 'listHotel'])->name('users.listHotel');
        Route::get('/edit-hotel/{user}/editHotelUser', [\App\Http\Controllers\Admin\UserController::class, 'editHotelUser'])->name('users.editHotelUser');
        Route::match(['put', 'patch'], 'auth/update-user-hotel/{user}', [\App\Http\Controllers\Admin\UserController::class, 'updateHotel'])->name('users.updateHotel');
        Route::get('/create-user-hotel', [\App\Http\Controllers\Admin\UserController::class, 'createView'])->name('users.createView');
        Route::post('/create-hotel-user', [\App\Http\Controllers\Admin\UserController::class, 'createHotel'])->name('users.createHotel');
        Route::delete('/delete-user-hotel/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroyHotel'])->name('users.destroyHotel');
    });

    require __DIR__ . '/auth.php';
    Route::middleware(['auth'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });


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
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    // routes/web.php
    Route::get('/search/suggest', [\App\Http\Controllers\SearchController::class, 'suggest'])
        ->name('search.suggest'); // вне middleware('auth')

    Route::get('/suggest', [\App\Http\Controllers\SearchController::class, 'suggest'])
        ->name('search.suggest'); // вне middleware('auth')

    //local
    Route::get('/search/hotel', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
    Route::get('/search/hotel/{hotel?}', [SearchController::class, 'search'])
        ->where('hotel', '.*')
        ->name('findHotel');

    //exely
    Route::get('/search/hotel/ex/{hotel}', [\App\Http\Controllers\SearchController::class, 'findHotelExely'])->name('findHotelExely');

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

    //pages
    Route::get('/hotels', [PageController::class, 'hotels'])->name('hotels');
    Route::get('/hotel/{hotel}', [PageController::class, 'hotel'])->name('hotel');
    Route::get('/contactspage', [PageController::class, 'contactspage'])->name('contactspage');
    Route::get('/companies', [PageController::class, 'companies'])->name('companies');
    Route::get('/apartments', [PageController::class, 'apartments'])->name('apartments');
    Route::get('/objects', [PageController::class, 'objects'])->name('objects');
    Route::get('/about', [PageController::class, 'about'])->name('about');
    Route::get('/service', [PageController::class, 'service'])->name('service');
    Route::get('/rules', [PageController::class, 'rules'])->name('rules');
    Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
    Route::get('/legal', [PageController::class, 'legal'])->name('legal');
    Route::get('/extranet', [PageController::class, 'extranet'])->name('extranet');
    Route::get('/offline', [PageController::class, 'offline_request'])->name('offline');
    Route::post('/offline_send', [PageController::class, 'offline_send'])->name('offline_send');
    Route::get('/exely_import', [PageController::class, 'exely_import'])->name('exely_import');

    //TourMind
    Route::get('/hotel-results', HotelResults::class)->name('hotel.results');
    Route::get('/hotel-rooms', HotelRooms::class)->name('hotel.rooms');
    Route::get('/bookingform', BookingForm::class)->name('bookingform');
    //Route::get('/allhotels', [PageController::class, 'hotels'])->name('hotels');

    Route::get('/hoteltm/{hid}', [\App\Http\Controllers\SearchController::class, 'hotel_tm'])->name('hotel_tm');
    Route::get('/book/order/tm', [\App\Http\Controllers\BookingTmController::class, 'order_tm'])->name('order_tm');
    Route::get('/book/verify/tm', [\App\Http\Controllers\BookingTmController::class, 'book_verify_tm'])->name('book_verify_tm');
    Route::get('/book/reserve/tm', [\App\Http\Controllers\BookingTmController::class, 'book_reserve_tm'])->name('book_reserve_tm');
    Route::get('/book/cancel/tm', [\App\Http\Controllers\BookingTmController::class, 'cancel_calculate_tm'])->name('cancel_calculate_tm');
    Route::get('/book/cancel/confirm/tm', [\App\Http\Controllers\BookingTmController::class, 'cancel_confirm_tm'])->name('cancel_confirm_tm');

    // Emerging
    Route::get('/hoteletg/{hid}', [\App\Http\Controllers\SearchController::class, 'hotel_etg'])
        ->whereNumber('hid')
        ->name('hotel_etg');
    Route::get('/book/order/etg', [\App\Http\Controllers\BookingEtgController::class, 'order_etg'])->name('order_etg');
    Route::get('/book/verify/etg', [\App\Http\Controllers\BookingEtgController::class, 'book_verify_etg'])->name('book_verify_etg');
    Route::get('/book/reserve/etg', [\App\Http\Controllers\BookingEtgController::class, 'book_reserve_etg'])->name('book_reserve_etg');
    Route::get('/book/cancel/etg', [\App\Http\Controllers\BookingEtgController::class, 'cancel_calculate_etg'])->name('cancel_calculate_etg');
    Route::get('/book/cancel/confirm/etg', [\App\Http\Controllers\BookingEtgController::class, 'cancel_confirm_etg'])->name('cancel_confirm_etg');

    // Hotelstar
    Route::get('/hotelehs/{hid}', [\App\Http\Controllers\SearchController::class, 'hotel_hs'])->name('hotel_hs');
    Route::get('/book/order/hs', [\App\Http\Controllers\BookingHsController::class, 'order_hs'])->name('order_hs');
    Route::get('/book/verify/hs', [\App\Http\Controllers\BookingHsController::class, 'book_verify_hs'])->name('book_verify_hs');
    Route::get('/book/reserve/hs', [\App\Http\Controllers\BookingHsController::class, 'book_reserve_hs'])->name('book_reserve_hs');
    Route::get('/book/cancel/hs', [\App\Http\Controllers\BookingHsController::class, 'cancel_calculate_hs'])->name('cancel_calculate_hs');
    Route::get('/book/cancel/confirm/hs', [\App\Http\Controllers\BookingHsController::class, 'cancel_confirm_hs'])->name('cancel_confirm_hs');

    //email
    Route::post('contact_mail', [MainController::class, 'contact_mail'])->name('contact_mail');
    Route::post('book_mail', [MainController::class, 'book_mail'])->name('book_mail');

    Route::get('/lwtester', [LWTester::class, 'render'])->name('livewire.lwtester');

    Route::get('/emerging/import', [\App\Http\Controllers\API\V1\Emerging\EmergingHotelStaticController::class, 'importFromJsonl']);
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    //Artisan::call('web:clear');
    return "Cache cleared successfully";
});

Route::get('/actualize-currency', function () {
    $cacheKey = 'fx_central_rates';
    return  Cache::get($cacheKey);
});


Route::get('/__health', fn() => response('OK', 200));

Route::get('/__db', function() {
    try {
        \DB::connection()->getPdo();
        return 'DB OK';
    } catch (\Throwable $e) {
        return 'DB FAIL: '.$e->getMessage();
    }
});