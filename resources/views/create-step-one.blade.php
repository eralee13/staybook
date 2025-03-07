@extends('layouts.master')

@section('title', 'Главная страница')

@section('content')
    <div class="page">
        <div class=" container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <h1>Добро пожаловать в Stay Book</h1>
                    <p>Расскажите нам о вашей недвижимости</p>
                    <form action="{{ route('postCreateStepOne') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                @error('title')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="title" value="{{ old('title', isset($hotel) ? $hotel->title :
                             null) }}">
                                </div>
                                @error('title_en')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($hotel) ?
                                $hotel->title_en :
                             null) }}">
                                </div>
                                @include('auth.layouts.error', ['fieldname' => 'type'])
                                <div class="form-group">
                                    <label for="type">Тип недвижимости</label>
                                    <select name="type" id="">
                                        <option value="">Выбрать</option>
                                        <option value="Отель" @if(old('type') == 'Отель') selected @endif>Отель</option>
                                        <option value="Апарт отель" @if(old('type') == 'Апарт отель') selected @endif>
                                            Апарт отель
                                        </option>
                                        <option value="Гостевой дом" @if(old('type') == 'Гостевой дом') selected @endif>
                                            Гостевой дом
                                        </option>
                                    </select>
                                </div>
                                @include('auth.layouts.error', ['fieldname' => 'city'])
                                <div class="form-group">
                                    <label for="city">Город, Страна</label>
                                    <input type="text" value="{{ old('city', isset($hotel) ? $hotel->city : null) }}"
                                           class="form-control" name="city"/>
                                </div>
                                @include('auth.layouts.error', ['fieldname' => 'address'])
                                <div class="form-group">
                                    <label for="address">Адрес</label>
                                    <input type="text"
                                           value="{{ old('address', isset($hotel) ? $hotel->address : null) }}"
                                           class="form-control" name="address"/>
                                </div>
                                @include('auth.layouts.error', ['fieldname' => 'address_en'])
                                <div class="form-group">
                                    <label for="address">Адрес EN</label>
                                    <input type="text"
                                           value="{{ old('address_en', isset($hotel) ? $hotel->address_en : null) }}"
                                           class="form-control" name="address_en"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        @include('auth.layouts.error', ['fieldname' => 'lat'])
                                        <div class="form-group">
                                            <label for="">@lang('admin.lat')</label>
                                            <input type="text" name="lat" id="lat"
                                                   value="{{ old('lat', isset($hotel) ? $hotel->lat : null) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @include('auth.layouts.error', ['fieldname' => 'lng'])
                                        <div class="form-group">
                                            <label for="">@lang('admin.lng')</label>
                                            <input type="text" name="lng" id="lng"
                                                   value="{{ old('lng', isset($hotel) ? $hotel->lng : null) }}">
                                        </div>
                                    </div>
                                </div>
                                <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
                                <div id="map"></div>
                                <script
                                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDDuGt0E5IEGkcE6ZfrKfUtE9Ko_de66pA&callback=initMap&v=weekly&channel=2"
                                    async></script>
                                <script>
                                    function initMap() {
                                        const myLatlng = {
                                            lat: 42.855608,
                                            lng: 74.618626
                                        };
                                        const map = new google.maps.Map(document.getElementById("map"), {
                                            zoom: 8,
                                            center: myLatlng,
                                        });


                                        // Create the initial InfoWindow.
                                        let infoWindow = new google.maps.InfoWindow({
                                            content: "Click the map to get Lat/Lng!",
                                            position: myLatlng,
                                        });

                                        infoWindow.open(map);
                                        // Configure the click listener.
                                        map.addListener("click", (mapsMouseEvent) => {
                                            // Close the current InfoWindow.
                                            infoWindow.close();
                                            // Create a new InfoWindow.
                                            infoWindow = new google.maps.InfoWindow({
                                                position: mapsMouseEvent.latLng,
                                            });
                                            document.getElementById('lat').value = mapsMouseEvent.latLng.lat();
                                            document.getElementById('lng').value = mapsMouseEvent.latLng.lng();
                                            infoWindow.setContent(
                                                JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2)
                                            );
                                            infoWindow.open(map);
                                        });
                                    }
                                </script>
                                <style>
                                    #map {
                                        height: 500px;
                                    }
                                </style>
                            </div>

                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary more">Далее</button>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>
@endsection
