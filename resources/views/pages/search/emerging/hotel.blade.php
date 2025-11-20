@extends('layouts.master')

@php
    /** @var \Illuminate\Http\Request $request */

    /** @var \App\Models\Hotel|null $hotel */
    $hotel = $hotel ?? null;

    // Отель из ответа ETG, если есть
    $etgHotel = data_get($etgrooms ?? [], 'hotel');

    // ---------- ДАТЫ ----------
    $arrivalRaw   = (string) ($request->arrivalDate ?? now()->format('Y-m-d'));
    $departureRaw = (string) ($request->departureDate ?? now()->addDay()->format('Y-m-d'));

    try {
        $arrival = isset($arrival) && $arrival instanceof \Carbon\Carbon
            ? $arrival
            : \Carbon\Carbon::parse($arrivalRaw);
    } catch (\Throwable $e) {
        $arrival = \Carbon\Carbon::now();
    }

    try {
        $departure = isset($departure) && $departure instanceof \Carbon\Carbon
            ? $departure
            : \Carbon\Carbon::parse($departureRaw);
    } catch (\Throwable $e) {
        $departure = (clone $arrival)->addDay();
    }

    if ($departure->lessThanOrEqualTo($arrival)) {
        $departure = (clone $arrival)->addDay();
    }

    // ---------- НАЗВАНИЕ / ГОРОД / АДРЕС ----------
    $hotelTitle = (string) (
        data_get($etgHotel, 'title')
        ?? data_get($hotel, 'title')
        ?? __('main.hotel')
    );

    $hotelCity  = (string) (
        data_get($etgHotel, 'city')
        ?? data_get($hotel, 'city')
        ?? ''
    );

    $addr = (string) (
        data_get($etgHotel, 'address')
        ?? data_get($hotel, 'address_en')
        ?? ''
    );

    // ---------- КООРДИНАТЫ ----------
    $lat = (float) (
        data_get($etgHotel, 'lat')
        ?? data_get($etgHotel, 'latitude')
        ?? data_get($hotel, 'lat')
        ?? 0
    );

    $lng = (float) (
        data_get($etgHotel, 'lng')
        ?? data_get($etgHotel, 'longitude')
        ?? data_get($hotel, 'lng')
        ?? 0
    );

    // ---------- ВАЛЮТА ----------
    $fxBase  = strtoupper((string) (session('currency', 'USD')));
    $symbols = ['USD'=>'$', 'EUR'=>'€', 'RUB'=>'₽', 'KGS'=>'сом', 'KZT'=>'₸', 'UZS'=>'сўм'];

    // ---------- УДОБСТВА ----------
    $amenities = $roomAmenity ?? [];
    $amenities = is_array($amenities) ? $amenities : (array)$amenities;

    // ---------- РЕЙТЫ ОТ ПОСТАВЩИКА ----------
    $rates   = (array) data_get($etgrooms ?? [], 'rates', []);
    $meals   = $meals ?? [];
    $hpHotelName = $hotelTitle;
    $hpHotelCity = $hotelCity;

    // ---------- HELPERS ДЛЯ РЕЙТОВ ----------
    $getPayment = function(array $rate) {
        $pay = (array) data_get($rate, 'payment_options.payment_types.0', []);
        return [
            'amount'   => (float) ($pay['amount'] ?? 0),
            'currency' => (string) ($pay['currency_code'] ?? 'USD'),
            'raw'      => $pay,
        ];
    };

    $getCancellation = function(array $rate) {
        $policies   = (array) data_get($rate, 'payment_options.payment_types.0.cancellation_penalties.policies', []);
        $freeBefore = data_get($rate, 'payment_options.payment_types.0.cancellation_penalties.free_cancellation_before');

        if (!$policies && !$freeBefore) {
            return null;
        }

        $policy = $policies ? (end($policies) ?: reset($policies)) : [];

        return [
            'free_until'    => $freeBefore ?: (data_get($policy, 'start_at') ?? data_get($policy, 'until') ?? null),
            'amount_charge' => (float) data_get($policy, 'amount_charge', 0),
            'currency'      => (string) (data_get($policy, 'currency_code', data_get($rate,'payment_options.payment_types.0.currency_code','USD'))),
            'is_free'       => ((float) data_get($policy,'amount_charge',0) == 0) || !empty($freeBefore),
        ];
    };
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
                                                    @foreach($rates as $rate)
                                                        @php
                                                            // --------- стоимость и отмена ----------
                                                            $cancel = $getCancellation($rate);
                                                            $freeText = '';
                                                            if ($cancel) {
                                                                $freeUntil = $cancel['free_until'];
                                                                $freeText = $cancel['is_free']
                                                                    ? __('main.free_cancellation') . ($freeUntil ? (' — ' . $freeUntil) : '')
                                                                    : __('main.cancellation_amount') . ': ' . number_format((float)$cancel['amount_charge'], 2, '.', ' ') . ' ' . ($symbols[$cancel['currency']] ?? $cancel['currency']);
                                                            }

                                                            $payInfo = $getPayment($rate);
                                                            $amount  = (float) $payInfo['amount'];
                                                            $curCode = strtoupper($payInfo['currency'] ?: 'USD');
                                                            $symbol  = $symbols[$curCode] ?? $curCode;

                                                            $roomsReq = $request->input('rooms', []);
                                                            $payment  = (array) $payInfo['raw'];

                                                            $coef        = (float) (config('app.main_coef') ?? 1);
                                                            $price       = (float) ($payment['amount'] ?? 0);
                                                            $totalPrice  = $coef > 0 ? number_format($price / $coef, 2, '.', '') : 0;

                                                            // отмена / штраф
                                                            if (data_get($payment, 'cancellation_penalties.free_cancellation_before')) {
                                                                $endAt       = data_get($payment, 'cancellation_penalties.policies.0.end_at');
                                                                $pay_end_date = $endAt
                                                                    ? \Carbon\Carbon::parse($endAt)->format('d.m.Y H:i:s')
                                                                    : '';

                                                                $penaltPrice = (float) data_get($payment, 'cancellation_penalties.policies.1.amount_charge', 0);
                                                                $penaltyPrice = $coef > 0 ? number_format($penaltPrice / $coef, 2, '.', '') : 0;
                                                            } else {
                                                                $pay_end_date = '';
                                                                $penaltyPrice = 0;
                                                            }

                                                            // конвертация в валюту пользователя
                                                            $converted        = app(\App\Services\FXService::class)->convert($totalPrice,  $curCode, $fxBase);
                                                            $cancelConverted  = app(\App\Services\FXService::class)->convert($penaltyPrice, $curCode, $fxBase);
                                                            $toSymbol         = $symbols[$fxBase] ?? $fxBase;

                                                            // Налоги без включения: БЕЗОПАСНО
                                                            $taxes = (array) data_get($payment, 'tax_data.taxes', []);
                                                            $tax_not_included = collect($taxes)
                                                                ->where('included_by_supplier', false)
                                                                ->values()
                                                                ->all();
                                                        @endphp

                                                        <div class="tariffs-item">
                                                            <h5>{{ $rate['room_name'] ?? '—' }}</h5>

                                                            <div class="item bed">
                                                                <div class="name">{{ data_get($rate, 'room_data_trans.bedding_type', '') }}</div>
                                                            </div>

                                                            <div class="item meal">
                                                                <div class="name">{{ $rate['meal'] ?? '—' }}</div>
                                                            </div>

                                                            <div class="item cancel">
                                                                <div class="name">{{ $freeText }}</div>
                                                            </div>

                                                            <div class="item price">
                                                                {{ number_format((int)$amount, 0, '.', ' ') }} {{ $symbol }}
                                                            </div>

                                                            <div class="btn-wrap">
                                                                <form action="{{ route('order_etg') }}">
                                                                    <input type="hidden" name="arrivalDate"   value="{{ $arrival->format('Y-m-d') }}">
                                                                    <input type="hidden" name="departureDate" value="{{ $departure->format('Y-m-d') }}">

                                                                    {{-- комнаты --}}
                                                                    @foreach ($roomsReq as $i => $room)
                                                                        @php
                                                                            $adults    = (int)($room['adults'] ?? 0);
                                                                            $childAges = isset($room['childAges']) && is_array($room['childAges'])
                                                                                ? $room['childAges']
                                                                                : [];
                                                                        @endphp
                                                                        <input type="hidden" name="rooms[{{ $i }}][adults]" value="{{ $adults }}">
                                                                        @foreach ($childAges as $age)
                                                                            <input type="hidden" name="rooms[{{ $i }}][childAges][]" value="{{ $age }}">
                                                                        @endforeach
                                                                    @endforeach

                                                                    <input type="hidden" name="meal_id"   value="{{ $rate['meal'] ?? null }}">
                                                                    <input type="hidden" name="hotel_id"  value="{{ $hotel->id ?? '' }}">
                                                                    <input type="hidden" name="rate_name" value="{{ $rate['room_name'] ?? '' }}">
                                                                    <input type="hidden" name="room_name" value="{{ data_get($rate, 'room_data_trans.main_name', '') }}">
                                                                    <input type="hidden" name="bedTypeDesc" value="{{ data_get($rate, 'room_data_trans.bedding_type', '') }}">
                                                                    <input type="hidden" name="book_hash" value="{{ $rate['book_hash'] ?? '' }}">
                                                                    <input type="hidden" name="match_hash" value="{{ $rate['match_hash'] ?? '' }}">

                                                                    <input type="hidden" name="refundable"
                                                                           value="{{ data_get($payment,'cancellation_penalties.free_cancellation_before') ? 1 : 0 }}">

                                                                    <input type="hidden" name="cancelDate" value="{{ $pay_end_date }}">
                                                                    <input type="hidden" name="cancelPriceAnullation"
                                                                           value="{{ data_get($payment,'cancellation_penalties.policies.0.amount_charge',0) }}">
                                                                    <input type="hidden" name="cancelPrice" value="{{ $cancelConverted }}">

                                                                    <input type="hidden" name="price"      value="{{ $price }}">
                                                                    <input type="hidden" name="totalPrice" value="{{ $converted }}">
                                                                    <input type="hidden" name="currency"   value="{{ $curCode }}">
                                                                    <input type="hidden" name="utc"        value="{{ $hotel->utc ?? 'UTC' }}">

                                                                    <input type="hidden" name="increase_percent" value="">
                                                                    <input type="hidden" name="residency" value="{{ $request->residency ?? '' }}">

                                                                    {{-- безопасно: уже подготовленный массив налогов --}}
                                                                    <input type="hidden" name="tax_not_included" value='@json($tax_not_included)'>

                                                                    <button class="more" id="order">@lang('main.book')</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($hotel?->description_en))
                                    <h4>@lang('main.description')</h4>
                                    <div class="descr">{!! $hotel->description_en !!}</div>
                                @endif

                                @if(!empty($amenities))
                                    @php
                                        $amenitiesList = collect(
                                            is_string($amenities)
                                                ? preg_split('/[,\n;]+/u', $amenities)
                                                : (array)$amenities
                                        )
                                        ->flatMap(function ($item) {
                                            if (is_string($item)) {
                                                return preg_split('/[,\n;]+/u', $item);
                                            }
                                            return (array)$item;
                                        })
                                        ->map(fn($v) => trim((string)$v))
                                        ->filter()
                                        ->unique()
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
                                    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
                                    <div id="map" style="height: 340px"></div>
                                    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
                                    <script>
                                        (function() {
                                            var lat = {{ $lat ?: 0 }};
                                            var lng = {{ $lng ?: 0 }};

                                            // если координаты нулевые — не инициализируем карту
                                            if (!lat && !lng) return;

                                            var map = L.map('map').setView([lat, lng], 15);

                                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                            }).addTo(map);

                                            var marker = L.marker([lat, lng]).addTo(map)
                                                .bindPopup('Широта: ' + lat.toFixed(6) + '<br>Долгота: ' + lng.toFixed(6))
                                                .openPopup();

                                            L.control.scale().addTo(map);
                                        })();
                                    </script>
                                    <div class="address">
                                        <img src="{{ route('index') }}/img/marker_in.svg" alt="">
                                        {{ $addr }}
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