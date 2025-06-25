@extends('layouts.head')

@section('title', 'Ваша бронь отменена')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.booking_cancelled')</h1>
                    <div class="alert alert-danger">@lang('main.status'): {{ $book->status }}</div>
                    @php
                        $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
                        $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                        $arrival = \Carbon\Carbon::createFromDate($book->arrivalDate)->format('d.m.Y');
                        $departure = \Carbon\Carbon::createFromDate($book->departureDate)->format('d.m.Y');
                        $cancel = \App\Models\CancellationRule::where('id', $book->cancellation_id)->firstOrFail();
                        $cancelDate = \Carbon\Carbon::createFromDate(now())->format('d.m.Y H:i');

                        $room = \App\Models\Room::where('id', $book->room_id)->firstOrFail();
                        $rate = \App\Models\Rate::where('id', $book->rate_id)->firstOrFail();
                    @endphp
                    <ul>
                        <li>@lang('main.booking_number'): {{ $book->book_token }}</li>
                        <li>@lang('main.check-in/check-out'): {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel_utc }})</li>
                        {{--                            <li>Дата отмены: {{ $cancel_date }}</li>--}}
{{--                        <li>--}}
{{--                            @if($cancel->is_refundable == true)--}}
{{--                                <td>Бесплатная отмена действует до {{ $cancelDate }} (UTC {{ $hotel_utc }}). Размер--}}
{{--                                    штрафа: {{ $book->cancel_penalty }} {{ $order->booking->currencyCode ?? '$' }}</td>--}}
{{--                            @else--}}
{{--                                <td>Возможность бесплатной отмены отсутствует. Размер штрафа: {{ $book->cancel_penalty }} {{ $order->booking->currencyCode ?? '$' }}</td>--}}
{{--                        @endif--}}
{{--                        </li>--}}
                        <li>@lang('main.hotel'): {{ $hotel->__('title') }}</li>
                        <li>@lang('main.room'): {{ $room->__('title') }}</li>
                        <li>@lang('main.rate'): {{ $rate->__('title') }}</li>
                        <li>@lang('main.guest'): {{ $book->adult }} (взрос.) @if($book->child) и {{ $book->child }} (дет.) @endif</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>


@endsection
