@php
    /** @var array $item */
    // $item приходит из search(): ['source'=>'etg', 'hotel'=>Model|array, 'apiHotelId'=>..., 'conv_total'=>..., 'conv_symbol'=>...]
    $hotel = $item['hotel'] ?? null;

    // цены
    $convTotal = (float) ($item['conv_total'] ?? 0);
    $symbol    = (string) ($item['conv_symbol'] ?? '');

    // даты и гости (из запроса)
    $arrivalDate   = request('arrivalDate', now()->toDateString());
    $departureDate = request('departureDate', now()->addDay()->toDateString());
    $residency     = strtoupper((string) request('residency', 'KG'));
    $rooms         = (array) request()->input('rooms', [['adults'=>1,'childAges'=>[]]]);

    // правильный HID для внутренней страницы
    $hid = (int) ($item['apiHotelId'] ?? 0);

    // query для detail-страницы
    $query = http_build_query([
        'apiHotelId'   => $hid,
        'arrivalDate'  => $arrivalDate,
        'departureDate'=> $departureDate,
        'residency'    => $residency,
        // rooms можно не передавать, но если нужно — раскомментируйте следующую строку:
        // 'rooms'        => $rooms,
    ], '', '&', PHP_QUERY_RFC3986);

    $detailsUrl = $hid > 0 ? route('hotel_etg', ['hid' => $hid]) . '?' . $query : null;

    // отображать ли цену
    $showPrice = $convTotal > 0 && filled($symbol);
@endphp

<div class="search-item" data-id="{{ $hotel->id ?? '' }}">
    <div class="row">
        <div class="col-md-6 order-xl-1 order-lg-1 order-1">
            <div class="img-wrap">
                <div class="owl-carousel owl-slider">
                    <div class="slider-item">
                        <img src="{{ $hotel?->image ? Storage::url($hotel->image) : asset('img/noimage.png') }}" alt="">
                    </div>
                </div>

                <div class="text-wrap">
                    @php
                        $symbol = $symbols[strtoupper($fxBase)] ?? strtoupper($fxBase);
                    @endphp

                    <div class="price">
                        @lang('main.from') {{ number_format($item['price'] ?? 0, 0, '.', ' ') }} {{ $symbol }}
                    </div>

                    @if(!empty($hotel?->rating) && $hotel->rating !== 'norating')
                        <div class="rating">
                            {{ $hotel->rating }}
                            <img src="{{ asset('img/star.svg') }}" alt="">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 order-xl-2 order-lg-2 order-2">
            <div class="wrap">
                {{--                    <div class="small">ETG</div>--}}
                <h4>{{ $hotel->title ?? 'Hotel' }}</h4>
                <div class="amenities">
                    {{-- при желании выведите часть удобств --}}
                </div>
                <div class="address">{{ $hotel->city ?? '' }}</div>
            </div>

            @if($detailsUrl)
                <div class="btn-wrap">
                    <a href="{{ $detailsUrl }}" class="more">
                        @lang('main.show_all_rooms', [], app()->getLocale()) {{-- или просто "Показать все номера" --}}
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- если нужно безопасно передать rooms как hidden-поля формы — оставьте блок ниже --}}
    @foreach($rooms as $ri => $r)
        <input type="hidden" name="rooms[{{ $ri }}][adults]" value="{{ (int)($r['adults'] ?? 1) }}">
        @foreach((array)($r['childAges'] ?? []) as $age)
            <input type="hidden" name="rooms[{{ $ri }}][childAges][]" value="{{ (int) $age }}">
        @endforeach
    @endforeach
</div>