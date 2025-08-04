@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')
 @php
    $basePrice = $request->totalPrice;
    $toCurrency = strtoupper($fxBase ?? 'USD');
    $fxRates = [
        'USD' => $fxRates['usd'] ?? 1,
        'RUB' => $fxRates['rub'] ?? 1,
        'KGS' => $fxRates['kgs'] ?? 1,
        'UZS' => $fxRates['uzs'] ?? 1,
    ];

    $symbols = [
        'USD' => '$',
        'RUB' => '₽',
        'KGS' => 'сом',
        'UZS' => 'сўм',
    ];

    $rateTo = $fxRates[$toCurrency] ?? 1;
    $converted = app(\App\Services\FXService::class)->convert($basePrice, $request->currency, $fxBase);
    $symbol = $symbols[$toCurrency] ?? $toCurrency;

    $cancelTotal = number_format(($request->cancelPrice  / config('services.main.coef')), 2, '.', '');
    $cancelConverted = app(\App\Services\FXService::class)->convert($cancelTotal, $request->currency, $fxBase);
    $cancelSymbol = $symbols[$toCurrency] ?? $toCurrency;

@endphp

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3><a href="javascript:history.back()"><img src="{{ route('index') }}/img/icons/arrow-left.svg" alt=""></a>
                        @lang('main.confirm_and_pay')
                    </h3>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-md-12 order-xl-1 order-lg-1 order-2">
                    <div class="clearfix">
                        <div id="timer" style="color: red;" class="d-flex justify-content-end">
                            @lang('main.time_booking') : &nbsp;<span id="countdown"></span>
                        </div>
                    </div>
                    <h5>@lang('main.trip')</h5>

                    <form action="{{ route('book_verify_tm') }}">
                        <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                        <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        <input type="hidden" name="hotel_id" value="{{ $request->hotel_id }}">
                        <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                        <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                        <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                        <input type="hidden" name="adult" value="{{ $request->adult }}">
                        <input type="hidden" name="child" value="{{ $request->child }}">
                        <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">
                            @if( isset($request->childAges) )
                                @foreach($request->childAges as $age)
                                    <input type="hidden" name="childAges[]"
                                        value="{{ $age }}">
                                @endforeach
                            @endif
                        <input type="hidden" name="room_name" value="{{ $request->room_name }}">
                        <input type="hidden" name="RoomTypeCode" value="{{ $request->RoomTypeCode }}">
                        <input type="hidden" name="rate_name" value="{{ $request->rate_name }}">
                        <input type="hidden" name="rate_code" value="{{ $request->rate_code }}">
                        <input type="hidden" name="refundable" value="{{ $request->refundable }}">
                        <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                        <input type="hidden" name="cancelPrice" value="{{ number_format(($request->cancelPrice  / config('services.main.coef')), 2, '.', '') }}">
                        <input type="hidden" name="currency"  value="{{ $request->currency }}">
                        <input type="hidden" name="utc" value="{{ $request->utc }}">
                        <input type="hidden" name="price" value="{{ $request->price }}">
                        <input type="hidden" name="sum" value="{{ number_format( ($request->price / config('services.main.coef')), 2, '.', '') }}">
                        <input type="hidden" name="api_name" value="TM">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="label">@lang('main.fio')</div>
                                    <input type="text" name="name" placeholder="Асанов А.А."
                                           value="{{ Auth::user()->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="label">@lang('main.count_adult')</div>
                                    <input type="text" value="{{ $request->adult }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">@lang('main.count_child')</label>
                                    <input type="text" value="{{ $request->child }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('main.phone')</label>
                                    <input type="text" name="phone" id="phone" value="{{ Auth::user()->phone }}"
                                           required>
                                    <div id="output"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input type="email" name="email" value="{{ Auth::user()->email }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'comment'])
                                    <label for="">@lang('main.comment')</label>
                                    <textarea name="comment" rows="3">@lang('main.comment')</textarea>
                                </div>
                            </div>
                            
                                <h5>@lang('main.quests')</h5>
                                @for ($i = 0; $i < $request->adult; $i++)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="paxfname">@lang('main.fio') #{{$i+1}}</label>
                                            <input type="text" name="paxfname{{$i}}" required>
                                        </div>
                                    </div>
                                @endfor

                                
                                @for ($i = 0; $i < $request->child; $i++)
                                <h5>@lang('main.count_child')</h5>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="paxlname">@lang('main.fio') #{{$i+1}}</label>
                                            <input type="text" name="child_name{{$i}}" required>
                                        </div>
                                    </div>
                                @endfor
                        </div>
                        {{-- <div class="line"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <h5>@lang('main.payment_options')</h5>
                                <div class="method-item current">
                                    <div class="name">@lang('main.pay_now') {{ round($converted) }} {{ $symbol }}
                                    </div>
                                </div> --}}
                                {{--                                <div class="method-item">--}}
                                {{--                                    <div class="name">Оплатите часть сейчас, а остаток внесите позже--}}
                                {{--                                        36,000 сом к оплате сегодня, 36,000 сом — 01 мар. 2025 г.</div>--}}
                                {{--                                </div>--}}
                            {{-- </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row payment-wrap">
                                    <div class="col-md-6">
                                        <h5>@lang('main.payment')</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="payment">
                                            <div class="payment-item">
                                                <img src="{{ route('index') }}/img/balance.svg" alt="">
                                            </div>
                                            <div class="payment-item">
                                                <img src="{{ route('index') }}/img/mega.svg" alt="">
                                            </div>
                                            <div class="payment-item">
                                                <img src="{{ route('index') }}/img/optima.svg" alt="">
                                            </div>
                                            <div class="payment-item">
                                                <img src="{{ route('index') }}/img/mbank.svg" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="payment-type">
                                    <select name="" class="payment_type" id="">
                                        <option value="">@lang('main.select_pay_type')</option>
                                        <option value="">Balance</option>
                                        <option value="">Mega</option>
                                        <option value="">Optima</option>
                                        <option value="">Mbank</option>
                                    </select>
                                </div>
                            </div>
                        </div> --}}
                        <div class="line"></div>
                        <div class="descr">@lang('main.order_description')
                        </div>
                        <div class="btn-wrap">
                            <button class="more" id="saveBtn">@lang('main.confirm_and_paye')</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 order-xl-2 order-lg-2 order-1">
                    @php
                        use App\Models\Hotel;
                        $hotel = Hotel::where('id', $request->hotel_id)->first();
                        // $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                        // $cancel_utc = \Carbon\Carbon::createFromDate($request->cancelDate)->format('P');
                        // $cancel = \App\Models\CancellationRule::where('id', $request->cancellation_id)->firstOrFail();
                        // $room = \App\Models\Room::where('id', $request->room_id)->firstOrFail();
                        // $rate = \App\Models\Rate::where('id', $request->rate_id)->firstOrFail();
                        
                    @endphp 
                    <div class="sidebar">
                        <div class="row">
                            <div class="col-md-4">
                                @if ( isset($tmimage) )
                                    <img src="{{ Storage::url($tmimage) }}" alt="">
                                @else
                                    <img src="{{ route('index') }}/img/noimage.png" alt="" width="100px">
                                @endif
                            </div>
                            <div class="col-md-8">
                                <div class="descr">@lang('main.hotel'): {{ $hotel->title }}</div>
                                <div class="descr">@lang('main.phone')Номер: {{ $request->room_name }}</div>
                                <div class="descr">@lang('main.rate')Тариф: {{ $request->rate_name }}</div>
                                <div class="date">@lang('main.check-in/check-out'): {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $request->utc }})
                                </div>
                                
                                <div class="cancel">@lang('main.cancellation_policy'):
                                    @if($request->refundable == true)
                                            @lang('main.free_cancellation') {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }}
                                            (UTC {{ $request->utc }})
                                        
                                        @lang('main.cancellation_amount_tm'): {{ round($cancelConverted) }} {{ $cancelSymbol }}
                                    @else
                                        @lang('main.non_refundable')
                                    @endif
                                </div>
                            </div>
                        </div>
                        {{--                        <div class="line"></div>--}}
                        {{--                        <h5>Детализация цены</h5>--}}
                        {{--                        <div class="row">--}}
                        {{--                            <div class="col-md-8">--}}
                        {{--                                <div class="price-item">--}}
                        {{--                                    <div class="name">36,000 {{ $request->currency }} * 2 ночи</div>--}}
                        {{--                                </div>--}}
                        {{--                            </div>--}}
                        {{--                            <div class="col-md-4">--}}
                        {{--                                <div class="price">{{ $request->price }} {{ $request->currency }}</div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        <div class="line"></div>
                        <div class="row mt">
                            <div class="col-md-8">
                                <div class="total">@lang('main.total')</div>
                            </div>
                            <div class="col-md-4">
                                <div class="price">{{ round($converted) }} {{ $symbol }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #phone {
            padding-left: 50px;
        }
    </style>


<script>

    let secondsLeft = localStorage.getItem('booking_tm_secondsLeft');
    console.log(secondsLeft);
    if (secondsLeft === null) {
    secondsLeft = 600;
    } else {
    secondsLeft = parseInt(secondsLeft);
    }

    let countdownInterval;
    let alertShown = false; // флаг

    function formatTime(sec) {
        let m = Math.floor(sec / 60);
        let s = sec % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    function tick() {
        if (secondsLeft <= 0) {
            if (!alertShown) {
                alertShown = true;
                clearInterval(countdownInterval); // остановить интервал
                alert("Время бронирования истекло. Пожалуйста, начните заново.");
                localStorage.removeItem('booking_tm_secondsLeft'); // Очистить данные
                window.location.href = "{{ route('index') }}";
            }
            return;
        }

        document.getElementById('countdown').innerText = formatTime(secondsLeft);
        secondsLeft--;
        localStorage.setItem('booking_tm_secondsLeft', secondsLeft);
    }

    tick(); // первый вызов сразу
    countdownInterval = setInterval(tick, 1000);
</script>

@endsection