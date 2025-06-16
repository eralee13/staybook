@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3><a href="search.html"><img src="{{ route('index') }}/img/icons/arrow-left.svg" alt=""></a>
                        Подтвердите и оплатите
                    </h3>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-md-12 order-xl-1 order-lg-1 order-2">
                    <h5>Ваша поездка</h5>

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
                        @if( isset($request->childAges))
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
                        <input type="hidden" name="cancelPrice" value="{{ number_format(($request->cancelPrice  * 0.08) + $request->cancelPrice, 2, '.', '') }}">
                        <input type="hidden" name="currency"  value="{{ $request->currency }}">
                        <input type="hidden" name="utc" value="{{ $request->utc }}">
                        <input type="hidden" name="price" value="{{ $request->price }}">
                        <input type="hidden" name="sum" value="{{ number_format( ($request->price * 0.08) + $request->price, 2, '.', '') }}">
                        <input type="hidden" name="api_name" value="TM">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="label">ФИО</div>
                                    <input type="text" name="name" placeholder="Асанов А.А."
                                           value="{{ Auth::user()->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="label">Гости</div>
                                    <input type="text" value="{{ $request->adult }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Кол-во детей</label>
                                    <input type="text" value="{{ $request->child }}" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Номер телефона</label>
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
                                    <label for="">Комментарий</label>
                                    <textarea name="comment" rows="3">Ваш комментарий</textarea>
                                </div>
                            </div>
                            
                                <h5>@lang('main.quests')</h5>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxfname">@lang('main.firstname')</label>
                                        <input type="text" name="paxfname" placeholder="" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxlname">@lang('main.lastname')</label>
                                        <input type="text" name="paxlname" placeholder="" required>
                                    </div>
                                </div>
                            @if( $request->roomCount == 2)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxfname2">@lang('main.firstname')</label>
                                        <input type="text" name="paxfname2" placeholder="" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxlname2">@lang('main.lastname')</label>
                                        <input type="text" name="paxlname2" placeholder="" required>
                                    </div>
                                </div>
                            @elseif( $request->roomCount == 3)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxfname3">@lang('main.firstname')</label>
                                        <input type="text" name="paxfname3" placeholder="" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxlname3">@lang('main.lastname')</label>
                                        <input type="text" name="paxlname3" placeholder="" required>
                                    </div>
                                </div>
                            @elseif( $request->roomCount == 4)
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxfname4">@lang('main.firstname')</label>
                                        <input type="text" name="paxfname4" placeholder="" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paxlname4">@lang('main.lastname')</label>
                                        <input type="text" name="paxlname4" placeholder="" required>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="line"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <h5>Варианты оплаты</h5>
                                <div class="method-item current">
                                    <div class="name">Оплатить
                                        сейчас {{ number_format($request->totalPrice, 2, '.', '') }} {{ $request->currency ?? '$' }}</div>
                                </div>
                                {{--                                <div class="method-item">--}}
                                {{--                                    <div class="name">Оплатите часть сейчас, а остаток внесите позже--}}
                                {{--                                        36,000 сом к оплате сегодня, 36,000 сом — 01 мар. 2025 г.</div>--}}
                                {{--                                </div>--}}
                            </div>
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
                        </div>
                        <div class="line"></div>
                        <div class="descr">Нажимая кнопку ниже, я принимаю условия (Правила дома, установленные
                            хозяином, Основные правила для гостей, Правила StayBook в отношении повторного бронирования
                            и возврата средств, Условия частичной предоплаты) и соглашаюсь, что StayBook может списать
                            средства с моего способа оплаты, если ответственность за ущерб лежит на мне.
                        </div>
                        <div class="btn-wrap">
                            <button class="more" id="saveBtn">Подтвердить и оплатить</button>
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
                                <div class="descr">Отель: {{ $hotel->title }}</div>
                                <div class="descr">Номер: {{ $request->room_name }}</div>
                                <div class="descr">Тариф: {{ $request->rate_name }}</div>
                                <div class="date">Время заезда/выезда: {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $request->utc }})
                                </div>
                                <div class="cancel">Правила отмены:
                                    @if($request->refundable == true)
                                        
                                            Бесплатная отмена действует до {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }}
                                            (UTC {{ $request->utc }})
                                        
                                        Размер штрафа: {{ number_format(($request->cancelPrice  * 0.08) + $request->cancelPrice, 2, '.', '')}} {{ $request->currency ?? '$' }}
                                    @else
                                        Невозвратный тариф.
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
                                <div class="total">Итого</div>
                            </div>
                            <div class="col-md-4">
                                <div class="price">{{ number_format($request->totalPrice, 2, '.', '') }} {{ $request->currency ?? '$'}}</div>
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

@endsection