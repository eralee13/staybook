@php use App\Models\Hotel; @endphp
@extends('layouts.master')

@section('title', 'Оформление заказа')

@section('content')
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

    <style>
        body {
            font-family: Unbounded, sans-serif;
            background-color: rgba(246, 246, 246, 1) !important;
            font-weight: 300 !important;
        }

        .page {
            padding-bottom: 60px;
        }
    </style>
    @auth
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
                        <div class="sidebar">
                            @if($hotel->image)
                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                            @else
                                <img src="{{ route('index')}}/img/noimage.png" alt="">
                            @endif
                            <div class="text-wrap">
                                <div class="descr">@lang('main.hotel') {{ $hotel->title }}</div>
                                <div class="descr">{{ $request->categoryName }}</div>
                                <div class="date">@lang('main.check-in/check-out')
                                    : {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel_utc }})
                                </div>
                                <div class="cancel">
                                    @if($request->cancelPossible == true)
                                        @lang('main.free_cancellation') {{ $request->cancelDate }} ({{ $offset }}).
                                        @lang('main.cancellation_amount')
                                        : {{ round($request->cancelPrice) }} {{ $request->currency }}
                                    @else
                                        @lang('main.cancellation_is_not_avaialble')
                                        . @lang('main.cancellation_amount')
                                        : {{ round($request->cancelPrice) }} {{ $request->currency }}
                                    @endif
                                </div>
                                <div class="row mt">
                                    <div class="col-md-8 col-8">
                                        <div class="total">@lang('main.total')</div>
                                    </div>

                                    <div class="col-md-4 col-4">
                                        <div class="price">{{ $request->sum }} {{ $request->currency }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-12">
                        <h5>@lang('main.trip')</h5>
                        <form action="{{ route('book_verify_exely') }}">
                            @php
                                $hotel = Hotel::where('exely_id', $request->propertyId)->first();
                            @endphp
                            <input type="hidden" name="propertyId" value="{{ $request->propertyId }}">
                            <input type="hidden" name="hotel_id" value="{{ $hotel->id}}">
                            <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                            <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                            <input type="hidden" name="ratePlanId" value="{{ $request->ratePlanId }}">
                            <input type="hidden" name="roomTypeId" value="{{ $request->roomTypeId }}">
                            <input type="hidden" name="adultCount" value="{{ $request->adultCount }}">
                            <input type="hidden" name="placements" value="{{ $request->placements }}">
                            <input type="hidden" name="net_price" value="{{ $request->price }}">
                            <input type="hidden" name="brut_price" value="{{ $request->sum }}">
                            <input type="hidden" name="cancel_net_price" value="{{ $request->cancelPriceSource }}">
                            <input type="hidden" name="cancel_brut_price" value="{{ $request->cancelPrice }}">
                            <input type="hidden" name="currency" value="{{ $request->currency }}">
                            <input type="hidden" name="source_sym" value="{{ $request->source_sym }}">
                            @if (request()->filled('childAges'))
                                <input type="hidden" name="childAges[]" value="{{ implode(',', $childs) }}">
                            @endif
                            <input type="hidden" name="checkSum" value="{{ $request->checkSum }}">
                            <input type="hidden" name="servicesId" value="{{ $request->servicesId }}">
                            @for ($i = 1; $i <= $request->adultCount; $i++)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="label">
                                            @if($i === 1)
                                                @lang('main.full_name')
                                            @else
                                                #{{ $i }} @lang('main.full_name')
                                            @endif
                                        </div>
                                        <input type="text" name="title{{ $i }}" placeholder="Асанов А.А."
                                               value=""
                                               required>
                                    </div>
                                </div>
                            @endfor
                            @for ($i = 1; $i <= count($childs); $i++)
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
                                        <input type="text" value="{{ $request->adultCount }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="label">@lang('main.count_child')</div>
                                        @if (request()->filled('childAges'))
                                            <input type="text" value="{{ count($childs) }}" readonly>
                                        @else
                                            <input type="text" value="0" readonly>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">@lang('main.phone')</label>
                                        <input type="text" name="phone" id="phone" value=""
                                               required>
                                        <div id="output"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">Email</label>
                                        <input type="email" name="email" value="" required>
                                    </div>
                                </div>
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
                            {{--                                <h5>Варианты оплаты</h5>--}}
                            {{--                                <div class="method-item current">--}}
                            {{--                                    <div class="name">Оплатить--}}
                            {{--                                        сейчас {{ $request->price }} {{ $request->currency ?? '$' }}</div>--}}
                            {{--                                </div>--}}
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
                            @if(app()->getLocale() == 'ru')
                                <p>Нажимая кнопку ниже, я принимаю условия (Правила отеля, установленные
                                    отельером, Основные правила для гостей, Правила StayBook в отношении повторного
                                    бронирования и возврата средств, Условия частичной предоплаты) и соглашаюсь, что
                                    StayBook может
                                    списать средства с моего способа оплаты, если ответственность за ущерб лежит на мне.</p>
                            @else
                                <p>By clicking the button below, I accept the terms (House Rules set by the Host, Guest
                                    Code of Conduct, StayBook’s Rebooking and Refund Policy, Partial Prepayment Terms) and
                                    agree that StayBook may charge my payment method if I am responsible for any damage.</p>
                            @endif
                            <div class="btn-wrap">
                                @hasrole('Demo')
                                <div class="alert alert-danger">
                                    @if(app()->getLocale() == 'ru')
                                    Доступ ограничен
                                    @else
                                        Access is restricted
                                    @endif
                                </div>
                                @else
                                    <button class="more" id="saveBtn">@lang('main.confirm')</button>
                                    @endhasrole
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

    @else
        @include('layouts.auth')
    @endauth

@endsection
