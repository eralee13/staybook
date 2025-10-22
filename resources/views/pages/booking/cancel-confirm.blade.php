@extends('layouts.master')

@section('title', 'Ваша бронь отменена')

@section('content')

    @auth
        <div class="page order">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.booking_cancelled')</h1>
                        <div class="alert alert-danger">@lang('main.status'): {{ $book->status }}</div>

                        @php
                            // Модели (падаем только на критичных сущностях)
                            $hotel = \App\Models\Hotel::findOrFail($book->hotel_id);
                            $room  = \App\Models\Room::findOrFail($book->room_id);
                            $rate  = \App\Models\Rate::findOrFail($book->rate_id);

                            // TZ/UTC
                            $hotelTz   = $hotel->timezone ?: 'UTC';
                            $hotel_utc = \Carbon\Carbon::now($hotelTz)->format('P');

                            // Даты в TZ отеля
                            $arrivalCarbon   = \Carbon\Carbon::parse($book->arrivalDate, $hotelTz)->timezone($hotelTz);
                            $departureCarbon = \Carbon\Carbon::parse($book->departureDate, $hotelTz)->timezone($hotelTz);
                            $arrival   = $arrivalCarbon->format('d.m.Y');
                            $departure = $departureCarbon->format('d.m.Y');

                            // Политика отмены может отсутствовать — НЕ падаем
                            $cancel = \App\Models\CancellationRule::find($book->cancellation_id)
                                      ?: \App\Models\CancellationRule::where('rate_id', $rate->id)->first();

                            // Время фактической отмены (сейчас) в TZ отеля
                            $cancelDate = \Carbon\Carbon::now($hotelTz)->format('d.m.Y H:i');
                        @endphp

                        <ul>
                            <li>@lang('main.booking_number'): {{ $book->book_token }}</li>
                            <li>
                                @lang('main.check-in/check-out'):
                                {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }}
                                (UTC {{ $hotel_utc }})
                            </li>

                            {{-- Сообщение об условиях отмены, если нужно показывать --}}
                            @if($cancel)
                                <li>
                                    @if($cancel->is_refundable)
                                        @lang('main.cancellation_policy'): @lang('main.free_cancellation') {{ $arrivalCarbon->format('d.m.Y H:i') }} (UTC {{ $hotel_utc }}).
                                        @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $book->currency }}
                                    @else
                                        @lang('main.cancellation_policy'): @lang('main.cancellation_is_not_avaialble').
                                        @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $book->currency }}
                                    @endif
                                </li>
                            @else
                                <li>
                                    @lang('main.cancellation_policy'): @lang('main.cancellation_rule_not_found').
                                    @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $book->currency }}
                                </li>
                            @endif
                            <li>@lang('main.hotel'): {{ $hotel->__('title') }}</li>
                            <li>@lang('main.room'): {{ $room->__('title') }}</li>
                            <li>@lang('main.rate'): {{ $rate->__('title') }}</li>
                            <li>
                                @lang('main.guest'):
                                {{ $book->adult }} @lang('main.adult')
                                @if($book->child) @lang('main.and') {{ $book->child }} @lang('main.child') @endif
                            </li>
                            <li>@lang('main.cancelled_at'): {{ $cancelDate }} (UTC {{ $hotel_utc }})</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('layouts.auth')
    @endauth

@endsection
