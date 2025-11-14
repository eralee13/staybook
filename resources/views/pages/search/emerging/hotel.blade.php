@extends('layouts.master')

@php
    // ---------- SAFE INPUTS ----------
    /** @var \Illuminate\Http\Request $request */
    $arrivalDate   = (string) request('arrivalDate', now()->format('Y-m-d'));
    $departureDate = (string) request('departureDate', now()->addDay()->format('Y-m-d'));
    $residency     = strtoupper((string) request('residency', 'KG'));
    $regionId      = (string) request('region_id', '');

    $hidParam      = (string) request('hid', request('apiHotelId', '')); // HID из строки запроса

    // Safe Carbon dates for template usage
    try {
        $arrival = isset($arrival) && $arrival instanceof \Carbon\Carbon
            ? $arrival
            : \Carbon\Carbon::parse($arrivalDate);
    } catch (\Throwable $e) {
        $arrival = \Carbon\Carbon::now();
    }
    try {
        $departure = isset($departure) && $departure instanceof \Carbon\Carbon
            ? $departure
            : \Carbon\Carbon::parse($departureDate);
    } catch (\Throwable $e) {
        $departure = \Carbon\Carbon::now()->addDay();
    }

    // Guarantee meals map exists
    if (!isset($meals) || !is_array($meals)) {
        $meals = [];
    }

    // rooms: гарантированно массив вида [[adults=>1, childAges=>[]], ...]
    $roomsFromReq = $request->input('rooms', []);
    $roomsSafe = [];
    if (is_array($roomsFromReq) || $roomsFromReq instanceof \Traversable) {
        foreach ($roomsFromReq as $r) {
            $ad = (int) data_get($r, 'adults', 1);
            $ca = data_get($r, 'childAges', []);
            $ca = is_array($ca) ? array_values(array_filter($ca, fn($v)=>$v!==null && $v!=='')) : [];
            $roomsSafe[] = ['adults'=>$ad, 'childAges'=>$ca];
        }
    }
    if (!$roomsSafe) $roomsSafe = [['adults'=>1,'childAges'=>[]]];

    $totalAdults = collect($roomsSafe)->sum(fn($r) => (int)($r['adults'] ?? 0));
    $childs = collect($roomsSafe)->sum(fn($r) => is_array($r['childAges'] ?? []) ? count($r['childAges']) : 0);

    // ---------- HOTEL FALLBACKS ----------
    $hotelTitle = (string) data_get($hotel ?? null, 'title', __('main.hotel'));
    $hotelCity  = (string) data_get($hotel ?? null, 'city', '');
    $hotelStars = data_get($hotel ?? null, 'rating');

    // ---------- ETG DATA ----------
    // Нормализованный контроллером $etgroom: ['hotel'=>[], 'rates'=>[], 'message'=>string]
    $rates   = (array) data_get($etgroom ?? null, 'rates', data_get($etgroom ?? null, 'data.rates', []));
    $message = (string) (data_get($etgroom ?? null, 'message') ?? '');

    // Картинки: сначала из ETG ответа (если есть), иначе из $tmimages
    $images = [];
    foreach ((array) data_get($etgroom ?? null, 'hotel.images', []) as $img) {
        if (is_string($img)) $images[] = $img;
        elseif (is_array($img) && !empty($img['image'])) $images[] = $img['image'];
    }
    if (empty($images)) {
        foreach ((array)($tmimages ?? []) as $im) {
            $src = is_object($im) ? ($im->image ?? null) : (is_array($im) ? ($im['image'] ?? null) : null);
            if ($src) $images[] = $src;
        }
    }

    // Удобства (срез)
    $amen = array_slice(array_filter(array_map('trim', (array)($roomAmenity ?? []))), 0, 12);

    // Валютные символы для отображения исходных ETG-цен
    $symbols = ['USD'=>'$', 'EUR'=>'€', 'RUB'=>'₽', 'KGS'=>'сом', 'KZT'=>'₸', 'UZS'=>'сўм'];

    // ---------- HELPERS ----------
    $getPayment = function(array $rate) {
        $pay = (array) data_get($rate, 'payment_options.payment_types.0', []);
        return [
            'amount'   => (float) ($pay['amount'] ?? 0),
            'currency' => (string) ($pay['currency_code'] ?? 'USD'),
        ];
    };

    $getCancellation = function(array $rate) {
        $policies = (array) data_get($rate, 'payment_options.payment_types.0.cancellation_penalties.policies', []);
        $freeBefore = data_get($rate, 'payment_options.payment_types.0.cancellation_penalties.free_cancellation_before');
        if (!$policies && !$freeBefore) return null;

        // Берём последнюю политику как «финальную»
        $policy = $policies ? (end($policies) ?: reset($policies)) : [];
        return [
            'free_until'    => $freeBefore ?: (data_get($policy, 'start_at') ?? data_get($policy, 'until') ?? null),
            'amount_charge' => (float) data_get($policy, 'amount_charge', 0),
            'currency'      => (string) (data_get($policy, 'currency_code', data_get($rate,'payment_options.payment_types.0.currency_code','USD'))),
            'is_free'       => ((float) data_get($policy,'amount_charge',0) == 0) || !empty($freeBefore),
        ];
    };

    $hpHotelName = (string) data_get($etgroom ?? null, 'hotel.title', $hotelTitle);
    $hpHotelCity = (string) data_get($etgroom ?? null, 'hotel.city',  $hotelCity);

