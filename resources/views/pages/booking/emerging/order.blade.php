@extends('layouts.master')

@section('title', 'Забронировать')

@section('content')
    @php
        use App\Models\Hotel;

        $hotel = Hotel::find($request->hotel_id);

        // ---- rooms ----
        $rooms = $request->input('rooms', []);
        if (is_string($rooms)) {
            $decoded = json_decode($rooms, true);
            $rooms   = is_array($decoded) ? $decoded : [];
        }

        $totalAdults  = 0;
        $allChildAges = [];
        $roomCount    = 0;
        $childs       = 0;

        foreach ($rooms as $room) {
            $adults = (int) data_get($room, 'adults', 0);
            $childAges = (array) data_get($room, 'childAges', []);

            $roomCount++;
            $totalAdults += $adults;

            foreach ($childAges as $age) {
                if ($age !== '' && $age !== null) {
                    $allChildAges[] = (int) $age;
                    $childs++;
                }
            }
        }
        if ($totalAdults <= 0) $totalAdults = 1;

        // ---- безопасно достаём rate из preBook ----
        $coef   = (float) (config('app.main_coef') ?? 1);
        $fxBase = $fxBase ?? 'USD';    // если вдруг не передали из контроллера

        $rate = is_array($preBook ?? null)
            ? data_get($preBook, 'data.hotels.0.rates.0', [])
            : [];

        // платёж
        $payment = (array) data_get($rate, 'payment_options.payment_types.0', []);

        $priceRaw       = (float) data_get($payment, 'amount', 0);
        $price          = $priceRaw; // как в исходном коде
        $penaltRaw      = (float) data_get($payment, 'cancellation_penalties.policies.1.amount_charge', 0);

        $totalPrice   = $coef > 0 ? number_format($price / $coef, 2, '.', '')   : 0;
        $penaltyPrice = $coef > 0 ? number_format($penaltRaw / $coef, 2, '.', '') : 0;

        // конвертация в валюту пользователя
        $converted        = app(\App\Services\FXService::class)->convert($totalPrice,   $request->currency, $fxBase);
        $cancelConverted  = app(\App\Services\FXService::class)->convert($penaltyPrice, $request->currency, $fxBase);

        // для hidden-полей
        $rateChanged = $rate;   // просто alias, чтобы ниже не падало
    @endphp

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>
                        <img src="{{ route('index') }}/img/arrow-left.svg" alt="">
                        @lang('main.booking')
                    </h1>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-12">
                    <div class="sidebar">
                        @if ( isset($request->etgimage) )
                            <img src="{{ Storage::url($request->etgimage) }}" alt="">
                        @else
                            <img src="{{ route('index') }}/img/noimage.png" alt="">
                        @endif
                        <div class="text-wrap">
                            <div class="descr">@lang('main.hotel'): {{ $hotel->title }}</div>
                            <div class="descr">@lang('main.room'): {{ $request->room_name }}</div>
                            <div class="descr">@lang('main.rate'): {{ $request->rate_name }}</div>
                            <div class="date">@lang('main.check-in/check-out'): {{ $arrival }} {{ $hotel->checkin }}
                                - {{ $departure }} {{ $hotel->checkout }} (UTC+0)
                                {{-- {{ $request->utc }} --}}
                            </div>
                            <div class="cancel">@lang('main.cancellation_policy'):
                                @if($request->refundable == true)

                                    @lang('main.free_cancellation') {{ $request->cancelDate }} UTC+0 <br>

                                    @lang('main.cancellation_amount')
                                    : {{ round($cancelConverted) }} {{ $request->currency ?? '$' }}
                                @else
                                    @lang('main.non_refundable')
                                @endif
                            </div>
                            <div class="nds_not_included" style="color: red; font-size: 13px;">
                                @lang('main.all_taxes_excluded')</div>
                            <span style="font-size: 13px;">@lang('main.pay_at_hotel')</span><br>
                            @php
                                $tax_not_included = $request->tax_not_included ?? [];

                                // Если пришёл JSON – декодируем
                                if (is_string($tax_not_included)) {
                                    $decoded = json_decode($tax_not_included, true);
                                    $tax_not_included = is_array($decoded) ? $decoded : [];
                                }

                                if (!is_array($tax_not_included)) {
                                    $tax_not_included = [];
                                }
                            @endphp

                            @foreach($tax_not_included as $tax)
                                @if(is_array($tax)
                                    && array_key_exists('included_by_supplier', $tax)
                                    && $tax['included_by_supplier'] == false)

                                    <span style="font-size: 13px;"><strong>
            @if(is_string($tax['name'] ?? null) && Lang::has('main.'.$tax['name']))
                                                @lang('main.'.$tax['name'])
                                            @else
                                                {{ $tax['name'] ?? '' }}
                                            @endif
            : </strong>
            {{ $tax['amount'] ?? '' }} {{ $tax['currency_code'] ?? '' }}
        </span><br>
                                @endif
                            @endforeach

                            <div class="line"></div>
                            <div class="row mt">
                                <div class="col-md-8">
                                    <div class="total">@lang('main.total')</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="price">{{ round($converted ?? $request->totalPrice) }} {{ $request->currency ?? '$'}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-md-12">
                    <div class="clearfix">
                        <div id="timer" style="color: red;" class="d-flex justify-content-end">
                            @lang('main.time_booking') : &nbsp;<span id="countdown"></span>
                        </div>
                    </div>

                    @if($message == 'price_changed_text')
                        <div class="alert alert-warning" role="alert">
                            @lang('main.'.$message)
                        </div>
                    @elseif($message)
                        <div class="alert alert-danger" role="alert">
                            @lang('main.'.$message)
                        </div>
                    @endif

                    @if($throwMessage)
                        <div class="alert alert-danger" role="alert">
                            {{ $throwMessage }}
                        </div>
                    @endif

                    <h5>@lang('main.trip')</h5>

                    <form action="{{ route('book_verify_etg') }}">
                        <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                        <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        <input type="hidden" name="hotel_id" value="{{ $request->hotel_id }}">
                        <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                        <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                        <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                        @foreach ($rooms as $i => $room)
                            @php
                                $adults    = (int)($room['adults'] ?? 0);
                                $childAges = isset($room['childAges']) && is_array($room['childAges'])
                                    ? $room['childAges']
                                    : [];
                            @endphp

                            <input type="hidden" name="rooms[{{ $i }}][adults]" value="{{ $adults }}">

                            @foreach ($childAges as $a => $age)
                                <input type="hidden" name="rooms[{{ $i }}][childAges][]" value="{{ $age }}">
                            @endforeach
                        @endforeach
                        <input type="hidden" name="book_hash"
                               value="{{ data_get($rateChanged, 'book_hash', $request->book_hash) }}">

                        <input type="hidden" name="match_hash"
                               value="{{ data_get($rateChanged, 'match_hash', $request->match_hash) }}">
                        <input type="hidden" name="room_name" value="{{ $request->room_name }}">
                        <input type="hidden" name="rate_name" value="{{ $request->rate_name }}">
                        <input type="hidden" name="bedTypeDesc" value="{{ $request->bedTypeDesc }}">
                        <input type="hidden" name="refundable" value="{{ $request->refundable }}">
                        <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                        <input type="hidden" name="cancelPriceAnullation" value="{{ $request->cancelPriceAnullation }}">
                        <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                        <input type="hidden" name="currency" value="{{ $request->currency }}">
                        <input type="hidden" name="utc" value="{{ $request->utc }}">
                        <input type="hidden" name="price" value="{{ $request->price }}">
                        <input type="hidden" name="sum" value="{{ $request->totalPrice }}">
                        <input type="hidden" name="tax_not_included" value="{{ $request->tax_not_included }}">
                        <input type="hidden" name="residency" value="{{ $request->residency ?? '' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="label">@lang('main.full_name')</div>
                                    <input type="text" name="name" placeholder="Асанов А.А."
                                           value="{{ Auth::user()->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="label">@lang('main.count_adult')</div>
                                    <input type="text" value="{{ $totalAdults }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">@lang('main.count_child')</label>
                                    <input type="text" value="{{ $childs }}" readonly>
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
                                    <label>@lang('main.message')</label>
                                    <textarea name="comment" rows="3">Ваш комментарий</textarea>
                                </div>
                            </div>

                            {{-- map quests --}}
                            <h5>@lang('main.quests')</h5>
                            @for ($i = 0; $i < $totalAdults; $i++)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxfname">@lang('main.full_name') #{{$i+1}}</label>
                                        <input type="text" name="paxfname{{$i}}"
                                               class="only-latin"
                                               required>
                                    </div>
                                </div>
                            @endfor

                            @if( !empty($childs) )
                                <h5>@lang('main.count_child')</h5>
                                @for ($i = 0; $i < $childs; $i++)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="paxlname">@lang('main.fio') #{{$i+1}}</label>
                                            <input type="text" name="child_name{{$i}}"
                                                   class="only-latin"
                                                   required>
                                        </div>
                                    </div>
                                @endfor
                            @endif
                            <script>
                                document.querySelectorAll('.only-latin').forEach(function (input) {
                                    input.addEventListener('input', function () {
                                        this.value = this.value.replace(/[^a-zа-яё\s]/gi, '');
                                    });
                                });

                                function validateFullName(value) {
                                    // Должно быть минимум два слова (Имя Фамилия), допускается 3 слова (Имя Отчество Фамилия)
                                    let regex = /^([A-Za-zА-Яа-яЁё]+)\s+([A-Za-zА-Яа-яЁё]+)(\s+[A-Za-zА-Яа-яЁё]+)?$/;
                                    return regex.test(value.trim());
                                }

                                document.querySelector('form').addEventListener('submit', function (e) {
                                    let inputs = document.querySelectorAll('.only-latin');
                                    let message = "{{ __('main.fio_validate_order') }}";
                                    for (let input of inputs) {
                                        if (!validateFullName(input.value)) {
                                            e.preventDefault();
                                            alert(message);
                                            return false;
                                        }
                                    }
                                });

                            </script>
                        </div>

                        <div class="descr">
                            @if(app()->getLocale() == 'ru')
                                Нажимая кнопку ниже, я принимаю условия (Правила дома, установленные хозяином,
                                Основные правила для гостей, Правила StayBook в отношении повторного бронирования и
                                возврата средств,
                                Условия частичной предоплаты) и соглашаюсь, что StayBook может списать средства с
                                моего способа оплаты,
                                если ответственность за ущерб лежит на мне.
                            @else
                                By clicking the button below, I accept the terms (House Rules set by the Host, Guest
                                Code of Conduct, StayBook’s Rebooking and Refund Policy, Partial Prepayment Terms)
                                and agree that StayBook may charge my payment method if I am responsible for any
                                damage.
                            @endif
                        </div>
                        <div class="btn-wrap d-flex" style="gap: 30px;">
                            <button class="more" id="saveBtn">@lang('main.confirm_and_paye')</button>
                            <a href="{{ route('index')}}" class="btn more" id="Home">@lang('main.go_home')</a>
                        </div>
                    </form>
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
        let secondsLeft = localStorage.getItem('booking_etg_secondsLeft');
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
                    localStorage.removeItem('booking_etg_secondsLeft'); // Очистить данные
                    window.location.href = "{{ route('index') }}";
                }
                return;
            }

            document.getElementById('countdown').innerText = formatTime(secondsLeft);
            secondsLeft--;
            localStorage.setItem('booking_etg_secondsLeft', secondsLeft);
        }

        tick(); // первый вызов сразу
        countdownInterval = setInterval(tick, 1000);
    </script>
@endsection