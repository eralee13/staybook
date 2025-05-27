@extends('layouts.master')

@section('title')

    @section('content')
        @auth
            <div class="page hotel">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            @php
                                $hotel = \App\Models\Hotel::where('exely_id', $request->propertyId)->get();
                                $hotel = $hotel->first();
                                $room = \App\Models\Room::where('exely_id', $request->roomTypeId)->get()->first();
                                $amenities = explode(',', $room->amenities);
                                $iconMap = [
                                    'wi-fi'             => 'wifi.svg',
                                    'интернет'          => 'wifi.svg',
                                    'Доступ в интернет' => 'wifi.svg',
                                    'чайный набор'      => 'tea.svg',
                                    'Питание включено'  => 'meal.svg',
                                    'минеральная вода'  => 'water.svg',
                                    'сауна'             => 'sauna.svg',
                                    'сейф'              => 'safe.svg',
                                    'Двуспальная кровать' => 'bed2.svg',
                                    'Гладильные принадлежности' => 'iron.svg',
                                    'Ванная комната' => 'bath.svg',
                                    'Сауна' => 'sauna.svg',
                                    'Сейф' => 'safe.svg',
                                    'Минибар' => 'minibar.svg',
                                    'Кондиционер' => 'cond.svg',
                                    'Туалетные принадлежности' => 'toilet.svg',
                                    'Душ' => 'shower.svg',
                                    'Звукоизоляция' => 'sound.svg',
                                    'Фен' => 'dry.svg',
                                    'Постельное бельё' => 'bed_sheets.svg',
                                    'Халат' => 'robe.svg',
                                    'Шкаф' => 'close.svg',
                                    'Телефон' => 'phone_hotel.svg',
                                    'Отопление' => 'heating.svg',
                                    'Письменный стол' => 'table.svg',
                                    'Минеральная вода' => 'water.svg',
                                ];
                            @endphp
                            <h1>{{ $hotel->city }}</h1>
                            <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"
                                 data-autoplay="30000">
                                <img loading="lazy" src="{{ Storage::url($hotel->image)}}" alt="">
                            </div>
                            <h3>{{ $hotel->title }}</h3>
                            <div class="address"><img src="{{ route('index') }}/img/marker_in.svg"
                                                      alt=""> {{ $hotel->address }}</div>
                            <h4>Описание</h4>
                            {!! $hotel->description !!}
                            <div class="amenities">
                                <h4>Услуги и удобства</h4>
                                @foreach($amenities as $amenity)
                                    @php
                                        $iconFile = 'check.svg';
                                        foreach ($iconMap as $keyword => $filename) {
                                            if (mb_stripos($amenity, $keyword) !== false) {
                                                $iconFile = $filename;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <div class="amenities-item">
                                        <img src="{{ asset('img/icons/' . $iconFile) }}" alt="{{ $amenity }}">
                                        <div class="name">{{ $amenity }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="maps">
                                <h4>Расположение</h4>
                                <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                                <div id="map" style="width: 100%; height: 500px;"></div>
                                <script>
                                    DG.then(function () {
                                        var map = DG.map('map', {
                                            center: [{{ $hotel->lat }}, {{ $hotel->lng }}],
                                            zoom: 12
                                        });

                                        DG.marker([{{ $hotel->lat }}, {{ $hotel->lng }}], {scrollWheelZoom: false})
                                            .addTo(map)
                                            .bindLabel('{{ $hotel->title }}', {
                                                static: true
                                            });
                                    });
                                </script>
                                <div class="address"><img src="{{ route('index') }}/img/marker_in.svg"
                                                          alt=""> {{ $hotel->address }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tariffs availabity">
                                <h4>Доступные варианты</h4>
                                <div class="row" style="margin-top: 30px">
                                    <div class="col-md-3">
                                        <div class="room">
                                            @if ($room)
                                                <img src="{{ Storage::url($room->image) }}" alt="">
                                            @else
                                                <img src="{{ route('index') }}/img/noimage.png" alt=""
                                                     width="100px">
                                            @endif
                                            <h5>{{ $room->title }}</h5>
                                            <div class="amenities">
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/area.svg" alt="">
                                                    <div class="name">{{ $room->area }} кв. м</div>
                                                </div>
                                                @foreach($amenities as $amenity)
                                                    @php
                                                        $iconFile = 'check.svg';
                                                        foreach ($iconMap as $keyword => $filename) {
                                                            if (mb_stripos($amenity, $keyword) !== false) {
                                                                $iconFile = $filename;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="amenities-item">
                                                        <img src="{{ asset('img/icons/' . $iconFile) }}" alt="{{ $amenity }}">
                                                        <div class="name">{{ $amenity }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="tariff-wrap">
                                            <div class="owl-carousel owl-tariffs">
                                                @foreach($rooms as $room)
                                                    @php
                                                        $roomName = \App\Models\Room::where('exely_id', $room->roomType->id)->first();
                                                           $arrival = \Carbon\Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                                           $departure = \Carbon\Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                                                           $cancelDate = \Carbon\Carbon::createFromDate($room->cancellationPolicy->freeCancellationDeadlineLocal)->format('d.m.Y H:i');
                                                            $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                                                            $cancel_utc = \Carbon\Carbon::createFromDate($room->cancellationPolicy->freeCancellationDeadlineLocal)->format('P');

                                                            $utc   = \Carbon\Carbon::parse($room->cancellationPolicy->freeCancellationDeadlineUtc);
                                                            $local = \Carbon\Carbon::parse($room->cancellationPolicy->freeCancellationDeadlineLocal . 'Z');

                                                            $hours = $utc->diffInHours($local, false);
                                                            $offset = sprintf('UTC%+03d:00', $hours);
                                                    @endphp
                                                    <div class="tariffs-item">
                                                        @isset($room->fullPlacementsName)
                                                            <h5>{{ $room->fullPlacementsName }}</h5>
                                                        @endisset
                                                        <div class="dates">
                                                            Время заезда: {{ $arrival }} UTC {{ $hotel_utc }}
                                                        </div>
                                                        <div class="dates">
                                                            Время выезда: {{ $departure }} UTC {{ $hotel_utc }}
                                                        </div>
                                                        <br>
                                                        <div class="item meal">
                                                            <div class="name">{{ $room->mealPlanCode }}</div>
                                                        </div>
                                                        <div class="item cancel">
                                                            <div class="name">Правила отмены:
                                                                @if($room->cancellationPolicy->freeCancellationPossible == true)
                                                                    Бесплатная отмена действует до {{ $cancelDate }}
                                                                    ({{ $offset }}). Размер
                                                                    штрафа: {{ $room->cancellationPolicy->penaltyAmount }} {{ $room->currencyCode }}
                                                                @else
                                                                    Размер
                                                                    штрафа: {{ $room->cancellationPolicy->penaltyAmount }} {{ $room->currencyCode }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="item price">{{ $room->total->priceBeforeTax }} {{ $room->currencyCode }}</div>
                                                        <div class="nds">Все налоги включены</div>
                                                        <div class="btn-wrap">
                                                            <form action="{{ route('order_exely', $room->roomType->id) }}">
                                                                <input type="hidden" name="propertyId"
                                                                       value="{{ $room->propertyId }}">
                                                                <input type="hidden" name="arrivalDate"
                                                                       value="{{ $room->stayDates->arrivalDateTime }}">
                                                                <input type="hidden" name="departureDate"
                                                                       value="{{ $room->stayDates->departureDateTime }}">
                                                                <input type="hidden" name="adultCount"
                                                                       value="{{ $room->guestCount->adultCount }}">
                                                                @empty($room->guestCount->childAges)

                                                                @else
                                                                    <input type="hidden" name="childAges[]"
                                                                           value="{{ implode(',', $room->guestCount->childAges) }}">
                                                                @endempty

                                                                <input type="hidden" name="ratePlanId"
                                                                       value="{{ $room->ratePlan->id }}">
                                                                <input type="hidden" name="roomTypeId"
                                                                       value="{{ $room->roomType->id }}">
                                                                <input type="hidden" name="placements"
                                                                       value="{{ json_encode($room->roomType->placements) }}">
                                                                {{--                                        <input type="hidden" name="roomType"--}}
                                                                {{--                                               value="{{ $room->roomType->placements[0]->kind }}">--}}
                                                                {{--                                        <input type="hidden" name="roomCount"--}}
                                                                {{--                                               value="{{ $room->roomType->placements[0]->count }}">--}}
                                                                {{--                                        <input type="hidden" name="roomCode"--}}
                                                                {{--                                               value="{{ $room->roomType->placements[0]->code }}">--}}
                                                                {{--                                        <input type="hidden" name="placementCode"--}}
                                                                {{--                                               value="{{ $room->roomType->placements[0]->code }}">--}}
                                                                <input type="hidden" name="categoryName"
                                                                       value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="mealCode"
                                                                       value="{{ $room->mealPlanCode }}">
                                                                <input type="hidden" name="cancelPossible"
                                                                       value="{{$room->cancellationPolicy->freeCancellationPossible}}">
                                                                <input type="hidden" name="cancelUtc"
                                                                       value="{{$room->cancellationPolicy->freeCancellationDeadlineUtc}}">
                                                                <input type="hidden" name="cancelLocal"
                                                                       value="{{$room->cancellationPolicy->freeCancellationDeadlineLocal}}">
                                                                <input type="hidden" name="cancelDate"
                                                                       value="{{ $cancelDate }}">
                                                                <input type="hidden" name="cancelPrice"
                                                                       value="{{ $room->cancellationPolicy->penaltyAmount  }}">
                                                                <input type="hidden" name="checkSum"
                                                                       value="{{ $room->checksum }}">
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
                                                                <button class="more">Забронировать</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            </div>
                                        </div>
                                    </div>

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="page auth">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-8 offset-lg-2 col-md-12">
                            <div class="img-wrap">
                                <img src="{{ route('index') }}/img/b2b.jpg" alt="">
                                <h4>@lang('main.b2b')</h4>
                            </div>
                            <div class="alert alert-danger">
                                <div class="descr">@lang('main.need_auth') <a
                                            href="{{ route('login') }}">@lang('main.auth')</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endauth

    @endsection
