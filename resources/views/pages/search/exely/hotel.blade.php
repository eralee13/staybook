@extends('layouts.master')

@section('title', optional($hotel ?? null)->title ?? 'Отель')

@section('content')
    @auth
        <div class="page hotel">
            <div class="container">
                @php
                    // ---------------- INPUT ----------------
                    $propertyId = (string) request('propertyId');
                    $roomTypeId = (string) request('roomTypeId');

                    // ---------------- MODELS (1 query each) ----------------
                    $hotel = \App\Models\Hotel::query()
                        ->where('exely_id', $propertyId)
                        ->first();

                    // если отель не найден — не падаем
                    if (!$hotel) {
                        $hotel = (object)[
                            'title' => '',
                            'city' => '',
                            'image' => null,
                            'lat' => 0,
                            'lng' => 0,
                            'timezone' => config('app.timezone', 'UTC'),
                        ];
                    }

                    // ---------------- TZ ----------------
                    $hotelTz = $hotel->timezone ?? config('app.timezone', 'UTC');
                    $hotelUtcOffset = \Carbon\Carbon::now($hotelTz)->format('P');

                    // ---------------- GROUP ROOMS (safe) ----------------
                    $roomsCollection = collect($rooms ?? []);
                    $groupedRooms = $roomsCollection->groupBy(fn($r) => data_get($r, 'roomType.id'));

                    // ---------------- PREFETCH Room models (avoid N+1) ----------------
                    $roomTypeIds = $groupedRooms->keys()->filter()->values()->all();

                    $roomModelsByExely = \App\Models\Room::query()
                        ->whereIn('exely_id', $roomTypeIds)
                        ->get()
                        ->keyBy('exely_id');

                    // ---------------- HOTEL IMAGES (optional) ----------------
                    $images = $images ?? collect();
                @endphp

                {{-- HEADER --}}
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{ $hotel->city ?? '' }}</h1>
                        <h3>{{ $hotel->title ?? '' }}</h3>

                        @if(!empty($hotel->image))
                            <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"
                                 data-autoplay="30000">
                                <img src="{{ Storage::url($hotel->image) }}" alt="">
                                @foreach($images as $file)
                                    <img loading="lazy" src="{{ Storage::url($file->image) }}" alt="">
                                @endforeach
                            </div>
                        @else
                            <img loading="lazy" src="{{ route('index') }}/img/noimage.png" alt=""
                                 style="margin-bottom: 10px">
                        @endif
                    </div>
                </div>

                {{-- TARIFFS --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="tariffs availabity">
                            <h4>@lang('main.available')</h4>

                            <div class="row" style="margin-top: 30px">
                                @foreach($groupedRooms as $roomTypeIdKey => $roomRates)
                                    @php
                                        /** @var \App\Models\Room|null $roomModel */
                                        $roomModel = $roomModelsByExely->get((string)$roomTypeIdKey);

                                        $amenitiesList = [];
                                        if ($roomModel?->amenities) {
                                            $amenitiesList = array_slice(
                                                array_values(array_filter(array_map('trim', explode(',', (string)$roomModel->amenities)), fn($a)=>$a!=='')),
                                                0, 8
                                            );
                                        }
                                    @endphp

                                    {{-- LEFT ROOM CARD --}}
                                    <div class="col-lg-3 col-md-5">
                                        <div class="room">
                                            <div class="wrap">
                                                <div class="img-wrap">
                                                    @if($roomModel?->image)
                                                        <img src="{{ Storage::url($roomModel->image) }}" alt="">
                                                    @else
                                                        <img loading="lazy" src="{{ route('index') }}/img/noimage.png"
                                                             alt="">
                                                    @endif
                                                </div>
                                                <div class="text-wrap">
                                                    <h5>{{ $roomModel?->__('title') ?? data_get($roomRates->first(), 'roomType.name', '') }}</h5>
                                                </div>
                                            </div>

                                            <div class="amenities">
                                                @if(!empty($roomModel?->area))
                                                    <div class="amenities-item">
                                                        <img src="{{ route('index') }}/img/icons/check.svg" alt="">
                                                        <div class="name">{{ $roomModel->area }} кв. м</div>
                                                    </div>
                                                @endif

                                                @foreach($amenitiesList as $amenity)
                                                    <div class="amenities-item">
                                                        <img src="{{ asset('img/icons/check.svg') }}"
                                                             alt="{{ $amenity }}">
                                                        <div class="name">{{ $amenity }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- RIGHT TARIFFS SLIDER --}}
                                    <div class="col-lg-9 col-md-7">
                                        <div class="tariff-wrap">
                                            <div class="owl-carousel owl-tariffs">
                                                @foreach($roomRates as $room)
                                                    @php
                                                        // даты
                                                        $arrivalDt = \Carbon\Carbon::parse(data_get($room, 'stayDates.arrivalDateTime'))->timezone($hotelTz);
                                                        $departDt  = \Carbon\Carbon::parse(data_get($room, 'stayDates.departureDateTime'))->timezone($hotelTz);

                                                        $arrival   = $arrivalDt->format('d.m.Y H:i');
                                                        $departure = $departDt->format('d.m.Y H:i');

                                                        // deadline (может быть null)
                                                        $cancelDeadline = data_get($room, 'cancellationPolicy.freeCancellationDeadlineLocal');
                                                        $cancelDate = $cancelDeadline
                                                            ? \Carbon\Carbon::parse($cancelDeadline)->timezone($hotelTz)->format('d.m.Y H:i')
                                                            : null;

                                                        // ВАЖНО: берём уже посчитанные значения из контроллера
                                                        $convTotal  = (float) data_get($room, 'conv_total', 0);
                                                        $convSymbol = (string) data_get($room, 'conv_symbol', '');

                                                        // conv_cancel может быть null (и это правильно)
                                                        $convCancel = data_get($room, 'conv_cancel'); // null|float
                                                        $freeCancel = data_get($room, 'cancellationPolicy.freeCancellationPossible') === true;
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
                                                                    @lang('main.free_cancellation')
                                                                    {{ $cancelDate }} (UTC {{ $hotelUtcOffset }}).
                                                                @endif

                                                                @php
                                                                    $convCancel = data_get($room,'conv_cancel');
                                                                    $convSymbol = data_get($room,'conv_symbol');
                                                                @endphp

                                                                @if($convCancel !== null)
                                                                    @lang('main.cancellation_amount'):
                                                                    {{ number_format((float)$convCancel,0,'.',' ') }} {{ $convSymbol }}
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="item price">
                                                            {{ number_format($convTotal, 0, '.', ' ') }} {{ $convSymbol }}
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
                                                                       value="{{ data_get($room, 'guestCount.adultCount', 1) }}">

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
                                                                       value="{{ $freeCancel ? 1 : 0 }}">
                                                                <input type="hidden" name="cancelUtc"
                                                                       value="{{ data_get($room, 'cancellationPolicy.freeCancellationDeadlineUtc') }}">
                                                                <input type="hidden" name="cancelLocal"
                                                                       value="{{ data_get($room, 'cancellationPolicy.freeCancellationDeadlineLocal') }}">
                                                                <input type="hidden" name="cancelDate"
                                                                       value="{{ $cancelDate }}">
                                                                <input type="hidden" name="cancelPrice"
                                                                       value="{{ $convCancel !== null ? round((float)$convCancel) : '' }}">

                                                                <input type="hidden" name="checkSum"
                                                                       value="{{ data_get($room, 'checksum') }}">

                                                                @foreach((array) data_get($room, 'includedServices', []) as $serv)
                                                                    <input type="hidden" name="servicesId"
                                                                           value="{{ data_get($serv, 'id') }}">
                                                                @endforeach

                                                                <input type="hidden" name="sum"
                                                                       value="{{ round($convTotal) }}">
                                                                <input type="hidden" name="currency"
                                                                       value="{{ $convSymbol }}">
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

                        {{-- DESCRIPTION --}}
                        @if(!empty($hotel?->description))
                            <h4>@lang('main.description')</h4>
                            <div class="descr">{!! $hotel?->__('description') !!}</div>
                        @endif

                        {{-- MAP --}}
                        <div class="maps">
                            <h4>@lang('main.location')</h4>
                            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
                            <div id="map" style="height: 340px"></div>
                            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
                            <script>
                                var lat = {{ (float)($hotel->lat ?? 0) }};
                                var lng = {{ (float)($hotel->lng ?? 0) }};
                                var map = L.map('map').setView([lat || 0, lng || 0], (lat && lng) ? 15 : 2);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(map);

                                if (lat && lng) {
                                    L.marker([lat, lng]).addTo(map);
                                }
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