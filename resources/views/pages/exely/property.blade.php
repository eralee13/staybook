@extends('layouts.master')

@section('title', 'Properties')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Properties</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Properties</li>
                        <li>></li>
                        <li>{{ $property->name }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page single">
        <div class="container">
            <div class="row">
                <div class="col-md-5">
                    <div class="main">
                        <img src="{{ $property->images[0]->url }}" alt="">
                    </div>
                    <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                    <div id="map" style="width: 100%; height: 500px; margin-top: 20px"></div>
                    <script>
                        DG.then(function () {
                            var map = DG.map('map', {
                                center: [{{ $property->contactInfo->address->latitude }}, {{ $property->contactInfo->address->longitude }}],
                                zoom: 14
                            });

                            DG.marker([{{ $property->contactInfo->address->latitude }}, {{ $property->contactInfo->address->longitude }}], {scrollWheelZoom: false})
                                .addTo(map)
                                .bindLabel('{{ $property->name }}', {
                                    static: true,
                                    center: true
                                });
                        });
                    </script>
                </div>
                <div class="col-md-7 summary">
                    <h1>{{ $property->name }}</h1>
                    <p>{{ $property->description }}</p>
                    <div class="rating">Rating: {{ $property->stars }}</div>
                    <div class="address">Address: {{ $property->contactInfo->address->addressLine }}</div>
                    <div class="phone">
                        Phone: <a
                            href="tel:{{$property->contactInfo->phones[0]->phoneNumber ?? ''}}">{{ $property->contactInfo->phones[0]->phoneNumber ?? '' }}</a>
                    </div>
                    <div class="email">
                        Email: <a
                            href="mailto: {{$property->contactInfo->emails[0]}}">{{$property->contactInfo->emails[0]}}</a>
                    </div>
                    <div class="checkin">Checkin: {{ $property->policy->checkInTime }}</div>
                    <div class="checkout">Checkout: {{ $property->policy->checkOutTime }}</div>
                    <h4 style="margin-top: 30px">Plans:</h4>
                    <div class="row">
                        @foreach($property->ratePlans as $plan)
                            <div class="col-md-6" style="margin-top: 20px">
                                <h6>{{ $plan->name }}</h6>
                                <p>{{ $plan->description }}</p>
                            </div>
                        @endforeach
                    </div>

                    <h4 style="margin-top: 30px">RoomTypes:</h4>
                    @foreach($property->roomTypes as $room)
                        <div class="col-md-12" style="margin: 20px 0">
                            @foreach($room->images as $image)
                                <img src="{{ $image->url }}" alt="" width="150px">
                            @endforeach
                            <h5>{{ $room->name }}</h5>
                            <p>{{ $room->categoryName }}</p>
                            <h6>Amentites:</h6>
                            <ul>
                                @foreach($room->amenities as $amenity)
                                    <li>{{ $amenity->name }}</li>
                                @endforeach
                            </ul>
                            <h6 style="margin-top: 30px">Occupancy:</h6>
                            <ul>
                                <li>AdultBed: {{ $room->occupancy->adultBed }}</li>
                                <li>ExtraBed: {{ $room->occupancy->extraBed }}</li>
                                <li>ChildWithoutBed: {{ $room->occupancy->childWithoutBed }}</li>
                            </ul>

                            <h6 style="margin-top: 30px">Placements:</h6>
                            <div class="row">
                                @foreach($room->placements as $place)
                                    <div class="col-md-4">
                                        <ul>
                                            <li>Kind: {{ $place->count }}</li>
                                            <li>Count: {{ $place->count }}</li>
                                            @isset($place->minAge)
                                                <li>MinAge: {{ $place->minAge }}</li>
                                            @endisset
                                            @isset($place->maxAge)
                                                <li>MaxAge: {{ $place->maxAge }}</li>
                                            @endisset
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                            <div class="btn-wrap">
                                <form action="{{ route('orderexely', $room->id) }}">
                                    <input type="hidden" name="hotel" value="{{ $property->name }}">
                                    <input type="hidden" name="hotel_id" value="{{ $property->id }}">
                                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                                    <input type="hidden" name="title" value="{{ $room->name}}">
                                    <input type="hidden" name="lng"
                                           value="{{ $property->contactInfo->address->longitude }}">
                                    <input type="hidden" name="lat"
                                           value="{{ $property->contactInfo->address->latitude }}">
                                    <input type="hidden" name="food" value="not_found">
                                    <input type="hidden" name="cancel" value="not_found">
                                    <input type="hidden" name="price" value="1">
                                    <input type="hidden" name="image" value="{{ $property->images[0]->url }}">
                                    <input type="hidden" name="bed" value="{{ $room->occupancy->adultBed }}">
                                    <button class="more">Забронировать</button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>

    <style>
        .single .main img {
            height: auto;
        }

        .single .summary img {
            height: 110px;
            object-fit: cover;
        }
    </style>

@endsection
