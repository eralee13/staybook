@extends('layouts.master')
@section('title', $hotel->title)
@section('content')

    @auth
        <div class="page hotel">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{ $hotel->city }}</h1>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs"
                                     data-loop="true"
                                     data-autoplay="6000">
                                    <img loading="lazy" src="{{ Storage::url($hotel->image)}}" alt="">
                                    @if($images)
                                        @foreach($images as $file)
                                            <img loading="lazy" src="{{ Storage::url($file->image)}}" alt="">
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <h3>{{ $hotel->__('title') }}</h3>
                        <div class="address"><img src="{{ route('index') }}/img/marker_in.svg"
                                                  alt=""> {{ $hotel->__('address') }}</div>
                        <h4>@lang('main.description')</h4>
                        {!! $hotel->__('description') !!}
                        <div class="amenities">
                            <h4>@lang('main.amenities')</h4>
                            @php
                                $amenities = \App\Models\Amenity::where('hotel_id', $hotel->id)->first();
                                $hotel_amenities = explode(',', $amenities->services ?? '');
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
                                        'Шкаф' => 'closet.svg',
                                        'Телефон' => 'phone_hotel.svg',
                                        'Отопление' => 'heating.svg',
                                        'Письменный стол' => 'table.svg',
                                        'Минеральная вода' => 'water.svg',
                                    ];
                            @endphp
                            @foreach($hotel_amenities as $amenity)
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
                                    <img src="{{ asset('img/icons/' . $iconFile) }}" alt="">
                                    <div class="name">{{ $amenity }}</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="maps">
                            <h4>@lang('main.location')</h4>
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
                            <div class="address"><img
                                        src="{{ route('index') }}/img/marker_in.svg"
                                        alt=""> {{ $hotel->__('address') }}</div>
                        </div>


                    </div>
                </div>
                @if($rooms->isNotEmpty())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tariffs availabity">
                                <h4>@lang('main.available')</h4>
                                @foreach($rooms as $room)
                                    @php
                                        $image = \App\Models\Image::where('room_id', $room->id)->orderBy('id', 'desc')->first();
                                    @endphp
                                    <div class="row" style="margin-top: 30px">
                                        <div class="col-md-3">
                                            <div class="room">
                                                @if ($room->image)
                                                    <img src="{{ Storage::url($room->image) }}"
                                                         alt="">
                                                @else
                                                    <img src="{{ route('index') }}/img/noimage.png"
                                                         alt=""
                                                         width="100px">
                                                @endif
                                                @if($room->__('title_local'))
                                                    <h5>{{ $room->__('title_local') }}</h5>
                                                @else
                                                    <h5>{{ $room->__('title') }}</h5>
                                                @endif
                                                {{--                                            <div class="bed">2 отдельные кровати</div>--}}
                                                @php
                                                    $amenities = \App\Models\Room::where('hotel_id', $hotel->id)->first();
                                                    $room_amenities = [];
                                                    if ($amenities) {
                                                        $room_amenities = explode(',', $amenities->amenities);
                                                    }
                                                    $items = array_slice($room_amenities, 0, 8);
                                                @endphp
                                                <div class="amenities">
                                                    <div class="amenities-item">
                                                        <img src="{{ route('index') }}/img/icons/area.svg"
                                                             alt="">
                                                        <div class="name">{{ $room->area }} кв. м
                                                        </div>
                                                    </div>
                                                    @foreach($items as $amenity)
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
                                                            <img src="{{ asset('img/icons/' . $iconFile) }}"
                                                                 alt="{{ $amenity }}">
                                                            <div class="name">{{ $amenity }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="tariff-wrap">
                                                @if($room->rates->isEmpty())
                                                    <p class="text-muted">
                                                        @if(app()->getLocale() == 'ru')
                                                        Нет доступных тарифов для этих дат и
                                                        гостей
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
                                                                    $arrival = \Carbon\Carbon::createFromDate($request->arrivalDate)->format('d.m.Y H:i');
                                                                    $departure = \Carbon\Carbon::createFromDate($request->departureDate)->format('d.m.Y H:i');
                                                                    $cancel = \App\Models\CancellationRule::where('rate_id', $rate->id)->first();
                                                                    $cancelDate = \Carbon\Carbon::parse($request->arrivalDate)->subDays($cancel->free_cancellation_days ?? 0)->format('d.m.Y H:i');
                                                                    $freeDate = \Carbon\Carbon::parse($request->arrivalDate)->format('d.m.Y H:i');
                                                                    $timezone = \Carbon\Carbon::parse($hotel->timezone)->format('P');

                                                                    //кол-во дней
                                                                    $arr = \Carbon\Carbon::parse($request->arrivalDate);
                                                                    $dep = \Carbon\Carbon::parse($request->departureDate);
                                                                    $nights = $arr->diffInDays($dep);
                                                                    $price_child = 0;
                                                                    if (count(array_filter($request->childAges, fn($item) => is_null($item))) === 0) {
                                                                        foreach (explode(',', implode($request->childAges)) as $age){
                                                                            if($rate->free_children_age <= $age ){
                                                                                $price_child += $rate->child_extra_fee;
                                                                            }
                                                                        }
                                                                    }
                                                                    $cancelPrice = 0;
                                                                    //общая сумма
                                                                    $calendarPrice = $ratePrices[$rate->id] ?? $rate->price;
                                                                    if($request->adultCount >= 2){
                                                                        $sum = ($rate->price2 + $price_child) * $request->adult * $nights;
                                                                        $sum = (config('services.main.coef') * $sum) + $sum;
                                                                        $converted = app(\App\Services\FXService::class)->convert($sum, $rate->currency ?? 'USD', $fxBase);
                                                                    } else {
                                                                        $sum = ($calendarPrice + $price_child) * $request->adult * $nights;
                                                                        $sum = (config('services.main.coef') * $sum) + $sum;
                                                                        $converted = app(\App\Services\FXService::class)->convert($sum, $rate->currency ?? 'USD', $fxBase);
                                                                    }
                                                                @endphp

                                                                <div class="item bed">
                                                                    <div class="name">{{ $rate->bed_type }}</div>
                                                                </div>
                                                                <div class="item meal">
                                                                    <div class="name">{{ $rate->meal->__('title') }}</div>
                                                                </div>
                                                                @if($cancel)
                                                                    @php
                                                                        $baseCancelPrice = round($cancel->penalty_amount ?? 1 * config('services.main.coef') / 100 + $cancel->penalty_amount ?? 1, 0);
                                                                        $basePrice = round($sum * config('services.main.coef') / 100 + $sum, 0);

                                                                        $toCurrency = strtoupper($fxBase ?? 'USD');

                                                                        $symbols = [
                                                                            'USD' => '$',
                                                                            'RUB' => '₽',
                                                                            'KGS' => 'сом',
                                                                            'UZS' => 'сўм',
                                                                        ];

                                                                        $symbol = $symbols[$toCurrency] ?? $toCurrency;

                                                                        // Рассчитываем основную сумму штрафа (до конвертации)
                                                                        if ($cancel->penalty_type === 'fixed') {
                                                                            $cancelRaw = round($cancel->penalty_amount);
                                                                        } elseif ($cancel->penalty_type === 'night') {
                                                                            $cancelRaw = round($cancel->penalty_nights * ($rate->price ?? 0));
                                                                        } else { // %
                                                                            $cancelRaw = round(($sum * $cancel->penalty_amount) / 100);
                                                                        }

                                                                        // Переводим в пользовательскую валюту
                                                                        $cancelPrice = app(\App\Services\FXService::class)->convert($cancelRaw, $rate->currency ?? 'USD', $fxBase);
                                                                    @endphp

                                                                    <div class="item cancel">
                                                                        <div class="name">
                                                                            @if($cancel->cancel_policy === 'free_until_checkin')
                                                                                @lang('main.free_cancellation') {{ $freeDate }}
                                                                                UTC {{ $timezone }}

                                                                            @elseif($cancel->cancel_policy === 'free_then_penalty')
                                                                                @if(now()->lte($cancelDate))
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
                                                                    {{ round($converted) }} {{ $symbol }}
                                                                </div>
                                                                {{--                                                            <div class="nds">Все налоги включены</div>--}}
                                                                {{--                                                        <div class="night">за ночь для 1 гостя</div>--}}
                                                                <div class="btn-wrap">
                                                                    <form action="{{ route('order', $rate->id) }}">
                                                                        <input type="hidden"
                                                                               name="propertyId"
                                                                               value="{{ $hotel->id }}">
                                                                        <input type="hidden"
                                                                               name="arrivalDate"
                                                                               value="{{ $request->arrivalDate }}">
                                                                        <input type="hidden"
                                                                               name="departureDate"
                                                                               value="{{ $request->departureDate }}">
                                                                        <input type="hidden" name="roomCount"
                                                                               value="{{ $request->roomCount }}">
                                                                        <input type="hidden"
                                                                               name="adult"
                                                                               value="{{ $request->adult }}">
                                                                        <input type="hidden"
                                                                               name="child"
                                                                               value="{{ $request->child }}">
                                                                        <input type="hidden"
                                                                               name="childAges[]"
                                                                               value="{{ implode(',', $request->childAges) }}">
                                                                        <input type="hidden"
                                                                               name="room_id"
                                                                               value="{{ $rate->room_id }}">
                                                                        <input type="hidden"
                                                                               name="rate_id"
                                                                               value="{{ $rate->id }}">
                                                                        <input type="hidden"
                                                                               name="meal_id"
                                                                               value="{{ $rate->meal_id }}">
                                                                        <input type="hidden"
                                                                               name="cancellation_id"
                                                                               value="{{ $cancel->id ?? ''}}">
                                                                        <input type="hidden"
                                                                               name="hotel_id"
                                                                               value="{{ $hotel->id }}">
                                                                        <input type="hidden"
                                                                               name="title"
                                                                               value="{{ $rate->title }}">
                                                                        <input type="hidden"
                                                                               name="cancelDate"
                                                                               value="{{ $cancelDate }}">
                                                                        <input type="hidden"
                                                                               name="cancelPrice"
                                                                               value="{{ round($cancelPrice) }}">
                                                                        <input type="hidden" name="price"
                                                                               value="{{ round($converted) }}">
                                                                        <input type="hidden" name="currency"
                                                                               value="{{ $symbol }}">
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
            </div>
        </div>
        </div>
    @else
        @include('layouts.auth')
    @endauth

@endsection