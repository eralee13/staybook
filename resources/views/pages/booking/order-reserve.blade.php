@extends('layouts.master')

@section('title', 'Бронь оформлена')

@section('content')

    @auth
        <div class="page order">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <h1>@lang('main.congratulations')!</h1>
                        <div class="wrap">
                            <ul>
                                <li><b style="font-size: 20px">{{ $book->status }}</b></li>
                                <li>@lang('main.booking_number'): {{ $book->id }}</li>
                                <li>ID @lang('main.hotel'): {{ $book->hotel_id }}</li>
                                <li>@lang('main.price'): {{ $book->sum }} {{ $book->currency }}</li>

                                @php
                                    // Модели
                                    $hotel = \App\Models\Hotel::findOrFail($book->hotel_id);
                                    $rate  = \App\Models\Rate::findOrFail($book->rate_id);

                                    // Часовой пояс и UTC-смещение
                                    $hotelTz   = $hotel->timezone ?: 'UTC';
                                    $hotel_utc = \Carbon\Carbon::now($hotelTz)->format('P');
                                    $timezone  = \Carbon\Carbon::now($hotelTz)->format('P');

                                    // Даты в TZ отеля
                                    $arrivalCarbon   = \Carbon\Carbon::parse($book->arrivalDate, $hotelTz)->timezone($hotelTz);
                                    $departureCarbon = \Carbon\Carbon::parse($book->departureDate, $hotelTz)->timezone($hotelTz);
                                    $arrival   = $arrivalCarbon->format('d.m.Y');
                                    $departure = $departureCarbon->format('d.m.Y');

                                    // Политика отмены: сначала по id, иначе по тарифу, иначе null
                                    $cancel = \App\Models\CancellationRule::find($book->cancellation_id)
                                              ?: \App\Models\CancellationRule::where('rate_id', $rate->id)->first();

                                    $freeDate  = $arrivalCarbon->format('d.m.Y H:i'); // бесплатная до чек-ина
                                    $cancelCutoffCarbon = $cancel
                                        ? \Carbon\Carbon::parse($book->arrivalDate, $hotelTz)
                                            ->subDays((int)($cancel->free_cancellation_days ?? 0))
                                        : null;
                                    $cancelDate = $cancelCutoffCarbon ? $cancelCutoffCarbon->format('d.m.Y H:i') : null;
                                @endphp

                                <li>
                                    @lang('main.dates'): {{ $arrival }} {{ $hotel->checkin }}
                                    - {{ $departure }} {{ $hotel->checkout }}
                                    (UTC {{ $hotel_utc }})
                                </li>

                                <li>
                                    <span>@lang('main.cancellation_policy')</span>:
                                    @if(!$cancel)
                                        @lang('main.cancellation_rule_not_found').
                                        @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $book->currency }}
                                    @elseif($cancel->cancel_policy === 'free_until_checkin')
                                        @lang('main.free_cancellation') {{ $freeDate }} UTC {{ $timezone }}
                                    @elseif($cancel->cancel_policy === 'free_then_penalty')
                                        @if($cancelCutoffCarbon && now($hotelTz)->lte($cancelCutoffCarbon))
                                            @lang('main.free_cancellation') {{ $cancelDate }} UTC {{ $timezone }}
                                        @else
                                            @lang('main.cancellation_is_not_avaialble') .
                                            @lang('main.cancellation_amount') {{ $book->cancel_penalty }} {{ $book->currency }}
                                        @endif
                                    @else
                                        @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $book->currency }}
                                    @endif
                                </li>

                                <li>
                                    @lang('main.guest'): {{ $book->title }}
                                    <ul>
                                        <li>@lang('main.phone'): {{ $book->phone }}</li>
                                        <li>Email: {{ $book->email }}</li>
                                        @if($book->message)
                                            <li>@lang('main.message'): {{ $book->message }}</li>
                                        @endif
                                    </ul>
                                </li>
                            </ul>

                            <div class="btn-wrap">
                                <form action="{{ route('cancel_calculate', $book->id) }}">
                                    <input type="hidden" name="number" value="{{ $res->booking->number ?? $book->book_token }}">
                                    <input type="hidden" name="currency" value="{{ $book->currency }}">
                                    <input type="hidden" name="cancelTime" value="{{ $cancelDate }}">
                                    {{-- Если нужен ISO-безопасный формат для бэкенда: --}}
                                    {{-- <input type="hidden" name="cancelTime" value="{{ optional($cancelCutoffCarbon)->toIso8601String() }}"> --}}
                                    <button class="more">@lang('main.cancel_booking')</button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('layouts.auth')
    @endauth

@endsection
