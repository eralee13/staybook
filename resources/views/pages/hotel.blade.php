@extends('layouts.master')
@section('title', $hotel->title)
@section('content')

    @auth
        @php
            $ram = \App\Models\Room::where('hotel_id', $hotel->id)->first();
            $hotel_amenities = explode(',', $ram->amenities ?? '');
        @endphp
        <div class="page hotel">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>{{ $hotel->city }}</h1>
                        <div class="row">
                            <div class="col-md-8">
                                @if($hotel->image)
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
                                @else
                                    <img loading="lazy" src="{{ route('index') }}/img/noimage.png" alt=""
                                         style="margin-bottom: 10px">
                                @endif
                            </div>
                        </div>
                        <h3>{{ $hotel->__('title') }}</h3>
                        <div class="address"><img src="{{ route('index') }}/img/marker_in.svg"
                                                  alt=""> {{ $hotel->__('address') }}</div>
                        @if($hotel->description)
                            <h4>@lang('main.description')</h4>
                            {!! $hotel->__('description') !!}
                        @endif
                        @if(collect($hotel_amenities)->filter()->isNotEmpty())
                            <div class="amenities">
                                <h4>@lang('main.amenities')</h4>
                                @php
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
                        @endif
                        <div class="maps">
                            <h4>@lang('main.location')</h4>
                            <!-- Подключение стилей Leaflet -->
                            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
                            <div id="map" style="height: 300px"></div>
                            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
                            <script>
                                var lat = {{ $hotel->lat }};
                                var lng = {{ $hotel->lng }};
                                var map = L.map('map').setView([lat, lng], 15);

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
                                L.control.scale().addTo(map);
                                map.on('click', function (e) {
                                    var lat = e.latlng.lat;
                                    var lng = e.latlng.lng;
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
    @else
        @include('layouts.auth')
    @endauth

@endsection