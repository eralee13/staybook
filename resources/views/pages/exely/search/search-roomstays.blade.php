@php use App\Models\Hotel; @endphp
@extends('layouts.head')

@section('title')

    @section('content')

        @auth
            <div class="page hotel">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            @php
                                $room = $rooms[0]->propertyId;
                                $hotel = Hotel::where('exely_id', $room)->first();
                            @endphp

                            <h1>{{ $hotel->city }}</h1>
{{--                            <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"--}}
{{--                                 data-autoplay="30000">--}}
{{--                                <img src="{{ Storage::url($hotel->image)}}" alt="">--}}
{{--                            </div>--}}
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="{{ Storage::url($hotel->image)}}" alt="" style="margin-bottom: 10px; border-radius: 12px;">
                                </div>
                            </div>
                            <h3>{{ $hotel->title }}</h3>
                            <div class="address"><img src="{{ route('index') }}/img/marker_in.svg" alt=""> {{ $hotel->address }}</div>
                            <h4>Описание</h4>
                            {{ $hotel->description }}
                            <div class="amenities">
                                <h4>Услуги и удобства</h4>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/area.svg" alt="">
                                    <div class="name">24 кв. м</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/bath.svg" alt="">
                                    <div class="name">Собственная ванная комната</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/sauna.svg" alt="">
                                    <div class="name">Сауна</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/safe.svg" alt="">
                                    <div class="name">Сейф</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/minibar.svg" alt="">
                                    <div class="name">Минибар</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/wifi.svg" alt="">
                                    <div class="name">Высокоскоростной доступ в Интернет</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/cond.svg" alt="">
                                    <div class="name">Кондиционер</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/toilet.svg" alt="">
                                    <div class="name">Туалетные принадлежности</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/shower.svg" alt="">
                                    <div class="name">Душ</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/sound.svg" alt="">
                                    <div class="name">Звукоизоляция</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/iron.svg" alt="">
                                    <div class="name">Гладильные принадлежности</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/dry.svg" alt="">
                                    <div class="name">Фен</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/bed_sheets.svg" alt="">
                                    <div class="name">Постельное бельё</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/robe.svg" alt="">
                                    <div class="name">Халат</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/closet.svg" alt="">
                                    <div class="name">Шкаф</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/phone_hotel.svg" alt="">
                                    <div class="name">Телефон</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/heating.svg" alt="">
                                    <div class="name">Отопление</div>
                                </div>
                                <div class="amenities-item">
                                    <img src="{{ route('index') }}/img/icons/table.svg" alt="">
                                    <div class="name">Письменный стол</div>
                                </div>
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

                                        DG.marker([{{ $hotel->lat }}, {{ $hotel->lng }}], { scrollWheelZoom: false })
                                            .addTo(map)
                                            .bindLabel('', {
                                                static: true
                                            });

                                    });
                                </script>
                                <div class="address"><img src="{{ route('index') }}/img/marker_in.svg" alt=""> {{ $hotel->address }}</div>
                            </div>
                        </div>
{{--                        <div class="col-md-3">--}}
{{--                            <div class="side-filter">--}}
{{--                                <div class="title">36,000 сом <span>ночь</span></div>--}}

