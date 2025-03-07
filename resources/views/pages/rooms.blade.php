@extends('layouts.master')

@section('title', 'Номера')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.rooms')</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>@lang('main.rooms')</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @auth
        {{--    <div class="search homesearch">--}}
        {{--        <div class="container">--}}
        {{--            <form action="{{ route('search') }}" class="row">--}}
        {{--                <div class="col-md-3">--}}
        {{--                    <div class="form-group">--}}
        {{--                        <label for="title">@lang('main.search-title')</label>--}}
        {{--                        <input type="search" name="title" id="title">--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="col-md-3">--}}
        {{--                    <div class="form-group">--}}
        {{--                        <label for="">@lang('main.search-date')</label>--}}
        {{--                        <input type="text" id="date">--}}
        {{--                        <input type="hidden" id="start_d" name="start_d">--}}
        {{--                        <input type="hidden" id="end_d" name="end_d">--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="col-md-3">--}}
        {{--                    <div class="form-group">--}}
        {{--                        <label for="">@lang('main.search-count')</label>--}}
        {{--                        <select name="count" id="">--}}
        {{--                            <option value="">@lang('main.choose')</option>--}}
        {{--                            <option value="1">1</option>--}}
        {{--                            <option value="2">2</option>--}}
        {{--                            <option value="3">3</option>--}}
        {{--                            <option value="4">4</option>--}}
        {{--                            <option value="5">5</option>--}}
        {{--                        </select>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="col-md-3">--}}
        {{--                    <div class="form-group">--}}
        {{--                        <button class="more">@lang('main.search')</button>--}}
        {{--                    </div>--}}
        {{--                </div>--}}

        {{--                <div class="col-md-3">--}}
        {{--                    <div class="form-group">--}}
        {{--                        <label for="rating">@lang('main.search-rating')</label>--}}
        {{--                        <select name="rating" id="rating">--}}
        {{--                            <option value="">@lang('main.choose')</option>--}}
        {{--                            <option value="2">2</option>--}}
        {{--                            <option value="3">3</option>--}}
        {{--                            <option value="4">4</option>--}}
        {{--                            <option value="5">5</option>--}}
        {{--                        </select>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="col-md-3">--}}
        {{--                    <div class="form-group">--}}
        {{--                        <label for="include">@lang('main.search-include')</label>--}}
        {{--                        <select name="include" id="include">--}}
        {{--                            <option value="">@lang('main.choose')</option>--}}
        {{--                            <option value="RO">RO</option>--}}
        {{--                            <option value="BB">BB</option>--}}
        {{--                            <option value="HB">HB</option>--}}
        {{--                            <option value="FB">FB</option>--}}
        {{--                            <option value="AI">AI</option>--}}
        {{--                        </select>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="col-md-2">--}}
        {{--                    <div class="form-group check">--}}
        {{--                        <input type="checkbox" name="early_in" id="check">--}}
        {{--                        <label for="check">@lang('main.search-early')</label>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="col-md-2">--}}
        {{--                    <div class="form-group check">--}}
        {{--                        <input type="checkbox" name="cancelled" id="cancelled">--}}
        {{--                        <label for="cancelled">@lang('main.cancelled')</label>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="col-md-2">--}}
        {{--                    <div class="form-group check">--}}
        {{--                        <input type="checkbox" name="extra_place" id="extra_place">--}}
        {{--                        <label for="extra_place">@lang('main.search-extra')</label>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--            </form>--}}

        {{--            <style>--}}
        {{--                .homesearch form button.more{--}}
        {{--                    background-color: transparent;--}}
        {{--                    color: #fff;--}}
        {{--                    border: 1px solid #fff;--}}
        {{--                }--}}
        {{--                .homesearch form button.more:hover{--}}
        {{--                    background-color: #035497;--}}
        {{--                    color: #fff;--}}
        {{--                }--}}
        {{--            </style>--}}
        {{--        </div>--}}
        {{--    </div>--}}

        <div class="rooms home-rooms">
            <div class="container">
                @foreach($rooms as $room)
                    @include('layouts.card', compact('room'))
                @endforeach
                <div class="row">
                    <div class="col-md-12">
                        {{ $rooms->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="maps">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                        <div id="map" style="width: 100%; height: 300px;"></div>
                        {{--                    <script>--}}
                        {{--                        DG.then(function () {--}}
                        {{--                            var map = DG.map('map', {--}}
                        {{--                                center: [42.855608, 74.618626],--}}
                        {{--                                zoom: 12--}}
                        {{--                            });--}}

                        {{--                            @foreach($rooms as $room)--}}
                        {{--                            DG.marker([{{ $room->hotel->lat ?? '' }}, {{ $room->hotel->lng ?? '' }}], {--}}
                        {{--                                scrollWheelZoom:--}}
                        {{--                                    false--}}
                        {{--                            })--}}
                        {{--                                .addTo(map)--}}
                        {{--                                .bindLabel('<a target="_blank" href="{{ route('hotel', $room->hotel->code ?? '')--}}
                        {{--                                        }}">{{Illuminate\Support\Str::limit--}}
                        {{--                                        (strip_tags($room->hotel->title ?? ''),12)}}<br>{{$room->where('hotel_id',--}}
                        {{--                                        $room->hotel->id ?? '')->whereNotNull('price')->min("price")}} @lang('main.som')--}}
                        {{--                                    </a>', {--}}
                        {{--                                    static: true--}}
                        {{--                                });--}}
                        {{--                            @endforeach--}}
                        {{--                        });--}}
                        {{--                    </script>--}}
                    </div>
                </div>
            </div>
        </div>

        <style>
            .check {
                margin-top: 40px;
            }

            input[type="checkbox"] {
                width: auto;
                height: auto;
                display: inline-block;
            }

        </style>

        {{--    <div class="page rooms">--}}
        {{--        <div class="container">--}}
        {{--            <div class="row">--}}
        {{--                <div class="col-md-3 d-xl-block d-lg-block d-none">--}}
        {{--                    <div class="filter">--}}
        {{--                        <h4>@lang('main.filter')</h4>--}}
        {{--                        <form class="row">--}}
        {{--                            <div class="form-group">--}}
        {{--                                <label for="hotel">@lang('main.hotel')</label>--}}
        {{--                                <select id="hotel">--}}
        {{--                                    <option value="0">@lang('main.choose')</option>--}}
        {{--                                    @foreach($hotels as $hotel)--}}
        {{--                                        <option value="{{ $hotel->id }}" {{ old('hotel') == $hotel->id ? 'selected' : ''--}}
        {{--                                    }}>{{ $hotel->title }}</option>--}}
        {{--                                    @endforeach--}}
        {{--                                </select>--}}
        {{--                            </div>--}}
        {{--                            <div class="form-group">--}}
        {{--                                <label for="amount">@lang('main.amount_from')</label>--}}
        {{--                                <input type="text" id="amount" readonly>--}}
        {{--                                <div id="slider-range"></div>--}}
        {{--                                <input type="hidden" id="min">--}}
        {{--                                <input type="hidden" id="max">--}}
        {{--                            </div>--}}
        {{--                            <div class="form-group">--}}
        {{--                                <label for="count">@lang('main.count')</label>--}}
        {{--                                <select id="count">--}}
        {{--                                    <option value="0">@lang('main.choose')</option>--}}
        {{--                                    @for($i=1; $i < $max_per; $i++)--}}
        {{--                                        <option value="{{ $i }}"> {{ $i }}</option>--}}
        {{--                                    @endfor--}}
        {{--                                </select>--}}
        {{--                            </div>--}}
        {{--                            <div class="form-group">--}}
        {{--                                <label for="date">@lang('main.date_from')</label>--}}
        {{--                                <input type="text" id="from" name="from">--}}
        {{--                            </div>--}}
        {{--                            <div class="form-group">--}}
        {{--                                <label for="date">@lang('main.date_to')</label>--}}
        {{--                                <input type="text" id="to" name="to">--}}
        {{--                            </div>--}}
        {{--                            <div class="form-group">--}}
        {{--                                <button class="more" type="button" id="filter_btn">@lang('main.apply')</button>--}}
        {{--                                <a href="{{ route('allrooms') }}" class="delete">@lang('main.reset')</a>--}}
        {{--                            </div>--}}
        {{--                        </form>--}}

        {{--                        --}}

        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="col-md-9">--}}
        {{--                    @foreach($rooms as $room)--}}
        {{--                        @include('layouts.card')--}}
        {{--                    @endforeach--}}
        {{--                    <div class="row">--}}
        {{--                        {{ $rooms->links('pagination::bootstrap-4') }}--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--            </div>--}}


        {{--        </div>--}}
        {{--    </div>--}}

        {{--    <script>--}}
        {{--        $("#slider-range").slider({--}}
        {{--            range: true,--}}
        {{--            min: {{ $min }},--}}
        {{--            max: {{ $max }},--}}
        {{--            values: [{{ $min }}, {{ $max }}],--}}
        {{--            slide: function (event, ui) {--}}
        {{--                $("#amount").val(ui.values[0] + " - " + ui.values[1]);--}}
        {{--                $('#min').val(ui.values[0]);--}}
        {{--                $('#max').val(ui.values[1]);--}}
        {{--            }--}}
        {{--        });--}}
        {{--        $("#amount").val($("#slider-range").slider("values", 0) +--}}
        {{--            " - " + $("#slider-range").slider("values", 1));--}}


        {{--        function submit_post_filter() {--}}
        {{--            let appUrl = {!! json_encode(url('/')) !!};--}}
        {{--            let hotel = $('#hotel').val();--}}
        {{--            let price_from = $('#min').val();--}}
        {{--            let price_to = $('#max').val();--}}
        {{--            let count = $('#count').val();--}}
        {{--            let date_from = $('#from').val();--}}
        {{--            let date_to = $('#to').val();--}}

        {{--            if (price_from != '') {--}}
        {{--                price_from = price_from;--}}
        {{--            } else {--}}
        {{--                price_from = '0';--}}
        {{--            }--}}

        {{--            if (price_to != '') {--}}
        {{--                price_to = price_to;--}}
        {{--            } else {--}}
        {{--                price_to = '100000';--}}
        {{--            }--}}

        {{--            if (date_from != '') {--}}
        {{--                date_from = date_from;--}}
        {{--            } else {--}}
        {{--                date_from = '';--}}
        {{--            }--}}

        {{--            if (date_to != '') {--}}
        {{--                date_to = date_to;--}}
        {{--            } else {--}}
        {{--                date_to = '';--}}
        {{--            }--}}

        {{--            window.location.href = appUrl + '/allrooms/' + hotel + '/' + price_from + '/' + price_to + '/' + count +--}}
        {{--                '/' + date_from + '/' + date_to;--}}
        {{--        }--}}

        {{--        $(document).ready(function () {--}}
        {{--            $('#filter_btn').click(function () {--}}
        {{--                submit_post_filter();--}}
        {{--            });--}}

        {{--        });--}}
        {{--    </script>--}}

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

