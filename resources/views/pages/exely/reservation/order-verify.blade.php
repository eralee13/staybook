@extends('layouts.master')

@section('title', 'Забронировать')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Забронировать</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Забронировать</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
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
                            <table>
                                <tr>
                                    <td>Name</td>
                                    <td>
                                        <ul>
                                            @foreach($order->booking->roomStays as $room)
                                                @foreach($room->guests as $guest)
                                                    <li>{{ $guest->firstName }} {{ $guest->lastName }}</li>
                                                @endforeach
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Price</td>
                                    <td>{{ $order->booking->total->priceBeforeTax }} {{ $order->booking->currencyCode }}</td>
                                </tr>
                                <tr>
                                    <td>Property ID</td>
                                    <td>{{ $order->booking->propertyId }}</td>
                                </tr>
                                <tr>
                                    <td>Comment</td>
                                    <td>{{ $order->booking->bookingComments[0] }}</td>
                                </tr>
                                <tr>
                                    <td>Stay Dates</td>
                                    <td>
                                        @foreach($order->booking->roomStays as $room)
                                            {{ $room->stayDates->arrivalDateTime }}
                                            - {{ $room->stayDates->departureDateTime }}
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rate Plan</td>
                                    <td>{{ $order->booking->roomStays[0]->ratePlan->name }}</td>
                                </tr>
                                <tr>
                                    <td>Guest Count</td>
                                    <td>{{ $order->booking->roomStays[0]->guestCount->adultCount }}</td>
                                </tr>
                                <tr>
                                    <td>Room Type</td>
                                    <td>{{ $order->booking->roomStays[0]->roomType->name }}</td>
                                </tr>
                            </table>
                        @else
                            <table>
                                <tr>
                                    <td>Name</td>
                                    <td>
                                        <ul>
                                            @foreach($order->alternativeBooking->roomStays as $room)
                                                @foreach($room->guests as $guest)
                                                    <li>{{ $guest->firstName }} {{ $guest->lastName }}</li>
                                                @endforeach
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Price</td>
                                    <td>{{ $order->alternativeBooking->total->priceBeforeTax }} {{ $order->alternativeBooking->currencyCode }}</td>
                                </tr>
                                <tr>
                                    <td>Property ID</td>
                                    <td>{{ $order->alternativeBooking->propertyId }}</td>
                                </tr>
                                <tr>
                                    <td>Comment</td>
                                    <td>{{ $order->alternativeBooking->bookingComments[0] }}</td>
                                </tr>
                                <tr>
                                    <td>Stay Dates</td>
                                    <td>
                                        @foreach($order->alternativeBooking->roomStays as $room)
                                            {{ $room->stayDates->arrivalDateTime }}
                                            - {{ $room->stayDates->departureDateTime }}
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rate Plan</td>
                                    <td>{{ $order->alternativeBooking->roomStays[0]->ratePlan->name }}</td>
                                </tr>
                                <tr>
                                    <td>Guest Count</td>
                                    <td>{{ $order->alternativeBooking->roomStays[0]->guestCount->adultCount }}</td>
                                </tr>
                                <tr>
                                    <td>Room Type</td>
                                    <td>{{ $order->alternativeBooking->roomStays[0]->roomType->name }}</td>
                                </tr>
                            </table>
                        @endif

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
                                <button class="more">Verify</button>
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