{{--                                <form action="">--}}
{{--                                    <div class="row">--}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <div class="label in">Заезд</div>--}}
{{--                                                <input type="date" id="date" value="2025-04-02">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-6">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <div class="label out">Выезд</div>--}}
{{--                                                <input type="date" id="date" class="out" value="2025-04-08">--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <div class="col-md-12">--}}
{{--                                            <div class="form-group">--}}
{{--                                                <div class="label">Кол-во гостей</div>--}}
{{--                                                <select name="" id="">--}}
{{--                                                    <option value="2">2 гостей</option>--}}
{{--                                                </select>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
{{--                                        <button class="more">Забронировать</button>--}}
{{--                                    </div>--}}
{{--                                </form>--}}
{{--                                <div class="sub">--}}
{{--                                    <div class="sub-title">36,000 сом * 2 ночи</div>--}}
{{--                                    <div class="sub-price">72,000 сом</div>--}}
{{--                                </div>--}}
{{--                                <div class="total">--}}
{{--                                    <div class="total-title">Всего</div>--}}
{{--                                    <div class="total-price">72,000 сом</div>--}}
{{--                                </div>--}}

{{--                            </div>--}}
{{--                        </div>--}}
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tariffs availabity">
                                <h4>Доступные варианты</h4>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="room">

                                            <img src="https://cdn.worldota.net/t/1024x768/extranet/08/6b/086b6cc68558e30e703961281c86447609d25b27.JPEG" alt="">
                                            <h5>Двухместный люкс Бизнес с доступом в Бизнес гостиную</h5>
                                            <div class="bed">2 отдельные кровати</div>
                                            <div class="amenties">
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/area.svg" alt="">
                                                    <div class="name">24 кв. м</div>
                                                </div>
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/bath.svg" alt="">
                                                    <div class="name">Собственная ванная комната</div>
                                                </div>
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/sauna.svg" alt="">
                                                    <div class="name">Сауна</div>
                                                </div>
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/safe.svg" alt="">
                                                    <div class="name">Сейф</div>
                                                </div>
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/minibar.svg" alt="">
                                                    <div class="name">Минибар</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="tariff-wrap">
                                            <div class="owl-carousel owl-tariffs">
                                                @foreach($rooms as $room)
                                                    <div class="tariffs-item">
                                                        @isset($room->fullPlacementsName)
                                                            <h5>{{ $room->fullPlacementsName }}</h5>
                                                        @endisset

                                                        @php
                                                            $roomName = \App\Models\Room::where('exely_id', $room->roomType->id)->first();
                                                            $arrival = \Carbon\Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                                            $departure = \Carbon\Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                                                            $cancelDate = \Carbon\Carbon::createFromDate($room->cancellationPolicy->freeCancellationDeadlineUtc)->format('d.m.Y H:i');
                                                        @endphp
                                                        <div class="item bed"><div class="name">{{ $room->fullPlacementsName }}</div></div>
                                                        <div class="item meal"><div class="name">{{ $room->mealPlanCode }}</div></div>
                                                        <div class="item cancel"><div class="name">Правила отмены: Бесплатная отмена действует до {{ $cancelDate }} ({{ $hotel->timezone }}). Размер штрафа: {{ $room->cancellationPolicy->penaltyAmount }} {{ $room->currencyCode }}</div></div>
                                                        <div class="item price">{{ $room->total->priceBeforeTax }} {{ $room->currencyCode }}</div>
                                                        <div class="nds">Все налоги включены</div>
                                                        {{--                                                        <div class="night">за ночь для 1 гостя</div>--}}

                                                        <div class="btn-wrap">
                                                            <form action="{{ route('orderexely', $room->roomType->id) }}">
                                                                <input type="hidden" name="propertyId" value="{{ $room->propertyId }}">
                                                                <input type="hidden" name="arrivalDate"
                                                                       value="{{ $room->stayDates->arrivalDateTime }}">
                                                                <input type="hidden" name="departureDate"
                                                                       value="{{ $room->stayDates->departureDateTime }}">
                                                                <input type="hidden" name="adultCount"
                                                                       value="{{ $room->guestCount->adultCount }}">
                                                                <input type="hidden" name="childAges[]"
                                                                       value="{{ implode(',', $room->guestCount->childAges) }}">
                                                                <input type="hidden" name="ratePlanId" value="{{ $room->ratePlan->id }}">
                                                                <input type="hidden" name="roomTypeId" value="{{ $room->roomType->id }}">
                                                                <input type="hidden" name="placements" value="{{ json_encode($room->roomType->placements) }}">
                                                                {{--                                        <input type="hidden" name="roomType"--}}
                                                                {{--                                               value="{{ $room->roomType->placements[0]->kind }}">--}}
                                                                {{--                                        <input type="hidden" name="roomCount"--}}
                                                                {{--                                               value="{{ $room->roomType->placements[0]->count }}">--}}
                                                                {{--                                        <input type="hidden" name="roomCode"--}}
                                                                {{--                                               value="{{ $room->roomType->placements[0]->code }}">--}}
                                                                {{--                                        <input type="hidden" name="placementCode"--}}
                                                                {{--                                               value="{{ $room->roomType->placements[0]->code }}">--}}
                                                                <input type="hidden" name="categoryName" value="{{ $roomName->category_id }} - {{ $roomName->title }}">
                                                                <input type="hidden" name="mealCode" value="{{ $room->mealPlanCode }}">
                                                                <input type="hidden" name="cancelDate" value="{{ $cancelDate }}">
                                                                <input type="hidden" name="cancelPrice" value="{{ $room->cancellationPolicy->penaltyAmount  }}">
                                                                <input type="hidden" name="checkSum" value="{{ $room->checksum }}">
                                                                @foreach($room->includedServices as $serv)
                                                                    <input type="hidden" name="servicesId" value="{{ $serv->id }}">
                                                                @endforeach

                                                                {{--                                            <input type="hidden" name="servicesQuantity" value="{{  }}">--}}
                                                                <input type="hidden" name="hotel" value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="hotel_id" value="{{ $room->propertyId }}">
                                                                <input type="hidden" name="room_id" value="{{ $room->roomType->id }}">
                                                                <input type="hidden" name="title" value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="price" value="{{ $room->total->priceBeforeTax }}">
                                                                <input type="hidden" name="currency" value="{{ $room->currencyCode }}">
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

