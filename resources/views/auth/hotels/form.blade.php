@extends('auth.layouts.master')

@isset($hotel)
    @section('title', 'Edit ' . $hotel->title)
@else
    @section('title', 'Add hotel')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @isset($hotel)
                        <h1>@lang('admin.edit') {{ $hotel->title }}</h1>
                    @else
                        <h1>@lang('admin.add_hotel')</h1>
                    @endisset
                    <form method="post" enctype="multipart/form-data"
                          @isset($hotel)
                              action="{{ route('hotels.update', $hotel) }}"
                          @else
                              action="{{ route('hotels.store') }}"
                            @endisset
                    >
                        @isset($hotel)
                            @method('PUT')
                        @endisset
                        @php
                            $user = \Illuminate\Support\Facades\Auth::user()->id;
                        @endphp
                        @if($user != 1 && $user != 3)
                            <input type="hidden" name="user_id" value="{{ \Illuminate\Support\Facades\Auth::user()
                            ->id }}">
                        @endif
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
                            </div>
                            <div class="col-md-6">
                                @error('title_en')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($hotel) ?
                                $hotel->title_en :
                             null) }}">
                                </div>
                            </div>
                        </div>
                        @include('auth.layouts.error', ['fieldname' => 'description'])
                        <div class="form-group">
                            <label for="">@lang('admin.description')</label>
                            <textarea name="description" id="editor" rows="3">{{ old('description', isset($hotel) ?
                            $hotel->description : null) }}</textarea>
                        </div>
                        @include('auth.layouts.error', ['fieldname' => 'description_en'])
                        <div class="form-group">
                            <label for="">@lang('admin.description') EN</label>
                            <textarea name="description_en" id="editor1" rows="3">{{ old('description_en', isset
                            ($hotel) ?
                            $hotel->description_en : null) }}</textarea>
                        </div>
                        <script src="https://cdn.tiny.cloud/1/yxonqgmruy7kchzsv4uizqanbapq2uta96cs0p4y91ov9iod/tinymce/6/tinymce.min.js"
                                referrerpolicy="origin"></script>
                        <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
                        <script>
                            ClassicEditor
                                .create(document.querySelector('#editor'))
                                .catch(error => {
                                    console.error(error);
                                });
                            ClassicEditor
                                .create(document.querySelector('#editor1'))
                                .catch(error => {
                                    console.error(error);
                                });
                        </script>

                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'type'])
                                <div class="form-group">
                                    <label for="type">@lang('admin.property_type')</label>
                                    <select name="type" id="type">
                                        @isset($hotel)
                                            <option @if($hotel->type)
                                                        selected>
                                                {{ $hotel->type }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        <option value="Hotel">@lang('admin.hotel')</option>
                                        {{--                                        <option value="Апартаменты">Апартаменты</option>--}}
                                        {{--                                        <option value="Хостел">Хостел</option>--}}
                                        <option value="Apart Hotel">Apart Hotel</option>
                                        <option value="Гостевой дом">Гостевой дом</option>

                                        {{--                                        <option value="Коттедж">Коттедж</option>--}}
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'count'])
                                <div class="form-group">
                                    <label for="">@lang('admin.number_of_room')</label>
                                    <input type="number" name="count" value="{{ old('count', isset($hotel) ?
                                    $hotel->count : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'checkin'])
                                    <label for="">@lang('admin.checkin')</label>
                                    <select name="checkin" id="">
                                        @isset($hotel)
                                            <option @if($hotel->checkin)
                                                        selected>
                                                {{ $hotel->checkin }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        <option value="13:00">13:00</option>
                                        <option value="14:00">14:00</option>
                                        <option value="15:00">15:00</option>
                                        <option value="16:00">16:00</option>
                                        <option value="17:00">17:00</option>
                                        <option value="18:00">18:00</option>
                                        <option value="19:00">19:00</option>
                                        <option value="20:00">20:00</option>
                                        <option value="21:00">21:00</option>
                                        <option value="22:00">22:00</option>
                                        <option value="23:00">23:00</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'checkout'])
                                    <label for="">@lang('admin.checkout')</label>
                                    <select name="checkout" id="">
                                        @isset($hotel)
                                            <option @if($hotel->checkout)
                                                        selected>
                                                {{ $hotel->checkout }}</option>
                                        @else
                                            <option>Выбрать</option>
                                        @endif
                                        @endisset
                                        <option value="01:00">01:00</option>
                                        <option value="02:00">02:00</option>
                                        <option value="03:00">03:00</option>
                                        <option value="04:00">04:00</option>
                                        <option value="05:00">05:00</option>
                                        <option value="06:00">06:00</option>
                                        <option value="07:00">07:00</option>
                                        <option value="08:00">08:00</option>
                                        <option value="09:00">09:00</option>
                                        <option value="10:00">10:00</option>
                                        <option value="11:00">11:00</option>
                                        <option value="12:00">12:00</option>
                                        <option value="13:00">13:00</option>
                                        <option value="14:00">14:00</option>
                                        <option value="15:00">15:00</option>
                                        <option value="16:00">16:00</option>
                                        <option value="17:00">17:00</option>
                                        <option value="18:00">18:00</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'early_in'])
                                    <label for="early_in">@lang('admin.early_checkin')</label>
                                    <select name="early_in" id="early_in">
                                        @isset($hotel)
                                            <option @if($hotel->early_in)
                                                        selected>
                                                {{ $hotel->early_in }}</option>
                                        @else
                                            <option>Choose</option>
                                        @endif
                                        @endisset
                                        <option value="06:00">06:00</option>
                                        <option value="07:00">07:00</option>
                                        <option value="08:00">08:00</option>
                                        <option value="09:00">09:00</option>
                                        <option value="10:00">10:00</option>
                                        <option value="11:00">11:00</option>
                                        <option value="12:00">12:00</option>
                                        <option value="13:00">13:00</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'early_out'])
                                    <label for="early_out">@lang('admin.late_checkout')</label>
                                    <select name="early_out" id="early_out">
                                        @isset($hotel)
                                            <option @if($hotel->early_out)
                                                        selected>
                                                {{ $hotel->early_out }}</option>
                                        @else
                                            <option>Choose</option>
                                        @endif
                                        @endisset
                                        <option value="15:00">15:00</option>
                                        <option value="16:00">16:00</option>
                                        <option value="17:00">17:00</option>
                                        <option value="18:00">18:00</option>
                                        <option value="19:00">19:00</option>
                                        <option value="20:00">20:00</option>
                                        <option value="21:00">21:00</option>
                                        <option value="22:00">22:00</option>
                                        <option value="23:00">23:00</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'rating'])
                                    <label for="rating">@lang('admin.rating')</label>
                                    <select name="rating" id="rating">
                                        @isset($hotel)
                                            <option @if($hotel->rating)
                                                        selected>
                                                {{ $hotel->rating }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'phone'])
                                <div class="form-group">
                                    <label for="">@lang('admin.phone_number')</label>
                                    <input type="tel" id="phone" name="phone" class="phone" value="{{ old('phone', isset
                                    ($hotel) ?
                                    $hotel->phone :
                             null) }}">
                                    <div id="output" class="output"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'address'])
                                <div class="form-group">
                                    <label for="">@lang('admin.address')</label>
                                    <input type="text" name="address" value="{{ old('address', isset($hotel) ?
                                $hotel->address : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'address_en'])
                                <div class="form-group">
                                    <label for="">@lang('admin.address') EN</label>
                                    <input type="text" name="address_en" value="{{ old('address_en', isset($hotel) ?
                                $hotel->address_en : null) }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="">@lang('admin.choose')</label>

                                <style>
                                    /* Фикс для серого экрана: задать размер контейнеру */
                                    #map {
                                        width: 100%;
                                        height: 500px;
                                    }
                                </style>

                                <!-- Подключение стилей Leaflet -->
                                <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

                                <div id="map"></div>

                                <!-- Подключение скрипта Leaflet -->
                                <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

                                <script>
                                    var lat = {{ old('lat', isset($hotel) ? $hotel->lat : 42.8746) }};  // Если нет данных, по умолчанию Москва
                                    var lng = {{ old('lng', isset($hotel) ? $hotel->lng : 74.6120) }};

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
                                    map.on('click', function(e) {
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
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'lat'])
                                <div class="form-group">
                                    <label for="">@lang('admin.lat')</label>
                                    <input type="text" name="lat" id="lat" value="{{ old('lat', isset($hotel) ?
                                $hotel->lat : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'lng'])
                                <div class="form-group">
                                    <label for="">@lang('admin.lng')</label>
                                    <input type="text" name="lng" id="lng" value="{{ old('lng', isset($hotel) ?
                                $hotel->lng : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'email'])
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input type="email" name="email" value="{{ old('email', isset($hotel) ? $hotel->email :
                             null) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'image'])
                                <div class="form-group">
                                    <label for="">@lang('admin.photo')</label>
                                    @isset($hotel->image)
                                        <img src="{{ Storage::url($hotel->image) }}" alt="">
                                    @endisset
                                    <input type="file" name="image">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="file" name="images[]" multiple="true">
                                </div>
                            </div>

                            <div class="col-md-2">
                                @include('auth.layouts.error', ['fieldname' => 'top'])
                                <div class="form-group">
                                    <label for="">TOP (order)</label>
                                    <input type="number" name="top" value="{{ old('top', isset($hotel) ?
                                    $hotel->top : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.status')</label>
                                    <select name="status">
                                        @if(isset($hotel))
                                            @if($hotel->status == 1)
                                                <option value="{{$hotel->status}}">@lang('admin.active')</option>
                                                <option value="0">@lang('admin.disable')</option>
                                            @else
                                                <option value="{{$hotel->status}}">@lang('admin.disable')</option>
                                                <option value="1">@lang('admin.active')</option>
                                            @endif
                                        @else
                                            <option value="1">@lang('admin.active')</option>
                                            <option value="0">@lang('admin.disable')</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        @csrf
                        <button class="more">@lang('admin.send')</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">@lang('admin.cancel')</a>
                    </form>

                    <div class="img-wrap">
                        <div class="row">
                            <label for="">Все изображения</label>
                            @isset($images)
                                @foreach($images as $image)
                                    <div class="col-md-2">
                                        <div class="img-item">
                                            <img src="{{ Storage::url($image->image) }}" alt="">
                                            <form action="{{ route('images.destroy', $image) }}"
                                                  method="post">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn delete"
                                                        onclick="return confirm('Do you want to delete this?');"><i
                                                            class="fa-regular
                                                    fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            @endisset
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
