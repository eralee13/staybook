@extends('layouts.master')

@section('title', 'Контакты')

@section('content')

    <div class="page-about contacts">
        <div class="page about">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>@lang('main.contact_us')</h1>
                        @if(app()->getLocale() == 'ru')
                        <p><a href="mailto:{{ $contacts->email }}">{{ $contacts->email }}</a> по любым другим
                            вопросам</p>
                        @else
                            <p>For any other inquiries: <a href="mailto:{{ $contacts->email }}">{{ $contacts->email }}</a></p>
                        @endif

                        <div class="maps">
                            <h4>@lang('main.location')</h4>
                            <!-- Подключение стилей Leaflet -->
                            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
                            <div id="map"></div>
                            <!-- Подключение скрипта Leaflet -->
                            <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
                            <script>
                                var lat = 42.839085;
                                var lng = 74.584437;
                                var map = L.map('map').setView([lat, lng], 16);

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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="wrap">
                            <form action="{{ route('contact_mail') }}" method="post">
                                <h3>@lang('main.contact_form')</h3>
                                @if(app()->getLocale() == 'ru')
                                    <p>Заполните все поля и нажмите «Отправить», мы обработаем ваше обращение и свяжемся в
                                    течение 24 часов.</p>
                                @else
                                    <p>Fill in all fields and click “Submit”. We will process your request and get back to you within 24 hours.</p>
                                @endif
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="text" placeholder="@lang('main.name')" name="name">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="email" placeholder="Email" name="email">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="text" id="phone" name="phone">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group">
                                        <input type="text" name="message" placeholder="@lang('main.message')">
                                    </div>
                                    <div class="form-group check">
                                        <input type="checkbox" id="check">
                                        @if(app()->getLocale() == 'ru')
                                        <label for="check">Согласен с <a href="{{ route('privacy') }}">условиями соглашения</a></label>
                                        @else
                                            <label for="check">I agree to the <a href="{{ route('privacy') }}">terms and conditions</a>
                                            </label>
                                        @endif
                                    </div>
                                    {!! NoCaptcha::display() !!}
                                    @csrf
                                    <div class="btn-wrap">
                                        <button class="more">@lang('main.send')</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
