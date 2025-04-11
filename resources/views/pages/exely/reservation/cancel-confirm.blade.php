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
                        <h1 data-aos="fade-up" data-aos-duration="2000">Ваша бронь отменена</h1>
                        <h2>{{ $cancel->booking->status }}</h2>
                        <ul>
                            <li>Номер брони: {{ $cancel->booking->number }}</li>
                            <li>Дата: {{ $cancel->booking->createdDateTime }}</li>
                            <li>
                                Аннуляция: {{ $cancel->booking->cancellation->reason }} {{ $cancel->booking->cancellation->penaltyAmount }}</li>
                            <li>Отель: {{ $cancel->booking->propertyId }}</li>
                            @foreach($cancel->booking->roomStays as $room)
                                <li>Дата заеда: {{ $room->stayDates->arrivalDateTime }}</li>
                                <li>Дата вызеда: {{ $room->stayDates->departureDateTime }}</li>
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
