<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\log;
use DateTimeZone;
use DateTime;
use App\Models\Book;
use App\Models\City;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Image;
use App\Models\CancellationRule;

class BookingTmController extends Controller
{
    // Booking Tourmind Controller
    public function __construct()
    {
        // $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');

        $this->middleware(function ($request, $next) {
            if (!auth()->check()) {
                return redirect('/');
            }

            return $next($request);
        });

    }

    public function order_tm(Request $request)
    {
        //dd($request->all());
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');

        return view('pages.booking.tourmind.order', compact('request', 'arrival', 'departure'));
    }

    public function book_verify_tm(Request $request)
    {
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');
        

        $hotel = Hotel::find($request->hotel_id);
        $token = '';
        do {
            $token = Str::random(40);
        } while (Book::where('book_token', $token)->exists());
        
        return view('pages.booking.tourmind.verify', compact('request', 'arrival', 'departure', 'hotel', 'token'));

    }

    public function book_reserve_tm(Request $request)
    {
       
            // $hotel = Hotel::find($request->hotel_id);
            $hotelService = new \App\Services\Tourmind\HotelServices();
            $order = $hotelService->createOrder($request);
            $message = '';
            // dd($order);
            if ( isset($order['Error']) == true) {

                // session()->flash('Success', 'Бронирование успешно создано!');
                $message = $order['ErrorMessage'];
                
            }elseif( $order['Success'] == 'Этот бронь уже существует!' ){

                // session()->flash('Error', $book);
                $message = 'Этот бронь уже существует!';
                $book = Book::where('book_token', $request->token)->first();

            }elseif( $order['Success'] == 'CONFIRMED' || $order['Success'] == 'PENDING' ){

                // session()->flash('Error', $book);
                $message = 'Бронирование успешно создано!';
                $book = Book::where('book_token', $request->token)->first();

            }else {
                
                // session()->flash('Error', 'Ошибка при создании бронирования!');
                $message = 'Ошибка при создании бронирования! Обратитесь в службу поддержки.';
            }

            return view('pages.booking.tourmind.rezerve', compact('book', 'request', 'message'));
        
    }

    public function cancel_calculate_tm(Request $request)
    {
        $book = Book::where('book_token', $request->number)->first();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = Room::where('id', $book->room_id)->first();
        $rate = Rate::where('id', $book->rate_id)->first();

        return view('pages.booking.tourmind.cancel', compact(
            'book', 'hotel', 'arrival', 'departure', 'room', 'rate', 'request'));
    }

    public function cancel_confirm_tm(Request $request, Book $book)
    {
        $book = Book::where('book_token', $request->number)->first();
        $api_type = $book->api_type ?? '';
        $userId = $book->user_id ?? Auth::id();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = Room::where('id', $book->room_id)->first();
        $rate = Rate::where('id', $book->rate_id)->first();
            
            $hotelService = new \App\Services\Tourmind\HotelServices();
            $cancel = $hotelService->cancelOrder($request, $book);
            $cancelRule = CancellationRule::where('id', $book->cancellation_id)->first();
            $book = Book::where('book_token', $request->number)->first();
            
                $cancelFee = 0;
                $curr = '';
                $message = '';
                $status = '';

                    // info for log
                    $userInfo = [
                        'user_id' => $userId,
                        'book_id' => $book->id,
                    ];
            
            
           if ( isset($cancel['Error']['ErrorMessage']) ){

                $message = $cancel['Error']['ErrorMessage'];
                Log::channel('tourmind')->info('Cancel Order User ID - ', $userInfo);
                Log::channel('tourmind')->info('Cancel Order - ', $cancel);
        
            } elseif( isset($cancel['CancelResult']['OrderStatus']) && $cancel['CancelResult']['OrderStatus'] == 'CANCELLED'){

                $cancelFee = $cancel['CancelResult']['CancelFee'];
                $cancelFee = (($cancelFee * 8) / 100) + $cancelFee;
                $curr = $cancel['CancelResult']['CurrencyCode'];
                $thisdate = Carbon::now()->format('Y-m-d H:i:s');

                Book::where('book_token', $request->number)->update([
                    'status' => 'Cancelled', 
                    'cancel_date' => $thisdate, 
                    // 'cancel_penalty' => $cancelFee, 
                    'currency' => $curr
                ]);
                $book = Book::where('book_token', $request->number)->first();
                $status = 'Cancelled';
                
                    $rato = Rate::where('id', $book->rate_id)->get('cancellation_rule_id')->first(); 
                    if ( isset($rato->cancellation_rule_id) ){
                        // CancellationRule::where('id', $rate->cancellation_rule_id)->update(['penalty_amount' => $cancelFee]);
                    }
                

                        Log::channel('tourmind')->info('Cancel Order User ID - ', $userInfo);
                        Log::channel('tourmind')->info('Cancel Order - ', $cancel);

                $message = "Ваша бронь отменена";

            }else{
                $message = $cancel['Error'];
                Log::channel('tourmind')->info('Cancel Order User ID - ', $userInfo);
                Log::channel('tourmind')->info('Cancel Order - ', $cancel);
            }
            
            return view('pages.booking.tourmind.confirm', compact(
                'book', 'hotel', 'cancel', 'cancelRule', 'arrival', 'departure', 'room', 'rate', 'request', 'message', 'status'));
    }
}