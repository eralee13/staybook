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
// use App\Mail\BookMail;
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
    public $coef;

    // Booking Tourmind Controller
    public function __construct()
    {
        // $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');
        $this->coef = config('app.main_coef');

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
            try {
                // $hotel = Hotel::find($request->hotel_id);
                $hotelService = new \App\Services\Tourmind\HotelServices();
                $order = $hotelService->createOrder($request);
                $message = ''; $key;
                // dd($order);
                if ( isset($order['Error']) == true) {

                    $message = $order['ErrorMessage'];
                    $key = '5';
                    
                }elseif( $order['Success'] == 'CONFIRMED'){

                    $message = 'Booking successfully created';
                    $key = '1';
                }
                elseif( $order['Success'] == 'Этот бронь уже существует!' ){

                    $message = 'This booking already exists';
                    $key = '2';

                }elseif( $order['Success'] == 'PENDING' ){

                    $orderStatus = '';
                    $attempt = 0;

                        do {
                            $orderStatus = $hotelService->getOneSearchOrder($book->agent_ref);

                            if ($orderStatus['Success'] === 'CONFIRMED') {
                                break;
                            }

                            sleep(1);
                            $attempt++;

                        } while ($attempt < 10);


                    if ( $orderStatus['Success'] == 'CONFIRMED' ){

                        Book::where('book_token', $this->token)
                        ->update([
                            'status' => ucfirst($orderStatus['OrderInfo']['OrderStatus']),
                            // 'rezervation_id' => $order['OrderInfo']['ReservationID']
                        ]);

                        $message = 'Booking successfully created';
                        $key = '1';
                        
                    }else{

                        Book::where('book_token', $this->token)
                        ->update([
                            'status' => ucfirst($order['OrderInfo']['OrderStatus']),
                            // 'rezervation_id' => $order['OrderInfo']['ReservationID']
                        ]);


                            if( $order['OrderInfo']['OrderStatus'] == 'PENDING' ){

                                $message = 'Booking is pending confirmation from the hotel';
                                $key = '3';
                                
                            }elseif( $order['OrderInfo']['OrderStatus'] == 'CANCELLED' ){

                                $message = 'Booking has been cancelled';
                                $key = '4';

                            }
                        
                    }

                }else {

                    $message = 'Error later or contact us';
                    $key = '5';
                }

            } catch (\Throwable $th) {
                $message = 'Error later or contact us!';
                Log::channel('tourmind')->info('Create Order Catch - ', $th->getMessage());
            }

            $book = Book::where('book_token', $request->token)->first();

            return view('pages.booking.tourmind.rezerve', compact('book', 'request', 'message', 'key'));
        
    }

    public function cancel_calculate_tm(Request $request)
    {
        $book = Book::where('book_token', $request->number)->first();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = Room::where('id', $book->room_id)->first();
        $rate = Rate::where('id', $book->rate_id)->first();
        $cancelRule = CancellationRule::where('id', $book->cancellation_id)->first();

        return view('pages.booking.tourmind.cancel', compact(
            'book', 'hotel', 'arrival', 'departure', 'room', 'rate', 'request', 'cancelRule'));
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
        
            } elseif( isset($cancel['CancelResult']['OrderStatus']) && $cancel['CancelResult']['OrderStatus'] == 'CANCELLED' && $book->status != 'Cancelled' ){

                // $cancelFee = $cancel['CancelResult']['CancelFee'];
                // $cancelFee = ($cancelFee * $this->coef) + $cancelFee;
                $curr = $cancel['CancelResult']['CurrencyCode'];
                $thisdate = Carbon::now()->format('Y-m-d H:i:s');

                Book::where('book_token', $request->number)->update([
                    'status' => 'Cancelled', 
                    'cancel_date' => $thisdate, 
                    // 'cancel_penalty' => $cancelFee, 
                    // 'currency' => $curr
                ]);

                $book = Book::where('book_token', $request->number)->first();

                if( $book->id ){
                    $userEmail = Auth::user()->email;
                    Mail::to($userEmail)->send(new BookCancelMail($book));
                }

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