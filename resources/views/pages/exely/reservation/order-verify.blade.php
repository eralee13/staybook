@php use Illuminate\Support\Facades\Http; @endphp
@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')


    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @if(isset($order->errors))
                        @foreach ($order->errors as $error)
                            <div class="alert alert-danger">
                                <h5>{{ $error->code }}</h5>
                                <p style="margin-bottom: 0">{{ $error->message }}</p>
                            </div>
                        @endforeach
                    @else
                        @if($order->booking != null)
                            @php
                                $hotel = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/' . $order->booking->propertyId);
                            @endphp
                        <h1>Подтверждение заказа</h1>
                            <table>
                                <tr>
                                    <td>Отель:</td>
                                    <td>{{ $hotel->object()->name }}</td>
                                </tr>
                                <tr>
                                    <td>Стоимость:</td>
                                    <td>{{ $order->booking->total->priceBeforeTax }} {{ $order->booking->currencyCode }}</td>
                                </tr>
                                <tr>
                                    <td>ФИО:</td>
                                    <td>
                                        @foreach($order->booking->roomStays as $room)
                                            @foreach($room->guests as $guest)
                                                <div class="name">{{ $guest->firstName }}</div>
                                            @endforeach
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <td>Комментарий:</td>
                                    <td>{{ $order->booking->bookingComments[0] }}</td>
                                </tr>
                                @foreach($order->booking->roomStays as $room)
                                    <tr>
                                        <td>Даты:</td>
                                        <td>{{ $room->stayDates->arrivalDateTime }}
                                            - {{ $room->stayDates->departureDateTime }}</td>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Тариф:</td>
                                        <td>{{ $room->ratePlan->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Кол-во взрослых:</td>
                                        <td>{{ $room->guestCount->adultCount }}</td>
                                    </tr>
                                    <tr>
                                        <td>Кол-во детей:</td>
                                        <td>{{ implode(',', $room->guestCount->childAges) }}</td>
                                        {{--                                    <td>{{ implode(',', explode($order->booking->roomStays[0]->guestCount->childAges)) }}</td>--}}
                                        {{--                                    <td>{{ count($order->booking->roomStays[0]->guestCount->guestCount->childAges) }}</td>--}}
                                    </tr>
                                    <tr>
                                        <td>Тип комнаты:</td>
                                        <td>{{ $room->roomType->name }}</td>
                                    </tr>
                                @endforeach

                            </table>
                            <div class="btn-wrap">
                                <form action="{{ route('res_bookings') }}" method="get">
                                    <input type="hidden" name="propertyId"
                                           value="{{ $order->booking->propertyId }}">
                                    <input type="hidden" name="total"
                                           value="{{ $order->booking->total->priceBeforeTax }}">
                                    <input type="hidden" name="cancellation"
                                           value="{{ $order->booking->cancellationPolicy->penaltyAmount }}">
                                    <input type="hidden" name="propertyId"
                                           value="{{ $order->booking->propertyId }}">
                                    <input type="hidden" name="arrivalDate"
                                           value="{{ $order->booking->roomStays[0]->stayDates->arrivalDateTime }}">
                                    <input type="hidden" name="departureDate"
                                           value="{{ $order->booking->roomStays[0]->stayDates->departureDateTime }}">
                                    <input type="hidden" name="ratePlanId"
                                           value="{{ $order->booking->roomStays[0]->ratePlan->id }}">
                                    <input type="hidden" name="roomTypeId"
                                           value="{{ $order->booking->roomStays[0]->roomType->id }}">
                                    <input type="hidden" name="roomCode"
                                           value="{{ $order->booking->roomStays[0]->roomType->placements[0]->code }}">
                                    <input type="hidden" name="firstName"
                                           value="{{ $order->booking->roomStays[0]->guests[0]->firstName }}">
                                    <input type="hidden" name="lastName"
                                           value="{{ $order->booking->roomStays[0]->guests[0]->lastName }}">
                                    <input type="hidden" name="sex" value="Male">
                                    <input type="hidden" name="citizenship" value="KGS">
                                    <input type="hidden" name="placements" value="{{ json_encode($order->booking->roomStays[0]->roomType->placements) }}">
                                    <input type="hidden" name="adultCount"
                                           value="{{ $order->booking->roomStays[0]->guestCount->adultCount }}">
                                    <input type="hidden" name="childAges[]"
                                           value="{{ implode(',', $order->booking->roomStays[0]->guestCount->childAges) }}">
                                    <input type="hidden" name="createBookingToken"
                                           value="{{ $order->booking->createBookingToken }}">
                                    <input type="hidden" name="checkSum"
                                           value="{{ $order->booking->roomStays[0]->checksum }}">
                                    <input type="hidden" name="comment"
                                           value="{{ $order->booking->bookingComments[0] }}">
                                    <input type="hidden" name="phone"
                                           value="{{ $order->booking->customer->contacts->phones[0]->phoneNumber }}">
                                    <input type="hidden" name="email"
                                           value="{{ $order->booking->customer->contacts->emails[0]->emailAddress }}">
                                    <button class="more">Подтвердить</button>
                                </form>
                            </div>
                        @else
                            <div class="alert alert-warning">Уважаемый посетитель! Данные по бронированию были изменены.
                                Мы можем вам предложить альтернативный вариант либо вы можете заново выполнить
                                <a href="{{ route('properties') }}">поиск проживания</a></div>
                            <table>
                                <tr>
                                    <td>Отель:</td>
                                    @php
                                        $hotel = \App\Models\Hotel::where('exely_id', $order->alternativeBooking->propertyId)->first();
                                    @endphp
                                    <td>{{ $hotel->title }}</td>
                                </tr>
                                <tr>
                                    <td>Стоимость:</td>
                                    <td>{{ $order->alternativeBooking->total->priceBeforeTax }} {{ $order->alternativeBooking->currencyCode }}</td>
                                </tr>
                                <tr>
                                    @php
                                        $cancelPossible = $order->alternativeBooking->cancellationPolicy;
                                    @endphp
                                    <td>Правило отмены:</td>
                                    @if($cancelPossible->freeCancellationPossible == true)
                                        <td>Бесплатная отмена действует до ({{ $hotel->timezone }}). Размер
                                            штрафа: {{ $request->cancelPrice }} {{ $order->alternativeBooking->currencyCode }}</td>
                                    @else
                                        <td>Возможность бесплатной отмены отсутствует. Размер штрафа
                                            составляет: {{ $cancelPossible->penaltyAmount }} {{ $order->alternativeBooking->currencyCode }}</td>
                                    @endif
                                </tr>
                                @foreach($order->alternativeBooking->roomStays as $room)
                                    @php
                                        $arrival = \Carbon\Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                        $departure = \Carbon\Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                                    @endphp
                                    <tr>
                                        <td>Тариф:</td>
                                        <td>{{ $room->ratePlan->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>Даты заезда-выезда:</td>
                                        <td>{{ $arrival }} - {{ $departure }}</td>
                                    </tr>
                                    <tr>
                                        <td>Кол-во взрослых:</td>
                                        <td>{{ $room->guestCount->adultCount }}</td>
                                    </tr>
                                    <tr>
                                        <td>Кол-во детей:</td>
                                        {{--                                        {{ implode(',', $room->guestCount->childAges) }}--}}
                                        <td>{{ count($room->guestCount->childAges) }}</td>
                                    </tr>
                                    <tr>
                                        <td>Тип комнаты:</td>
                                        <td>{{ $room->roomType->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>ФИО:</td>
                                        <td>
                                            <ul>
                                                @foreach($room->guests as $guest)
                                                    <li>{{ $guest->firstName }} {{ $guest->lastName }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>Комментарий:</td>
                                    <td>{{ $order->alternativeBooking->bookingComments[0] }}</td>
                                </tr>
                            </table>
                            <div class="btn-wrap">
                                <form action="{{ route('res_bookings') }}" method="get">
                                    <input type="hidden" name="propertyId"
                                           value="{{ $order->alternativeBooking->propertyId }}">
                                    <input type="hidden" name="total"
                                           value="{{ $order->alternativeBooking->total->priceBeforeTax }}">
                                    {{--                            <input type="hidden" name="taxes" value="{{ $order->booking->total->taxes }}">--}}
                                    <input type="hidden" name="cancellation"
                                           value="{{ $order->alternativeBooking->cancellationPolicy->penaltyAmount }}">
                                    <input type="hidden" name="propertyId"
                                           value="{{ $order->alternativeBooking->propertyId }}">
                                    <input type="hidden" name="arrivalDate"
                                           value="{{ $order->alternativeBooking->roomStays[0]->stayDates->arrivalDateTime }}">
                                    <input type="hidden" name="departureDate"
                                           value="{{ $order->alternativeBooking->roomStays[0]->stayDates->departureDateTime }}">
                                    <input type="hidden" name="ratePlanId"
                                           value="{{ $order->alternativeBooking->roomStays[0]->ratePlan->id }}">
                                    <input type="hidden" name="roomTypeId"
                                           value="{{ $order->alternativeBooking->roomStays[0]->roomType->id }}">
                                    <input type="hidden" name="roomCode"
                                           value="{{ $order->alternativeBooking->roomStays[0]->roomType->placements[0]->code }}">
                                    <input type="hidden" name="firstName"
                                           value="{{ $order->alternativeBooking->roomStays[0]->guests[0]->firstName }}">
                                    <input type="hidden" name="lastName"
                                           value="{{ $order->alternativeBooking->roomStays[0]->guests[0]->lastName }}">
                                    <input type="hidden" name="sex" value="Male">
                                    <input type="hidden" name="citizenship" value="KGS">
                                    <input type="hidden" name="guestCount"
                                           value="{{ $order->alternativeBooking->roomStays[0]->guestCount->adultCount }}">
                                    <input type="hidden" name="childAges[]"
                                           value="{{ implode(',',  $order->alternativeBooking->roomStays[0]->guestCount->childAges)  }}">
                                    <input type="hidden" name="checksum" value="KGS">
                                    <input type="hidden" name="createBookingToken"
                                           value="{{ $order->alternativeBooking->createBookingToken }}">
                                    <input type="hidden" name="checkSum"
                                           value="{{ $order->alternativeBooking->roomStays[0]->checksum }}">
                                    <input type="hidden" name="comment"
                                           value="{{ $order->alternativeBooking->bookingComments[0] }}">
                                    <input type="hidden" name="phone"
                                           value="{{ $order->alternativeBooking->customer->contacts->phones[0]->phoneNumber }}">
                                    <input type="hidden" name="email"
                                           value="{{ $order->alternativeBooking->customer->contacts->emails[0]->emailAddress }}">
                                    <button class="more">Подтвердить</button>
                                </form>
                            </div>
                        @endif
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
