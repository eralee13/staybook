@php use Illuminate\Support\Facades\Http; @endphp
@extends('layouts.head')

@section('title', 'Подтверждение заказа')

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
                        @if($order == null)
                            <div class="alert alert-danger">Бронирование не удалось оформить! Пожалуйста <a
                                        href="{{ route('index') }}">повторите снова</a>!
                            </div>
                        @else
                            @if($order->booking != null)
                                {{--                                @dd($order->booking)--}}
                                @php
                                    $hotel = \App\Models\Hotel::where('exely_id', $order->booking->propertyId)->get()->first();
                                    $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                                    $cancelPossible = $order->booking->cancellationPolicy;
                                    if($cancelPossible->freeCancellationPossible == true) {
                                        $cancelLocal = \Carbon\Carbon::createFromDate($cancelPossible->freeCancellationDeadlineLocal)->format('d.m.Y H:i');
                                        $cancel_utc = \Carbon\Carbon::createFromDate($cancelPossible->freeCancellationDeadlineLocal)->format('P');
                                    }

                                    $utc   = \Carbon\Carbon::parse($cancelPossible->freeCancellationDeadlineUtc);
                                    $local = \Carbon\Carbon::parse($cancelPossible->freeCancellationDeadlineLocal . 'Z');

    // сколько часов между ними (signed)
    $hours = $utc->diffInHours($local, false);

    // формат UTC±HH:00
    $offset = sprintf('UTC%+03d:00', $hours);
                                @endphp
                                <h1>Подтверждение заказа</h1>
                                <table>
                                    <tr>
                                        <td>Отель:</td>
                                        <td>{{ $hotel->title }}</td>
                                    </tr>
                                    <tr>
                                        <td>Стоимость:</td>
                                        <td>{{ $order->booking->total->priceBeforeTax }} {{ $order->booking->currencyCode }}</td>
                                    </tr>
                                    <tr>
                                        <td>Правило отмены:</td>
                                        @if($cancelPossible->freeCancellationPossible == true)
                                            <td>Бесплатная отмена действует до {{ $cancelLocal }} ({{ $offset }}).
                                                Размер
                                                штрафа: {{ $cancelPossible->penaltyAmount }} {{ $order->booking->currencyCode }}</td>
                                        @else
                                            <td>Возможность бесплатной отмены отсутствует. Размер
                                                штрафа: {{ $cancelPossible->penaltyAmount }} {{ $order->booking->currencyCode }}</td>
                                        @endif
                                    </tr>

                                    @foreach($order->booking->roomStays as $room)
                                        <tr>
                                            <td>ФИО:</td>
                                            <td>

                                                @foreach($room->guests as $guest)
                                                    <div class="name">{{ $guest->firstName }}</div>
                                                @endforeach
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Даты:</td>
                                            @php
                                                $arrival = \Carbon\Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                                $departure = \Carbon\Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                                            @endphp
                                            <td>{{ $arrival }} - {{ $departure }}
                                                @if($order->booking->cancellationPolicy->freeCancellationDeadlineLocal == null)
                                                    (UTC {{ $hotel_utc }})
                                                @else
                                                    {{ $order->booking->cancellationPolicy->freeCancellationDeadlineLocal }}
                                                @endif
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
                                            <td>
                                                @if (request()->filled('childAges'))
                                                    {{ count($room->guestCount->childAges) }}
                                                @else
                                                    0
                                                @endif</td>
                                            {{--                                    <td>{{ implode(',', explode($order->booking->roomStays[0]->guestCount->childAges)) }}</td>--}}
                                            {{--                                    <td>{{ count($order->booking->roomStays[0]->guestCount->guestCount->childAges) }}</td>--}}
                                        </tr>
                                        <tr>
                                            <td>Тип комнаты:</td>
                                            <td>{{ $room->roomType->name }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>Комментарий:</td>
                                        <td>{{ $order->booking->customer->comment }}</td>
                                    </tr>
                                </table>

                                <div class="btn-wrap">
                                    <form action="{{ route('book_reserve_exely') }}" method="get">
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
                                        <input type="hidden" name="placements"
                                               value="{{ json_encode($order->booking->roomStays[0]->roomType->placements) }}">
                                        <input type="hidden" name="adultCount"
                                               value="{{ $order->booking->roomStays[0]->guestCount->adultCount }}">
                                        @if (request()->filled('childAges'))
                                            <input type="hidden" name="childAges[]"
                                                   value="{{ implode(',', $order->booking->roomStays[0]->guestCount->childAges) }}">
                                        @endif
                                        <input type="hidden" name="createBookingToken"
                                               value="{{ $order->booking->createBookingToken }}">
                                        <input type="hidden" name="checkSum"
                                               value="{{ $order->booking->roomStays[0]->checksum }}">
                                        <input type="hidden" name="comment"
                                               value="{{ $order->booking->customer->comment }}">
                                        <input type="hidden" name="phone"
                                               value="{{ $order->booking->customer->contacts->phones[0]->phoneNumber }}">
                                        <input type="hidden" name="email"
                                               value="{{ $order->booking->customer->contacts->emails[0]->emailAddress }}">
                                        <button class="more">Подтвердить</button>
                                    </form>
                                </div>
                            @else
                                <div class="alert alert-warning">Уважаемый посетитель! Данные по бронированию были
                                    изменены.
                                    Мы можем вам предложить альтернативный вариант либо вы можете заново выполнить
                                    <a href="{{ route('index') }}">поиск проживания</a></div>
                                <table>
                                    <tr>
                                        <td>Отель:</td>
                                        @php
                                            $hotel = \App\Models\Hotel::where('exely_id', $order->alternativeBooking->propertyId)->get()->first();
                                            $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
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
                                            if($cancelPossible->freeCancellationPossible == true) {
                                                $cancelLocal = \Carbon\Carbon::createFromDate($cancelPossible->freeCancellationDeadlineLocal)->format('d.m.Y H:i');
                                                $cancel_utc = \Carbon\Carbon::createFromDate($cancelPossible->freeCancellationDeadlineLocal)->format('P');
                                            }

                                            $utc   = \Carbon\Carbon::parse($cancelPossible->freeCancellationDeadlineUtc);
                                    $local = \Carbon\Carbon::parse($cancelPossible->freeCancellationDeadlineLocal . 'Z');

                                    // сколько часов между ними (signed)
                                    $hours = $utc->diffInHours($local, false);

                                    // формат UTC±HH:00
                                    $offset = sprintf('UTC%+03d:00', $hours);
                                        @endphp
                                        <td>Правило отмены:</td>
                                        @if($cancelPossible->freeCancellationPossible == true)
                                            <td>Бесплатная отмена действует до {{ $cancelLocal }} ({{ $offset }}).
                                                Размер
                                                штрафа: {{ $cancelPossible->penaltyAmount }} {{ $order->alternativeBooking->currencyCode }}</td>
                                        @else
                                            <td>Возможность бесплатной отмены отсутствует. Размер
                                                штрафа: {{ $cancelPossible->penaltyAmount }} {{ $order->alternativeBooking->currencyCode }}</td>
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
                                            <td>{{ $arrival }} - {{ $departure }} (UTC {{ $hotel_utc }})
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Кол-во взрослых:</td>
                                            <td>{{ $room->guestCount->adultCount }}</td>
                                        </tr>
                                        <tr>
                                            <td>Кол-во детей:</td>
                                            {{--                                        {{ implode(',', $room->guestCount->childAges) }}--}}
                                            <td>
                                                @if (request()->filled('childAges'))
                                                    {{ count($room->guestCount->childAges) }}
                                                @else
                                                    0
                                                @endif
                                            </td>
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
                                        <td>{{ $order->alternativeBooking->customer->comment }}</td>
                                    </tr>
                                </table>
                                <div class="btn-wrap">
                                    <form action="{{ route('book_reserve_exely') }}" method="get">
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
                                        <input type="hidden" name="placements"
                                               value="{{ json_encode($order->alternativeBooking->roomStays[0]->roomType->placements) }}">
                                        <input type="hidden" name="adultCount"
                                               value="{{ $order->alternativeBooking->roomStays[0]->guestCount->adultCount }}">
                                        @if (request()->filled('childAges'))
                                            <input type="hidden" name="childAges[]"
                                                   value="{{ implode(',',  $order->alternativeBooking->roomStays[0]->guestCount->childAges)  }}">
                                        @endif
                                        <input type="hidden" name="createBookingToken"
                                               value="{{ $order->alternativeBooking->createBookingToken }}">
                                        <input type="hidden" name="checkSum"
                                               value="{{ $order->alternativeBooking->roomStays[0]->checksum }}">
                                        <input type="hidden" name="comment"
                                               value="{{ $order->alternativeBooking->customer->comment }}">
                                        <input type="hidden" name="phone"
                                               value="{{ $order->alternativeBooking->customer->contacts->phones[0]->phoneNumber }}">
                                        <input type="hidden" name="email"
                                               value="{{ $order->alternativeBooking->customer->contacts->emails[0]->emailAddress }}">
                                        <button class="more">Подтвердить</button>
                                    </form>
                                </div>
                            @endif
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
