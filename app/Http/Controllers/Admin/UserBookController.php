<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookCancelMail;
use App\Models\Book;
use App\Models\Hotel;
use App\Models\Rate;
use App\Models\Room;
use App\Models\CancellationRule;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserBookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:show-userbook|cancel-userbook', ['only' => ['show','cancel']]);
        $this->middleware('permission:show-userbook', ['only' => ['show']]);
        $this->middleware('permission:cancel-book', ['only' => ['cancel']]);
    }
    public function index(Request $request)
    {
        $user = Auth::id();
        $books = Book::where('user_id', $user)->where('sum', '!=', 0)->latest()->get();
        return view('auth.userbooks.index', compact('books'));
    }

    public function showBook($id)
    {
        $book = Book::where('id', $id)->firstOrFail();
        return view('auth.userbooks.show', compact('book'));
    }

    public function cancel_calculate(Request $request, Book $book)
    {
        return view('auth.userbooks.cancel-calculate', compact('book', 'request'));
    }

    public function cancel_confirm(Request $request, Book $book)
    {
        $user = Auth::id();
        $books = Book::where('user_id', $user)->where('status', 'Reserved')->get();
        Book::where('id', $book->id)->update(['status' => 'Cancelled']);
        Log::warning('Отмена брони: ' . $book->id);
        Mail::to('info@staybook.asia')->send(new BookCancelMail($book));
        session()->flash('success', 'Booking ' . $request->title . ' is cancelled');
        return redirect()->route('auth.userbooks.index', compact('books'));
    }

    //exely
    public function cancel_calculate_exely(Request $request, Book $book)
    {
        try {
            $cancel = Carbon::createFromDate(now())->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');

            $response = Http::timeout(30)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->get(config('services.exely.base_url') . 'reservation/v1/bookings/' . $book->book_token . '/calculate-cancellation-penalty?cancellationDateTimeUtc=' . $cancel);
            if ($response->successful()) {
                $calc = $response->object();
                return view('auth.userbooks.cancel-calculate-exely', compact('calc', 'request', 'book'));
            } else {
                Log::warning('Запрос завершился ошибкой: ' . $response->status());
                return view('errors.400', compact('response'));
            }
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

    public function cancel_confirm_exely(Request $request, Book $book)
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->post(config('services.exely.base_url') . 'reservation/v1/bookings/' . $request->number . '/cancel', [
                    "reason" => "Booking cancellation",
                    "expectedPenaltyAmount" => $request->amount
                ]);

            if ($response->successful()) {
                $cancel = $response->object();
                Book::where('id', $book->id)->update(['status' => 'Cancelled']);
                Log::warning('Отмена брони: ' . $book->id);
                Mail::to('info@staybook.asia')->send(new BookCancelMail($book));
                return view('auth.userbooks.cancel-confirm-exely', compact('cancel'));
            } else {
                Log::warning('Запрос завершился ошибкой: ' . $response->status());
                return view('errors.400', compact('response'));
            }

        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());

            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

    // tourmind
    public function cancelCalculateBookingTM(Request $request, Book $book){
        $book = Book::where('book_token', $book->book_token)->first();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = Room::where('id', $book->room_id)->first();
        $rate = Rate::where('id', $book->rate_id)->first();
        $cancelRule = CancellationRule::where('id', $book->cancellation_id)->first();

        return view('auth.userbooks.cancel-calculate-tm', compact(
            'book', 'hotel', 'arrival', 'departure', 'room', 'rate', 'request', 'cancelRule'));
    }

    public function cancelBookingTM(Request $request, Book $book)
    {
        $user = Auth::id();
        $message = '';

        
            $res = $this->cancelOrderTm($request, $book);
            
            if ( isset($res['Error']['ErrorMessage']) ){

                $message = $res['Error']['ErrorMessage'];
        
            }
             elseif( isset($res['CancelResult']['OrderStatus']) && $res['CancelResult']['OrderStatus'] == 'CANCELLED'){

                $cancelFee = $res['CancelResult']['CancelFee'];
                $curr = $res['CancelResult']['CurrencyCode'];

                Book::where('id', $book->id)->update(['status' => 'Cancelled']);
                $message = "is {$res['CancelResult']['OrderStatus']} CancelFee {$cancelFee} {$curr}";
            }else{
                $message = $res['Error'];
            }


        session()->flash('success', $message);
        $books = Book::where('user_id', $user)->orderBy('id', 'desc')->get();
        return redirect()->route('userbooks.index', compact('books'));
    }

    public function cancelOrderTm(Request $request, Book $book){

        // cancel order from tourmind
        $this->baseUrl = config('app.tm_base_url');
        // $userId = Auth::id();
        

       try {
        
            $agent = $book->agent_ref_id;
            $reservId = $book->revervation_id;
            $token = $book->book_token;

                $payload = [
                    "AgentRefID" => $agent,
                    "RequestHeader" => [
                        "AgentCode" => "tms_test",
                        "Password" => "tms_test",
                        "UserName" => "tms_test",
                        "TransactionID" => $token,
                        "RequestTime" => now()->format('Y-m-d H:i:s')
                    ]
                ];
            
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ])->post("{$this->baseUrl}/CancelOrder", $payload);
            
            // if ( $response->failed() ) {
            //     return ['error' => 'CancelOrder Ошибка при запросе к API', 'status' => $response->status()];
            // }

            $data = $response->json();

            return $data;
            

        } catch (\Throwable $th) {
                return ["Error" => "TM CancelOrder Ошибка при запросе к API: " . $th->getMessage()];
                // throw new \Exception("TM CancelOrder Ошибка при запросе к API: " . $th->getMessage(), 0, $th);
           }
        
    }

    // Emerging
    public function cancelCalculateBookingETG(Request $request, Book $book)
    {
        $book = Book::where('book_token', $book->book_token)->first();
        $hotel = Hotel::where('id', $book->hotel_id)->first();
        $arrival = Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($book->departureDate)->format('d.m.Y');
        $room = Room::where('id', $book->room_id)->first();
        $rate = Rate::where('id', $book->rate_id)->first();
        $cancelRule = CancellationRule::where('id', $book->cancellation_id)->first();

        return view('auth.userbooks.cancel_calculate_etg', compact(
            'book', 'hotel', 'arrival', 'departure', 'room', 'rate', 'request', 'cancelRule'));
    }
    
    /**
     * Cancel booking in ETG
     *
     * @param Request $request
     * @param Book $book
     * @return void
     */
    public function cancelBookingETG(Request $request, Book $book){

        $user = Auth::id();
        $cancel = '';

        try {

            $response = Http::timeout(30)
            ->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/cancel/', [

                "partner_order_id" => $book->book_token
                
            ]);

            $cancel = json_decode( $response->body() );


                if ($cancel['status'] == 'ok') {

                    $book->update(['status' => 'Cancelled']);
                    Mail::to('info@staybook.asia')->send(new BookCancelMail($book));
                    session()->flash('success', __('main.booking_cancelled').'era');
                    
                } else {

                    Log::channel('emerging')->error('Cancel Order - ', [$th->getMessage()]);
                    session()->flash('error', __('main.cancel_failed').'era2');
                }

            // $books = Book::where('user_id', $user)->orderBy('id', 'desc')->get();
            // return redirect()->route('userbooks.index', compact('books'));

        } catch (\Throwable $th) {
            
            Log::channel('emerging')->error('UserBook Cancel Order - ', [$th->getMessage()]);
            session()->flash('error', __('main.cancel_failed').'era3'); 

            // $books = Book::where('user_id', $user)->orderBy('id', 'desc')->get();
            // return redirect()->route('userbooks.index', compact('books'));
        }

        return view('auth.userbooks.cancel-confirm-etg', compact('cancel'));
        

    }

}

