<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Mail;
use DateTimeZone;
use DateTime;
use App\Mail\BookCancelMail;
use App\Mail\BookMail;
use App\Models\Book;
use App\Models\City;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Image;
use App\Models\CancellationRule;
use App\Models\Meal;


class BookingHsController extends Controller
{
    public function __construct()
    {
        $this->apiKey = config('app.hs_api_key');
        $this->url = config('app.hs_url');
        $this->coef = config('app.main_coef');

        $this->middleware(function ($request, $next) {
            if (!auth()->check()) {
                return redirect('/');
            }

            return $next($request);
        });
    }

    public function order_hs(Request $request)
    {
        //dd($request->all());
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');
        $message = ''; $actialize = ''; $throwMessage = ''; 
        $earlyCheckIn = []; $lateCheckOut = [];

        try {

            $search = new \App\Http\Controllers\API\V1\Hotelstar\HotelstarFormController();
            $actualize = $search->searchActualize($request);

                // время заезда и выезда доп услуги подготовка
                if ( isset($actualize['search_item']['extras']) ) {
                    
                    foreach ($actualize['search_item']['extras'] as $extra) {
                        if ($extra['code'] === 'early_check_in') {
                            $earlyCheckIn[] = $extra;
                        } elseif ($extra['code'] === 'late_check_out') {
                            $lateCheckOut[] = $extra;
                        }
                    }
                }

        } catch (\Throwable $th) {
            $throwMessage = $th->getMessage();
            Log::channel('hotelstar')->info('Actualize Catch - ', [$throwMessage]);
        }

        return view('pages.booking.hotelstar.order', 
                compact('request', 'arrival', 'departure', 'actualize', 
                    'message', 'throwMessage', 'earlyCheckIn', 'lateCheckOut'));
    }

    public function book_verify_hs(Request $request)
    {
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');
        

        $hotel = Hotel::find($request->hotel_id);
        $token = '';
        do {
            $token = Str::random(40);
        } while (Book::where('book_token', $token)->exists());
        
        return view('pages.booking.hotelstar.verify', compact('request', 'arrival', 'departure', 'hotel', 'token'));

    }

    public function book_reserve_hs(Request $request)
    {

        $message = ''; $finish = ''; $book; $order='';

        $book = Book::where('book_token', $request->token)->first();

        if ( isset($book->id) ) {
            $message = 'This booking already exists';
        }else{
        
            try {
                // Расшифровка статусов:
                //     1 - Новый
                //     2 - Оформлен
                //     3 - Отменен
                //     4 - Отклонен
                //     10 - Ожидает подтверждения бронирования*
                //     20 - Ожидает подтверждения отмены*
                //     30 - Ожидает подтверждения изменений*
                //     500 - Ошибка бронирования

                $search = new \App\Http\Controllers\API\V1\Hotelstar\HotelstarFormController();
                $order = $search->metaOrder($request);

                if( isset($order->status) && $order->status == 1 || $order->status == 2 ){

                    $message = 'Booking successfully created';

                    }elseif( isset($order->status) && $order->status == 3 || $order->status == 4){

                        $message = 'Booking cancelled';
                        Book::where('book_token', $request->token)->update(['status' => 'Cancelled']);

                        }elseif( isset($order->status) && $order->status == 10 ){

                            $message = 'Booking is pending confirmation from the hotel';

                            } elseif( isset($order->status) && $order->status == 20 ){

                                $message = 'Booking cancellation is pending confirmation';

                                } elseif( isset($order->status) && $order->status == 30 ){

                                    $message = 'Booking modification is pending confirmation';

                                    } elseif( isset($order->status) && $order->status == 500 ){

                                        $message = 'Booking error';
                    
                }else {

                    $message = 'Booking error';

                }
                
            } catch (\Throwable $th) {
                $message = $th->getMessage();
                Log::channel('hotelstar')->info('Create Order Catch - ', [$message]);
            }

            $book = Book::where('book_token', $request->token)->first();
            
        }

        return view('pages.booking.hotelstar.rezerve', compact(
            'book', 'request', 'message', 
                'finish', 'order'));
    }
    
}
