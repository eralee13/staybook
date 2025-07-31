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
            // $hotel = Hotel::find($request->hotel_id);
            $emergingPrebook = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $preBook = $emergingPrebook->preBook($request);
            
            $emergingOrder = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
            $order = $emergingOrder->startProcess($request);
            
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
                            'type' => $paytype['type'],
                            'item_id' => $item_id,
                            'order_id' => $order_id,
                            'etoken' => $etoken,
                        ];

                    if( $paytype['currency_code'] == 'USD'){
                        $emergingFinish = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
                        $finish = $emergingFinish->bookingFinish($request, $data);
                        // dd($finish);

                        if($finish['status'] == 'ok'){
                            $message = 'Бронирование успешно создано!';
                        }

                        if( isset( $finish['error'] ) ){

                            $emergingStatus = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
                            $finishStatus = $emergingStatus->finishStatus($request);

                            switch ($finish['error']) {
                                case 'book_hash_not_found':
                                    $message = "Ошибка тарифа, выберите другой тариф!";
                                    break;

                                case 'booking_form_expired':
                                    $message = "Создайте бронь заново!";
                                    break;

                                case 'chosen_payment_type_was_not_available_on_booking_form':
                                    $message = "Тип платежа не указан!";
                                    break;

                                case 'double_booking_finish':
                                    $message = "Попытка завершить бронирование во второй раз, при этом статус первой попытки не является ошибкой.";
                                    break;

                                case 'email':
                                    $message = "Указанный адрес электронной почты недействителен.";
                                    break;

                                case 'incorrect_chosen_payment_type':
                                    $message = "Неверное значение поля type";
                                    break;

                                case 'incorrect_guests_number':
                                    $message = "Номер взрослого гостя не совпадает с номером взрослого гостя в запросе вызова";
                                    break;

                                case 'incorrect_children_data':
                                    $message = "Номер гостя-ребенка не совпадает с номером гостя-ребенка или Возраст детей указан неверно";
                                    break;
                                    
                                case 'incorrect_rooms_number':
                                    $message = "Номер комнаты не совпадает с номером комнаты в запросе";
                                    break;

                                case 'insufficient_b2b_balance':
                                    $message = "Кредитный лимит достигнут. Обратитесь к своему менеджеру по работе с клиентами.";
                                    break;

                                case 'order_not_found':
                                    $message = "Заказ не найден";
                                    break;

                                case 'rate_not_found':
                                    $message = "Тариф не найден";
                                    break;

                                case 'return_path_required':
                                    $message = "Поле return_pathобязательно для заполнения, если тариф, который вы бронируете, содержит payment_typesполе со nowзначением";
                                    break;

                                case 'unauthorized_group_booking':
                                    $message = "Попытка сделать запрос с условиями:
                                            Более 9 бронирований в одном и том же отеле.
                                            Более 9 бронирований на одни и те же даты.
                                            В одном запросе.";
                                    break;

                                case 'arrival_date_differs_from_checkin_date':
                                    $message = "Дата заезда должна совпадать или быть на следующий день после даты заезда в запросе.";
                                    break;

                                case 'sandbox_restriction':
                                    $message = "Попытка забронировать тестовый отель производственной среде.";
                                    break;
                                
                                default:
                                    $message = $finish['debug']['validation_error'];
                                    break;
                            }
                        }
                    }
                }
            }

            if ( isset($order['error']) ) {
                
                $emergingStatus = new \App\Http\Controllers\API\V1\Emerging\EmergingFormController();
                $finishStatus = $emergingStatus->finishStatus($request);

                switch ($order['error']) {
                    case 'double_booking_form':
                        $message = "Этот бронь уже существует!";
                        break;
                        
                    case 'error_dublicate_local':
                        $message = "Ошибка при создании брони на стейбук! Пожалуйста, попробуйте позже!";
                        break;

                    case 'contract_mismatch':
                        $message = "Попытка сделать бронирование по тарифу, найденному в другом договоре.";
                        break;

                    case 'duplicate_reservation':
                        $message = "Попытка сделать новое бронирование с использованием , {$etoken} которое уже используется для контракта ключа API";
                        break;
                        
                    case 'hotel_not_found':
                        $message = "Отель не найден.";
                        break;

                    case 'reservation_is_not_allowed':
                        $message = "Нет разрешения использовать этот вызов для этого контракта. Обратитесь к своему менеджеру по работе с клиентами.";
                        break;

                    case 'rate_not_found':
                        $message = "Ставка со book_hashзначением поля не найдена или Значение поля book_hash устарело. Попробуйте создать новый бронь!";
                        break;

                    case 'sandbox_restriction':
                        $message = "Попытка забронировать реальный отель в тестовой среде.";
                        break;
                    
                    default:
                        $message = '';
                        break;
                }
            }

            $book = Book::where('book_token', $request->token)->first();
            

            return view('pages.booking.emerging.rezerve', compact('book', 'request', 'message', 'preBook', 'finish', 'order', 'finishStatus'));
        
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

                $message = "Заказ выполнен со статусом, отличным от completed или rejected.";

                    Log::channel('emerging')->info('Cancel Order User ID - ', $userInfo);
                    Log::channel('emerging')->info('Cancel Order - ', [$cancel]);
        
            } 
            elseif ( isset($cancel->status) == 'error' && $cancel->error == 'order_not_cancellable' ){

                $message = "У вас нет разрешения на отмену невозвратных бронирований. Обратитесь к своему менеджеру по работе с клиентами.";

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