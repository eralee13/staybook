@extends('auth.layouts.master')

@isset($hotel)
    @section('title', 'Edit ' . $hotel->title)
@else
    @section('title', 'Add hotel')
@endisset

@section('content')

    <style>
        .admin .img-wrap img {
            max-width: 100%;
            height: 12vh;
            object-fit: cover;
            width: 100%;
        }
        .output{
            color: red;
            font-size: 12px;
        }
        .output.agree{
            color: green;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
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
                        @else
                            <input type="hidden" name="user_id" value="{{ \Illuminate\Support\Facades\Auth::user()
                            ->id }}">
                        @endisset

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="title" value="{{ old('title', isset($hotel) ? $hotel->title :
                             null) }}">
                                    @error('title')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($hotel) ?
                                $hotel->title_en :
                             null) }}">
                                    @error('title_en')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="">@lang('admin.description')</label>
                            <textarea name="description" id="editor" rows="3">{{ old('description', isset($hotel) ?
                            $hotel->description : null) }}</textarea>
                            @include('auth.layouts.error', ['fieldname' => 'description'])
                        </div>

                        <div class="form-group">
                            <label for="">@lang('admin.description') EN</label>
                            <textarea name="description_en" id="editor1" rows="3">{{ old('description_en', isset
                            ($hotel) ?
                            $hotel->description_en : null) }}</textarea>
                            @include('auth.layouts.error', ['fieldname' => 'description_en'])
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
                            <div class="col-md-12">
                                <h4>@lang('admin.property_currency')</h4>
                                @if(app()->getLocale() === 'ru')
                                    <p>Пожалуйста, укажите валюту, в которой цены на ваше размещение будут отображаться
                                        для пользователей.</p>
                                    <p>Важно: валюта объекта размещения может отличаться от валюты договора, в которой
                                        вы будете получать выплаты от нас.</p>
                                @else
                                    <p>Please specify the currency in which prices for your property will be displayed
                                        to users. Important: The property currency may differ from the contract currency
                                        in which you will receive payments from us.</p>
                                @endif
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">@lang('admin.property_currency')</label>
                                            <select name="currency" id="currency">
                                                @isset($hotel)
                                                    <option @if($hotel->currency)
                                                                selected>
                                                        {{ $hotel->currency }}@endif</option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endisset
                                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>
                                                    USD
                                                </option>
                                                <option value="KGS" {{ old('currency') == 'KGS' ? 'selected' : '' }}>
                                                    KGS
                                                </option>
                                            </select>
                                            @include('auth.layouts.error', ['fieldname' => 'currency'])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
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
                                        <option value="Hotel" {{ old('type') == 'Hotel' ? 'selected' : '' }}>@lang('admin.hotel')</option>
                                        <option value="Small Hotel" {{ old('type') == 'Small Hotel' ? 'selected' : '' }}>@lang('admin.small_hotel')</option>
                                        <option value="Apart Hotel" {{ old('type') == 'Apart Hotel' ? 'selected' : '' }}>@lang('admin.apart_hotel')</option>
                                        <option value="Guesthouse" {{ old('type') == 'Guesthouse' ? 'selected' : '' }}>@lang('admin.guesthouse')</option>
                                        <option value="Hostel" {{ old('type') == 'Hostel' ? 'selected' : '' }}>@lang('admin.hostel')</option>
                                        <option value="Apartments" {{ old('type') == 'Apartments' ? 'selected' : '' }}>@lang('admin.apartments')</option>
                                        <option value="Holiday guesthouse" {{ old('type') == 'Holiday guesthouse' ? 'selected' : '' }}>@lang('admin.holiday_guesthouse')</option>
                                        <option value="Sanatorium" {{ old('type') == 'Sanatorium' ? 'selected' : '' }}>@lang('admin.sanatorium')</option>
                                        <option value="Holiday camp" {{ old('type') == 'Holiday camp' ? 'selected' : '' }}>@lang('admin.holiday_camp')</option>
                                        <option value="Resort complex" {{ old('type') == 'Resort complex' ? 'selected' : '' }}>@lang('admin.resort_complex')</option>
                                        <option value="Resort" {{ old('type') == 'Resort' ? 'selected' : '' }}>@lang('admin.resort')</option>
                                        <option value="Glamping" {{ old('type') == 'Glamping' ? 'selected' : '' }}>@lang('admin.glamping')</option>
                                        <option value="Yurt camp" {{ old('type') == 'Yurt camp' ? 'selected' : '' }}>@lang('admin.yurt_camp')</option>
                                        <option value="Campsite" {{ old('type') == 'Campsite' ? 'selected' : '' }}>@lang('admin.campsite')</option>
                                        <option value="Cabins" {{ old('type') == 'Cabins' ? 'selected' : '' }}>@lang('admin.cabins')</option>
                                        <option value="Long stay" {{ old('type') == 'Long stay' ? 'selected' : '' }}>@lang('admin.long_stay')</option>
                                    </select>
                                    @include('auth.layouts.error', ['fieldname' => 'type'])
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.timezone')</label>
                                    <select name="timezone" id="timezone">
                                        @isset($hotel)
                                            <option value="{{ $hotel->timezone }}"
                                                    selected>{{ $hotel->timezone }}</option>
                                        @else
                                            <option value="">@lang('admin.choose')</option>
                                        @endisset
                                        @foreach($timezones as $timezone)
                                            @isset($hotel)
                                                @if($hotel->timezone != $timezone)
                                                    <option value="{{ $timezone }}" {{ old('timezone') == $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $timezone }}" {{ old('timezone') == $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                                            @endisset
                                        @endforeach
                                    </select>
                                    @include('auth.layouts.error', ['fieldname' => 'timezone'])
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
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
                                        @for ($hour = 13; $hour <= 23; $hour++)
                                            @php $time = sprintf('%02d:00', $hour); @endphp
                                            <option value="{{ $time }}" {{ old('checkin') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                        @endfor
                                    </select>
                                    @include('auth.layouts.error', ['fieldname' => 'checkin'])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.checkout')</label>
                                    <select name="checkout" id="">
                                        @isset($hotel)
                                            <option @if($hotel->checkout)
                                                        selected>
                                                {{ $hotel->checkout }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        @for ($hour = 01; $hour <= 13; $hour++)
                                            @php $time = sprintf('%02d:00', $hour); @endphp
                                            <option value="{{ $time }}" {{ old('checkout') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                        @endfor
                                    </select>
                                    @include('auth.layouts.error', ['fieldname' => 'checkout'])
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
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
                                        <option value="norating">@lang('admin.norating')</option>
                                        <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>4</option>
                                        <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>5</option>
                                    </select>
                                    @include('auth.layouts.error', ['fieldname' => 'rating'])
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.city')</label>
                                    <input type="text" name="city" value="{{ old('city', isset($hotel) ? $hotel->city :
                             null) }}">
                                    @include('auth.layouts.error', ['fieldname' => 'city'])
                                </div>
                            </div>

                            <style>
                                .select2-container--default .select2-selection--single {
                                    height: 50px;
                                    line-height: 50px;
                                    display: block;
                                }

                                .select2-container--default .select2-selection--single .select2-selection__rendered {
                                    line-height: 50px;
                                }
                            </style>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.address')</label>
                                    <input type="text" name="address" value="{{ old('address', isset($hotel) ?
                                $hotel->address : null) }}">
                                    @include('auth.layouts.error', ['fieldname' => 'address'])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.address') EN</label>
                                    <input type="text" name="address_en" value="{{ old('address_en', isset($hotel) ?
                                $hotel->address_en : null) }}">
                                    @include('auth.layouts.error', ['fieldname' => 'address_en'])
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="">@lang('admin.choose')</label>
                                <style>
                                    #map {
                                        width: 100%;
                                        height: 500px;
                                    }
                                </style>
                                <!-- Подключение стилей Leaflet -->
                                <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

                                <div id="map"></div>

                                <!-- Подключение скрипта Leaflet -->
                                <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

                                <script>
                                    @isset($hotel)
                                    var lat = {{ old('lat', $hotel->lat) }};
                                    var lng = {{ old('lng', $hotel->lng) }};
                                    @else
                                    var lat = 42.8746;
                                    var lng = 74.585902;
                                    @endisset

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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.lat')</label>
                                    <input type="text" name="lat" id="lat" value="{{ old('lat', isset($hotel) ?
                                $hotel->lat : null) }}">
                                    @include('auth.layouts.error', ['fieldname' => 'lat'])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.lng')</label>
                                    <input type="text" name="lng" id="lng" value="{{ old('lng', isset($hotel) ?
                                $hotel->lng : null) }}">
                                    @include('auth.layouts.error', ['fieldname' => 'lng'])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.phone_number')</label>
                                    <input type="tel" id="phone" name="phone" class="phone" value="{{ old('phone', isset
                                    ($hotel) ?
                                    $hotel->phone :
                             null) }}">
                                    <div id="output" class="output"></div>
                                    @include('auth.layouts.error', ['fieldname' => 'phone'])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Email</label>
                                    <input type="email" name="email" value="{{ old('email', isset($hotel) ? $hotel->email :
                             null) }}">
                                    @include('auth.layouts.error', ['fieldname' => 'email'])
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.photo')</label>
                                    @isset($hotel->image)
                                        <img src="{{ Storage::url($hotel->image) }}" alt="">
                                    @endisset
                                    <input type="file" name="image">
                                    @include('auth.layouts.error', ['fieldname' => 'image'])
                                </div>
                            </div>

                            <style>
                                .img-item {
                                    border: 1px solid #e0e0e0;
                                    padding: 10px;
                                    margin-bottom: 20px;
                                    border-radius: 10px;
                                    background-color: #fafafa;
                                    text-align: center;
                                    transition: box-shadow 0.3s;
                                }

                                .img-item:hover {
                                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
                                }

                                .img-item img {
                                    max-width: 100%;
                                    border-radius: 6px;
                                    object-fit: cover;
                                    height: 120px;
                                }

                                .pretty-checkbox {
                                    position: relative;
                                    padding-left: 30px;
                                    cursor: pointer;
                                    user-select: none;
                                    font-size: 14px;
                                    display: inline-block;
                                    margin-top: 10px;
                                }

                                .pretty-checkbox input[type="checkbox"] {
                                    position: absolute;
                                    opacity: 0;
                                    cursor: pointer;
                                }

                                .pretty-checkbox .checkmark {
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    height: 20px;
                                    width: 20px;
                                    background-color: #eee;
                                    border-radius: 4px;
                                    transition: background-color 0.3s;
                                    border: 1px solid #ccc;
                                }

                                .pretty-checkbox:hover input ~ .checkmark {
                                    background-color: #d6f1ff;
                                }

                                .pretty-checkbox input:checked ~ .checkmark {
                                    background-color: #00bcd4;
                                    border-color: #00bcd4;
                                }

                                .pretty-checkbox .checkmark:after {
                                    content: "";
                                    position: absolute;
                                    display: none;
                                }

                                .pretty-checkbox input:checked ~ .checkmark:after {
                                    display: block;
                                }

                                .pretty-checkbox .checkmark:after {
                                    left: 6px;
                                    top: 2px;
                                    width: 6px;
                                    height: 12px;
                                    border: solid white;
                                    border-width: 0 2px 2px 0;
                                    transform: rotate(45deg);
                                }
                            </style>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.images')</label>
                                    <input type="file" name="images[]" multiple="true">
                                    @isset($images)
                                        <div class="img-wrap">
                                            <div class="row">
                                                @isset($images)
                                                    @foreach($images as $image)
                                                        <div class="col-md-4">
                                                            <div class="img-item">
                                                                <img src="{{ Storage::url($image->image) }}" alt="">
                                                                <label class="pretty-checkbox">
                                                                    <input type="checkbox" name="delete_images[]"
                                                                           value="{{ $image->id }}">
                                                                    <span class="checkmark"></span>
                                                                    Удалить
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endisset
                                            </div>
                                        </div>
                                    @endisset
                                </div>
                            </div>

                            {{--                            <div class="col-md-2">--}}
                            {{--                                @include('auth.layouts.error', ['fieldname' => 'top'])--}}
                            {{--                                <div class="form-group">--}}
                            {{--                                    <label for="">TOP (order)</label>--}}
                            {{--                                    <input type="number" name="top" value="{{ old('top', isset($hotel) ?--}}
                            {{--                                    $hotel->top : null) }}">--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}

                            <div class="amenities">
                                @foreach($serviceCategories as $category => $services)
                                    <div class="row">
                                        <h5 class="col-md-12 mt-3">{{ $category }}</h5>

                                        @foreach($services as $service)
                                            @php
                                                $inputId = Str::slug($service);
                                                $checked = in_array($service, old('services', $amenities ?? []));
                                            @endphp

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <input type="checkbox"
                                                           id="{{ $inputId }}"
                                                           name="services[]"
                                                           value="{{ $service }}"
                                                            {{ $checked ? 'checked' : '' }}>
                                                    <label for="{{ $inputId }}">{{ $service }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                            <style>
                                .amenities label{
                                    display: inline-block;
                                }
                            </style>

                            @can('edit-contact')
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
                            @endcan
                        </div>
                        @csrf
                        <button class="more">@lang('admin.send')</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">@lang('admin.cancel')</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
