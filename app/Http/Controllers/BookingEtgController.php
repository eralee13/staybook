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

class BookingEtgController extends Controller
{
    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');

        $this->middleware(function ($request, $next) {
            if (!auth()->check()) {
                return redirect('/');
            }

            return $next($request);
        });

    }

    public function order_etg(Request $request)
    {
        //dd($request->all());
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');

        return view('pages.booking.emerging.order', compact('request', 'arrival', 'departure'));
    }

    public function book_verify_etg(Request $request)
    {
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');
        

        $hotel = Hotel::find($request->hotel_id);
        $token = '';
        do {
            $token = Str::random(40);
        } while (Book::where('book_token', $token)->exists());
        
        return view('pages.booking.emerging.verify', compact('request', 'arrival', 'departure', 'hotel', 'token'));

    }

    public function book_reserve_etg(Request $request)
    {
       
            // $hotel = Hotel::find($request->hotel_id);
            $emergingOrder = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $order = $emergingOrder->startProcess($request);
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

            return view('pages.booking.emerging.rezerve', compact('book', 'request', 'message'));
        
    }

    public function cancel_calculate_etg(Request $request)
    {
        $book = Book::where('book_token', $request->number)->first();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = Room::where('id', $book->room_id)->first();
        $rate = Rate::where('id', $book->rate_id)->first();

        return view('pages.booking.emerging.cancel', compact(
            'book', 'hotel', 'arrival', 'departure', 'room', 'rate', 'request'));
    }

    public function cancel_confirm_etg(Request $request, Book $book)
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
                Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                Log::channel('emerging')->info('Cancel Order - ', $cancel);
        
            } elseif( isset($cancel['CancelResult']['OrderStatus']) && $cancel['CancelResult']['OrderStatus'] == 'CANCELLED'){

                $cancelFee = $cancel['CancelResult']['CancelFee'];
                $cancelFee = (($cancelFee * 8) / 100) + $cancelFee;
                $curr = $cancel['CancelResult']['CurrencyCode'];

                Book::where('book_token', $request->number)->update([
                    'status' => 'Cancelled', 
                    // 'cancel_penalty' => $cancelFee, 
                    'currency' => $curr
                ]);
                $book = Book::where('book_token', $request->number)->first();
                $status = 'Cancelled';
                
                    $rato = Rate::where('id', $book->rate_id)->get('cancellation_rule_id')->first(); 
                    if ( isset($rato->cancellation_rule_id) ){
                        // CancellationRule::where('id', $rate->cancellation_rule_id)->update(['penalty_amount' => $cancelFee]);
                    }
                

                        Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                        Log::channel('emerging')->info('Cancel Order - ', $cancel);

                $message = "Ваша бронь отменена";

            }else{
                $message = $cancel['Error'];
                Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                Log::channel('emerging')->info('Cancel Order - ', $cancel);
            }
            
            return view('pages.booking.emerging.confirm', compact(
                'book', 'hotel', 'cancel', 'cancelRule', 'arrival', 'departure', 'room', 'rate', 'request', 'message', 'status'));
    }
}