@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')

@php
    $rooms = $request->input('rooms', []);
        $totalAdults    = 0;
        $allChildAges   = [];
        $roomCount=0;
        $childs = 0;

            foreach ($rooms as $room) {
                $roomCount++;
                // Взрослые
                $totalAdults += (int) ($room['adults'] ?? 0);

                // Возрасты детей (если есть) собираем в единый массив
                if (!empty($room['childAges']) && is_array($room['childAges'])) {
                    foreach ($room['childAges'] as $age) {
                        $allChildAges[] = (int) $age;
                        $childs++;
                    }
                }
            }

        // $coef = config('app.main_coef'); 
        // $totalPrice = number_format( ($request->totalPrice / $coef ) , 2, '.', '');
        // $penaltyPrice = number_format( ($request->cancelPrice / $coef ) , 2, '.', '');
        // $converted = app(\App\Services\FXService::class)->convert($totalPrice, $request->currency, $fxBase);
        // $cancelConverted = app(\App\Services\FXService::class)->convert($penaltyPrice, $request->currency, $fxBase);
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

                    {{-- @if($message == 'price_changed_text')
                        <div class="alert alert-warning" role="alert">
                            @lang('main.'.$message)
                        </div>
                    @elseif($message)
                        <div class="alert alert-danger" role="alert">
                            @lang('main.'.$message)
                        </div>
                    @endif --}}

                    {{-- @if($throwMessage)
                        <div class="alert alert-danger" role="alert">
                            {{ $throwMessage }}
                        </div>
                    @endif --}}

                    <h5>@lang('main.trip')</h5>

                    <form action="{{ route('book_verify_hs') }}">
                        <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                        <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        <input type="hidden" name="hotel_id" value="{{ $request->hotel_id }}">
                        <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                        <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                        <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                        <input type="hidden" name="city" value="{{ $request->city }}">

                            @foreach ($request->input('rooms', []) as $i => $room)
                                <input type="hidden" name="rooms[{{ $i }}][adults]" value="{{ $room['adults'] }}">
                                
                                @if (isset($room['childAges']))
                                    @foreach ($room['childAges'] as $a => $age)
                                        <input type="hidden" name="rooms[{{ $i }}][childAges][]" value="{{ $age }}">
                                    @endforeach
                                @endif
                            @endforeach

                        <input type="hidden" name="hash" value="{{ $request['hash'] }}">
                        <input type="hidden" name="provider_id" value="{{ $request['provider_id'] }}">
                        <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                        <input type="hidden" name="room_name" value="{{ $request->room_name }}">
                        <input type="hidden" name="rate_name" value="{{ $request->rate_name }}">
                        {{-- <input type="hidden" name="bedTypeDesc" value="{{ $request->bedTypeDesc }}"> --}}
                        <input type="hidden" name="refundable" value="{{ $request->refundable }}">
                        <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                        <input type="hidden" name="cancelPriceAnullation" value="{{ $request->cancelPriceAnullation }}">
                        <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                        <input type="hidden" name="currency"  value="{{ $request->currency }}">
                        <input type="hidden" name="utc" value="{{ $request->utc }}">
                        <input type="hidden" name="price" value="{{ $request->price }}">
                        <input type="hidden" name="totalPrice" value="{{ $request->totalPrice }}">

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
                                    <label for="">@lang('main.comment')</label>
                                    <textarea name="comment" rows="3">Ваш комментарий</textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="row">
                                    <h5>@lang('main.payable_services')</h5>

                                    @if( isset($actualize['search_item']['meals'][0]['included']) && 
                                            !empty($actualize['search_item']['meals'][0]['included']) == false )
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="paxfname">@lang('main.meal')</label>
                                                <select name="payable_meal[]" class="extra">
                                                    <option value="">@lang('main.select_value')</option>
                                                    @foreach($actualize['search_item']['meals'] as $item)

                                                        @if($item['included'] == false)
                                                            <option value="{{ $item['code'] }}-{{ $item['price'] }}">
                                                                {{ $item['name'] }} ({{ $item['price'] }} {{ $item['currency'] }})
                                                            </option>
                                                        @endif

                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    @if( !empty($earlyCheckIn) )
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="paxfname">@lang('main.check_in')</label>
                                                <select name="early_check_in" class="extra">
                                                    <option value="">@lang('main.select_value')</option>
                                                    @foreach($earlyCheckIn as $item)
                                                        <option value="{{ $item['value']['time'] }}-{{ $item['price'] }}">
                                                            {{ $item['name'] }} ({{ $item['price'] }} {{ $item['currency'] }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if( !empty($lateCheckOut) )
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="paxfname">@lang('main.check_out')</label>
                                                <select name="late_check_out" class="extra">
                                                    <option value="">@lang('main.select_value')</option>
                                                    @foreach($lateCheckOut as $item)
                                                        <option value="{{ $item['value']['time'] }}-{{ $item['price'] }}">
                                                            {{ $item['name'] }} ({{ $item['price'] }} {{ $item['currency'] }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    
                                </div>
                            </div>
                            {{-- map quests --}}
                                <h5>@lang('main.quests')</h5>
                                @for ($i = 0; $i < $totalAdults; $i++)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="paxfname">@lang('main.fio') #{{$i+1}}</label>
                                            <input type="text" name="paxfname{{$i}}" 
                                                class="only-latin" 
                                                required >
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
                               
                        </div>
                        {{-- <div class="line"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <h5>@lang('main.payment_options')</h5>
                                <div class="method-item current">
                                    <div class="name">@lang('main.pay_now') {{ round($request->totalPrice) }} {{ $request->currency ?? '$' }}</div>
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
                                        <h5>Оплата</h5>
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
                                        <option value="">Выбрать способ оплаты</option>
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
                        <div class="btn-wrap d-flex" style="gap: 30px;">
                            <button class="more" id="saveBtn">@lang('main.confirm_and_paye')</button>
                            <a href="{{ route('index')}}" class="btn more" id="Home">@lang('main.go_home')</a>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 order-xl-2 order-lg-2 order-1">

                    @php
                        use App\Models\Hotel;
                        $hotel = Hotel::where('id', $request->hotel_id)->first();
                    @endphp 

                    <div class="sidebar">
                        <div class="row">
                            <div class="col-md-4">
                                
                                @if ( isset($request->etgimage) )
                                    <img src="{{ Storage::url($request->etgimage) }}" alt="">
                                @else
                                    <img src="{{ route('index') }}/img/noimage.png" alt="" width="100px">
                                @endif
                            </div>
                            <div class="col-md-8">
                                <div class="descr">@lang('main.hotel'): {{ $hotel->title }}</div>
                                <div class="descr">@lang('main.room'): {{ $request->room_name }}</div>
                                <div class="descr">@lang('main.rate'): {{ $request->rate_name }}</div>
                                <div class="date">@lang('main.check-in/check-out'): {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $request->utc }})
                                </div>
                                <div class="cancel">@lang('main.cancellation_policy'):
                                    @if($request->refundable == true)
                                        
                                        @lang('main.free_cancellation') {{ $request->cancelDate }} UTC {{$request->utc}}. <br>
                                            
                                        @lang('main.cancellation_amount_tm'): {{ round($request->cancelPrice) }} {{ $request->currency ?? '$' }}
                                    @else
                                        @lang('main.non_refundable')
                                    @endif
                                </div>
                                <div>

                                </div>
                            </div>
                        </div>  
            
                        <div class="line"></div>
                        <div class="row mt">
                            <div class="col-md-8">
                                <div class="total">@lang('main.total')</div>
                            </div>
                            <div class="col-md-4">
                                <div class="price"><span id="total">{{ round($request->totalPrice) }}</span> {{ $request->currency ?? '$'}}</div>
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

        let secondsLeft = localStorage.getItem('booking_hs_secondsLeft');
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
                    localStorage.removeItem('booking_hs_secondsLeft'); // Очистить данные
                    window.location.href = "{{ route('index') }}";
                }
                return;
            }

            document.getElementById('countdown').innerText = formatTime(secondsLeft);
            secondsLeft--;
            localStorage.setItem('booking_hs_secondsLeft', secondsLeft);
        }

        tick(); // первый вызов сразу
        countdownInterval = setInterval(tick, 1000);
    </script>
    <script>
        function calculateTotal() {
            let total = {{ $request->totalPrice }};

            document.querySelectorAll('.extra').forEach(select => {
                let value = select.value; // например "3:00-6.46"
                if (value) {
                    let parts = value.split('-'); // ["3:00", "6.46"]
                    let price = parseFloat(parts[1]) || 0;
                    total += price;
                }
            });

            document.getElementById('total').textContent = Math.round(total);
            document.getElementsByName('totalPrice')[0].value = total.toFixed(2);
        }

        // слушаем изменения
        document.querySelectorAll('.extra').forEach(select => {
            select.addEventListener('change', calculateTotal);
        });

        // первый расчет при загрузке
        calculateTotal();

    </script>
    <script>
        document.querySelectorAll('.only-latin').forEach(function(input) {
            input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^a-zа-яё\s]/gi, '');
            });
        });

        function validateFullName(value) {
            // Должно быть минимум два слова (Имя Фамилия), допускается 3 слова (Имя Отчество Фамилия)
            let regex = /^([A-Za-zА-Яа-яЁё]+)\s+([A-Za-zА-Яа-яЁё]+)(\s+[A-Za-zА-Яа-яЁё]+)?$/;
            return regex.test(value.trim());
        }

        document.querySelector('form').addEventListener('submit', function(e) {
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
@endsection