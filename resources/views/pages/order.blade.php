@extends('layouts.master')

@section('title', 'Забронировать')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.book')</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>@lang('main.book')</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @php
                        $now = \Carbon\Carbon::now();
                                $date = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $now);
                    @endphp
                    <div class="row">
                        <div class="col-md-8">
                            <div class="main"><img src="{{ $request->image }}" alt=""></div>
                        </div>
                        <div class="col-md-4">
                            <h3>{{ $request->hotel }}</h3>
                            <h5>{{ $request->title }}</h5>
                            @if($request->bed)
                                <div class="bed"><i class="fa-light fa-bed"></i> {{ $request->bed }}</div>
                            @endif
                            @if($request->food)
                                <div class="food"><i class="fa-solid fa-utensils"></i> {{ $request->food }}</div>
                            @endif
                            @if($request->cancel)
                                <div class="cancel"><i class="fa-solid fa-rotate-left"></i> {{ $request->cancel }}</div>
                            @endif
                            <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                            <div id="map" style="width: 100%; height: 400px;"></div>
                            <script>
                                DG.then(function () {
                                    var map = DG.map('map', {
                                        center: [{{ $request->lat }}, {{ $request->lng }}],
                                        zoom: 12
                                    });

                                    DG.marker([{{ $request->lat }}, {{ $request->lng }}], {scrollWheelZoom: false})
                                        .addTo(map)
                                        .bindLabel('{{ $request->hotel }}', {
                                            static: true
                                        });

                                });
                            </script>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-10 offset-lg-1">
                            <form action="{{ route('book_mail') }}" method="post" id="callback">
                                <input type="hidden" name="room_id" value="{{ $request->room_id}}">
                                <input type="hidden" name="hotel_id" value="{{$request->hotel_id}}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @if($request->start_d)
                                                <div class="date">
                                                    <label for="">@lang('main.search-title')</label>
                                                    <input type="date" id="start_d" name="start_d"
                                                           value="{{ $start }}" readonly>
                                                </div>
                                            @else
                                                <label class="col-xs-4" for="end_d">@lang('main.date')</label>
                                                <input type="text" id="date" class="date">
                                                <input type="hidden" id="start_d" name="start_d"
                                                       value="{{ date('Y-m-d H:s:i') }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @if($end)
                                                <label for="">@lang('main.search-title')</label>
                                                <input type="date" id="end_d" name="end_d"
                                                       value="{{ $end }}" readonly>
                                            @else
                                                <input type="hidden" id="end_d" name="end_d"
                                                       value="{{ $date->addDays(1) }}">
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-xs-4" for="title">@lang('main.name')</label>
                                            <input type="text" class="form-control" name="title" required/>
                                        </div>
                                    </div>

                                    {{--                                    <div class="col-md-6">--}}
                                    {{--                                        <div class="form-group">--}}
                                    {{--                                            @include('auth.layouts.error', ['fieldname' => 'count'])--}}
                                    {{--                                            <label class="col-xs-4" for="count">@lang('main.search-count')</label>--}}
                                    {{--                                            <select name="count" id="count" onchange="countCheck(this);" required>--}}
                                    {{--                                                <option value="">@lang('main.choose')</option>--}}
                                    {{--                                                <option value="1">1</option>--}}
                                    {{--                                                <option value="2">2</option>--}}
                                    {{--                                            </select>--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </div>--}}

                                    {{--                                    <div class="form-group" id="title">--}}
                                    {{--                                        <label class="col-xs-4" for="title">@lang('main.adult_name1')</label>--}}
                                    {{--                                        <input type="text" class="form-control" name="title" required/>--}}
                                    {{--                                    </div>--}}
                                    {{--                                    <div class="form-group" id="title2">--}}
                                    {{--                                        <label class="col-xs-4" for="title2">@lang('main.adult_name2')</label>--}}
                                    {{--                                        <input type="text" class="form-control" name="title2"/>--}}
                                    {{--                                    </div>--}}

                                    @isset($child)
                                        <div class="form-group">
                                            <label for="">@lang('main.count_child')</label>
                                            <select name="countc" id="countc" onchange="ageCheck(this);">
                                                <option value="">@lang('main.choose')</option>
                                                @if($child->extra_place == 1)
                                                    <option value="1">1</option>
                                                @elseif($child->extra_place == 2)
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                @elseif($child->extra_place == 3)
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                @endif
                                            </select>
                                        </div>
                                        @if($child->extra_place == 1)
                                            <div class="row" id="child1">
                                                <div class="col-md-4">
                                                    <select name="age1" id="age1">
                                                        <option value="1">@lang('main.1_year')</option>
                                                        <option value="2">@lang('main.2_year')</option>
                                                        <option value="3">@lang('main.3_year')</option>
                                                        <option value="4">@lang('main.4_year')</option>
                                                        <option value="5">@lang('main.5_year')</option>
                                                        <option value="6">@lang('main.6_year')</option>
                                                        <option value="7">@lang('main.7_year')</option>
                                                        <option value="8">@lang('main.8_year')</option>
                                                        <option value="9">@lang('main.9_year')</option>
                                                        <option value="10">@lang('main.10_year')</option>
                                                        <option value="11">@lang('main.11_year')</option>
                                                        <option value="12">@lang('main.12_year')</option>
                                                        <option value="13">@lang('main.13_year')</option>
                                                        <option value="14">@lang('main.14_year')</option>
                                                        <option value="16">@lang('main.15_year')</option>
                                                        <option value="16">@lang('main.16_year')</option>
                                                        <option value="17">@lang('main.17_year')</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="titlec1"
                                                               id="titlec1" placeholder="@lang('main.child_name1')"/>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($child->extra_place == 2)
                                            <div class="row" id="child1">
                                                <div class="col-md-4">
                                                    <select name="age1" id="age1">
                                                        <option value="1">@lang('main.1_year')</option>
                                                        <option value="2">@lang('main.2_year')</option>
                                                        <option value="3">@lang('main.3_year')</option>
                                                        <option value="4">@lang('main.4_year')</option>
                                                        <option value="5">@lang('main.5_year')</option>
                                                        <option value="6">@lang('main.6_year')</option>
                                                        <option value="7">@lang('main.7_year')</option>
                                                        <option value="8">@lang('main.8_year')</option>
                                                        <option value="9">@lang('main.9_year')</option>
                                                        <option value="10">@lang('main.10_year')</option>
                                                        <option value="11">@lang('main.11_year')</option>
                                                        <option value="12">@lang('main.12_year')</option>
                                                        <option value="13">@lang('main.13_year')</option>
                                                        <option value="14">@lang('main.14_year')</option>
                                                        <option value="16">@lang('main.15_year')</option>
                                                        <option value="16">@lang('main.16_year')</option>
                                                        <option value="17">@lang('main.17_year')</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="titlec1"
                                                               id="titlec1" placeholder="@lang('main.child_name1')"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row" id="child2">
                                                <div class="col-md-4">
                                                    <select name="age2" id="age2">
                                                        <option value="1">@lang('main.1_year')</option>
                                                        <option value="2">@lang('main.2_year')</option>
                                                        <option value="3">@lang('main.3_year')</option>
                                                        <option value="4">@lang('main.4_year')</option>
                                                        <option value="5">@lang('main.5_year')</option>
                                                        <option value="6">@lang('main.6_year')</option>
                                                        <option value="7">@lang('main.7_year')</option>
                                                        <option value="8">@lang('main.8_year')</option>
                                                        <option value="9">@lang('main.9_year')</option>
                                                        <option value="10">@lang('main.10_year')</option>
                                                        <option value="11">@lang('main.11_year')</option>
                                                        <option value="12">@lang('main.12_year')</option>
                                                        <option value="13">@lang('main.13_year')</option>
                                                        <option value="14">@lang('main.14_year')</option>
                                                        <option value="16">@lang('main.15_year')</option>
                                                        <option value="16">@lang('main.16_year')</option>
                                                        <option value="17">@lang('main.17_year')</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="titlec2"
                                                               id="titlec2" placeholder="@lang('main.child_name2')"/>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($child->extra_place == 3)
                                            <div class="row" id="child1">
                                                <div class="col-md-4">
                                                    <select name="age1" id="age1">
                                                        <option value="1">@lang('main.1_year')</option>
                                                        <option value="2">@lang('main.2_year')</option>
                                                        <option value="3">@lang('main.3_year')</option>
                                                        <option value="4">@lang('main.4_year')</option>
                                                        <option value="5">@lang('main.5_year')</option>
                                                        <option value="6">@lang('main.6_year')</option>
                                                        <option value="7">@lang('main.7_year')</option>
                                                        <option value="8">@lang('main.8_year')</option>
                                                        <option value="9">@lang('main.9_year')</option>
                                                        <option value="10">@lang('main.10_year')</option>
                                                        <option value="11">@lang('main.11_year')</option>
                                                        <option value="12">@lang('main.12_year')</option>
                                                        <option value="13">@lang('main.13_year')</option>
                                                        <option value="14">@lang('main.14_year')</option>
                                                        <option value="16">@lang('main.15_year')</option>
                                                        <option value="16">@lang('main.16_year')</option>
                                                        <option value="17">@lang('main.17_year')</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="titlec1"
                                                               id="titlec1" placeholder="@lang('main.child_name1')"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row" id="child2">
                                                <div class="col-md-4">
                                                    <select name="age2" id="age2">
                                                        <option value="1">@lang('main.1_year')</option>
                                                        <option value="2">@lang('main.2_year')</option>
                                                        <option value="3">@lang('main.3_year')</option>
                                                        <option value="4">@lang('main.4_year')</option>
                                                        <option value="5">@lang('main.5_year')</option>
                                                        <option value="6">@lang('main.6_year')</option>
                                                        <option value="7">@lang('main.7_year')</option>
                                                        <option value="8">@lang('main.8_year')</option>
                                                        <option value="9">@lang('main.9_year')</option>
                                                        <option value="10">@lang('main.10_year')</option>
                                                        <option value="11">@lang('main.11_year')</option>
                                                        <option value="12">@lang('main.12_year')</option>
                                                        <option value="13">@lang('main.13_year')</option>
                                                        <option value="14">@lang('main.14_year')</option>
                                                        <option value="16">@lang('main.15_year')</option>
                                                        <option value="16">@lang('main.16_year')</option>
                                                        <option value="17">@lang('main.17_year')</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="titlec2"
                                                               id="titlec2" placeholder="@lang('main.child_name2')"/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row" id="child3">
                                                <div class="col-md-4">
                                                    <select name="age3" id="age3">
                                                        <option value="1">@lang('main.1_year')</option>
                                                        <option value="2">@lang('main.2_year')</option>
                                                        <option value="3">@lang('main.3_year')</option>
                                                        <option value="4">@lang('main.4_year')</option>
                                                        <option value="5">@lang('main.5_year')</option>
                                                        <option value="6">@lang('main.6_year')</option>
                                                        <option value="7">@lang('main.7_year')</option>
                                                        <option value="8">@lang('main.8_year')</option>
                                                        <option value="9">@lang('main.9_year')</option>
                                                        <option value="10">@lang('main.10_year')</option>
                                                        <option value="11">@lang('main.11_year')</option>
                                                        <option value="12">@lang('main.12_year')</option>
                                                        <option value="13">@lang('main.13_year')</option>
                                                        <option value="14">@lang('main.14_year')</option>
                                                        <option value="16">@lang('main.15_year')</option>
                                                        <option value="16">@lang('main.16_year')</option>
                                                        <option value="17">@lang('main.17_year')</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" name="titlec3"
                                                               id="titlec3" placeholder="@lang('main.child_name3')"/>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endisset

                                    <style>
                                        #title, #title2, #child1, #child2, #child3 {
                                            display: none;
                                        }
                                    </style>

                                    <script>
                                        function countCheck(that) {
                                            if (that.value == 2) {
                                                document.getElementById("title").style.display = "block";
                                                document.getElementById("title2").style.display = "block";
                                            } else {
                                                document.getElementById("title").style.display = "block";
                                                document.getElementById("title2").style.display = "none";
                                            }
                                        }

                                        function ageCheck(that) {
                                            if (that.value == 1) {
                                                document.getElementById("child1").style.display = "flex";
                                                document.getElementById("child2").style.display = "none";
                                                document.getElementById("child3").style.display = "none";
                                            } else if (that.value == 2) {
                                                document.getElementById("child1").style.display = "flex";
                                                document.getElementById("child2").style.display = "flex";
                                                document.getElementById("child3").style.display = "none";
                                            } else if (that.value == 3) {
                                                document.getElementById("child1").style.display = "flex";
                                                document.getElementById("child2").style.display = "flex";
                                                document.getElementById("child3").style.display = "flex";
                                            } else {
                                                document.getElementById("child1").style.display = "none";
                                                document.getElementById("child2").style.display = "none";
                                                document.getElementById("child3").style.display = "none";
                                            }
                                        }
                                    </script>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">@lang('main.phone')</label>
                                            <input type="text" name="phone" id="phone">
                                            <div id="output"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'email'])
                                            <label class="col-xs-4" for="email">Email</label>
                                            <input type="email" class="form-control" name="email" id="email"/>
                                        </div>
                                    </div>
                                    {{--                            <input type="hidden" id="price" value="{{ $room->price }}">--}}
                                    {{--                            <input type="hidden" id="price2" value="{{ $room->price2 }}">--}}
                                    @isset($child)
                                        <input type="hidden" id="pricec" class="pricec"
                                               value="{{$child->price_extra }}">
                                        <input type="hidden" id="pricec2" class="pricec"
                                               value="{{$child->price_extra2 }}">
                                        <input type="hidden" id="pricec3" class="pricec"
                                               value="{{$child->price_extra3 }}">
                                    @endisset

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">@lang('main.sum') $</label>
                                            <input type="text" id="sum" name="sum" value="{{ $price }}" readonly>
                                        </div>

                                    </div>
                                    {{--                            <input type="hidden" name="book_id" value="{{ $random }}">--}}
                                    <input type="hidden" name="status" value="@lang('main.paid')">

                                    {{--                                    <script>--}}
                                    {{--                                        $("#count, #countc, #date, #age1, #age2, #age3").change(function () {--}}
                                    {{--                                            let price = $('#price').val();--}}
                                    {{--                                            let price2 = $('#price2').val();--}}
                                    {{--                                            let pricec = $('#pricec').val();--}}
                                    {{--                                            let pricec2 = $('#pricec2').val();--}}
                                    {{--                                            let pricec3 = $('#pricec3').val();--}}

                                    {{--                                            let age1 = $('#age1').val();--}}
                                    {{--                                            let age2 = $('#age2').val();--}}
                                    {{--                                            let age3 = $('#age3').val();--}}


                                    {{--                                            @isset($child)--}}
                                    {{--                                            //age1--}}
                                    {{--                                            if (age1 >= {{ $child->age_from }} && age1 <= {{ $child->age_to }}) {--}}
                                    {{--                                                pricec = pricec;--}}
                                    {{--                                            }--}}
                                    {{--                                            if (age1 >= {{ $child->age_from2 }} && age1 <= {{ $child->age_to2 }}) {--}}
                                    {{--                                                pricec = pricec2;--}}
                                    {{--                                            }--}}
                                    {{--                                            if (age1 >= {{ $child->age_from3 }} && age1 <= {{ $child->age_to3 }}) {--}}
                                    {{--                                                pricec = pricec3;--}}
                                    {{--                                            }--}}

                                    {{--                                            //age2--}}
                                    {{--                                            if (age2 >= {{ $child->age_from }} && age3 <= {{ $child->age_to }}) {--}}
                                    {{--                                                pricec = pricec;--}}
                                    {{--                                            }--}}
                                    {{--                                            if (age2 >= {{ $child->age_from2 }} && age3 <= {{ $child->age_to2 }}) {--}}
                                    {{--                                                pricec = pricec2;--}}
                                    {{--                                            }--}}
                                    {{--                                            if (age2 >= {{ $child->age_from3 }} && age3 <= {{ $child->age_to3 }}) {--}}
                                    {{--                                                pricec = pricec3;--}}
                                    {{--                                            }--}}

                                    {{--                                            //age3--}}
                                    {{--                                            if (age3 >= {{ $child->age_from }} && age3 <= {{ $child->age_to }}) {--}}
                                    {{--                                                pricec = pricec;--}}
                                    {{--                                            }--}}
                                    {{--                                            if (age3 >= {{ $child->age_from2 }} && age3 <= {{ $child->age_to2 }}) {--}}
                                    {{--                                                pricec = pricec2;--}}
                                    {{--                                            }--}}
                                    {{--                                            if (age3 >= {{ $child->age_from3 }} && age3 <= {{ $child->age_to3 }}) {--}}
                                    {{--                                                pricec = pricec3;--}}
                                    {{--                                            }--}}
                                    {{--                                            var countc = $('#countc').val();--}}
                                    {{--                                            @else--}}
                                    {{--                                            var countc = 1;--}}
                                    {{--                                            pricec = 0;--}}
                                    {{--                                            @endisset--}}

                                    {{--                                            let count = $('#count').val();--}}
                                    {{--                                            let start_d = $('#start_d').val();--}}
                                    {{--                                            let end_d = $('#end_d').val();--}}
                                    {{--                                            let date1 = new Date(start_d);--}}
                                    {{--                                            let date2 = new Date(end_d);--}}
                                    {{--                                            let days = (date2 - date1) / (1000 * 60 * 60 * 24);--}}
                                    {{--                                            let sum = (price * days) + (pricec * countc * days);--}}
                                    {{--                                            let sum2 = (price2 * days) + (pricec * countc * days);--}}
                                    {{--                                            if (count == 2) {--}}
                                    {{--                                                $('#sum').val('$ ' + sum2);--}}
                                    {{--                                            } else {--}}
                                    {{--                                                $('#sum').val('$ ' + sum);--}}
                                    {{--                                            }--}}
                                    {{--                                        });--}}

                                    {{--                                    </script>--}}

                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'comment'])
                                        <label for="">@lang('main.message')</label>
                                        <textarea name="comment" rows="3"></textarea>
                                    </div>
                                    @csrf
                                    <button class="more" id="saveBtn">@lang('main.book')</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        .page #map {
            margin-top: 20px;
        }

        .page i {
            color: darkblue;
        }

        .page form {
            margin-top: 50px;
        }

        .page form button {
            width: auto;
            padding: 10px 30px;
            margin-left: 10px;
        }
        .main img{
            width: 100%;
        }
    </style>

@endsection
