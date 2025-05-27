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
                            <li>Номер брони: {{ $cancel->booking->number }}</li>
                            {{--                            <li>Дата отмены: {{ $cancel_date }}</li>--}}
                            <li>
                                @if($cancel->booking->cancellationPolicy->freeCancellationPossible == true)
                                    <td>Бесплатная отмена действует до
                                        ({{ $cancel->booking->cancellationPolicy->freeCancellationDeadlineLocal }}).
                                        Размер
                                        штрафа: {{ $cancel->booking->cancellationPolicy->penaltyAmount }} {{ $cancel->booking->currencyCode }}</td>
                                @else
                                    <td>Возможность бесплатной отмены отсутствует. Размер штрафа
                                        составляет: {{ $cancel->booking->cancellationPolicy->penaltyAmount }} {{ $cancel->booking->currencyCode }}</td>
                            @endif
                            <li>Отель: {{ $cancel->booking->propertyId }}</li>
                            @foreach($cancel->booking->roomStays as $room)
                                @php
                                    $arrival = \Carbon\Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                    $departure = \Carbon\Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                                @endphp
                                <li>Дата заеда/выезда: {{ $arrival }} - {{ $departure }} (UTC {{ $hotel_utc }})</li>
                                <li>Тариф: {{ $room->ratePlan->name }}</li>
                                <li>Тип комнаты: {{ $room->roomType->name }}</li>
                                <li>Кол-во гостей: {{ $room->guestCount->adultCount }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
