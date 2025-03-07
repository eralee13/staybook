@extends('layouts.master')

@section('title', 'Поиск')

@section('content')

    @auth
        <div class="pagetitle">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.search')</h1>
                        <ul class="breadcrumbs">
                            <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                            <li>></li>
                            <li>@lang('main.search')</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if($hotels)
            <div class="page rooms search sf">
                <div class="container">
                    <ul class="tabs">
                        <li class="tab-link current" id="maptab" data-tab="tab-1">@lang('main.on_map')</li>
                        <li class="tab-link" data-tab="tab-2">@lang('main.list')</li>
                        
                    </ul>
                    <div id="tab-1" class="tab-content current">
                        <style>
                            .leaflet-popup-content {
                                width: auto;
                                max-width: 100%; /* максимальная ширина */
                                white-space: normal; /* разрешаем перенос текста */
                            }
                        </style>
                        <!-- Подключение стилей Leaflet -->
                        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

                        <div id="map" style="width: 100%; height: 500px;"></div>

                        <!-- Подключение скрипта Leaflet -->
                        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

                        <script>
                                var map = L.map('map').setView([0, 0], 10);
                        
                                // Добавление слоя OpenStreetMap
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: ''
                                }).addTo(map);
                        
                                // Массив отелей из Blade
                                var hotels = [
                                    @foreach($hotels as $hotel)
                                        {
                                            "name": "{{ Illuminate\Support\Str::limit(strip_tags($hotel->title ?? ''), 100) }}",
                                            "lat": {{ $hotel->lat }},
                                            "lng": {{ $hotel->lng }},
                                            "url": "{{ route('hotel', $hotel->code ?? '') }}"
                                        },
                                    @endforeach
                                ];
                        
                                // Добавление маркеров на карту
                                hotels.forEach(function(hotel) {
                                    L.marker([hotel.lat, hotel.lng]).addTo(map)
                                        .bindPopup('<b><a target="_blank" href="' + hotel.url + '">' + hotel.name + '</a></b>')
                                        .openPopup();
                                });
                        
                                // Масштабируем карту под все маркеры
                                if (hotels.length > 0) {
                                    var bounds = L.latLngBounds(hotels.map(h => [h.lat, h.lng]));
                                    map.fitBounds(bounds);
                                }
                        
                                // Добавление масштаба
                                L.control.scale().addTo(map);
                                

                                document.addEventListener("DOMContentLoaded", function () {
                                    document.querySelector('#maptab').addEventListener('shown.bs.tab', function () {
                                        map.invalidateSize();
                                    });
                                });
                        </script>

                            
                        
                    </div>
                    <div id="tab-2" class="tab-content">
                        @if($hotels->isNotEmpty())
                        @foreach($hotels as $hotel)
                            @include('layouts.cardsearchtest')
                        @endforeach
                        @else
                            <div class="page rooms home-rooms">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="alert alert-danger" style="margin-bottom: 40px">@lang('main.not_found')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h2>@lang('main.related')</h2>
                                        </div>
                                    </div>
                                    <div class="row">
                                        @foreach($relhotels as $hotel)
                                            @include('layouts.cardsearchtest', compact('hotel'))
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
{{--                        @foreach($properties as $property)--}}
{{--                            <div class="row rooms-item">--}}
{{--                                <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-duration="2000">--}}
{{--                                    <a href="{{ route('property', $property->id) }}" target="_blank">--}}
{{--                                        <div class="img"--}}
{{--                                             style="background-image: url({{ $property->images[0]->url }})"></div>--}}
{{--                                    </a>--}}
{{--                                </div>--}}
{{--                                <div class="col-lg-8 col-md-6">--}}
{{--                                    <div class="start">@lang('main.start_d') {{ $property->policy->checkInTime }}</div>--}}
{{--                                    <div class="end">@lang('main.end_d') {{ $property->policy->checkOutTime }}</div>--}}
{{--                                    <div class="title">{{ $property->name}}</div>--}}
{{--                                    <div class="address">{{ $property->contactInfo->address->addressLine }}</div>--}}
{{--                                    <div class="room" style="margin-top: 20px">--}}
{{--                                        @php--}}
{{--                                            $room = collect($property->roomTypes)->first();--}}
{{--                                        @endphp--}}

