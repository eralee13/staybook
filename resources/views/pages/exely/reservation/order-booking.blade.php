@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')


    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @if(isset($res->errors))
                        @foreach ($res->errors as $error)
                            <div class="alert alert-danger">
                                <h5>{{ $error->code }}</h5>
                                <p style="margin-bottom: 0">{{ $error->message }}</p>
                            </div>
                        @endforeach
                    @else
                        @isset($res->booking)
                            <h1>Поздравляем!</h1>
                            <ul>
                                <li>Статус: {{ $res->booking->status }}</li>
                                <li>Номер брони: {{ $res->booking->number }}</li>
                                <li>ID отеля: {{ $res->booking->propertyId }}</li>
                                <li>Даты: {{ $res->booking->roomStays[0]->stayDates->arrivalDateTime }}
                                    - {{ $res->booking->roomStays[0]->stayDates->departureDateTime }}</li>
                                <li>Аннуляция брони
                                    до: {{ $res->booking->cancellationPolicy->freeCancellationDeadlineLocal }}
                                    - {{ $res->booking->cancellationPolicy->penaltyAmount }} {{ $res->booking->currencyCode }}</li>
                                <li>
                                    Заказчик: {{ $res->booking->customer->firstName }} {{ $res->booking->customer->lastName }}
                                    <ul>
                                        <li>Номер
                                            телефона: {{ $res->booking->customer->contacts->phones[0]->phoneNumber }}</li>
                                        <li>Email: {{ $res->booking->customer->contacts->emails[0]->emailAddress }}</li>
                                        <li>Комментарий: {{ $res->booking->customer->comment }}</li>
                                    </ul>
                                </li>
                            </ul>
                            <div class="bnt-wrap">
                                <form action="{{ route('res_calculate') }}">
                                    <input type="hidden" name="number" value="{{ $res->booking->number }}">
                                    @if($res->booking->cancellation == null)
                                        <input type="hidden" name="cancelTime"
                                               value="{{ $res->booking->cancellationPolicy->freeCancellationDeadlineUtc }}">
                                    @endif
                                    <button class="more">Отменить бронь</button>
                                </form>
                            </div>
                        @endisset
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
