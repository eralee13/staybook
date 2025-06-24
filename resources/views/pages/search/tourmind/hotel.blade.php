@extends('layouts.master')

@section('title')
    {{-- @dump($request) --}}
    @section('content')
        @php

            $amenities = explode(',', $hotel->amenity->services ?? '');
            // $amenities = array_slice($amenities, 0, 8);
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
                            'Шкаф' => 'close.svg',
                            'Телефон' => 'phone_hotel.svg',
                            'Отопление' => 'heating.svg',
                            'Письменный стол' => 'table.svg',
                            'Минеральная вода' => 'water.svg',
                        ];
        @endphp

        @auth
            <div class="page hotel">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h1>{{ $hotel->city }}</h1>
                            <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"
                                 data-autoplay="30000">
                                @if( isset($hotel->image) )
                                    <img loading="lazy" src="{{ Storage::url($hotel->image)}}" alt="">
                                @else
                                    <img loading="lazy" src="{{ route('index') }}/img/noimage.png" alt="">
                                @endif
                            </div>
                            <h3>{{ $hotel->title_en }}</h3>
                                <div class="address">
                                    <img src="{{ route('index') }}/img/marker_in.svg" alt=""> {{ $hotel->address_en }}
                                </div>
                            <h4>Описание</h4>
                            {{$hotel->description_en}}
                            <div class="amenities">
                                <h4>Услуги и удобства</h4>
                                
                                @if( isset($amenities) )
                                    @foreach($amenities as $amenity)
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
                                            <img src="{{ asset('img/icons/' . $iconFile) }}" alt="{{ $amenity }}">
                                            <div class="name">{{ $amenity }}</div>
                                        </div>
                                    @endforeach
                                @endif

                            </div>
                            <div class="maps">
                                <h4>Расположение</h4>
                                <!-- Подключаем Leaflet -->
                                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                                <!-- Контейнер карты -->
                                <div id="map" style="width: 100%; height: 500px;"></div>

                                <script>
                                    document.addEventListener("DOMContentLoaded", function () {
                                        // Координаты от Laravel-переменных
                                        var lat = {{ $hotel->lat }};
                                        var lng = {{ $hotel->lng }};
                                        var title = @json($hotel->title_en);

                                        // Инициализация карты
                                        var map = L.map('map').setView([lat, lng], 14);

                                        // Добавление слоя OSM
                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                                        }).addTo(map);

                                        // Маркер + popup
                                        L.marker([lat, lng])
                                            .addTo(map)
                                            .bindPopup(title)
                                            .openPopup();
                                    });
                                </script>


                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="tariffs availabity">
                                <h4>Доступные варианты</h4>

                                @include('pages.search.tourmind.rooms', ['tmroom' => $tmroom, 'tmimages' => $tmimages])
                                
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
