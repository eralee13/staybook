@php use Carbon\Carbon;use Illuminate\Support\Facades\Http; @endphp
@extends('layouts.filter_mini')

@section('title', 'Поиск')

@section('content')


    <div class="page search">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    {{--                    <h1>Sheraton</h1>--}}
                    {{--                    <div class="rating"><img src="img/star.svg" alt="">4.76</div>--}}


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
                                     $hotel = \App\Models\Hotel::where('exely_id', $room->propertyId)->first();
                                @endphp
                                <div class="search-item">
                                    <div class="row">
                                        <div class="col-md-5 order-xl-1 order-lg-1 order-1">
                                            <div class="img-wrap">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="main">
                                                            <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="primary">
                                                            <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                        </div>
                                                        <div class="primary">
                                                            <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-5 order-xl-2 order-lg-2 order-3">
                                            <h4>{{ $property->object()->name }}</h4>
                                            <div class="amenities">
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/bed2.svg" alt="">
                                                    <div class="name">Двуспальная кровать</div>
                                                </div>
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/meal.svg" alt="">
                                                    <div class="name">Питание включено</div>
                                                </div>
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/iron.svg" alt="">
                                                    <div class="name">Гладильные принадлежности</div>
                                                </div>
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/wifi.svg" alt="">
                                                    <div class="name">Доступ в интернет</div>
                                                </div>
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/bath.svg" alt="">
                                                    <div class="name">Ванная комната</div>
                                                </div>
                                            </div>
                                            <div class="btn-wrap">
                                                <div class="btn-wrap">
                                                    <form action="{{ route('search_roomstays', $room->roomType->id) }}">
                                                        <input type="hidden" name="propertyId"
                                                               value="{{ $room->propertyId }}">
                                                        <input type="hidden" name="arrivalDate"
                                                               value="{{ $request->arrivalDate }}">
                                                        <input type="hidden" name="departureDate"
                                                               value="{{ $request->departureDate }}">
                                                        <input type="hidden" name="adultCount"
                                                               value="{{ $room->guestCount->adultCount }}">
                                                        <input type="hidden" name="childAges[]"
                                                               value="{{ implode(',', $room->guestCount->childAges) }}">
                                                        <input type="hidden" name="ratePlanId"
                                                               value="{{ $room->ratePlan->id }}">
                                                        <input type="hidden" name="roomTypeId"
                                                               value="{{ $room->roomType->id }}">
                                                        <input type="hidden" name="roomType"
                                                               value="{{ $room->roomType->placements[0]->kind }}">
                                                        <input type="hidden" name="roomCount"
                                                               value="{{ $room->roomType->placements[0]->count }}">
                                                        <input type="hidden" name="roomCode"
                                                               value="{{ $room->roomType->placements[0]->code }}">
                                                        <input type="hidden" name="placementCode"
                                                               value="{{ $room->roomType->placements[0]->code }}">
                                                        <input type="hidden" name="adults"
                                                               value="{{ $room->guestCount->adultCount }}">
                                                        <input type="hidden" name="checkSum"
                                                               value="{{ $room->checksum }}">
                                                        {{--                                            <input type="hidden" name="childAges[]" value="{{ $room->guestCount->childAges }}">--}}
                                                        @php
                                                            $array_child = [];
                                                        @endphp
                                                        @foreach($room->guestCount->childAges as $child)
                                                            @php
                                                                $array_child[] = $child
                                                            @endphp
                                                        @endforeach
                                                        <input type="hidden" name="childAges"
                                                               value="{{ implode(',', $array_child) }}">
                                                        @foreach($room->includedServices as $serv)
                                                            <input type="hidden" name="servicesId"
                                                                   value="{{ $serv->id }}">
                                                        @endforeach

                                                        {{--                                            <input type="hidden" name="servicesQuantity" value="{{  }}">--}}
                                                        <input type="hidden" name="hotel"
                                                               value="{{ $room->fullPlacementsName }}">
                                                        <input type="hidden" name="hotel_id"
                                                               value="{{ $room->propertyId }}">
                                                        <input type="hidden" name="room_id"
                                                               value="{{ $room->roomType->id }}">
                                                        <input type="hidden" name="title"
                                                               value="{{ $room->fullPlacementsName }}">
                                                        <input type="hidden" name="price"
                                                               value="{{ $room->total->priceBeforeTax }}">
                                                        <input type="hidden" name="currency"
                                                               value="{{ $room->currencyCode }}">
                                                        <button class="more">Показать все номера</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 order-xl-3 order-lg-3 order-2">
                                            <div class="price">{{ $room->total->priceBeforeTax }} {{ $room->currencyCode }}</div>
                                            <div class="night">ночь</div>
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

        </div>
    </div>

@endsection
