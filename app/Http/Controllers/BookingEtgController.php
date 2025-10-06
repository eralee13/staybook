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
        $message = ''; $preBook = ''; $throwMessage = '';

        try {
            // $hotel = Hotel::find($request->hotel_id);
            $emergingPrebook = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $preBook = $emergingPrebook->preBook($request);
            Log::channel('emerging')->info('Create Order Prebook - ', $preBook);

                if( $preBook['status'] == 'ok' ){

                    if( $preBook['data']['changes']['price_changed'] == true ){
                        $message = 'price_changed_text';
                    }
                } else{
                    $message = $preBook['error'];
                } //preBook else

        } catch (\Throwable $th) {
            //throw $th;
            $throwMessage = 'prebook_error';
            Log::channel('emerging')->info('preBook Search Error- ', [$th->getMessage()]);
        }

        return view('pages.booking.emerging.order', 
                compact('request', 'arrival', 'departure', 'preBook', 'message', 'throwMessage'));
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

        $message = ''; $finish = ''; $finishStatus=''; $book; $order=''; $preBook = '';

        $book = Book::where('book_token', $request->token)->first();

        if ( isset($book->id) ) {
            $message = 'This booking already exists';
        }else{
         
            try {
                $emergingForm = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
                $order = $emergingForm->startProcess($request);
                Log::channel('emerging')->info('Create Order Process - ', $order);

                if (isset($order['status']) && $order['status'] == "ok" && isset($order['data']['item_id'])) {
                    $item_id = $order['data']['item_id'];
                    $order_id = $order['data']['order_id'];
                    $etoken = $order['data']['partner_order_id'];

                    foreach ($order['data']['payment_types'] as $paytype) {

                        if ($paytype['currency_code'] == 'USD') {
                            $data = [
                                'amount'   => $paytype['amount'],
                                'curr'     => $paytype['currency_code'],
                                'type'     => 'deposit',
                                'item_id'  => $item_id,
                                'order_id' => $order_id,
                                'etoken'   => $etoken,
                            ];

                            // $emergingForm = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();

                            $finish = $this->callWithRetryFinish(
                                fn() => $emergingForm->bookingFinish($request, $data),
                                61,
                                [
                                    'retryable' => ['timeout', 'unknown', '5xx'],
                                    'fatal'     => ['insufficient_b2b_balance', 'booking_form_expired', 'rate_not_found', 'return_path_required'],
                                ]
                            );
                            Log::channel('emerging')->info('Order Finish - ', $finish);

                            if ( isset($finish['error']) && $finish['error'] == 'insufficient_b2b_balance' || $finish['error'] == 'booking_form_expired' || $finish['error'] == 'rate_not_found' || $finish['error'] == 'return_path_required' ) {
                                $message = 'insufficient_b2b_balance';
                                Book::where('book_token', $request->token)->update(['status' => 'Cancelled']);
                            }

                            if ( (isset($finish['status']) && $finish['status'] == 'ok')
                                || (isset($finish['error']) && $finish['error'] == 'double_booking_finish') ) {
                                
                                    // finishStatus с ретраями
                                // $emergingStatus = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
                                $finishStatus = $this->callWithRetryStatus(
                                    fn() => $emergingForm->finishStatus($request),
                                    61,
                                    [
                                        'retryable' => ['timeout', 'unknown', '5xx'],
                                        'fatal' => ['block', 'charge', '3ds', 'soldout', 'provider', 'book_limit', 'not_allowed', 'booking_finish_did_not_succeed']
                                    ]
                                );
                                Log::channel('emerging')->info('Order Status - ', $finishStatus);

                                if (isset($finishStatus['status']) && $finishStatus['status'] == 'ok') {
                                    $message = 'Booking successfully created';

                                    $book = Book::where('book_token', $request->token)->first();
                                    if ($book) {
                                        $book->status = 'Confirmed';
                                        $book->save();
                                    }

                                    $userEmail = Auth::user()->email;
                                    Mail::to($userEmail)->send(new BookMail($book));
                                } else {
                                    $message = 'Booking is pending confirmation from the hotel';
                                }
                            }

                            break;
                        }
                    }
                } elseif (isset($order['error'])) {
                    $message = $order['error'];
                    
                }

            } catch (\Throwable $th) {
                $message = $th->getMessage();
                Log::channel('emerging')->info('Create Order Catch - ', [$message]);
            }

            $book = Book::where('book_token', $request->token)->first();
            
        }

        return view('pages.booking.emerging.rezerve', compact(
            'book', 'request', 'message', 
            'preBook', 'finish', 'order', 'finishStatus'));
    }

    private function callWithRetryFinish(callable $callback, int $timeoutSeconds, array $errors = [])
    {
        $start = time();

        do {
            try {
                $response = $callback();

                // Успех
                if (isset($response['status']) && $response['status'] == 'ok') {
                    return $response;
                }

                // Уже была попытка завершить — повторно не надо
                if (isset($response['error']) && $response['error'] == 'double_booking_finish') {
                    Log::channel('emerging')->error('Double booking finish: ', $response);
                    return $response;
                }

                if (isset($response['error']) && $response['error'] == 'insufficient_b2b_balance') {
                    Log::channel('emerging')->error('insufficient_b2b_balance Finish: ', $response);
                    return $response;
                }

                // Фатальные ошибки → сразу выходим
                if (isset($response['error']) && in_array($response['error'], $errors['fatal'] ?? [])) {
                    Log::channel('emerging')->warning('Fatal error: ', $response);
                    return $response;
                }

                // Временные ошибки → повторяем
                if (isset($response['error']) && in_array($response['error'], $errors['retryable'] ?? [])) {
                    Log::channel('emerging')->error('Retryable error, retrying Finish: ' . $response['error']);
                } else {
                    // Любой неожиданный ответ → стоп
                    Log::channel('emerging')->error('Unexpected response: ', $response);
                    throw new \Exception('Unexpected response: ' . json_encode($response));
                }

            } catch (\Throwable $e) {
                Log::channel('emerging')->warning('Retry after failure: ' . $e->getMessage());
            }

            sleep(2);
        } while (time() - $start < $timeoutSeconds);

        throw new \Exception('Timeout waiting for valid response');
    }

    private function callWithRetryStatus(callable $callback, int $timeoutSeconds, array $errors = [])
    {
        $start = time();

        do {
            try {
                $response = $callback();
                // dd($response);
                // Успех
                if (isset($response['status']) && $response['status'] == 'ok') {
                    // Log::channel('emerging')->warning('Status OK: ', $response);
                    return $response;
                }
                
                // Промежуточный статус → ждём
                if (isset($response['status']) && $response['status'] == 'processing') {
                    Log::channel('emerging')->info('Status processing, retry ', $response);
                    sleep(1);
                    continue; // не ошибка, просто повтор
                }

                // Фатальные ошибки → выходим сразу
                if (isset($response['error']) && in_array($response['error'], $errors['fatal'] ?? [])) {
                    Log::channel('emerging')->warning('Fatal error ', $response);
                    throw new \Exception('Fatal error: ' . $response['error']);
                }

                // Временные ошибки → повторяем
                if (isset($response['error']) && in_array($response['error'], $errors['retryable'] ?? [])) {
                    Log::channel('emerging')->info('Retryable error, retrying Status: ' . $response['error']);
                } else {
                    // Любое неожиданное → стоп
                    Log::channel('emerging')->warning('Unexpected response: ', $response);
                    throw new \Exception('Unexpected response: ' . json_encode($response));
                }

            } catch (\Throwable $e) {
                Log::channel('emerging')->warning('Retry after failure: ' . $e->getMessage());
            }

            sleep(2);
        } while (time() - $start < $timeoutSeconds);

        throw new \Exception('Timeout waiting for valid response');
    }

    public function cancel_calculate_etg(Request $request)
    {
        $book = Book::where('book_token', $request->number)->first();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = Room::where('id', $book->room_id)->first();
        $rate = Rate::where('id', $book->rate_id)->first();
        $cancelRule = CancellationRule::where('id', $book->cancellation_id)->first();
        $cancelDate = Carbon::createFromDate($cancelRule->end_date)->format('d.m.Y H:i:s');

        return view('pages.booking.emerging.cancel', compact(
            'book', 'hotel', 'arrival', 'departure', 'room', 'rate', 'request', 'cancelRule', 'cancelDate'));
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
        $cancel = null; $cancelRule = null; $status = '';

        $cancelRule = CancellationRule::where('id', $book->cancellation_id)->first();
        $cancelDate = Carbon::createFromDate($cancelRule->end_date)->format('d.m.Y H:i:s');
        $book = Book::where('book_token', $request->number)->first();

        try {
            $emergingService = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $cancel = $emergingService->etg_cancel($request);
            
            
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
                
                if( $book->id ){
                    $userEmail = Auth::user()->email;
                    Mail::to($userEmail)->send(new BookCancelMail($book));
                }

                    $rato = Rate::where('id', $book->rate_id)->get('cancellation_rule_id')->first(); 
                    if ( isset($rato->cancellation_rule_id) ){
                        CancellationRule::where('id', $rato->cancellation_rule_id)->update(['penalty_amount' => $cancelFee]);
                    }
                

                        Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                        Log::channel('emerging')->info('Cancel Order - ', (array)$cancel);

                $message = "booking_cancelled";

            }else{
                // dd($cancel);
                // $message = $cancel->error;
                Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                Log::channel('emerging')->info('Cancel Order - ', (array)$cancel);
            }

        } catch (\Throwable $th) {
            //throw $th;
            $message = 'booking_cancel_error';
            Log::channel('emerging')->error('Cancel Order Catch - ', [$th->getMessage()]);
        }

        // $book = Book::where('book_token', $request->number)->first();
        
        return view('pages.booking.emerging.confirm', compact(
            'book', 'hotel', 'cancel', 'cancelRule', 'arrival', 'departure', 'room', 'rate', 'request', 'message', 'status', 'cancelDate'));
    }
}