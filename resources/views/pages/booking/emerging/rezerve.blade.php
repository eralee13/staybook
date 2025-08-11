@extends('layouts.head')

@section('title', 'Бронирование')
<span>/hotel/prebook/</span>
    @dump($preBook)
<span>/hotel/order/booking/form/</span>
    @dump($order)
<span>/hotel/order/booking/finish/</span>
    @dump($finish)
<span>/hotel/order/booking/finish/status/</span>
    @dump($finishStatus)
@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    @if($message == 'Бронирование успешно создано!' || $message == 'Этот бронь уже существует!')
                        <h1>@lang('main.Congratulations')</h1>

                            <div class="alert alert-primary" role="alert">
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
                        <li>
                            @if($request->refundable == true)
                                
                                    @lang('main.free_cancellation') {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }} 
                                    (UTC {{ $request->utc }})!
                                    
                                    @lang('main.cancellation_amount_tm'): {{ round($book->cancel_penalty) }} {{ $request->currency ?? '$' }}
                            @else
                                    @lang('main.non_refundable')
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
                    @if( isset($book->id) ) 
                        <div class="bnt-wrap">
                            <form action="{{ route('cancel_calculate_etg', $book->id) }}">
                                <input type="hidden" name="number" value="{{ $book->book_token }}">
                                <button class="more">@lang('main.cancel_booking')</button>
                            </form>
                            {{-- @if($message == 'Бронирование успешно создано!' || $message == 'Этот бронь уже существует!')
                                <button class="more primary" id="getStatus">Узнать статус брони</button>
                            @endif --}}
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

    {{-- <script>
        document.getElementById('getStatus').addEventListener('click', function() {
            fetch('{{ route('get.data') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    param: 'значение'
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                // вставь data куда нужно
            })
            .catch(error => console.error(error));
        });
    </script> --}}

@endsection
