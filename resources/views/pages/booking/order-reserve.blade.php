@extends('layouts.head')

@section('title', 'Бронь оформлена')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <h1>@lang('main.congratulations')!</h1>
                    <ul>
                        <li>@lang('main.status'): {{ $res->booking->status ?? $book->status }}</li>
                        <li>@lang('main.booking_number'): {{ $res->booking->number ?? $book->id }}</li>
                        <li>ID @lang('main.hotel'): {{ $res->booking->propertyId ?? $book->hotel_id }}</li>
                        @php
                            $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
                            $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                            $arrival = \Carbon\Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
                            $departure = \Carbon\Carbon::createFromDate($book->departureDate)->format('d.m.Y');
                            $cancel = \App\Models\CancellationRule::where('rate_id', $book->cancellation_id)->firstOrFail();
                            $cancelDate = \Carbon\Carbon::parse($arrival)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
                        @endphp

                        <li>@lang('main.dates'): {{ $arrival }} {{$hotel->checkin}} - {{ $departure }} {{ $hotel->checkout }}
                            (UTC {{ $hotel_utc }})
                        </li>
                        <li>
                            @if($cancel->is_refundable == true)
                                <td>
                                    @if(now() <= $cancelDate)
                                        @lang('main.free_cancellation') {{ $cancelDate }} (UTC {{ $hotel_utc }}).
                                    @endif
                                        @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $order->booking->currencyCode ?? '$' }}</td>
                            @else
                                <td>@lang('main.free_cancellation'). @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $order->booking->currencyCode ?? '$' }}</td>
                        @endif
                        <li>
                            @lang('main.guest'): {{ $book->title }}
                            <ul>
                                <li>@lang('main.phone'): {{ $res->booking->customer->contacts->phones[0]->phoneNumber ?? $book->phone }}</li>
                                <li>
                                    Email: {{ $res->booking->customer->contacts->emails[0]->emailAddress ?? $book->email }}</li>
                                <li>@lang('main.message'): {{ $res->booking->customer->comment ?? $book->email }}</li>
                            </ul>
                        </li>
                    </ul>
                    <div class="bnt-wrap">
                        <form action="{{ route('cancel_calculate', $book->id) }}">
                            <input type="hidden" name="number" value="{{ $res->booking->number ?? $book->book_token }}">
                            <input type="hidden" name="currency" value="{{ $res->booking->currencyCode ?? '$' }}">
                            <input type="hidden" name="cancelTime"
                                   value="{{ $res->booking->cancellationPolicy->freeCancellationDeadlineUtc ?? $cancelDate }}">
                            <button class="more">@lang('main.cancel_booking')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
