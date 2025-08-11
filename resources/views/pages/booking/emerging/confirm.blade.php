@extends('layouts.head')

@section('title', 'Подтверждение отмены брони')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    
                    @if($book->status == 'Cancelled')
                        <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.booking_cancelled')</h1>
                        <div class="alert alert-success">@lang('main.status'): @lang('main.' . $book->status)</div>
                    @else
                        <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.error_book_not_cancelled')</h1>
                        <p>@lang('main.error_book_not_cancelled_description')</p>
                        <div class="alert alert-danger">@lang('main.status'): @lang('main.' . $book->status)</div>
                    @endif
                    <ul>
                        <li>@lang('main.booking_number'): {{ $book->id }}</li>
                        {{--                            <li>Дата отмены: {{ $cancel_date }}</li>--}}
                        <li>
                            @if(isset($cancelRule->is_refundable) && $cancelRule->is_refundable == true)
                                <td>@lang('main.free_cancellation') {{ $cancelRule->end_date }} (UTC {{ $hotel->utc }}). <br>
                                    @lang('main.cancellation_amount_tm'): {{ $book->cancel_penalty }} {{ $book->currency ?? '$' }}</td>
                            @else
                                @lang('main.non_refundable'): {{ $book->cancel_penalty }} {{ $book->currency ?? 'USD' }}
                            @endif
                        <li>@lang('main.hotel'): {{ $hotel->title }}</li>
                        <li>@lang('main.date_checkin/checkout'): {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel->utc }})</li>
                        <li>@lang('main.room_type'): {{ $room->title ?? ''}}</li>
                        <li>@lang('main.rate'): {{ $rate->title ?? ''}}</li>
                        <li>@lang('main.count_adult'): {{ $book->adult ?? ''}}</li>
                    </ul>

                    <a href="{{ route('index') }}" class="more btn">@lang('main.go_home')</a>
                </div>
            </div>
        </div>
    </div>


@endsection