@endphp

@section('title', $hpHotelName)

@section('content')

    @auth
        <div class="page hotel">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <h1>{{ $hpHotelCity }}</h1>
                                <h3>{{ $hpHotelName }}</h3>
                                {{--                                    @if(!empty(optional($hotel)->image))--}}
                                {{--                                        <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"--}}
                                {{--                                             data-autoplay="30000">--}}
                                {{--                                            <img src="{{ Storage::url($hotel->image) }}" alt="">--}}
                                {{--                                            @isset($images)--}}
                                {{--                                                @foreach($images as $file)--}}
                                {{--                                                    <img loading="lazy" src="{{ Storage::url($file->image)}}" alt="">--}}
                                {{--                                                @endforeach--}}
                                {{--                                            @endisset--}}
                                {{--                                        </div>--}}
                                {{--                                    @else--}}
                                {{--                                        <img loading="lazy" src="{{ route('index')}}/img/noimage.png" alt=""--}}
                                {{--                                             style="margin-bottom: 10px">--}}
                                {{--                                    @endif--}}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="tariffs availabity">
                                    <h4>@lang('main.available')</h4>
                                    <div class="row" style="margin-top: 30px">
                                        <div class="col-lg-12 col-md-12">
                                            <div class="tariff-wrap">
                                                <div class="owl-carousel owl-tariffs">
                                                    @foreach($etgrooms['rates'] as $rate)
                                                        @php
                                                            $cancel = $getCancellation($rate);
                                                            $freeText = '';
                                                            if ($cancel) {
                                                                $freeUntil = $cancel['free_until'];
                                                                $freeText = $cancel['is_free']
                                                                    ? __('main.free_cancellation') . ($freeUntil ? (' — ' . $freeUntil) : '')
                                                                    : __('main.cancellation_amount') . ': ' . number_format((float)$cancel['amount_charge'], 2, '.', ' ') . ' ' . ($symbols[$cancel['currency']] ?? $cancel['currency']);
                                                            }
                                                            $pay    = $getPayment($rate);
                                                            $cancel = $getCancellation($rate);

                                                            $amount = (float) ($pay['amount'] ?? 0);
                                                            $cur    = strtoupper($pay['currency'] ?? 'USD');
                                                            $symbol = $symbols[$cur] ?? $cur;
                                                            $mealKey   = (string) data_get($rate,'meal','');
                                                            $mealHuman = $meals[$mealKey] ?? $mealKey;

                                                            $match_hash = (string) data_get($rate,'match_hash','');
                                                            $book_hash  = (string) data_get($rate,'book_hash','');
                                                            $rooms = $request->input('rooms', []);
                                                            $payment = $rate['payment_options']['payment_types'][0];
                                                            $coef = config('app.main_coef');
            $price = $rate['payment_options']['payment_types'][0]['amount'] ?? 0;
            $totalPrice = number_format( ($price / $coef ) , 2, '.', '');
            $rooms = $request->input('rooms', []);
            $payment = $rate['payment_options']['payment_types'][0];
            // $localDate = Carbon\Carbon::parse($payment['cancellation_penalties']['policies'][0]['end_at'], 'UTC')->setTimezone(trim($hotel->utc));
            // $localDate = $localDate->format('d-m-Y H:i:s');

            if($payment['cancellation_penalties']['free_cancellation_before'] == true){

                $pay_end_date = Carbon\Carbon::createFromDate($payment['cancellation_penalties']['policies'][0]['end_at'])->format('d.m.Y H:i:s');

                $penaltPrice = $payment['cancellation_penalties']['policies'][1]['amount_charge'] ?? 0;
                $penaltyPrice = number_format( ( (float)$penaltPrice  / $coef), 2, '.', '');

            }else{
                $pay_end_date = '';
                $penaltyPrice = 0;
            }

            $toCurrency = strtoupper($fxBase ?? 'USD');

            $symbols = [
                'USD' => '$',
                'RUB' => '₽',
                'KGS' => 'сом',
                'UZS' => 'сўм',
            ];

            $converted = app(\App\Services\FXService::class)->convert($totalPrice, $payment['currency_code'], $fxBase);
            $symbol = $symbols[$toCurrency] ?? $toCurrency;

            $cancelConverted = app(\App\Services\FXService::class)->convert($penaltyPrice, $payment['currency_code'], $fxBase);

                                                        @endphp
                                                        <div class="tariffs-item">
                                                            <h5>{{ $rate['room_name'] ?? '—' }}</h5>
                                                            <div class="item bed">
                                                                <div class="name">{{ $rate['room_data_trans']['bedding_type'] }}</div>
                                                            </div>
                                                            <div class="item meal">
                                                                <div class="name">{{ $rate['meal'] ?? '—' }}</div>
                                                            </div>
                                                            <div class="item cancel">
                                                                <div class="name">
                                                                    {{ $freeText }}
                                                                </div>
                                                            </div>
                                                            <div class="item price">
                                                                {{ number_format((int)$amount, 0, '.', ' ') }} {{ $symbol }}
                                                            </div>
                                                            <div class="btn-wrap">
                                                                <form action="{{ route('order_etg') }}">
                                                                    <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                                                                    <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                                                                    @foreach ($rooms as $i => $room)
                                                                        <input type="hidden" name="rooms[{{ $i }}][adults]" value="{{ $room['adults'] }}">

                                                                        @if (isset($room['childAges']))
                                                                            @foreach ($room['childAges'] as $a => $age)
                                                                                <input type="hidden" name="rooms[{{ $i }}][childAges][]" value="{{ $age }}">
                                                                            @endforeach
                                                                        @endif
                                                                    @endforeach
                                                                    <input type="hidden" name="meal_id" value="{{ $rate['meal'] ?? null }}">
                                                                    <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                                                                    <input type="hidden" name="rate_name" value="{{ $rate['room_name'] }}">
                                                                    <input type="hidden" name="room_name" value="{{ $rate['room_data_trans']['main_name'] }}">
                                                                    <input type="hidden" name="bedTypeDesc" value="{{ $rate['room_data_trans']['bedding_type'] }}">
                                                                    <input type="hidden" name="book_hash" value="{{ $rate['book_hash'] }}">
                                                                    <input type="hidden" name="match_hash" value="{{ $rate['match_hash'] }}">
                                                                    <input type="hidden" name="refundable" value="{{ $payment['cancellation_penalties']['free_cancellation_before'] }}">
                                                                    <input type="hidden" name="cancelDate"
                                                                           value="{{ $pay_end_date }}">
                                                                    <input type="hidden" name="cancelPriceAnullation"
                                                                           value="{{ $payment['cancellation_penalties']['policies'][0]['amount_charge'] }}">
                                                                    <input type="hidden" name="cancelPrice"
                                                                           value="{{ $cancelConverted }}">
                                                                    <input type="hidden" name="price" value="{{ $price }}">
                                                                    <input type="hidden" name="totalPrice" value="{{ $converted }}">
                                                                    <input type="hidden" name="currency"
                                                                           value="{{ $rate['payment_options']['payment_types'][0]['currency_code'] }}">
                                                                    <input type="hidden" name="utc"  value="{{ $hotel->utc }}">
{{--                                                                    <input type="hidden" name="etgimage"  value="{{ $tmimage }}">--}}
                                                                    <input type="hidden" name="increase_percent">
                                                                    <input type="hidden" name="residency" value="{{ $request->residency ?? '' }}">
                                                                    <input type="hidden" name="tax_not_included"
                                                                           value='@json(collect($payment["tax_data"]["taxes"])->where("included_by_supplier", false)->values())'>

                                                                    <button class="more" id="order">@lang('main.book')</button>
                                                                </form>
                                                        </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        {{--                                            @endforeach--}}
                                    </div>
                                </div>
                                @if(!empty($hotel?->description_en))
                                    <h4>@lang('main.description')</h4>
                                    <div class="descr">
                                        {!! $hotel?->description_en !!}
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
                                        {{ $hotel?->address_en }}
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
