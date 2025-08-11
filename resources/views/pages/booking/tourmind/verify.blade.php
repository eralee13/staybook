@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')
    
    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <div class="clearfix">
                        <div id="timer" style="color: red;" class="d-flex justify-content-end">
                            @lang('main.time_booking') : &nbsp;<span id="countdown"></span>
                        </div>
                    </div>

                    <h1>@lang('main.order_confirmation')</h1>
                    <table>
                        <tr>
                            <td>@lang('main.hotel'):</td>
                            <td>{{ $hotel->title_en }}</td>
                        </tr>
                        <tr>
                            <td>@lang('main.room_type'):</td>
                            <td>{{ $request->room_name }}</td>
                        </tr>
                        <tr>
                            <td>@lang('main.rate'):</td>
                            <td>{{ $request->rate_name }}</td>
                        </tr>
                        <tr>
                            <td>@lang('main.count_adult'):</td>
                            <td>{{ $request->adult }}</td>
                        </tr>
                        <tr>
                            <td>@lang('main.count_child'):</td>
                            <td>{{ $request->child }}</td>
                            {{--                                    <td>{{ implode(',', explode($order->booking->roomStays[0]->guestCount->childAges)) }}</td>--}}
                            {{--                                    <td>{{ count($order->booking->roomStays[0]->guestCount->guestCount->childAges) }}</td>--}}
                        </tr>
                        <tr>
                            <td>@lang('main.count_room'):</td>
                            <td>{{ $request->roomCount }}</td>
                        </tr>
                        <tr>
                            <td>@lang('main.dates'):</td>
                            <td>
                                @if (!empty($request->arrivalDate))
                                    {{ \Carbon\Carbon::parse($request->arrivalDate)->format('d.m.Y') }}
                                @endif
                                - 
                                @if (!empty($request->departureDate))
                                    {{ \Carbon\Carbon::parse($request->departureDate)->format('d.m.Y') }}
                                @endif
                                
                                (UTC {{ $request->utc }})
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('main.price'):</td>     
                            <td>
                                @php
                                    $basePrice = $request->sum;
                                    $toCurrency = strtoupper($fxBase ?? 'USD');

                                    $symbols = [
                                        'USD' => '$',
                                        'RUB' => '₽',
                                        'KGS' => 'сом',
                                        'UZS' => 'сўм',
                                    ];

                                    $converted = app(\App\Services\FXService::class)->convert($basePrice, $request->currency, $fxBase);
                                    $symbol = $symbols[$toCurrency] ?? $toCurrency;

                                    $cancelConverted = app(\App\Services\FXService::class)->convert($request->cancelPrice, $request->currency, $fxBase);
                                    $cancelSymbol = $symbols[$toCurrency] ?? $toCurrency;
                                @endphp
                                
                                {{ round($converted) }} {{ $symbol }}
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('main.cancel_rule'):</td>
                            <td>
                                @if($request->refundable == true)
                                    
                                        @lang('main.free_cancellation') {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }} 
                                        (UTC {{ $request->utc }})
                                        
                                        @lang('main.cancellation_amount_tm'): {{ $cancelConverted }} {{ $cancelSymbol ?? '$' }}
                                @else
                                        @lang('main.non_refundable')
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('main.fio'):</td>
                            <td>
                                <div class="name">{{ $request->name }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('main.phone'):</td>
                            <td>
                                <div class="name">{{ $request->phone }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td>
                                <div class="name">{{ $request->email }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>@lang('main.comment'):</td>
                            <td>{{ $request->comment }}</td>
                        </tr>
                    </table>

                <div class="btn-wrap">
                    <form action="{{ route('book_reserve_tm') }}" method="get">
                        <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                        <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        <input type="hidden" name="hotel_id" value="{{ $request->hotel_id }}">
                        <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                        <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                        <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                        <input type="hidden" name="adult" value="{{ $request->adult }}">
                        <input type="hidden" name="child" value="{{ $request->child }}">
                            @if( isset($request->childAges))
                                @foreach($request->childAges as $age)
                                    <input type="hidden" name="childAges[]"
                                        value="{{ $age }}">
                                @endforeach
                            @endif
                        <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">
                        <input type="hidden" name="room_name" value="{{ $request->room_name }}">
                        <input type="hidden" name="RoomTypeCode" value="{{ $request->RoomTypeCode }}">
                        <input type="hidden" name="rate_name" value="{{ $request->rate_name }}">
                        <input type="hidden" name="rate_code" value="{{ $request->rate_code }}">
                        <input type="hidden" name="refundable" value="{{ $request->refundable }}">
                        <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                        <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                        <input type="hidden" name="currency"  value="{{ $request->currency }}">
                        <input type="hidden" name="utc" value="{{ $request->utc }}">
                        <input type="hidden" name="price" value="{{ $request->price }}">
                        <input type="hidden" name="source_sym" value="{{ $toCurrency }}">
                        <input type="hidden" name="sum" value="{{ $request->sum }}">
                        <input type="hidden" name="api_name" value="TM">
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="sex" value="Male">
                        <input type="hidden" name="citizenship" value="KGS">
                        <input type="hidden" name="comment" value="{{ $request->comment }}">
                        <input type="hidden" name="phone" value="{{ $request->phone }}">
                        <input type="hidden" name="email" value="{{ $request->email }}">

                            @for ($i = 0; $i < $request->adult; $i++)
                                <input type="hidden" name="paxfname{{$i}}" value="{{ $request->input('paxfname' . $i) }}">
                            @endfor
                            @for ($i = 0; $i < $request->child; $i++)
                                <input type="hidden" name="child_name{{$i}}" value="{{ $request->input('child_name' . $i) }}">
                            @endfor

                        <button class="more" id="booking">@lang('main.confirm')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

    let secondsLeft = localStorage.getItem('booking_tm_secondsLeft');
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

    document.getElementById('booking').addEventListener('click', function() {
        clearInterval(countdownInterval); // Остановить таймер
        localStorage.removeItem('booking_tm_secondsLeft'); // Очистить данные
    });
</script>

@endsection
