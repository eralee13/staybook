@php use Illuminate\Support\Facades\Http; @endphp
@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @php
                        $hotel = \App\Models\Hotel::where('id', $request->propertyId)->first();
                        $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                        $arrival = \Carbon\Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
                        $departure = \Carbon\Carbon::createFromDate($request->departureDate)->format('d.m.Y');
                        $room = \App\Models\Room::where('id', $request->room_id)->firstOrFail();
                        $rate = \App\Models\Rate::where('id', $request->rate_id)->firstOrFail();
                        $cancelPossible = \App\Models\CancellationRule::where('id', $rate->cancellation_rule_id)->firstOrFail();
                    @endphp
                    <h1>Подтверждение заказа</h1>
                    <table>
                        <tr>
                            <td>Отель:</td>
                            <td>{{ $hotel->title }}</td>
                        </tr>
                        <tr>
                            <td>Тип комнаты:</td>
                            <td>{{ $room->title }}</td>
                        </tr>
                        <tr>
                            <td>Тариф:</td>
                            <td>{{ $rate->title }}</td>
                        </tr>
                        <tr>
                            <td>Кол-во взрослых:</td>
                            <td>{{ $room->guestCount->adultCount ?? $request->adult }}</td>
                        </tr>
                        <tr>
                            <td>Кол-во детей:</td>
                            <td>{{ $request->child }}</td>
                            {{--                                    <td>{{ implode(',', explode($order->booking->roomStays[0]->guestCount->childAges)) }}</td>--}}
                            {{--                                    <td>{{ count($order->booking->roomStays[0]->guestCount->guestCount->childAges) }}</td>--}}
                        </tr>
                        <tr>
                            <td>Даты:</td>
                            <td>{{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }}
                                (UTC {{ $hotel_utc }})
                            </td>
                        </tr>
                        <tr>
                            <td>Стоимость:</td>
                            <td>{{ $order->booking->total->priceBeforeTax ?? $request->price }} {{ $order->booking->currencyCode ?? '$' }}</td>
                        </tr>
                        <tr>
                            <td>Правило отмены:</td>
                            @if($cancelPossible->is_refundable == true)
                                <td>
                                    @if(now() <= $request->cancelDate)
                                        Бесплатная отмена действует до {{ $request->cancelDate }} (UTC {{ $hotel_utc }}
                                        ).
                                    @endif
                                    Размер
                                    штрафа: {{ $request->cancelPrice }} {{ $order->booking->currencyCode ?? '$' }}</td>
                            @else
                                <td>Возможность бесплатной отмены отсутствует. Размер
                                    штрафа: {{ $cancelPossible->penaltyAmount ?? $request->cancelPrice }} {{ $order->booking->currencyCode ?? '$' }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>ФИО:</td>
                            <td>
                                <div class="name">{{ $request->name }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Номер телефона:</td>
                            <td>
                                <div class="name">{{ $request->phone }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td>
                                <div class="name">{{ $request->email }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Комментарий:</td>
                            <td>{{ $order->booking->bookingComments[0] ?? $request->comment }}</td>
                        </tr>
                    </table>

                    <div class="btn-wrap">
                        <form action="{{ route('book_reserve') }}" method="get">
                            <input type="hidden" name="propertyId"
                                   value="{{ $order->booking->propertyId ?? $request->propertyId }}">
                            <input type="hidden" name="total"
                                   value="{{ $order->booking->total->priceBeforeTax ?? $request->price }}">
                            <input type="hidden" name="cancellation_id" value="{{ $request->cancellation_id }}">
                            <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                            <input type="hidden" name="arrivalDate"
                                   value="{{ $order->booking->roomStays[0]->stayDates->arrivalDateTime ?? $request->arrivalDate }}">
                            <input type="hidden" name="departureDate"
                                   value="{{ $order->booking->roomStays[0]->stayDates->departureDateTime ?? $request->departureDate }}">
                            <input type="hidden" name="ratePlanId"
                                   value="{{ $order->booking->roomStays[0]->ratePlan->id ?? $request->rate_id }}">
                            <input type="hidden" name="roomTypeId"
                                   value="{{ $order->booking->roomStays[0]->roomType->id ?? $request->room_id }}">
                            <input type="hidden" name="roomCode"
                                   value="{{ $order->booking->roomStays[0]->roomType->placements[0]->code ?? '' }}">
                            <input type="hidden" name="firstName"
                                   value="{{ $order->booking->roomStays[0]->guests[0]->firstName ?? $request->name }}">
                            <input type="hidden" name="lastName"
                                   value="{{ $order->booking->roomStays[0]->guests[0]->lastName ?? $request->name }}">
                            <input type="hidden" name="sex" value="Male">
                            <input type="hidden" name="citizenship" value="KGS">
                            {{--                            <input type="hidden" name="placements"--}}
                            {{--                                   value="{{ json_encode($order->booking->roomStays[0]->roomType->placements) ?? '' }}">--}}
                            <input type="hidden" name="adultCount"
                                   value="{{ $order->booking->roomStays[0]->guestCount->adultCount ?? $request->adult }}">
                            <input type="hidden" name="child" value="{{ $request->child }}">
                                                        <input type="hidden" name="childAges[]"
                                                               value="{{ implode($request->childAges) }}">
                            {{--                            <input type="hidden" name="createBookingToken"--}}
                            {{--                                   value="{{ $order->booking->createBookingToken ?? '' }}">--}}
                            {{--                            <input type="hidden" name="checkSum"--}}
                            {{--                                   value="{{ $order->booking->roomStays[0]->checksum ?? '' }}">--}}
                            <input type="hidden" name="comment"
                                   value="{{ $order->booking->bookingComments[0] ?? $request->comment }}">
                            <input type="hidden" name="phone"
                                   value="{{ $order->booking->customer->contacts->phones[0]->phoneNumber ?? $request->phone  }}">
                            <input type="hidden" name="email"
                                   value="{{ $order->booking->customer->contacts->emails[0]->emailAddress?? $request->email }}">
                            <button class="more">Подтвердить</button>
                        </form>
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
