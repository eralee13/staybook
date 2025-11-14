@extends('layouts.master')

@section('title', 'Бронирование')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    @if($message == 'Booking successfully created' 
                        || $message == 'This booking already exists' 
                        || $message == 'Booking is pending confirmation from the hotel'
                            )
                        <h1>@lang('main.congratulations')!</h1>
                            <div class="alert alert-primary" role="alert">
                                <strong>@lang('main.'.$message)</strong>
                            </div>
                    @elseif($message == 'Timeout waiting for valid response')
                        <h1>@lang('main.timeout')</h1>
                        <div class="alert alert-info" role="alert">
                            <strong>@lang('main.'.$message)</strong>
                        </div>
                    @else
                        <h1>@lang('main.Booking error')</h1>
                        <div class="alert alert-danger" role="alert">
                            <strong>@lang('main.'.$message)</strong>
                        </div>
                    @endif
                    @if( isset($book->id) ) 
                        <ul>
                            <li>@lang('main.status'): @lang('main.' . $book->status ?? $message)</li>
                            <li>@lang('main.booking_number'): {{ $book->id ?? ''}}</li>
                            <li>@lang('main.hotel'): {{ $request->hotel_id ?? ''}}</li>
                            <li>
                                @lang('main.dates'): {{ Carbon\Carbon::createFromDate($request->arrivalDate)->format('d.m.Y') }} {{$hotel->checkin ?? ''}} 
                                - {{ Carbon\Carbon::createFromDate($request->departureDate)->format('d.m.Y') }} {{ $hotel->checkout ?? ''}}
                                (UTC {{ $request->utc }})
                            </li>
                            <li>
                                @if($request->refundable == true)
                                    
                                        @lang('main.free_cancellation') {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }} 
                                        (UTC {{ $request->utc }})!
                                        
                                        @lang('main.cancellation_amount_tm'): {{ round($book->cancel_penalty) }} {{ $request->currency ?? '$' }}
                                @else
                                        @lang('main.non_refundable')
                                @endif
                            <li>
                                @lang('main.guest'): {{ $request->name ? $book->title : '' }}
                                <ul>
                                    <li>@lang('main.phone'): {{ $request->phone ?? '' }}</li>
                                    <li>
                                        Email: {{ $request->email ?? '' }}</li>
                                    <li>@lang('main.message'): {{ $request->comment ?? '' }}</li>
                                </ul>
                            </li>
                        </ul>
                        <div class="btn-wrap">
                            <div class="row">
                                <div class="col-md-6">
                                    @if($book->status != 'Cancelled')
                                        <form action="{{ route('cancel_calculate_etg', $book->id) }}">
                                            <input type="hidden" name="number" value="{{ $book->book_token }}">
                                            <button class="more">@lang('main.cancel_booking')</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <button class="more" onclick="location.href='{{ route('index') }}'">
                                        @lang('main.go_home')
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bnt-wrap">
                            <button class="more" onclick="location.href='{{ route('index') }}'">
                                @lang('main.go_home')
                            </button>
                        </div>
                    @endif
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

        .page form{
            margin-top: 0;
            margin-bottom: 0;
        }
        .page .bnt-wrap {
            display: inline-flex;
            gap: 20px;
            margin-top: 50px;
        }

        .page form button {
            width: auto;
            padding: 10px 30px;
            margin-left: 10px;
        }
    </style>
    <script>
        document.getElementById('order').addEventListener('click', function() {
            localStorage.removeItem('booking_etg_secondsLeft'); // Очистить данные
        });
    </script>

@endsection
