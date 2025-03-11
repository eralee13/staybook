@extends('layouts.master')

@section('title', 'Search amenities')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Searh services</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Search services</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page property">
        <div class="container">
            <div class="row">
                @if(isset($results->errors))
                    @foreach ($results->errors as $error)
                        <div class="alert alert-danger">
                            <h5>{{ $error->code }}</h5>
                            <p style="margin-bottom: 0">{{ $error->message }}</p>
                        </div>
                    @endforeach
                @else
                    @if($results != null)
                        @foreach($results->roomStays as $room)
                            <div class="col-md-12">
                                <div class="property-item">
                                    <div class="date">{{ $room->stayDates->arrivalDateTime }}
                                        - {{ $room->stayDates->departureDateTime }}</div>
                                    <div class="hotel">Hotel ID: {{ $room->propertyId }}</div>
                                    <h4>{{ $room->fullPlacementsName }}</h4>
                                    <div class="price">
                                        Price: {{ $room->total->priceBeforeTax }} {{ $room->currencyCode }}</div>
                                    <div class="meal">{{ $room->mealPlanCode }}</div>
                                    <div class="btn-wrap">
                                        <form action="{{ route('orderexely', $room->roomType->id) }}">
                                            <input type="hidden" name="propertyId" value="{{ $room->propertyId }}">
                                            <input type="hidden" name="arrivalDate"
                                                   value="{{ $room->stayDates->arrivalDateTime }}">
                                            <input type="hidden" name="departureDate"
                                                   value="{{ $room->stayDates->departureDateTime }}">
                                            <input type="hidden" name="adultCount"
                                                   value="{{ $room->guestCount->adultCount }}">
                                            <input type="hidden" name="ratePlanId" value="{{ $room->ratePlan->id }}">
                                            <input type="hidden" name="roomTypeId" value="{{ $room->roomType->id }}">
                                            <input type="hidden" name="roomType"
                                                   value="{{ $room->roomType->placements[0]->kind }}">
                                            <input type="hidden" name="roomCount"
                                                   value="{{ $room->roomType->placements[0]->count }}">
                                            <input type="hidden" name="roomCode"
                                                   value="{{ $room->roomType->placements[0]->code }}">
                                            <input type="hidden" name="placementCode"
                                                   value="{{ $room->roomType->placements[0]->code }}">
                                            <input type="hidden" name="guestCount"
                                                   value="{{ $room->guestCount->adultCount }}">
                                            {{--                                            <input type="hidden" name="childAges[]" value="{{ $room->guestCount->childAges }}">--}}
                                            <input type="hidden" name="checkSum" value="{{ $room->checksum }}">
                                            @foreach($room->includedServices as $serv)
                                                <input type="hidden" name="servicesId" value="{{ $serv->id }}">
                                            @endforeach

                                            {{--                                            <input type="hidden" name="servicesQuantity" value="{{  }}">--}}
                                            <input type="hidden" name="hotel" value="{{ $room->fullPlacementsName }}">
                                            <input type="hidden" name="hotel_id" value="{{ $room->propertyId }}">
                                            <input type="hidden" name="room_id" value="{{ $room->roomType->id }}">
                                            <input type="hidden" name="title" value="{{ $room->fullPlacementsName }}">
                                            <input type="hidden" name="price"
                                                   value="{{ $room->total->priceBeforeTax }}">
                                            <button class="more">Book</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="alert alert-danger">Not found</div>
                    @endif
                @endif
            </div>
        </div>
    </div>

@endsection
