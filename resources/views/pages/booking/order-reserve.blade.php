@extends('layouts.head')

@section('title', 'Бронь оформлена')

@section('content')

    @auth
    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <h1>@lang('main.congratulations')!</h1>
                    <ul>
                        <li>@lang('main.status'): {{ $book->status }}</li>
                        <li>@lang('main.booking_number'): {{ $book->id }}</li>
                        <li>ID @lang('main.hotel'): {{ $book->hotel_id }}</li>
                        <li>@lang('main.price'): {{ $book->sum }} {{ $book->currency }}</li>
                        @php
                            $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
                            $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                            $rate = \App\Models\Rate::where('id', $book->rate_id)->firstOrFail();
                            $arrival = \Carbon\Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
                            $departure = \Carbon\Carbon::createFromDate($book->departureDate)->format('d.m.Y');
                            $cancel = \App\Models\CancellationRule::where('id', $book->cancellation_id)->firstOrFail();
                            $cancelDate = \Carbon\Carbon::parse($arrival)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
                            $freeDate = \Carbon\Carbon::parse($book->arrivalDate)->format('d.m.Y H:i');
                            $timezone = \Carbon\Carbon::parse($hotel->timezone)->format('P');
                        @endphp

                        <li>@lang('main.dates'): {{ $arrival }} {{$hotel->checkin}} - {{ $departure }} {{ $hotel->checkout }}
                            (UTC {{ $hotel_utc }})
                        </li>
                        <li>
                            @if($cancel->cancel_policy === 'free_until_checkin')
                                <td>@lang('main.free_cancellation') {{ $freeDate }}
                                    UTC {{ $timezone }}</td>
                            @elseif($cancel->cancel_policy === 'free_then_penalty')
                                @if(now()->lte($cancelDate))
                                    <td> @lang('main.free_cancellation') {{ $cancelDate }}
                                        UTC {{ $timezone }}</td>
                                @else
                                    <td>@lang('main.cancellation_is_not_avaialble'). @lang('main.cancellation_amount') {{ $book->cancel_penalty }} {{ $book->currency }}</td>
                                @endif
                            @else
                                <td>@lang('main.cancellation_amount')
                                    : {{ $book->cancel_penalty }} {{ $book->currency }}</td>
                        @endif
                        <li>
                            @lang('main.guest'): {{ $book->title }}
                            <ul>
                                <li>@lang('main.phone'): {{ $book->phone }}</li>
                                <li>
                                    Email: {{ $book->email }}</li>
                                @if($book->message)
                                    <li>@lang('main.message'): {{ $book->message }}</li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                    <div class="bnt-wrap">
                        <form action="{{ route('cancel_calculate', $book->id) }}">
                            <input type="hidden" name="number" value="{{ $res->booking->number ?? $book->book_token }}">
                            <input type="hidden" name="currency" value="{{ $request->currency }}">
                            <input type="hidden" name="cancelTime" value="{{ $cancelDate }}">
                            <button class="more">@lang('main.cancel_booking')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
        @include('layouts.auth')
    @endauth

@endsection
