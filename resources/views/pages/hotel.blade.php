@extends('layouts.master')

@section('title')

    @section('content')

        @auth
            @isset($hotel)
                <div class="page hotel">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-8">
                                <h1>{{$hotel->__('title')}}</h1>
                                <div class="address">{{ $hotel->__('address') }}</div>
                            </div>
                            <div class="col-md-4">
                                @php
                                    if($count_day == 0){
                                        $count_day = 1;
                                    }
                                    if($count == null){
                                        $count = 1;
                                    }
                                @endphp
                                <div class="min_price">@lang('main.from') {{ $min * $count_day * $count ?? '' }} $
                                    <div class="btn-wrap">
                                        <a href="#price" class="more">@lang('main.view_price')</a>
                                    </div>
                                </div>
                            </div>
                            <div class="row gallery">
                                <div class="col-md-2">
                                    <a href="{{ Storage::url($hotel->image) }}"><img
                                                src="{{ Storage::url($hotel->image) }}" alt=""></a>
                                </div>
                                @php
                                    $images = \App\Models\Image::where('hotel_id', $hotel->id)->get();
                                @endphp
                                @isset($images)
                                    @foreach($images as $image)
                                        <div class="col-md-2">
                                            <a href="{{ Storage::url($image->image) }}"><img loading="lazy"
                                                                                             src="{{ Storage::url($image->image) }}"
                                                                                             alt=""></a>
                                        </div>
                                    @endforeach
                                @endisset
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <div class="servlisting">
                                        <h5>@lang('main.amenities'):</h5>
                                        <div class="row">
                                            @php
                                                $amenities = \App\Models\Amenity::where('hotel_id', $hotel->id)->firstOrFail();
                                                $servs = explode(', ', $amenities->services);
                                            @endphp
                                            @foreach($servs as $service)
                                                <div class="col-md-4">
                                                    <div class="item">
                                                        <i class="fa-regular fa-check"></i> {{ $service }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        {!! $hotel->__('description') !!}
                                        <div class="phone"><span>@lang('main.hphone')</span> <a
                                                    href="tel:{{ $hotel->phone }}">{{
                    $hotel->phone}}</a></div>
                                        <div class="email"><span>Email:</span> <a
                                                    href="mailto:{{ $hotel->email }}">{{ $hotel->email }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <h2>@lang('main.location')</h2>
                                <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                                <div id="map" style="width: 100%; height: 400px;"></div>
                                <script>
                                    DG.then(function () {
                                        var map = DG.map('map', {
                                            center: [{{$hotel->lat}}, {{$hotel->lng}}],
                                            zoom: 14
                                        });

                                        DG.marker([{{$hotel->lat}}, {{$hotel->lng}}], {scrollWheelZoom: false})
                                            .addTo(map)
                                            .bindLabel('{{$hotel->__('title')}}', {
                                                static: true
                                            });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>

                    <div class="rooms" id="price">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-12">
                                    <h2>@lang('main.available_types')
                                    </h2>
                                </div>
                            </div>
                            @if($rooms->isNotEmpty())
                                <div class="row rooms-item">
                                    @foreach($rooms as $room)
                                        <div class="col-lg-4">
                                            <a href="{{ route('room', [isset($hotel) ? $hotel->code : $room->hotel->code, $room->code])
                }}"><img src="{{ Storage::url($room->image) }}" alt=""></a>
                                            <h5>{{ $room->__('title') }}</h5>
                                            <div class="bed"><i class="fa-light fa-bed"></i> {{ $room->bed }}</div>
                                            {{--                                <div class="amenities">--}}
                                            {{--                                    {{ $room->amenities }}--}}
                                            {{--                                </div>--}}
                                            <div class="price">$ {{ $room->price * $count * $count_day }}</div>
                                            <div class="nds">@lang('main.tax_included')</div>
                                            <div class="btn-wrap">
                                                <form action="{{ route('order', $room->id) }}">
                                                    @csrf
                                                    <input type="hidden" name="hotel"
                                                           value="{{ $room->hotel->__('title') }}">
                                                    <input type="hidden" name="hotel_id" value="{{ $room->hotel->id }}">
                                                    <input type="hidden" name="lng" value="{{ $room->hotel->lng }}">
                                                    <input type="hidden" name="lat" value="{{ $room->hotel->lat }}">
                                                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                                                    <input type="hidden" name="title" value="{{ $room->__('title') }}">
                                                    {{--                                        <input type="hidden" name="food" value="{{ $room->food_id }}">--}}
                                                    {{--                                        <input type="hidden" name="cancel" value="{{ $room->rule->__('title') }}">--}}
                                                    <input type="hidden" name="price"
                                                           value="{{ $room->price * $count * $count_day }}">
                                                    <input type="hidden" name="image"
                                                           value="{{ Storage::url($room->image) }}">
                                                    <input type="hidden" name="bed" value="{{ $room->bed }}">
                                                    <input type="hidden" name="start_d" value="{{ $request->start_d }}">
                                                    <input type="hidden" name="end_d" value="{{ $request->end_d }}">
                                                    <button class="more">@lang('main.book')</button>
                                                </form>
                                            </div>
                                        </div>

                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-danger">@lang('main.not_found')</div>
                            @endif
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pagination">
                                        {{ $rooms->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                        <div class="page">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h1>404</h1>
                                        <p>Такой страницы не существует</p>
                                        <div class="btn-wrap">
                                            <a href="{{ route('index') }}">Вернуться на главную страницу</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endisset
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

                    <style>
                        .hotel .min_price {
                            font-size: 24px;
                        }

                        .hotel .btn-wrap {
                            margin-top: 20px;
                        }

                        .hotel .btn-wrap .more {
                            color: #fff;
                        }

                        .hotel .gallery {
                            margin-top: 20px;
                        }

                        .hotel .gallery img {
                            height: 20vh;
                            object-fit: cover;
                            margin-bottom: 20px;
                        }

                        .servlisting {
                            margin: 20px 0;
                        }

                        .hotel #map {
                            margin-top: 0px;
                        }

                        .hotel i {
                            color: #0163b4;
                        }

                        .nds {
                            font-size: 14px;
                            color: #0163b4;
                        }

                        .rooms-item .col-lg-4 {
                            margin-bottom: 40px;
                        }

                        .rooms-item img {
                            height: 30vh;
                            width: 100%;
                            object-fit: cover;
                            margin-bottom: 10px;
                        }
                    </style>

                @endsection
