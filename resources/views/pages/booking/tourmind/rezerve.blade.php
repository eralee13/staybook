@extends('layouts.head')

@section('title', 'Бронирование')

@section('content')

@php
    $symbols = [
        'USD' => '$',
        'RUB' => '₽',
        'KGS' => 'сом',
        'UZS' => 'сўм',
    ];
@endphp

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    @if($key == '1' || $key == '2')

                        <h1>@lang('main.Congratulations')</h1>
                            <div class="alert alert-primary" role="alert">
                                <strong>@lang('main.'.$message)</strong>
                            </div>

                    @elseif($key == '3')
                    
                        <h1>@lang('main.Booking is pending')</h1>
                            <div class="alert alert-primary" role="alert">
                                <strong>@lang('main.'.$message)</strong>
                            </div>

                    @elseif($key == '4')

                        <h1>@lang('main.Booking has been cancelled')</h1>
                            <div class="alert alert-info" role="alert">
                                <strong>@lang('main.'.$message)</strong>
                            </div>
                    @else

                        <h1>@lang('main.Booking error')</h1>
                            <div class="alert alert-danger" role="alert">
                                <strong>@lang('main.'.$message)</strong>
                            </div>
                    @endif
    
                    <ul>
                        <li>@lang('main.status'): @lang('main.' . $book->status ?? $message)</li>
                        <li>@lang('main.booking_number'): {{ $book->id ?? ''}}</li>
                        <li>@lang('main.hotel_id'): {{ $request->hotel_id ?? ''}}</li>
                        <li>
                            @lang('main.dates'): {{ Carbon\Carbon::createFromDate($request->arrivalDate)->format('d.m.Y') }} {{$hotel->checkin ?? ''}} 
                            - {{ Carbon\Carbon::createFromDate($request->departureDate)->format('d.m.Y') }} {{ $hotel->checkout ?? ''}}
                            (UTC {{ $request->utc }})
                        </li>
                        <li>@lang('main.price'): {{ round($book->sum) }} {{ $symbols[$book->currency] }}</li>
                        <li>
                            @if($request->refundable == true)
                                
                                    @lang('main.free_cancellation') {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }} 
                                    (UTC {{ $request->utc }})!
                                    
                                    @lang('main.cancellation_amount_tm'): {{ round($book->cancel_penalty) }} {{ $symbols[$book->currency] ?? '$' }}
                            @else
                                @lang('main.cancellation_is_not_avaialble')
                            @endif
                        <li>
                            @lang('main.сustomer'): {{ $request->name ? $book->title : '' }}
                            <ul>
                                <li>@lang('main.phone'): {{ $request->phone ?? '' }}</li>
                                <li>
                                    Email: {{ $request->email ?? '' }}</li>
                                <li>@lang('main.comment'): {{ $request->comment ?? '' }}</li>
                            </ul>
                        </li>
                    </ul>
                    <style>.bnt-wrap{display: flex; gap: 20px; margin-top: 50px;} .bnt-wrap form{margin-top: 0!important;}</style>   
                        <div class="bnt-wrap">
                            <button class="more" onclick="window.location.reload()">@lang('main.check_status')</button>
                            @if( isset($book->id) ) 
                                <form action="{{ route('cancel_calculate_tm', $book->id) }}">
                                    <input type="hidden" name="number" value="{{ $book->book_token }}">
                                    <button class="more">@lang('main.cancel_booking')</button>
                                </form>
                            @endif
                        </div>
            
                </div>
            </div>
        </div>
    </div>

    <style>
        .page #map {
            margin-top: 20px;
        }

        .page i {
            color: darkblue;
        }

        .page form {
            margin-top: 50px;
        }

        .page form button {
            width: auto;
            padding: 10px 30px;
            margin-left: 10px;
        }
    </style>

@endsection
