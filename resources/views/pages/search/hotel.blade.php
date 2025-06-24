@extends('layouts.master')

<<<<<<< HEAD
@section('title', $hotel->title)
=======
@section('title')

    @section('content')
        @if($_GET['api_name'] == 'TM')
            @dump($tmroom)
        @endif
        
        @auth
            <div class="page hotel">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
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
>>>>>>> origin/eralast

@section('content')

    @auth
        <div class="page hotel">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{ $hotel->city }}</h1>
                        <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"
                             data-autoplay="30000">
                            <img loading="lazy" src="{{ Storage::url($hotel->image)}}" alt="">
                        </div>
                        <h3>{{ $hotel->__('title') }}</h3>
                        <div class="address"><img src="{{ route('index') }}/img/marker_in.svg" alt=""> {{ $hotel->__('address') }}</div>
                        <h4>@lang('main.description')</h4>
                        {!! $hotel->__('description') !!}
                        <div class="amenities">
                            <h4>@lang('main.amenities')</h4>
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
                            <div class="address"><img src="{{ route('index') }}/img/marker_in.svg"
                                                      alt=""> {{ $hotel->__('address') }}</div>
                        </div>


                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="tariffs availabity">
                            <h4>@lang('main.available')</h4>
                            @foreach($rooms as $room)
                                @php
                                    $image = \App\Models\Image::where('room_id', $room->id)->orderBy('id', 'desc')->first();
                                    $rates = \App\Models\Rate::where('hotel_id', $hotel->id)->where('room_id', $room->id)->orderBy('price', 'asc')->get();
                                @endphp
                                <div class="row" style="margin-top: 30px">
                                    <div class="col-md-3">
                                        <div class="room">
                                            @if ($image)
                                                <img src="{{ Storage::url($image->image) }}" alt="">
                                            @else
                                                <img src="{{ route('index') }}/img/noimage.png" alt=""
                                                     width="100px">
                                            @endif
                                            <h5>{{ $room->__('title') }}</h5>
                                            {{--                                            <div class="bed">2 отдельные кровати</div>--}}
                                            <div class="amenities">
                                                <div class="amenities-item">
                                                    <img src="{{ route('index') }}/img/icons/area.svg" alt="">
                                                    <div class="name">{{ $room->area }} кв. м</div>
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
                                                @foreach($rates as $rate)
                                                    <div class="tariffs-item">
                                                        @isset($rate)
                                                            <h5>{{ $rate->__('title') }}</h5>
                                                        @endisset
                                                        @php
                                                            $arrival = \Carbon\Carbon::createFromDate($request->arrivalDate)->format('d.m.Y H:i');
                                                            $departure = \Carbon\Carbon::createFromDate($request->departureDate)->format('d.m.Y H:i');
                                                            $cancel = \App\Models\CancellationRule::where('rate_id', $rate->id)->firstOrFail();
                                                            $cancelDate = \Carbon\Carbon::parse($request->arrivalDate)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
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
                                                            //общая сумма
                                                            if($request->adultCount >= 2){
                                                                $sum = ($rate->price2 + $price_child) * $request->adult * $nights;
                                                            } else {
                                                                $sum = ($rate->price + $price_child) * $request->adult * $nights;
                                                            }
                                                        @endphp
                                                        <div class="item bed">
                                                            <div class="name">{{ $rate->bed_type }}</div>
                                                        </div>
                                                        <div class="item meal">
                                                            <div class="name">{{ $rate->meal->__('title') }}</div>
                                                        </div>
                                                        <div class="item cancel">
                                                            <div class="name">@lang('main.cancellation_policy'):
                                                                @if($cancel->is_refundable == 1)
                                                                    @if(now()->lte($cancelDate))
                                                                        @lang('main.free_cancellation') {{ $cancelDate }}
                                                                        UTC +06:00.
                                                                    @endif
                                                                    @lang('main.cancellation_amount'):
                                                                    @if($cancel->penalty_type === 'fixed')
                                                                        ${{ $cancel->penalty_amount }}
                                                                    @else
                                                                        ${{ ($sum * $cancel->penalty_amount) / 100 }}
                                                                    @endif
                                                                @else
                                                                    @lang('main.cancellation_amount'):
                                                                    @if($cancel->penalty_type === 'fixed')
                                                                        ${{ $cancel->penalty_amount }}
                                                                    @else
                                                                        ${{ ($sum * $cancel->penalty_amount) / 100 }}
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="item price">
                                                            ${{ round($sum * config('services.main.coef')/100 + $sum, 0) }}</div>
                                                        {{--                                                            <div class="nds">Все налоги включены</div>--}}
                                                        {{--                                                        <div class="night">за ночь для 1 гостя</div>--}}
                                                        <div class="btn-wrap">
                                                            <form action="{{ route('order', $rate->id) }}">
                                                                <input type="hidden" name="propertyId"
                                                                       value="{{ $hotel->id }}">
                                                                <input type="hidden" name="arrivalDate"
                                                                       value="{{ $request->arrivalDate }}">
                                                                <input type="hidden" name="departureDate"
                                                                       value="{{ $request->departureDate }}">
                                                                <input type="hidden" name="adult"
                                                                       value="{{ $request->adult }}">
                                                                <input type="hidden" name="child"
                                                                       value="{{ $request->child }}">
                                                                <input type="hidden" name="childAges[]"
                                                                       value="{{ implode(',', $request->childAges) }}">
                                                                <input type="hidden" name="room_id"
                                                                       value="{{ $rate->room_id }}">
                                                                <input type="hidden" name="rate_id"
                                                                       value="{{ $rate->id }}">
                                                                <input type="hidden" name="meal_id"
                                                                       value="{{ $rate->meal_id }}">
                                                                <input type="hidden" name="cancellation_id"
                                                                       value="{{ $rate->cancellation_rule_id }}">
                                                                {{--                                                                <input type="hidden" name="cancelDate" value="{{ $cancelDate }}">--}}
                                                                {{--                                                                <input type="hidden" name="cancelPrice" value="{{ $room->cancellationPolicy->penaltyAmount  }}">--}}

                                                                <input type="hidden" name="hotel_id"
                                                                       value="{{ $hotel->id }}">
                                                                <input type="hidden" name="title"
                                                                       value="{{ $rate->title }}">
                                                                <input type="hidden" name="cancelDate"
                                                                       value="{{ $cancelDate }}">
                                                                <input type="hidden" name="cancelPrice"
                                                                       value="{{ $cancel->penalty_amount }}">
                                                                <input type="hidden" name="price"
                                                                       value="{{ round($sum * config('services.main.coef')/100 + $sum, 0) }}">
                                                                {{--                                                                <input type="hidden" name="currency" value="{{ $room->currencyCode }}">--}}
                                                                <button class="more">@lang('main.book')</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            </div>
                                        </div>
                                    </div>

<<<<<<< HEAD
                                </div>
                            @endforeach

=======
                                @if($_GET['api_name'] == 'TM')
                                    @include('pages.tourmind.rooms', ['tmroom' => $tmroom, 'tmimages' => $tmimages])
                                @endif

                            </div>
>>>>>>> origin/eralast
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
