@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @if(isset($cancel->errors))
                        @foreach ($cancel->errors as $error)
                            <div class="alert alert-danger">
                                <h5>{{ $error->code }}</h5>
                                <p style="margin-bottom: 0">{{ $error->message }}</p>
                            </div>
                        @endforeach
                    @else
                        <h1>{{ $cancel->booking->status }}</h1>
                        @php
                            $cancel_date = \Carbon\Carbon::createFromDate($cancel->booking->createdDateTime)->format('d.m.Y H:i');
                            $hotel = \App\Models\Hotel::where('exely_id', $cancel->booking->propertyId)->get()->first();
                            $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                        @endphp
                        <ul>
                            <li>@lang('main.booking_number'): {{ $cancel->booking->number }}</li>
                            {{--                            <li>Дата отмены: {{ $cancel_date }}</li>--}}
                            <li>
                                @if($cancel->booking->cancellationPolicy->freeCancellationPossible == true)
                                    <td>@lang('main.free_cancellation')
                                        ({{ $cancel->booking->cancellationPolicy->freeCancellationDeadlineLocal }}).
                                        @lang('main.cancellation_amount'): {{ $cancel->booking->cancellationPolicy->penaltyAmount }} {{ $cancel->booking->currencyCode }}</td>
                                @else
                                    <td>@lang('main.free_cancellation'). @lang('main.cancellation_amount'): {{ $cancel->booking->cancellationPolicy->penaltyAmount }} {{ $cancel->booking->currencyCode }}</td>
                            @endif
                            <li>@lang('main.hotel'): {{ $cancel->booking->propertyId }}</li>
                            @foreach($cancel->booking->roomStays as $room)
                                @php
                                    $arrival = \Carbon\Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                    $departure = \Carbon\Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                                @endphp
                                <li>@lang('main.check-in/check-out'): {{ $arrival }} - {{ $departure }} (UTC {{ $hotel_utc }})</li>
                                <li>@lang('main.room'): {{ $room->roomType->name }}</li>
                                <li>@lang('main.rate'): {{ $room->ratePlan->name }}</li>
                                <li>@lang('main.guest'): {{ $room->guestCount->adultCount }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
