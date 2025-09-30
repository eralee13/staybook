@extends('layouts.master')
@section('title', $hotel->title)

@section('content')
    @auth
        @php
            // Валюта интерфейса
            $fxBase  = strtoupper(session('currency', 'USD'));

            // Нормализуем ages: массив/строка "3,7"/null
            $childAgesRaw = $request->input('childAges', []);
            if (is_string($childAgesRaw)) {
                $childAgesArr = array_values(array_filter(array_map('trim', explode(',', $childAgesRaw)), 'strlen'));
            } else {
                $childAgesArr = \Illuminate\Support\Arr::wrap($childAgesRaw);
            }

            // Кол-во взрослых (может приходить adultCount или adult)
            $adultCount = (int)($request->adultCount ?? $request->adult ?? 1);

            // Символы валют
            $symbols = ['USD'=>'$','RUB'=>'₽','KGS'=>'сом','UZS'=>'сўм','KZT'=>'₸','EUR'=>'€'];
            $symbol  = $symbols[$fxBase] ?? $fxBase;

            // Удобства отеля
            $amenities     = \App\Models\Amenity::where('hotel_id', $hotel->id)->first();
            $hotel_amenities = explode(',', $amenities->services ?? '');
        @endphp

        <div class="page hotel">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{ $hotel->city }}</h1>
                        <h3>{{ $hotel->__('title') }}</h3>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"
                                     data-autoplay="6000">
                                    <img loading="lazy" src="{{ Storage::url($hotel->image)}}" alt="">
                                    @if(!empty($images))
                                        @foreach($images as $file)
                                            <img loading="lazy" src="{{ Storage::url($file->image)}}" alt="">
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="address">
                            <img src="{{ route('index') }}/img/marker_in.svg" alt="">
                            {{ $hotel->__('address') }}
                        </div>

                        @if($rooms->isNotEmpty())
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="tariffs availabity">
                                        <h4>@lang('main.available')</h4>
                                        @foreach($rooms as $room)
                                            @php
                                                $image = \App\Models\Image::where('room_id', $room->id)->latest('id')->first();
                                                $roomAmenitiesOwner = \App\Models\Room::where('hotel_id', $hotel->id)->first();
                                                $room_amenities = $roomAmenitiesOwner && $roomAmenitiesOwner->amenities
                                                    ? explode(',', $roomAmenitiesOwner->amenities) : [];
                                                $items = array_slice($room_amenities, 0, 8);
                                            @endphp
                                            <div class="row">
                                                <div class="col-lg-3 col-md-5">
                                                    <div class="room">
                                                        <div class="wrap">
                                                            <div class="img-wrap">
                                                                @if ($room->image)
                                                                    <img src="{{ Storage::url($room->image) }}" alt="">
                                                                @else
                                                                    <img src="{{ route('index') }}/img/noimage.png" alt=""
                                                                         width="100">
                                                                @endif
                                                            </div>
                                                            <div class="text-wrap">
                                                                @if($room->__('title_local'))
                                                                    <h5>{{ $room->__('title_local') }}</h5>
                                                                @else
                                                                    <h5>{{ $room->__('title') }}</h5>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="amenities">
                                                            <div class="amenities-item">
                                                                <img src="{{ route('index') }}/img/icons/check.svg"
                                                                     alt="">
                                                                <div class="name">{{ $room->area }} кв. м</div>
                                                            </div>
                                                            @foreach($items as $amenity)
                                                                <div class="amenities-item">
                                                                    <img src="{{ asset('img/icons/check.svg') }}"
                                                                         alt="{{ $amenity }}">
                                                                    <div class="name">{{ $amenity }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-9 col-md-7">
                                                    <div class="tariff-wrap">
                                                        @if($room->rates->isEmpty())
                                                            <p class="text-muted">
                                                                @if(app()->getLocale() == 'ru')
                                                                    Нет доступных тарифов для этих дат и гостей
                                                                @else
                                                                    No available rates for the selected dates and guests
                                                                @endif
                                                            </p>
                                                        @else
                                                            <div class="owl-carousel owl-tariffs">
                                                                @foreach($room->rates as $rate)
                                                                    <div class="tariffs-item">
                                                                        @isset($rate)
                                                                            <h5>{{ $rate->__('title') }}</h5>
                                                                        @endisset
                                                                        @php
                                                                            // Даты / часовой пояс
                                                                            $arrival    = \Carbon\Carbon::parse($request->arrivalDate)->format('d.m.Y H:i');
                                                                            $departure  = \Carbon\Carbon::parse($request->departureDate)->format('d.m.Y H:i');
                                                                            $timezone   = \Carbon\Carbon::parse($hotel->timezone)->format('P');

                                                                            // Правила отмены (если привязаны к rate)
                                                                            $cancel     = \App\Models\CancellationRule::where('rate_id', $rate->id)->first();
                                                                            $cancelDate = \Carbon\Carbon::parse($request->arrivalDate)->subDays($cancel->free_cancellation_days ?? 0)->format('d.m.Y H:i');
                                                                            $freeDate   = \Carbon\Carbon::parse($request->arrivalDate)->format('d.m.Y H:i');

                                                                            // Кол-во ночей (минимум 1)
                                                                            $arr    = \Carbon\Carbon::parse($request->arrivalDate);
                                                                            $dep    = \Carbon\Carbon::parse($request->departureDate);
                                                                            $nights = max(1, $arr->diffInDays($dep));

                                                                            // Доплата за детей
                                                                            $price_child = 0;
                                                                            foreach ($childAgesArr as $age) {
                                                                                $age = (int)$age;
                                                                                if ($rate->free_children_age !== null && $age >= (int)$rate->free_children_age) {
                                                                                    $price_child += (int)($rate->child_extra_fee ?? 0);
                                                                                }
                                                                            }

                                                                            // Базовая цена тарифа (если у вас где-то заранее подсчитанные цены на даты — используйте их)
                                                                            $calendarPrice = (isset($ratePrices) && is_array($ratePrices) && array_key_exists($rate->id, $ratePrices))
                                                                                ? (float)$ratePrices[$rate->id]
                                                                                : (float)($rate->price ?? 0);

                                                                            // Сумма за ночь * гости * ночи + детские доплаты
                                                                            if ($adultCount >= 2) {
                                                                                $perNight = (float)($rate->price2 ?? $calendarPrice) + $price_child;
                                                                                $sum = $perNight * $adultCount * $nights;
                                                                            } else {
                                                                                $perNight = (float)$calendarPrice + $price_child;
                                                                                $sum = $perNight * max(1, $adultCount) * $nights;
                                                                            }

                                                                            // Наценка (coef) — если это коэффициент 0.08 => +8%
                                                                            $coef = (float) (config('services.main.coef') ?? 0);
                                                                            $sum  = $sum + $sum * $coef;

                                                                            // Конвертация в валюту интерфейса
                                                                            $converted = app(\App\Services\FXService::class)->convert(
                                                                                $sum,
                                                                                $rate->currency ?? ($hotel->currency ?? 'USD'),
                                                                                $fxBase
                                                                            );

                                                                            // Штраф за отмену
                                                                            $cancelRaw = 0.0;
                                                                            if ($cancel) {
                                                                                if ($cancel->penalty_type === 'fixed') {
                                                                                    $cancelRaw = (float)($cancel->penalty_amount ?? 0);
                                                                                } elseif ($cancel->penalty_type === 'night') {
                                                                                    $cancelRaw = (float)($cancel->penalty_nights ?? 0) * (float)($rate->price ?? 0);
                                                                                } else { // percent
                                                                                    $cancelRaw = ((float)($cancel->penalty_amount ?? 0) / 100.0) * $sum;
                                                                                }
                                                                            }
                                                                            $cancelPrice = app(\App\Services\FXService::class)->convert(
                                                                                $cancelRaw,
                                                                                $rate->currency ?? ($hotel->currency ?? 'USD'),
                                                                                $fxBase
                                                                            );

                                                                            // Базовые для hidden (если нужны как «источник»)
                                                                            $baseCancelPrice = round($cancelRaw);
                                                                            $basePrice       = round($sum);
                                                                        @endphp
                                                                        <div class="item bed">
                                                                            <div class="name">{{ $rate->bed_type }}</div>
                                                                        </div>
                                                                        <div class="item meal">
                                                                            <div class="name">{{ $rate->meal->__('title') }}</div>
                                                                        </div>
                                                                        @if($cancel)
                                                                            <div class="item cancel">
                                                                                <div class="name">
                                                                                    @if($cancel->cancel_policy === 'free_until_checkin')
                                                                                        @lang('main.free_cancellation') {{ $freeDate }}
                                                                                        UTC {{ $timezone }}
                                                                                    @elseif($cancel->cancel_policy === 'free_then_penalty')
                                                                                        @if(now()->lte(\Carbon\Carbon::createFromFormat('d.m.Y H:i', $cancelDate)))
                                                                                            @lang('main.free_cancellation') {{ $cancelDate }}
                                                                                            UTC {{ $timezone }}
                                                                                        @else
                                                                                            @lang('main.cancellation_is_not_avaialble')
                                                                                            .
                                                                                        @endif
                                                                                        @lang('main.cancellation_amount')
                                                                                        : {{ round($cancelPrice) }} {{ $symbol }}
                                                                                    @else
                                                                                        @lang('main.cancellation_amount')
                                                                                        : {{ round($cancelPrice) }} {{ $symbol }}
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        <div class="item price">
                                                                            {{ number_format(round($converted), 0, '.', ' ') }} {{ $symbol }}
                                                                        </div>
                                                                        <div class="btn-wrap">
                                                                            <form action="{{ route('order', $rate->id) }}">
                                                                                <input type="hidden" name="propertyId"
                                                                                       value="{{ $hotel->id }}">
                                                                                <input type="hidden" name="arrivalDate"
                                                                                       value="{{ $request->arrivalDate }}">
                                                                                <input type="hidden"
                                                                                       name="departureDate"
                                                                                       value="{{ $request->departureDate }}">
                                                                                <input type="hidden" name="roomCount"
                                                                                       value="{{ $request->roomCount }}">
                                                                                <input type="hidden" name="adult"
                                                                                       value="{{ $adultCount }}">
                                                                                <input type="hidden" name="child"
                                                                                       value="{{ (int)($request->child ?? 0) }}">
                                                                                <input type="hidden" name="childAges[]"
                                                                                       value="{{ implode(',', $childAgesArr) }}">
                                                                                <input type="hidden" name="room_id"
                                                                                       value="{{ $rate->room_id }}">
                                                                                <input type="hidden" name="rate_id"
                                                                                       value="{{ $rate->id }}">
                                                                                <input type="hidden" name="meal_id"
                                                                                       value="{{ $rate->meal_id }}">
                                                                                <input type="hidden"
                                                                                       name="cancellation_id"
                                                                                       value="{{ $cancel->id ?? ''}}">
                                                                                <input type="hidden" name="hotel_id"
                                                                                       value="{{ $hotel->id }}">
                                                                                <input type="hidden" name="title"
                                                                                       value="{{ $rate->title }}">
                                                                                <input type="hidden" name="cancelDate"
                                                                                       value="{{ $cancelDate }}">
                                                                                <input type="hidden" name="cancelPrice"
                                                                                       value="{{ round($cancelPrice) }}">
                                                                                <input type="hidden"
                                                                                       name="cancelPriceSource"
                                                                                       value="{{ round($baseCancelPrice) }}">
                                                                                <input type="hidden" name="sum"
                                                                                       value="{{ round($converted) }}">
                                                                                <input type="hidden" name="price"
                                                                                       value="{{ round($basePrice) }}">
                                                                                <input type="hidden" name="currency"
                                                                                       value="{{ $symbol }}">
                                                                                <input type="hidden" name="source_sym"
                                                                                       value="{{ $rate->currency ?? ($hotel->currency ?? 'USD') }}">
                                                                                <button class="more">@lang('main.book')</button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <h4>@lang('main.description')</h4>
                        <div class="descr">{!! $hotel->__('description') !!}</div>
                        @empty($hotel_amenities)
                        @else
                            <div class="row amenities">
                                <h4>@lang('main.amenities')</h4>
                                @foreach($hotel_amenities as $amenity)
                                    <div class="col-lg-4 col-md-6 amenities-item">
                                        <img src="{{ asset('img/icons/check.svg') }}" alt="">
                                        <div class="name">{{ $amenity }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endempty
                        <div class="maps">
                            <h4>@lang('main.location')</h4>
                            <!-- Подключение стилей Leaflet -->
                            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
                            <div id="map"></div>
                            <!-- Подключение скрипта Leaflet -->
                            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
                            <script>
                                var lat = {{ $hotel->lat }};
                                var lng = {{ $hotel->lng }};
                                var map = L.map('map').setView([lat, lng], 15);

                                // Добавление слоя OpenStreetMap
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                }).addTo(map);

                                var marker = null; // Переменная для хранения последнего маркера

                                // Если есть начальные координаты, устанавливаем маркер
                                if (lat && lng) {
                                    marker = L.marker([lat, lng]).addTo(map)
                                        .bindPopup('Широта: ' + lat.toFixed(6) + '<br>Долгота: ' + lng.toFixed(6))
                                        .openPopup();
                                }

                                // // Добавление масштаба
                                L.control.scale().addTo(map);

                                // Обработчик клика по карте
                                map.on('click', function (e) {
                                    var lat = e.latlng.lat;  // Широта
                                    var lng = e.latlng.lng;  // Долгота

                                    // Удаление старого маркера, если он есть
                                    if (marker) {
                                        map.removeLayer(marker);
                                    }

                                    // Обновление значений в полях ввода
                                    document.getElementById('lat').value = lat.toFixed(6);
                                    document.getElementById('lng').value = lng.toFixed(6);

                                    // Добавление маркера на выбранную точку
                                    if (lat && lng) {
                                        marker = L.marker([lat, lng]).addTo(map)
                                            .bindPopup('Широта: ' + lat.toFixed(6) + '<br>Долгота: ' + lng.toFixed(6))
                                            .openPopup();
                                    }
                                });
                            </script>
                            <div class="address">
                                <img src="{{ route('index') }}/img/marker_in.svg" alt="">
                                {{ $hotel?->__('address') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    @else
        @include('layouts.auth')
    @endauth
@endsection