{{--                                            <div class="room-item">--}}
{{--                                                <div class="row">--}}
{{--                                                    <div class="col-md-4">--}}
{{--                                                        <h5>{{ $room->name ?? '' }}</h5>--}}
{{--                                                        <div class="plan">Тариф: {{ $room->categoryName }}</div>--}}
{{--                                                        @if($room->occupancy->adultBed > 0)--}}
{{--                                                        <div class="bed"><i--}}
{{--                                                                class="fa-light fa-bed"></i> Кровать</div>--}}
{{--                                                        @endif--}}
{{--                                                    </div>--}}
{{--                                                    <div class="col-md-4">--}}
{{--                                                        <div class="listings">--}}
{{--                                                            <ul>--}}
{{--                                                                <li>--}}
{{--                                                                    <i class="fa-solid fa-utensils"></i>--}}
{{--                                                                </li>--}}
{{--                                                                <li>--}}
{{--                                                                    <i class="fa-solid fa-rotate-left"></i> {{ $cat->rule->__('title') }}--}}
{{--                                                                </li>--}}
{{--                                                                @if($room->occupancy->extraBed > 0)--}}
{{--                                                                    <li>Есть дополнительное место</li>--}}
{{--                                                                @endif--}}
{{--                                                            </ul>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="col-md-4">--}}
{{--                                                        @php--}}
{{--                                                            if($count_day != null){--}}
{{--                                                                $price = ($cat->room->price + $cat->food->price) * $count * $count_day;--}}
{{--                                                            } else {--}}
{{--                                                                $fprice = \App\Models\Food::where('title_en', $cat->food_id)->first();--}}
{{--                                                                $fprice = $fprice->price;--}}
{{--                                                                $price = ($cat->room->price + $fprice) * $count;--}}
{{--                                                            }--}}
{{--                                                        @endphp--}}
{{--                                                        <div class="price">{{ $price }} $</div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                    </div>--}}
{{--                                    <div class="btn-wrap">--}}
{{--                                        <a href="{{ route('property', $property->id) }}"--}}
{{--                                           target="_blank" class="more">Показать все номера</a>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        @endforeach--}}
                    </div>
                    
                </div>
            </div>

        @else
            <div class="page rooms home-rooms">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger" style="margin-bottom: 40px">Отели не найдены по вашему
                                запросу
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2>Похожие</h2>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($relrooms as $hotel)
                            @include('layouts.cardsearchtest')
                        @endforeach
                        @foreach($relprops as $property)
                                <div class="row rooms-item">
                                    <div class="col-lg-4 col-md-6" data-aos="fade-right" data-aos-duration="2000">
                                        <a href="{{ route('property', $property->id) }}" target="_blank">
                                            <div class="img"
                                                 style="background-image: url({{ $property->images[0]->url }})"></div>
                                        </a>
                                    </div>
                                    <div class="col-lg-8 col-md-6">
                                        <div class="start">@lang('main.start_d') {{ $property->policy->checkInTime }}</div>
                                        <div class="end">@lang('main.end_d') {{ $property->policy->checkOutTime }}</div>
                                        <div class="title">{{ $property->name}}</div>
                                        <div class="address">{{ $property->contactInfo->address->addressLine }}</div>
                                        <div class="room" style="margin-top: 20px">
                                            @php
                                                $room = collect($property->roomTypes)->first();
                                                //dd($room);
                                            @endphp

                                            <div class="room-item">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <h5>{{ $room->name ?? '' }}</h5>
{{--                                                        <div class="plan">Тариф: {{ $room->categoryName }}</div>--}}
                                                        @if($room->occupancy->adultBed > 0)
                                                            <div class="bed"><i
                                                                    class="fa-light fa-bed"></i> Кровать</div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="listings">
                                                            <ul>
                                                                <li>
                                                                    <i class="fa-solid fa-utensils"></i>
                                                                </li>
                                                                {{--                                                                <li>--}}
                                                                {{--                                                                    <i class="fa-solid fa-rotate-left"></i> {{ $cat->rule->__('title') }}--}}
                                                                {{--                                                                </li>--}}
                                                                @if($room->occupancy->extraBed > 0)
                                                                    <li>Есть дополнительное место</li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        {{--                                                        @php--}}
                                                        {{--                                                            if($count_day != null){--}}
                                                        {{--                                                                $price = ($cat->room->price + $cat->food->price) * $count * $count_day;--}}
                                                        {{--                                                            } else {--}}
                                                        {{--                                                                $fprice = \App\Models\Food::where('title_en', $cat->food_id)->first();--}}
                                                        {{--                                                                $fprice = $fprice->price;--}}
                                                        {{--                                                                $price = ($cat->room->price + $fprice) * $count;--}}
                                                        {{--                                                            }--}}
                                                        {{--                                                        @endphp--}}
                                                        {{--                                                        <div class="price">{{ $price }} $</div>--}}
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="btn-wrap">
                                            <a href="{{ route('property', $property->id) }}"
                                               target="_blank" class="more">@lang('main.all-rooms')</a>
                                        </div>
                                    </div>
                                </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

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
                            <div class="descr">@lang('main.need_auth') <a href="{{ route('login') }}">@lang('main.auth')</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth

@endsection

