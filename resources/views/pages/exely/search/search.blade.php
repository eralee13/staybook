@php use Carbon\Carbon;use Illuminate\Support\Facades\Http; @endphp
@extends('layouts.master')

@section('title', 'Search amenities')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Поиск отелей</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Поиск отелей</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page rooms">
        <div class="container">
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
                        @php
                            $property = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/' . $room->propertyId);
                             $arrival = Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                             $departure = Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                        @endphp
                        <div class="row rooms-item">
                            <div class="col-md-3">
                                <img src="{{ $property->object()->images[0]->url }}" alt="">
                            </div>
                            <div class="col-md-9">
                                <h3>{{ $property->object()->name }}</h3>
                                <div class="date">{{ $arrival }} - {{ $departure }}</div>
                                <h5>{{ $room->fullPlacementsName }}</h5>
                                <div class="price">
                                    Цена: {{ $room->total->priceBeforeTax }} {{ $room->currencyCode }}</div>
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
                                        <input type="hidden" name="currency" value="{{ $room->currencyCode }}">
                                        <button class="more">Забронировать</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-danger">Не найдено</div>
                @endif
            @endif
        </div>
    </div>

    <style>
        .page {
            padding: 200px 0;
        }

        .property-item {
            margin-bottom: 40px;
        }

        .rooms-item .date {
            opacity: .5;
        }

        .rooms-item h5 {
            font-size: 20px;
            margin-top: 20px;
        }

        .rooms-item .price {
            opacity: .7;
            font-size: 18px;
        }
    </style>

@endsection
