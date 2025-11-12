<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
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

// Расшифровка статусов:
    // $statuses = [
    //     1   => 'Новый',
    //     2   => 'Оформлен',
    //     3   => 'Отменен',
    //     4   => 'Отклонен',
    //     10  => 'Ожидает подтверждения бронирования',
    //     20  => 'Ожидает подтверждения отмены',
    //     30  => 'Ожидает подтверждения изменений',
    //     500 => 'Ошибка бронирования',
    // ];


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
        $message = ''; $actualize = ''; $throwMessage = ''; 
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

        } catch (ConnectionException $e) {

            $message = 'connection_error';
            Log::channel('hotelstar')->info('Create Order Catch - ', [$e->getMessage()]);

        } catch (RequestException $e) {

            $message = 'connection_error';
            Log::channel('hotelstar')->info('Create Order Catch - ', [$e->getMessage()]);

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

                $search = new \App\Http\Controllers\API\V1\Hotelstar\HotelstarFormController();
                $order = $search->metaOrder($request);

                    if( isset($order->code) && ($order->code == 40000)){
                        $message = 'invalid_reguest';
                    }

                        if( isset($order->code) && ($order->code == 40001)){
                            $message = 'search_is_expired';
                        }

                            if( isset($order->code) && ($order->code == 40400)){
                                $message = 'resource_not_wanted';
                            }

                                if( isset($order->code) && ($order->code == 40005)){
                                    $message = 'This booking already exists';
                                }

                        if( isset($order->code) && ($order->code == 50000)){
                            $message = 'Please try again in a few minutes and refresh the page!';
                        }

                    if( isset($order->code) && ($order->code == 50001)){
                        $message = 'Booking error, manual check is required, please contact your account manager!';
                    }


                if( isset($order->status) ){
                    if( $order->status == 1 || $order->status == 2 ){
                        $message = 'Booking successfully created';
                        Book::where('book_token', $request->token)->update(['status' => 'Reserved']);

                    }elseif( $order->status == 3 || $order->status == 4){

                        $message = 'Booking cancelled';
                        Book::where('book_token', $request->token)->update(['status' => 'Cancelled']);

                    }elseif( $order->status == 10 ){

                        $message = 'Booking is pending confirmation from the hotel';

                        } elseif( $order->status == 20 ){

                            $message = 'Booking cancellation is pending confirmation';

                            } elseif( $order->status == 30 ){

                                $message = 'Booking modification is pending confirmation';

                    } elseif( $order->status == 500 ){

                        $message = 'Booking error';
                    
                    }
                }
                
            } catch (ConnectionException $e) {

                $message = 'connection_error';
                Log::channel('hotelstar')->info('Create Order Catch - ', [$e->getMessage()]);

            } catch (RequestException $e) {

                $message = 'connection_error';
                Log::channel('hotelstar')->info('Create Order Catch - ', [$e->getMessage()]);

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
    
    public function cancel_calculate_hs(Request $request)
    {
        $book = Book::where('book_token', $request->number)->first();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = ''; //Room::where('id', $book->room_id)->first();
        $rate = ''; //Rate::where('id', $book->rate_id)->first();
        $cancelRule = CancellationRule::where('id', $book->cancellation_id)->first();
        $cancelDate = Carbon::createFromDate($cancelRule->end_date)->format('d.m.Y H:i:s');

        return view('pages.booking.hotelstar.cancel', compact(
            'book', 'hotel', 'arrival', 'departure', 'room', 'rate', 'request', 'cancelRule', 'cancelDate'));
    }

    public function cancel_confirm_hs(Request $request, Book $book)
    {
        $book = Book::where('book_token', $request->number)->first();
        $api_type = $book->api_type ?? '';
        $userId = $book->user_id ?? Auth::id();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = Room::where('id', $book->room_id)->first();
        $rate = Rate::where('id', $book->rate_id)->first();
        $cancel = null; $cancelRule = null; $status = '';

        $cancelRule = CancellationRule::where('id', $book->cancellation_id)->first();
        $cancelDate = Carbon::createFromDate($cancelRule->end_date)->format('d.m.Y H:i:s');
        $book = Book::where('book_token', $request->number)->first();

        try {

            $cancelFee = 0;
            $curr = '';
            $message = '';
            $status = '';

                $statuses = [
                    1   => 'new',
                    2   => 'confirmed',
                    3   => 'cancelled',
                    4   => 'rejected',
                    10  => 'pending_booking_confirmation',
                    20  => 'pending_cancellation_confirmation',
                    30  => 'pending_modification_confirmation',
                    500 => 'booking_error',
                ];

                    // info for log
                    $userInfo = [
                        'user_id' => $userId,
                        'book_id' => $book->id,
                    ];
            
            $search = new \App\Http\Controllers\API\V1\Hotelstar\HotelstarFormController();
            $cancel = $search->metaCancelOrder($request, $rate, $book);
            
            if( isset($cancel->success) == true ){

                $cancelFee = ($cancel->penalty / $this->coef) ?? 0;
                Book::where('book_token', $request->number)->update([
                    'status' => 'Cancelled', 
                    // 'cancel_penalty' => $cancelFee, 
                    // 'currency' => $cancel->currency ?? $book->currency,
                ]);

                $book = Book::where('book_token', $request->number)->first();
                $status = $statuses[$cancel->status] ?? 'cancelled';
                
                if( $book->id ){
                    $userEmail = Auth::user()->email;
                    Mail::to($userEmail)->send(new BookCancelMail($book));
                }

                    $rato = Rate::where('id', $book->rate_id)->get('cancellation_rule_id')->first(); 
                    if ( isset($rato->cancellation_rule_id) ){
                        CancellationRule::where('id', $rato->cancellation_rule_id)->update(['penalty_amount' => $cancelFee]);
                    }
                

                        Log::channel('hotelstar')->info('Cancel Order User ID - ', $userInfo);
                        Log::channel('hotelstar')->info('Cancel Order - ', (array)$cancel);

                $message = "booking_cancelled";

            }
            elseif( isset($cancel->code) ){

                $code = $cancel->code ?? null;

                $message = match ($code) {
                    50000 => 'try_check_status_later',
                    50001 => 'manual_check_required',
                    50002 => 'supplier_data_not_updated',
                    40000 => 'invalid_request',
                    40001 => 'search_expired',
                    40002 => 'offer_unavailable',
                    40003 => 'no_quota_left',
                    40004 => 'insufficient_deposit',
                    40005 => 'duplicate_booking_code',
                    40006 => 'duplicate_booking',
                    40400 => 'not_found',
                    40500 => 'method_not_allowed',
                    default => 'unknown_error',
                };

                Log::channel('hotelstar')->info('Cancel Order User ID - ', $userInfo);
                Log::channel('hotelstar')->info("Cancel Order {$message} - ", (array)$cancel);

            }else{
                // $message = "book_status_completed_rejected";
                $message = "user_not_book_cancellable";

                Log::channel('hotelstar')->info('Cancel Order User ID - ', $userInfo);
                Log::channel('hotelstar')->info('Cancel Order - ', (array)$cancel);
            }

        } catch (ConnectionException $e) {

            $message = 'connection_error';
            Log::channel('hotelstar')->info('Create Order Catch - ', [$e->getMessage()]);

        } catch (RequestException $e) {

            $message = 'connection_error';
            Log::channel('hotelstar')->info('Create Order Catch - ', [$e->getMessage()]);

        } catch (\Throwable $th) {
            //throw $th;
            $message = 'booking_cancel_error';
            Log::channel('hotelstar')->error('Cancel Order Catch - ', [$th->getMessage()]);
        }

        // $book = Book::where('book_token', $request->number)->first();
        
        return view('pages.booking.hotelstar.confirm', compact(
            'book', 'hotel', 'cancel', 'cancelRule', 'arrival', 'departure', 'room', 'rate', 'request', 'message', 'status', 'cancelDate'));
    }
}
