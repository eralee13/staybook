@extends('layouts.master')

@section('title', 'Забронировать')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Бронь успешно прошла</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Забронировано</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @isset($res->booking)
                        <h1>Поздравляем!</h1>
                        <ul>
                            <li>Статус: {{ $res->booking->status }}</li>
                            <li>Номер брони: {{ $res->booking->number }}</li>
                            <li>ID отеля: {{ $res->booking->propertyId }}</li>
                            <li>Даты: {{ $res->booking->roomStays[0]->stayDates->arrivalDateTime }} - {{ $res->booking->roomStays[0]->stayDates->departureDateTime }}</li>
                            <li>Аннуляция брони до: {{ $res->booking->cancellationPolicy->freeCancellationDeadlineLocal }} - {{ $res->booking->cancellationPolicy->penaltyAmount }} {{ $res->booking->currencyCode }}</li>
                            <li>Заказчик: {{ $res->booking->customer->firstName }} {{ $res->booking->customer->lastName }}
                                <ul>
                                    <li>Номер телефона: {{ $res->booking->customer->contacts->phones[0]->phoneNumber }}</li>
                                    <li>Email: {{ $res->booking->customer->contacts->emails[0]->emailAddress }}</li>
                                    <li>Комментарий: {{ $res->booking->customer->comment }}</li>
                                </ul>
                            </li>
                        </ul>
                        <div class="bnt-wrap">
                            <form action="{{ route('res_calculate') }}">
                                <input type="hidden" name="number" value="{{ $res->booking->number }}">
                                @if($res->booking->cancellation == null)
                                    <input type="hidden" name="cancelTime" value="{{ $res->booking->cancellationPolicy->freeCancellationDeadlineUtc }}">
                                @endif
                                <button class="more">Отменить бронь</button>
                            </form>
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
