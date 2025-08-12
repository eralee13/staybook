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
use App\Models\Meal;


class BookingEtgController extends Controller
{
    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
        $this->coef = config('app.main_coef');

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

        $message = ''; $finish = ''; $finishStatus='';

        try {
            
            // $hotel = Hotel::find($request->hotel_id);
            $emergingPrebook = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $preBook = $emergingPrebook->preBook($request);
            Log::channel('emerging')->info('Create Order Prebook - ', $preBook);

            $emergingOrder = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $order = $emergingOrder->startProcess($request);
            Log::channel('emerging')->info('Create Order Process - ', $order);
            
            // dd($request);
            // dd($order);


            if( isset($order['status'])  == "ok" && isset($order['data']['item_id']) ){

                $item_id = $order['data']['item_id'];
                $order_id = $order['data']['order_id'];
                $etoken = $order['data']['partner_order_id'];

                foreach( $order['data']['payment_types'] as $paytype ){

                    // "amount" => "225"
                    // "currency_code" => "USD"
                    // "is_need_credit_card_data" => false
                    // "is_need_cvc" => false
                    // "recommended_price" => null
                    // "type" => "deposit" || now
                        $data = [
                            'amount' => $paytype['amount'],
                            'curr' => $paytype['currency_code'],
                            'type' => 'deposit', // $paytype['type'], // deposit, now
                            'item_id' => $item_id,
                            'order_id' => $order_id,
                            'etoken' => $etoken,
                        ];
                        
                        // Create booking on API
                    // if( $paytype['currency_code'] == 'USD'){
                        $emergingFinish = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
                        $finish = $emergingFinish->bookingFinish($request, $data);
                        Log::channel('emerging')->info('Create Order Finish - ', $finish);
                        // dd($finish);
                        

                        if( $finish['error'] == 'booking_form_expired' || $finish['error'] == 'rate_not_found' || $finish['error'] == 'return_path_required' ){

                            $message = 'Ошибка бронь не создана! Попробуйте заного еще раз!';

                                Book::where('book_token', $request->number)->update([
                                    'status' => 'Cancelled', 
                                ]);

                        }else{

                            $emergingStatus = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
                            $finishStatus = $emergingStatus->finishStatus($request);
                            Log::channel('emerging')->info('Create Order Status - ', $finishStatus);

                            $errors = ['block', 'charge', '3ds', 'soldout', 'provider', 'book_limit', 'not_allowed', 'booking_finish_did_not_succeed'];
                            
                            if ( in_array($finish['error'], $errors) ){

                                $message = 'Ошибка бронь не создана! Попробуйте заного еще раз!';
                                // $message = $finish['error'];

                            }else{

                                if( $order['status'] == 'ок' ){
                                    
                                    $message = 'Booking successfully created';
                                    Book::where('book_token', $request->number)->update([
                                        'status' => 'Confirmed', 
                                    ]);

                                }else{

                                    $maxAttempts = 10; // максимум попыток
                                    $attempt = 0;
                                    $response = null;
                                    
                                        do {
                                            $attempt++;

                                            // Запрос к API
                                            $response = $emergingStatus->finishStatus($request);

                                            // Если ошибка из списка — ждём и пробуем снова
                                            if ( $response['status'] != 'ок' ) {
                                                sleep(1); // пауза в секундах
                                            }

                                        } while ( $response['status'] != 'ок' && $attempt < $maxAttempts);

                                            if ( $response['status'] == 'ok' ) {

                                                $message = 'Booking successfully created';
                                                Book::where('book_token', $request->number)->update([
                                                    'status' => 'Confirmed', 
                                                ]);

                                            } else {
                                                $message = 'Booking is pending confirmation from the hotel';
                                            }
                                }
                            }   
                            
                        }
                    // }
                }
            }

            elseif ( isset($order['error']) ) {
                
                $message = "Ошибка бронь не создана! Попробуйте заного еще раз!";
            }

        } catch (\Throwable $th) {

            $message = $th->getMessage();
            Log::channel('emerging')->info('Create Order Catch - ', [$th->getMessage()]);
        }

            $book = Book::where('book_token', $request->token)->first();
            

        return view('pages.booking.emerging.rezerve', compact(
            'book', 'request', 'message', 
            'preBook', 'finish', 'order', 'finishStatus'));
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
            
            $emergingService = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $cancel = $emergingService->etg_cancel($request);
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
            
            
           if ( isset($cancel->status) == 'error' && $cancel->error == 'order_not_found' ){

                $message = "book_status_completed_rejected";

                    Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                    Log::channel('emerging')->info('Cancel Order - ', [$cancel]);
        
            } 
            elseif ( isset($cancel->status) == 'error' && $cancel->error == 'order_not_cancellable' ){

                $message = "user_not_book_cancellable";

                    Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                    Log::channel('emerging')->info('Cancel Order - ', [$cancel]);
        
            }
            
            if( isset($cancel->status) && $cancel->status == 'ok' ){
                // dd($cancel);
                $cancelFee = $cancel->data->amount_payable->amount;
                $cancelFee = ($cancelFee * $this->coef) + $cancelFee;
                $curr = $cancel->data->amount_payable->currency_code;

                Book::where('book_token', $request->number)->update([
                    'status' => 'Cancelled', 
                    'cancel_penalty' => $cancelFee, 
                    'currency' => $curr
                ]);

                $book = Book::where('book_token', $request->number)->first();
                $status = 'Cancelled';
                
                    $rato = Rate::where('id', $book->rate_id)->get('cancellation_rule_id')->first(); 
                    if ( isset($rato->cancellation_rule_id) ){
                        CancellationRule::where('id', $rato->cancellation_rule_id)->update(['penalty_amount' => $cancelFee]);
                    }
                

                        Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                        Log::channel('emerging')->info('Cancel Order - ', (array)$cancel);

                $message = "Ваша бронь отменена!";

            }else{
                // dd($cancel);
                $message = $cancel->error;
                Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                Log::channel('emerging')->info('Cancel Order - ', (array)$cancel);
            }
            
            return view('pages.booking.emerging.confirm', compact(
                'book', 'hotel', 'cancel', 'cancelRule', 'arrival', 'departure', 'room', 'rate', 'request', 'message', 'status'));
    }
}