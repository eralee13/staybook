@php use App\Models\Hotel; @endphp
@extends('layouts.master')

@section('title', 'Оформление заказа')

@section('content')

    @auth
        <style>
            .check input{
                width: auto;
            }
            body{
                font-family: Unbounded, sans-serif !important;
                background-color: rgba(246, 246, 246, 1) !important;
            }
            .page{
                padding-bottom: 60px;
            }
        </style>
        <div class="page order">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1><img src="{{ route('index') }}/img/arrow-left.svg" alt="">
                            @lang('main.booking')
                        </h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-12">
                        @php
                            $hotel = Hotel::where('exely_id', $request->propertyId)->orWhere('id', $request->propertyId)->first();
                            $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                            $cancel_utc = \Carbon\Carbon::createFromDate($request->cancelDate)->format('P');
                            $room = \App\Models\Room::where('id', $request->room_id)->firstOrFail();
                            $rate = \App\Models\Rate::where('id', $request->rate_id)->firstOrFail();
                            $cancel = \App\Models\CancellationRule::where('id', $request->cancellation_id)->firstOrFail();
                            $cancelDate = \Carbon\Carbon::parse($request->arrivalDate)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
                            $freeDate = \Carbon\Carbon::parse($request->arrivalDate)->format('d.m.Y H:i');
                            $timezone = \Carbon\Carbon::parse($hotel->timezone)->format('P');
                        @endphp
                        <div class="sidebar">
                            @if($hotel->image)
                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                            @else
                                <img src="{{ route('index')}}/img/noimage.png" alt="">
                            @endif
                            <div class="text-wrap">
                                <div class="descr">@lang('main.hotel'): {{ $hotel->__('title') }}</div>
                                <div class="descr">@lang('main.room'): {{ $room->__('title') }}</div>
                                <div class="descr">@lang('main.rate'): {{ $rate->__('title') }}</div>
                                <div class="date">@lang('main.check-in/check-out')
                                    : {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel_utc }})
                                </div>
                                <div class="cancel">@lang('main.cancellation_policy'):
                                    @if($cancel->cancel_policy === 'free_until_checkin')
                                        @lang('main.free_cancellation') {{ $freeDate }}
                                        UTC {{ $timezone }}
                                    @elseif($cancel->cancel_policy === 'free_then_penalty')
                                        @if(now()->lte($cancelDate))
                                            @lang('main.free_cancellation') {{ $cancelDate }}
                                            UTC {{ $timezone }}
                                        @else
                                            @lang('main.cancellation_is_not_avaialble').
                                        @endif
                                        {{ $request->cancelPrice }} {{ $request->currency }}
                                    @else
                                        {{ $request->cancelPrice }} {{ $request->currency }}
                                    @endif
                                </div>
                                <div class="row mt">
                                    <div class="col-md-8 col-8">
                                        <div class="total">@lang('main.total')</div>
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <div class="price">{{ $request->sum }} {{ $request->currency}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <h5>@lang('main.trip')</h5>
                        <form action="{{ route('book_verify') }}">
                            <input type="hidden" name="propertyId" value="{{ $request->propertyId }}">
                            <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                            <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                            <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                            <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                            <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                            <input type="hidden" name="roomCount" value="{{ $request->roomCount ?? 1 }}">
                            <input type="hidden" name="childAges[]" value="{{ implode(',', $request->childAges) }}">
                            <input type="hidden" name="cancellation_id" value="{{ $request->cancellation_id }}">
                            <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                            <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                            <input type="hidden" name="cancelPriceSource" value="{{ round($request->cancelPriceSource) }}">
                            <input type="hidden" name="price" value="{{ $request->price }}">
                            <input type="hidden" name="sum" value="{{ round($request->sum) }}">
                            <input type="hidden" name="currency" value="{{ $request->currency }}">
                            <input type="hidden" name="source_sym" value="{{ $request->source_sym }}">
                            @for ($i = 1; $i <= $request->adult; $i++)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="">@if($i === 1)
                                                @lang('main.full_name')
                                            @else
                                                #{{ $i }} @lang('main.full_name')
                                            @endif</label>
                                        <input type="text" name="title{{ $i }}" placeholder="@if($i === 1)
                                                    @lang('main.full_name')
                                                @else
                                                    #{{ $i }} @lang('main.full_name')
                                                @endif"
                                               value="{{ $i === 1 && Auth::check() ? Auth::user()->name : '' }}"
                                               required>
                                    </div>
                                </div>
                            @endfor
                            @for ($i = 1; $i <= $request->child; $i++)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="label">
                                            @if($i === 1)
                                                @lang('main.full_name') @lang('main.child')
                                            @else
                                                #{{ $i }} @lang('main.full_name') @lang('main.child')
                                            @endif
                                        </div>
                                        <input type="text" name="child_name{{ $i }}" placeholder="Усенов У.У."
                                               required>
                                    </div>
                                </div>
                            @endfor
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="label">@lang('main.count_adult')</div>
                                        <input type="text" name="adult" value="{{ $request->adult }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="label">@lang('main.count_child')</div>
                                        <input type="text" name="child" value="{{ $request->child }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">@lang('main.phone')</label>
                                        <input type="text" name="phone" id="phone" style="padding-left: 50px"
                                               value="{{ Auth::user()->phone }}"
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
{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group check">--}}
{{--                                        <input type="checkbox" name="checkin_request" id="late_checkin" value="1" {{ old('checkin_request') ? 'checked' : '' }}>--}}
{{--                                        <label for="late_checkin">@lang('main.late_checkin')</label>--}}
{{--                                        <select name="checkin_time" id="">--}}
{{--                                            <option>@lang('admin.choose')</option>--}}
{{--                                            @for ($hour = 06; $hour <= 14; $hour++)--}}
{{--                                                @php $time = sprintf('%02d:00', $hour); @endphp--}}
{{--                                                <option value="{{ $time }}" {{ old('checkin_time') == $time ? 'selected' : '' }}>{{ $time }}</option>--}}
{{--                                            @endfor--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                </div>--}}

{{--                                <div class="col-md-6">--}}
{{--                                    <div class="form-group check">--}}
{{--                                        <input type="checkbox" name="checkout_request" id="late_checkout" value="1" {{ old('checkout_request') ? 'checked' : '' }}>--}}
{{--                                        <label for="late_checkout">@lang('main.late_checkout')</label>--}}
{{--                                        <select name="checkout_time" id="">--}}
{{--                                            <option>@lang('admin.choose')</option>--}}
{{--                                            @for ($hour = 12; $hour <= 18; $hour++)--}}
{{--                                                @php $time = sprintf('%02d:00', $hour); @endphp--}}
{{--                                                <option value="{{ $time }}" {{ old('checkout_time') == $time ? 'selected' : '' }}>{{ $time }}</option>--}}
{{--                                            @endfor--}}
{{--                                        </select>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                    <script>--}}
{{--                                        document.addEventListener("DOMContentLoaded", function () {--}}
{{--                                            let checkin = document.getElementById('late_checkin');--}}
{{--                                            let checkinTime = document.querySelector('[name="checkin_time"]');--}}
{{--                                            checkinTime.style.display = checkin.checked ? 'block' : 'none';--}}
{{--                                            checkin.addEventListener('change', () => {--}}
{{--                                                checkinTime.style.display = checkin.checked ? 'block' : 'none';--}}
{{--                                            });--}}

{{--                                            let checkout = document.getElementById('late_checkout');--}}
{{--                                            let checkoutTime = document.querySelector('[name="checkout_time"]');--}}
{{--                                            checkoutTime.style.display = checkout.checked ? 'block' : 'none';--}}
{{--                                            checkout.addEventListener('change', () => {--}}
{{--                                                checkoutTime.style.display = checkout.checked ? 'block' : 'none';--}}
{{--                                            });--}}
{{--                                        });--}}
{{--                                    </script>--}}
                                <div class="col-md-12">
                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'comment'])
                                        <label for="">@lang('main.message')</label>
                                        <input type="text" name="comment">
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
                                    Code of Conduct, StayBook’s Rebooking and Refund Policy, Partial Prepayment Terms)
                                    and
                                    agree that StayBook may charge my payment method if I am responsible for any damage.
                                @endif
                            </div>
                            <div class="btn-wrap">
                                @hasrole('Demo')
                                <div class="alert alert-danger">Доступ ограничен</div>
                                @else
                                    <button class="more" id="saveBtn">@lang('main.confirm')</button>
                                    @endhasrole
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="timeoutModal" tabindex="-1" aria-labelledby="timeoutLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                    @if(app()->getLocale() == 'ru')
                        <div class="modal-header">
                            <h5 class="modal-title" id="timeoutLabel">Время истекло</h5>
                        </div>
                        <div class="modal-body">
                            Время бронирования истекло. Вы будете перенаправлены на поиск.
                        </div>
                    @else
                        <div class="modal-header">
                            <h5 class="modal-title" id="timeoutLabel">Time has expired</h5>
                        </div>
                        <div class="modal-body">
                            The booking time has expired. You will be redirected to the search page.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            setTimeout(function () {
                $('#timeoutModal').modal('show');
                setTimeout(function () {
                    window.location.href = "{{ route('index') }}";
                }, 4000);
            }, 600000);
        </script>
    @else
        @include('layouts.auth')
    @endauth

@endsection