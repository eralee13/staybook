@php use App\Models\Hotel; @endphp
@extends('layouts.head')

@section('title', 'Оформление заказа')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3><a href="search.html"><img src="{{ route('index') }}/img/icons/arrow-left.svg" alt=""></a>
                        @lang('main.booking')
                    </h3>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 col-md-12 order-xl-1 order-lg-1 order-2">
                    <h5>@lang('main.trip')</h5>
                    <form action="{{ route('book_verify') }}">
                        <input type="hidden" name="propertyId" value="{{ $request->propertyId }}">
                        <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                        <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                        <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                        <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                        {{-- <input type="hidden" name="roomType" value="{{ $request->roomType }}">--}}
                        {{-- <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">--}}
                        {{-- <input type="hidden" name="roomCode" value="{{ $request->roomCode }}">--}}
                        {{-- <input type="hidden" name="placementCode" value="{{ $request->placementCode }}">--}}
                        @if(!empty($request->rooms) && is_array($request->rooms))
                            @foreach($request->rooms as $i => $room)
                                <input
                                        type="hidden"
                                        name="rooms[{{ $i }}][adults]"
                                        value="{{ $room['adults'] }}"
                                >
                                @if(!empty($room['childAges']) && is_array($room['childAges']))
                                    @foreach($room['childAges'] as $j => $age)
                                        <input
                                                type="hidden"
                                                name="rooms[{{ $i }}][childAges][{{ $j }}]"
                                                value="{{ $age }}"
                                        >
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                        <input type="hidden" name="cancellation_id" value="{{ $request->cancellation_id }}">
                        <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                        <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                        <input type="hidden" name="price" value="{{ $request->price }}">
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
                                    <input type="text" name="adult" value="{{ $request->adult }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <div class="label">@lang('main.count_child')</div>
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
                                    <label for="">@lang('main.message')</label>
                                    <textarea name="comment" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        {{--                        <div class="line"></div>--}}
                        {{--                        <div class="row">--}}
                        {{--                            <div class="col-md-12">--}}
                        {{--                                <h5>@lang('payment_options')</h5>--}}
                        {{--                                <div class="method-item current">--}}
                        {{--                                    <div class="name">Оплатить--}}
                        {{--                                        сейчас {{ $request->price }} {{ $request->currency ?? '$' }}</div>--}}
                        {{--                                </div>--}}
                        {{--                                --}}{{--                                <div class="method-item">--}}
                        {{--                                --}}{{--                                    <div class="name">Оплатите часть сейчас, а остаток внесите позже--}}
                        {{--                                --}}{{--                                        36,000 сом к оплате сегодня, 36,000 сом — 01 мар. 2025 г.</div>--}}
                        {{--                                --}}{{--                                </div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        {{--                        <div class="row">--}}
                        {{--                            <div class="col-md-12">--}}
                        {{--                                <div class="row payment-wrap">--}}
                        {{--                                    <div class="col-md-6">--}}
                        {{--                                        <h5>Оплата</h5>--}}
                        {{--                                    </div>--}}
                        {{--                                    <div class="col-md-6">--}}
                        {{--                                        <div class="payment">--}}
                        {{--                                            <div class="payment-item">--}}
                        {{--                                                <img src="{{ route('index') }}/img/balance.svg" alt="">--}}
                        {{--                                            </div>--}}
                        {{--                                            <div class="payment-item">--}}
                        {{--                                                <img src="{{ route('index') }}/img/mega.svg" alt="">--}}
                        {{--                                            </div>--}}
                        {{--                                            <div class="payment-item">--}}
                        {{--                                                <img src="{{ route('index') }}/img/optima.svg" alt="">--}}
                        {{--                                            </div>--}}
                        {{--                                            <div class="payment-item">--}}
                        {{--                                                <img src="{{ route('index') }}/img/mbank.svg" alt="">--}}
                        {{--                                            </div>--}}
                        {{--                                        </div>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}
                        {{--                                <div class="payment-type">--}}
                        {{--                                    <select name="" class="payment_type" id="">--}}
                        {{--                                        <option value="">Выбрать способ оплаты</option>--}}
                        {{--                                        <option value="">Balance</option>--}}
                        {{--                                        <option value="">Mega</option>--}}
                        {{--                                        <option value="">Optima</option>--}}
                        {{--                                        <option value="">Mbank</option>--}}
                        {{--                                    </select>--}}
                        {{--                                </div>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}
                        <div class="line"></div>
                        <div class="descr">
                            @if(app()->getLocale() == 'ru')
                                Нажимая кнопку ниже, я принимаю условия (Правила дома, установленные
                                хозяином, Основные правила для гостей, Правила StayBook в отношении повторного
                                бронирования
                                и возврата средств, Условия частичной предоплаты) и соглашаюсь, что StayBook может
                                списать
                                средства с моего способа оплаты, если ответственность за ущерб лежит на мне.
                            @else
                                By clicking the button below, I accept the terms (House Rules set by the Host, Guest
                                Code of Conduct, StayBook’s Rebooking and Refund Policy, Partial Prepayment Terms) and
                                agree that StayBook may charge my payment method if I am responsible for any damage.
                            @endif
                        </div>
                        <div class="btn-wrap">
                            <button class="more" id="saveBtn">@lang('main.confirm')</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 order-xl-2 order-lg-2 order-1">
                    @php

                        $hotel = Hotel::where('exely_id', $request->propertyId)->orWhere('id', $request->propertyId)->first();
                        $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                        $cancel_utc = \Carbon\Carbon::createFromDate($request->cancelDate)->format('P');
                        $cancel = \App\Models\CancellationRule::where('id', $request->cancellation_id)->firstOrFail();
                        $room = \App\Models\Room::where('id', $request->room_id)->firstOrFail();
                        $rate = \App\Models\Rate::where('id', $request->rate_id)->firstOrFail();

                    @endphp
                    <div class="sidebar">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                            </div>
                            <div class="col-md-8">
                                <div class="descr">@lang('main.hotel'): {{ $hotel->__('title') }}</div>
                                <div class="descr">@lang('main.room'): {{ $room->__('title') }}</div>
                                <div class="descr">@lang('main.rate'): {{ $rate->__('title') }}</div>
                                <div class="date">@lang('main.check-in/check-out'): {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel_utc }})
                                </div>
                                <div class="cancel">@lang('main.cancellation_policy'):
                                    @if($cancel->is_refundable == 1)
                                        @if(now()->lte($request->cancelDate))
                                            @lang('main.free_cancellation') {{ $request->cancelDate }}
                                            (UTC {{ $cancel_utc }}).
                                        @else
                                            @lang('main.cancellation_is_not_avaialble').
                                        @endif
                                        @lang('main.cancellation_amount')
                                        : {{ $request->cancelPrice }} {{ $request->currency ?? '$' }}
                                    @else
                                        @lang('main.cancellation_is_not_avaialble'). @lang('main.cancellation_amount')
                                        : {{ $request->cancelPrice }} {{ $request->currency ?? '$' }}
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
                                <div class="price">{{ $request->price }} {{ $request->currency ?? '$'}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection