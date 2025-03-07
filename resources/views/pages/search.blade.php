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

        @if($categories->isNotEmpty())

            <div class="page rooms search sf">
                <div class="container">
                    {{--                <ul class="tabs">--}}
                    {{--                    <li class="tab-link current" data-tab="tab-1">@lang('main.list')</li>--}}
                    {{--                    <li class="tab-link" data-tab="tab-2">@lang('main.on_map')</li>--}}
                    {{--                </ul>--}}
                    <div id="tab-1" class="tab-content current">
                        @if($categories->count() <= 1)
                            @foreach($categories as $category)
                                @include('layouts.cardsearch')
                            @endforeach
                        @else
                            @foreach($categories as $cat)
                                @php
                                    $cats = array(explode(', ', $cat->room_id));

                                @endphp



                                @foreach($cats as $r)
                                    @php
                                        $category = \App\Models\Room::where('id', $r)->get();
                                    @endphp

                                {{ $category }}

                                    @include('layouts.cardsearch', compact('category'))
                                @endforeach
                            @endforeach
                        @endif
                    </div>
                    <div id="tab-2" class="tab-content">
                        <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                        <div id="map" style="width: 100%; height: 500px;"></div>
                        <script>
                            DG.then(function () {
                                var map = DG.map('map', {
                                    center: [42.855608, 74.618626],
                                    zoom: 12
                                });

                                @foreach($categories as $category)
                                DG.marker([{{ $category->room->hotel->lat ?? '' }}, {{ $category->room->hotel->lng ?? '' }}], {
                                    scrollWheelZoom:
                                        false
                                }).addTo(map).bindLabel('<a target="_blank" href="{{ route('hotel', $category->room->hotel->code ?? '')
                                        }}">{{Illuminate\Support\Str::limit(strip_tags($category->room->hotel->title ?? ''),12)
                                        }}</a>', {
                                    static: true
                                });
                                @endforeach
                            });

                            function countCheck(that) {
                                if (that.value == 2) {
                                    document.getElementById("title").style.display = "block";
                                    document.getElementById("title2").style.display = "block";
                                } else {
                                    document.getElementById("title").style.display = "block";
                                    document.getElementById("title2").style.display = "none";
                                }
                            }
                        </script>
                    </div>
                </div>
            </div>

        @else
            <div class="page rooms home-rooms">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger" style="margin-bottom: 40px">Номера не найдены по вашему
                                запросу
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h2>Другие номера</h2>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($relrooms as $room)
                            @include('layouts.card', compact('room'))
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    @else
        <div class="page">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <div class="descr">Необходимо пройти <a href="{{ route('login') }}">авторизацию</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth

@endsection

