@extends('layouts.master')

@php
    use App\Models\Hotel;
            use App\Models\Room;
            use App\Models\Rate;
            use App\Models\CancellationRule;
            use Carbon\Carbon;
    @endphp

@section('title', 'Оформление заказа')

@section('content')

    @auth
        @php

            // --- Lookups ---
            $hotel = Hotel::where('exely_id', $request->propertyId)
                          ->orWhere('id', $request->propertyId)
                          ->firstOrFail();

            $room  = Room::findOrFail($request->room_id);
            $rate  = Rate::findOrFail($request->rate_id);

            // Cancellation can be absent — don't throw
            $cancel = CancellationRule::find($request->cancellation_id);

            // --- Time & formatting helpers ---
            $hotelTz   = $hotel->timezone ?: 'UTC';

            $arrivalCarbon   = Carbon::parse($request->arrivalDate)->timezone($hotelTz);
            $departureCarbon = Carbon::parse($request->departureDate)->timezone($hotelTz);

            $arrival   = $arrivalCarbon->format('d.m.Y');
            $departure = $departureCarbon->format('d.m.Y');

            $hotel_utc = Carbon::now($hotelTz)->format('P');   // e.g. +06:00
            $timezone  = Carbon::now($hotelTz)->format('P');

            $freeDateCarbon = Carbon::parse($request->arrivalDate, $hotelTz);
            $freeDate       = $freeDateCarbon->format('d.m.Y H:i');

            $cancelCutoffCarbon = null;
            $cancelDate         = null;
            if ($cancel) {
                $days = (int) ($cancel->free_cancellation_days ?? 0);
                $cancelCutoffCarbon = Carbon::parse($request->arrivalDate, $hotelTz)->subDays($days);
                $cancelDate = $cancelCutoffCarbon->format('d.m.Y H:i');
            }

            // Child ages guard
            $childAges = is_array($request->childAges) ? $request->childAges : (empty($request->childAges) ? [] : explode(',', (string)$request->childAges));
        @endphp

        <style>
            .check input{ width: auto; }
            body{
                font-family: Unbounded, sans-serif !important;
                background-color: rgba(246, 246, 246, 1) !important;
            }
            .page{ padding-bottom: 60px; }
        </style>

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
                            @if($hotel->image)
                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                            @else
                                <img src="{{ route('index')}}/img/noimage.png" alt="">
                            @endif
                            <div class="text-wrap">
                                <div class="descr">@lang('main.hotel'): {{ $hotel->__('title') }}</div>
                                <div class="descr">@lang('main.room'): {{ $room->__('title') }}</div>
                                <div class="descr">@lang('main.rate'): {{ $rate->__('title') }}</div>

                                <div class="date">
                                    @lang('main.check-in/check-out'):
                                    {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }}
                                    (UTC {{ $hotel_utc }})
                                </div>

                                <div class="cancel">
                                    @lang('main.cancellation_policy'):
                                    @if(!$cancel)
                                        @lang('main.cancellation_rule_not_found').
                                        {{ $request->cancelPrice }} {{ $request->currency }}
                                    @elseif($cancel->cancel_policy === 'free_until_checkin')
                                        @lang('main.free_cancellation') {{ $freeDate }} UTC {{ $timezone }}
                                    @elseif($cancel->cancel_policy === 'free_then_penalty')
                                        @if(now($hotelTz)->lte($cancelCutoffCarbon))
                                            @lang('main.free_cancellation') {{ $cancelDate }} UTC {{ $timezone }}
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
                                        <div class="price">{{ round($request->sum) }} {{ $request->currency }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 col-md-12">
                        <h5>@lang('main.trip')</h5>

                        <form action="{{ route('book_verify') }}">
                            @csrf
                            <input type="hidden" name="propertyId" value="{{ $request->propertyId }}">
                            <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                            <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                            <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                            <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                            <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                            <input type="hidden" name="roomCount" value="{{ $request->roomCount ?? 1 }}">

                            {{-- Child ages as multiple inputs --}}
                            @foreach ($childAges as $age)
                                <input type="hidden" name="childAges[]" value="{{ $age }}">
                            @endforeach

                            <input type="hidden" name="cancellation_id" value="{{ $request->cancellation_id }}">
                            <input type="hidden" name="cancelDate" value="{{ $request->cancelDate }}">
                            <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                            <input type="hidden" name="cancelPriceSource" value="{{ round($request->cancelPriceSource) }}">
                            <input type="hidden" name="price" value="{{ $request->price }}">
                            <input type="hidden" name="sum" value="{{ round($request->sum) }}">
                            <input type="hidden" name="currency" value="{{ $request->currency }}">
                            <input type="hidden" name="source_sym" value="{{ $request->source_sym }}">

                            @for ($i = 1; $i <= (int)$request->adult; $i++)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>
                                            @if($i === 1)
                                                @lang('main.full_name')
                                            @else
                                                #{{ $i }} @lang('main.full_name')
                                            @endif
                                        </label>
                                        <input
                                                type="text"
                                                name="title{{ $i }}"
                                                placeholder="@if($i === 1) @lang('main.full_name') @else #{{ $i }} @lang('main.full_name') @endif"
                                                value="{{ $i === 1 && Auth::check() ? Auth::user()->name : '' }}"
                                                required
                                        >
                                    </div>
                                </div>
                            @endfor

                            @for ($i = 1; $i <= (int)$request->child; $i++)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="label">
                                            @if($i === 1)
                                                @lang('main.full_name') @lang('main.child')
                                            @else
                                                #{{ $i }} @lang('main.full_name') @lang('main.child')
                                            @endif
                                        </div>
                                        <input type="text" name="child_name{{ $i }}" placeholder="Усенов У.У." required>
                                    </div>
                                </div>
                            @endfor

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="label">@lang('main.count_adult')</div>
                                        <input type="text" name="adult" value="{{ (int)$request->adult }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="label">@lang('main.count_child')</div>
                                        <input type="text" name="child" value="{{ (int)$request->child }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('main.phone')</label>
                                        <input
                                                type="text"
                                                name="phone"
                                                id="phone"
                                                style="padding-left: 50px"
                                                value="{{ Auth::user()->phone ?? '' }}"
                                                required
                                        >
                                        <div id="output"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" value="{{ Auth::user()->email ?? '' }}" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'comment'])
                                        <label>@lang('main.message')</label>
                                        <input type="text" name="comment">
                                    </div>
                                </div>
                            </div>

                            <div class="line"></div>

                            <div class="descr">
                                @if(app()->getLocale() == 'ru')
                                    Нажимая кнопку ниже, я принимаю условия (Правила дома, установленные хозяином,
                                    Основные правила для гостей, Правила StayBook в отношении повторного бронирования и возврата средств,
                                    Условия частичной предоплаты) и соглашаюсь, что StayBook может списать средства с моего способа оплаты,
                                    если ответственность за ущерб лежит на мне.
                                @else
                                    By clicking the button below, I accept the terms (House Rules set by the Host, Guest
                                    Code of Conduct, StayBook’s Rebooking and Refund Policy, Partial Prepayment Terms)
                                    and agree that StayBook may charge my payment method if I am responsible for any damage.
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

        {{-- Timeout Modal --}}
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

        {{-- Bootstrap 5 (no jQuery needed) --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            setTimeout(function () {
                const modal = new bootstrap.Modal(document.getElementById('timeoutModal'));
                modal.show();
                setTimeout(function () {
                    window.location.href = "{{ route('index') }}";
                }, 4000);
            }, 600000);
        </script>
    @else
        @include('layouts.auth')
    @endauth

@endsection
