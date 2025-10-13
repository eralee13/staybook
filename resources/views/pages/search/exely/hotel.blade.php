@extends('layouts.master')

@section('title', optional($hotel ?? null)->title ?? 'Отель')

@section('content')
    @auth
        <div class="page hotel">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        @php
                            // входные параметры
                            $propertyId = request('propertyId');
                            $roomTypeId = request('roomTypeId');

                            // модели
                            $hotel = \App\Models\Hotel::where('exely_id', $propertyId)->first();
                            $roomModelFromRequest = \App\Models\Room::where('exely_id', $roomTypeId)->first();

                            // коллекции (если они есть у roomModelFromRequest)
                            $rates = collect(data_get($roomModelFromRequest, 'rates', []))->filter()->all();
                            $amenities = collect(data_get($roomModelFromRequest, 'amenities', []))->filter()->all();

                            // таймзона/UTC
                            $hotelTz = $hotel->timezone ?? config('app.timezone', 'UTC');
                            $hotelUtcOffset = \Carbon\Carbon::now($hotelTz)->format('P');

                            // валюта
                            $fxBase = $fxBase ?? 'USD';
                            $fxRates = $fxRates ?? [];
                            $toCurrency = strtoupper($fxBase);
                            $symbols = ['USD' => '$', 'RUB' => '₽', 'KGS' => 'сом', 'UZS' => 'сўм'];
                            $symbol = $symbols[$toCurrency] ?? $toCurrency;

                            // группировка номеров (rooms может не быть)
                            $groupedRooms = collect($rooms ?? [])->groupBy(fn($r) => data_get($r, 'roomType.id'));
                        @endphp
                        <div class="row">
                            <div class="col-md-12">
                                <h1>{{ $hotel->city ?? '' }}</h1>
                                <h3>{{ $hotel->title ?? '' }}</h3>
                                @if(!empty(optional($hotel)->image))
                                    <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"
                                         data-autoplay="30000">
                                        <img src="{{ Storage::url($hotel->image) }}" alt="">
                                        @isset($images)
                                            @foreach($images as $file)
                                                <img loading="lazy" src="{{ Storage::url($file->image)}}" alt="">
                                            @endforeach
                                        @endisset
                                    </div>
                                @else
                                    <img loading="lazy" src="{{ route('index')}}/img/noimage.png" alt=""
                                         style="margin-bottom: 10px">
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="tariffs availabity">
                                    <h4>@lang('main.available')</h4>
                                    <div class="row" style="margin-top: 30px">
                                        @foreach($groupedRooms as $roomTypeIdKey => $roomRates)
                                            @php
                                                $roomModel = \App\Models\Room::where('exely_id', $roomTypeIdKey)->first();
                                                $amenitiesList = [];
                                                if ($roomModel?->amenities) {
                                                    $amenitiesList = array_slice(
                                                        array_values(array_filter(array_map('trim', explode(',', (string)$roomModel->amenities)), fn($a)=>$a!=='')),
                                                        0, 8
                                                    );
                                                }
                                            @endphp
                                            <div class="col-lg-3 col-md-5">
                                                <div class="room">
                                                    @if($roomModel)
                                                        <div class="wrap">
                                                            <div class="img-wrap">
                                                                @if ($roomModel->image)
                                                                    <img src="{{ Storage::url($roomModel->image) }}" alt="">
                                                                @else
                                                                    <img loading="lazy"
                                                                         src="{{ route('index')}}/img/noimage.png"
                                                                         alt="">
                                                                @endif
                                                            </div>
                                                            <div class="text-wrap">
                                                                <h5>{{ $roomModel?->__('title') }}</h5>
                                                            </div>
                                                        </div>
                                                        <div class="amenities">
                                                            <div class="amenities-item">
                                                                <img src="{{ route('index') }}/img/icons/check.svg"
                                                                     alt="">
                                                                <div class="name">{{ $roomModel->area }} кв. м</div>
                                                            </div>
                                                            @foreach($amenitiesList as $amenity)
                                                                <div class="amenities-item">
                                                                    <img src="{{ asset('img/icons/check.svg') }}"
                                                                         alt="{{ $amenity }}">
                                                                    <div class="name">{{ $amenity }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-9 col-md-7">
                                                <div class="tariff-wrap">
                                                    <div class="owl-carousel owl-tariffs">
                                                        @foreach($roomRates as $room)
                                                            @php
                                                                $arrival   = optional(\Carbon\Carbon::parse(data_get($room, 'stayDates.arrivalDateTime')))->timezone($hotelTz)->format('d.m.Y H:i');
                                                                $departure = optional(\Carbon\Carbon::parse(data_get($room, 'stayDates.departureDateTime')))->timezone($hotelTz)->format('d.m.Y H:i');
                                                                $cancelDate = optional(\Carbon\Carbon::parse(data_get($room, 'cancellationPolicy.freeCancellationDeadlineLocal'), $hotelTz))->format('d.m.Y H:i');

                                                                $penalty = (float) (data_get($room, 'cancellationPolicy.penaltyAmount', 0));
                                                                $coef = (float) (config('services.main.coef') ?? 0);
                                                                $baseCancelPrice = round($penalty * $coef + $penalty);

                                                                $priceBeforeTax = (float) data_get($room, 'total.priceBeforeTax', 0);
                                                                $brutPrice = $priceBeforeTax > 0 ? round($priceBeforeTax / 0.92) : 0;

                                                                $srcCurrency = data_get($room, 'currencyCode', 'USD');
                                                                $fx = app(\App\Services\FXService::class);

                                                                $convertedCancel = $fx->convert($baseCancelPrice, $srcCurrency, $fxBase);
                                                                $netConv = $fx->convert($priceBeforeTax, $srcCurrency, $fxBase);
                                                                $brutConv = $fx->convert($brutPrice, $srcCurrency, $fxBase);
                                                            @endphp

                                                            <div class="tariffs-item">
                                                                @can('edit-contact')
                                                                    <small>Осталось
                                                                        квот: {{ data_get($room, 'availability') }}</small>
                                                                @endcan
                                                                @if(data_get($room, 'fullPlacementsName'))
                                                                    <h5>{{ data_get($room, 'fullPlacementsName') }}</h5>
                                                                @endif

                                                                <div class="dates">
                                                                    @lang('main.check-in'): {{ $arrival }}
                                                                    UTC {{ $hotelUtcOffset }}
                                                                </div>
                                                                <div class="dates">
                                                                    @lang('main.check-out'): {{ $departure }}
                                                                    UTC {{ $hotelUtcOffset }}
                                                                </div>
                                                                <br>
                                                                <div class="item meal">
                                                                    <div class="name">{{ data_get($room, 'mealPlanCode') }}</div>
                                                                </div>
                                                                <div class="item cancel">
                                                                    <div class="name">
                                                                        @if(data_get($room, 'cancellationPolicy.freeCancellationPossible') === true)
                                                                            @lang('main.free_cancellation') {{ $cancelDate }}
                                                                            (UTC {{ $hotelUtcOffset }}).
                                                                            @lang('main.cancellation_amount')
                                                                            : {{ round($convertedCancel) }} {{ $symbol }}
                                                                        @else
                                                                            @lang('main.cancellation_amount')
                                                                            : {{ round($convertedCancel) }} {{ $symbol }}
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div class="item price">
{{--                                                                    @can('edit-contact')--}}
{{--                                                                        <small style="font-size: 12px">NET: {{ round($netConv) }} {{ $symbol }}</small>--}}
{{--                                                                        <br>--}}
{{--                                                                    @endcan--}}
                                                                    {{ round($brutConv) }} {{ $symbol }}
                                                                </div>
                                                                <div class="btn-wrap">
                                                                    <form action="{{ route('order_exely', data_get($room, 'roomType.id')) }}">
                                                                        <input type="hidden" name="propertyId"
                                                                               value="{{ data_get($room, 'propertyId') }}">
                                                                        <input type="hidden" name="arrivalDate"
                                                                               value="{{ data_get($room, 'stayDates.arrivalDateTime') }}">
                                                                        <input type="hidden" name="departureDate"
                                                                               value="{{ data_get($room, 'stayDates.departureDateTime') }}">
                                                                        <input type="hidden" name="adultCount"
                                                                               value="{{ data_get($room, 'guestCount.adultCount', 0) }}">

                                                                        @php $childAges = (array) data_get($room, 'guestCount.childAges', []); @endphp
                                                                        @if(!empty($childAges))
                                                                            <input type="hidden" name="childAges[]"
                                                                                   value="{{ implode(',', $childAges) }}">
                                                                        @endif

                                                                        <input type="hidden" name="ratePlanId"
                                                                               value="{{ data_get($room, 'ratePlan.id') }}">
                                                                        <input type="hidden" name="roomTypeId"
                                                                               value="{{ data_get($room, 'roomType.id') }}">
                                                                        <input type="hidden" name="placements"
                                                                               value="{{ json_encode(data_get($room, 'roomType.placements', [])) }}">

                                                                        <input type="hidden" name="categoryName"
                                                                               value="{{ data_get($room, 'fullPlacementsName') }}">
                                                                        <input type="hidden" name="mealCode"
                                                                               value="{{ data_get($room, 'mealPlanCode') }}">
                                                                        <input type="hidden" name="cancelPossible"
                                                                               value="{{ data_get($room, 'cancellationPolicy.freeCancellationPossible') ? 1 : 0 }}">
                                                                        <input type="hidden" name="cancelUtc"
                                                                               value="{{ data_get($room, 'cancellationPolicy.freeCancellationDeadlineUtc') }}">
                                                                        <input type="hidden" name="cancelLocal"
                                                                               value="{{ data_get($room, 'cancellationPolicy.freeCancellationDeadlineLocal') }}">
                                                                        <input type="hidden" name="cancelDate"
                                                                               value="{{ $cancelDate }}">
                                                                        <input type="hidden" name="cancelPrice"
                                                                               value="{{ round($convertedCancel) }}">
                                                                        <input type="hidden" name="cancelPriceSource"
                                                                               value="{{ data_get($room, 'cancellationPolicy.penaltyAmount') }}">
                                                                        <input type="hidden" name="checkSum"
                                                                               value="{{ data_get($room, 'checksum') }}">

                                                                        @foreach((array) data_get($room, 'includedServices', []) as $serv)
                                                                            <input type="hidden" name="servicesId"
                                                                                   value="{{ data_get($serv, 'id') }}">
                                                                        @endforeach

                                                                        <input type="hidden" name="hotel"
                                                                               value="{{ data_get($room, 'fullPlacementsName') }}">
                                                                        <input type="hidden" name="hotel_id"
                                                                               value="{{ data_get($room, 'propertyId') }}">
                                                                        <input type="hidden" name="room_id"
                                                                               value="{{ data_get($room, 'roomType.id') }}">
                                                                        <input type="hidden" name="title"
                                                                               value="{{ data_get($room, 'fullPlacementsName') }}">
                                                                        <input type="hidden" name="sum"
                                                                               value="{{ round($brutConv) }}">
                                                                        <input type="hidden" name="price"
                                                                               value="{{ round((float) data_get($room, 'total.priceBeforeTax', 0)) }}">
                                                                        <input type="hidden" name="currency"
                                                                               value="{{ $symbol }}">
                                                                        <input type="hidden" name="source_sym"
                                                                               value="{{ data_get($room, 'currencyCode') }}">

                                                                        <button class="more">@lang('main.book')</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if(!empty($hotel?->description))
                                    <h4>@lang('main.description')</h4>
                                    <div class="descr">
                                        {!! $hotel?->__('description') !!}
                                    </div>
                                @endif

                                @if(!empty($amenities))
                                    @php
                                        // Нормализуем удобства в единый массив строк
                                        $amenitiesList = collect(
                                            is_string($amenities)
                                                ? preg_split('/[,\n;]+/u', $amenities)   // одна строка -> разбить
                                                : (array)$amenities                       // массив/коллекция
                                        )
                                        // Если внутри есть элементы-строки с запятыми, разрежем и их
                                        ->flatMap(function ($item) {
                                            if (is_string($item)) {
                                                return preg_split('/[,\n;]+/u', $item);
                                            }
                                            return (array)$item;
                                        })
                                        ->map(fn($v) => trim((string)$v))
                                        ->filter()            // убрать пустые
                                        ->unique()            // убрать дубли
                                        ->values();
                                    @endphp

                                    @if($amenitiesList->isNotEmpty())
                                        <div class="row amenities">
                                            <h4>@lang('main.amenities')</h4>
                                            @foreach($amenitiesList as $amenity)
                                                <div class="col-lg-4 col-md-6">
                                                    <div class="amenities-item">
                                                        <img src="{{ asset('img/icons/check.svg') }}"
                                                             alt="{{ $amenity }}"
                                                             style="width:20px;height:20px;margin-right:8px;">
                                                        <span class="name">{{ $amenity }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                                <div class="maps">
                                    <h4>@lang('main.location')</h4>
                                    <!-- Подключение стилей Leaflet -->
                                    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
                                    <div id="map" style="height: 340px"></div>
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
            </div>
        </div>
    @else
        @include('layouts.auth')
    @endauth
@endsection