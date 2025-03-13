@extends('layouts.master')

@section('title', $room->__('title'))

@section('content')

    @auth
        @php
            $hotel_exely = \App\Models\Hotel::where('exely_id', $room->hotel_id)->first();
        @endphp
        @if($hotel_exely != null)
            <div class="page rooms single">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"
                                 data-autoplay="3000">
                                <img loading="lazy" src="{{ Storage::url($room->image) }}" alt="">
                                @foreach($images as $image)
                                    <img loading="lazy" src="{{ Storage::url($image->image) }}" alt="">
                                @endforeach
                            </div>

                            <div class="cont">
                                <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                                <div id="map" style="width: 100%; height: 300px;"></div>
                                <script>
                                    DG.then(function () {
                                        var map = DG.map('map', {
                                            center: [{{$hotel_exely->lat}}, {{$hotel_exely->lng}}],
                                            zoom: 14
                                        });

                                        DG.marker([{{$hotel_exely->lat}}, {{$hotel_exely->lng}}], {scrollWheelZoom: false})
                                            .addTo(map)
                                            .bindLabel('{{$hotel_exely->title}}', {
                                                static: true
                                            });
                                    });

                                </script>
                                <div class="phone"><span>@lang('main.hphone')</span> <a
                                            href="tel:{{ $hotel_exely->phone }}">{{
                $hotel_exely->phone
                }}</a></div>
                                <div class="address">
                                    <span>@lang('main.address')</span> {{ $hotel_exely->address }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" data-aos="fade-left" data-aos-duration="2000">
                            <h1>{{ $room->__('title') }}</h1>
                            <h5>{{ $hotel_exely->title }}</h5>
                            <div class="price">@lang('main.price')
                                @php
                                    //$comission = \Illuminate\Support\Facades\Auth::user()->comission;
                                    $plan = \App\Models\Rate::where('room_id', $room->id)->first();
                                    $now = \Carbon\Carbon::now();
                                    $date = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $now);
                                    $child = \App\Models\Accommodation::where('room_id', $room->id)->first();
                                    $cat = \App\Models\Rate::where('room_id', $room->id)->first();
                                    $rule = \App\Models\Rule::where('id', $cat->rule_id ?? '')->first();
                                @endphp
                                $ {{ $room->price }}
                            </div>
                            <div class="btn-wrap">
                                <a href="#callback" class="more">@lang('main.book')</a>
                                <div class="hidden">
                                    <form action="{{ route('book_mail') }}" method="post" id="callback"
                                          class="form-callback">
                                        <h3>@lang('main.book') {{ $room->title }} <br> {{ $hotel_exely->title }}</h3>
                                        <input type="hidden" name="room_id" value="{{ $room->id}}">
                                        <input type="hidden" name="hotel_id" value="{{$hotel_exely->id}}">
                                        <input type="hidden" name="user_id" value="{{ $user }}">
                                        <input type="hidden" name="book_token" value="{{ $random }}">
                                        <div class="form-group">
                                            <label class="col-xs-4" for="end_d">@lang('main.date')</label>
                                            <input type="text" id="date" class="date">
                                            <input type="hidden" id="start_d" name="arrivalDate"
                                                   value="{{ date('Y-m-d H:s:i') }}">
                                            <input type="hidden" id="end_d" name="departureDate"
                                                   value="{{ $date->addDays(1) }}">
                                        </div>
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'count'])
                                            <label class="col-xs-4" for="count">@lang('main.search-count')</label>
                                            <select name="adult" id="count" onchange="countCheck(this);" required>
                                                <option value="">@lang('main.choose')</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                            </select>
                                        </div>

                                        <div class="form-group" id="title">
                                            <label class="col-xs-4" for="title">@lang('main.adult_name1')</label>
                                            <input type="text" class="form-control" name="title" required/>
                                        </div>
                                        <div class="form-group" id="title2">
                                            <label class="col-xs-4" for="title2">@lang('main.adult_name2')</label>
                                            <input type="text" class="form-control" name="title2"/>
                                        </div>

                                        @isset($child)
                                            <div class="form-group">
                                                <label for="">@lang('main.count_child')</label>
                                                <select name="child" id="countc" onchange="ageCheck(this);">
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
                                                                   id="titlec1"
                                                                   placeholder="@lang('main.child_name1')"/>
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
                                                                   id="titlec1"
                                                                   placeholder="@lang('main.child_name1')"/>
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
                                                                   id="titlec2"
                                                                   placeholder="@lang('main.child_name2')"/>
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
                                                                   id="titlec1"
                                                                   placeholder="@lang('main.child_name1')"/>
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
                                                                   id="titlec2"
                                                                   placeholder="@lang('main.child_name2')"/>
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
                                                                   id="titlec3"
                                                                   placeholder="@lang('main.child_name3')"/>
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

                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'phone'])
                                            <label class="col-xs-4" for="phone">@lang('main.phone')</label>
                                            <input type="text" class="form-control" name="phone" id="phone"
                                                   required>
                                            <div id="output" class="output"></div>
                                        </div>
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'email'])
                                            <label class="col-xs-4" for="email">Email</label>
                                            <input type="email" class="form-control" name="email" id="email"/>
                                        </div>
                                        <input type="hidden" id="price" value="{{ $room->price }}">
                                        <input type="hidden" id="price2" value="{{ $room->price2 }}">
                                        @isset($child)
                                            <input type="hidden" id="pricec" class="pricec"
                                                   value="{{$child->price_extra }}">
                                            <input type="hidden" id="pricec2" class="pricec"
                                                   value="{{$child->price_extra2 }}">
                                            <input type="hidden" id="pricec3" class="pricec"
                                                   value="{{$child->price_extra3 }}">
                                        @endisset

                                        <div class="form-group">
                                            <label for="">@lang('main.sum')</label>
                                            <input type="text" id="sum" name="sum" readonly>
                                        </div>

                                        <input type="hidden" name="book_id" value="{{ $random }}">
                                        <input type="hidden" name="status" value="Reserved">


                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'comment'])
                                            <label for="">Комментарий</label>
                                            <textarea name="comment" rows="3"></textarea>
                                        </div>
                                        @csrf
                                        <button class="more" id="saveBtn">@lang('main.book')</button>
                                    </form>
                                </div>
                            </div>
                            {!! $room->__('description') !!}
                            <div class="list">
                                @if($room->bed != '')
                                    <p><i class="fa-light fa-bed"></i> @lang('main.bed'): {{$room->bed}}</p>
                                @endif
                                @empty($plan->food->title)

                                @else
                                    <p><i class="fa-light fa-mug-saucer"></i> {{$plan->food->title}}</p>
                                @endempty

                                @isset($rule)
                                    <p><i class="fa-regular fa-money-bill"></i> {{ $rule->title }}</p>
                                @endisset
                            </div>

                            <div class="share">
                                <div class="descr">@lang('main.share')</div>
                                <script src="https://yastatic.net/share2/share.js"></script>
                                <div class="ya-share2" data-curtain data-shape="round"
                                     data-services="vkontakte,odnoklassniki,telegram,twitter,whatsapp,linkedin"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <div class="page rooms single">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fotorama" data-allowfullscreen="true" data-nav="thumbs" data-loop="true"
                                 data-autoplay="3000">
                                <img loading="lazy" src="{{ Storage::url($room->image) }}" alt="">
                                @foreach($images as $image)
                                    <img loading="lazy" src="{{ Storage::url($image->image) }}" alt="">
                                @endforeach
                            </div>

                            <div class="cont">
                                <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                                <div id="map" style="width: 100%; height: 300px;"></div>
                                <script>
                                    DG.then(function () {
                                        var map = DG.map('map', {
                                            center: [{{$room->hotel->lat}}, {{$room->hotel->lng}}],
                                            zoom: 14
                                        });

                                        DG.marker([{{$room->hotel->lat}}, {{$room->hotel->lng}}], {scrollWheelZoom: false})
                                            .addTo(map)
                                            .bindLabel('{{$room->hotel->__('title')}}', {
                                                static: true
                                            });
                                    });

                                </script>
                                <div class="phone"><span>@lang('main.hphone')</span> <a
                                            href="tel:{{ $room->hotel->phone }}">{{
                $room->hotel->phone
                }}</a></div>
                                <div class="address">
                                    <span>@lang('main.address')</span> {{ $room->hotel->__('address') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" data-aos="fade-left" data-aos-duration="2000">
                            <h1>{{ $room->__('title') }}</h1>
                            <h5>{{ $room->hotel->__('title') }}</h5>
                            <div class="price">@lang('main.price')
                                @php
                                    //$comission = \Illuminate\Support\Facades\Auth::user()->comission;
                                    $plan = \App\Models\Rate::where('room_id', $room->id)->first();
                                    $now = \Carbon\Carbon::now();
                                    $date = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $now);
                                    $child = \App\Models\Accommodation::where('room_id', $room->id)->first();
                                    $cat = \App\Models\Rate::where('room_id', $room->id)->first();
                                    $rule = \App\Models\Rule::where('id', $cat->rule_id ?? '')->first();
                                @endphp
                                $ {{ $room->price }}
                            </div>
                            <div class="btn-wrap">
                                <a href="#callback" class="more">@lang('main.book')</a>
                                <div class="hidden">

                                    <form action="{{ route('book_mail') }}" method="post" id="callback"
                                          class="form-callback">
                                        <h3>@lang('main.book') {{ $room->__('title') }} <br> {{ $room->hotel->__('title')
                                }}</h3>
                                        <input type="hidden" name="room_id" value="{{ $room->id}}">
                                        <input type="hidden" name="hotel_id" value="{{$room->hotel->id}}">
                                        <input type="hidden" name="user_id" value="{{ $user }}">
                                        <input type="hidden" name="book_token" value="{{ $random }}">
                                        <div class="form-group">
                                            <label class="col-xs-4" for="end_d">@lang('main.date')</label>
                                            <input type="text" id="date" class="date">
                                            <input type="hidden" id="start_d" name="arrivalDate"
                                                   value="{{ date('Y-m-d H:s:i') }}">
                                            <input type="hidden" id="end_d" name="departureDate"
                                                   value="{{ $date->addDays(1) }}">
                                        </div>
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'count'])
                                            <label class="col-xs-4" for="count">@lang('main.search-count')</label>
                                            <select name="adult" id="count" onchange="countCheck(this);" required>
                                                <option value="">@lang('main.choose')</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                            </select>
                                        </div>

                                        <div class="form-group" id="title">
                                            <label class="col-xs-4" for="title">@lang('main.adult_name1')</label>
                                            <input type="text" class="form-control" name="title" required/>
                                        </div>
                                        <div class="form-group" id="title2">
                                            <label class="col-xs-4" for="title2">@lang('main.adult_name2')</label>
                                            <input type="text" class="form-control" name="title2"/>
                                        </div>

                                        <style>
                                            #title, #title2, #child1, #child2, #child3 {
                                                display: none;
                                            }
                                        </style>


                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'phone'])
                                            <label class="col-xs-4" for="phone">@lang('main.phone')</label>
                                            <input type="text" class="form-control" name="phone" id="phone"
                                                   required>
                                            <div id="output" class="output"></div>
                                        </div>
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'email'])
                                            <label class="col-xs-4" for="email">Email</label>
                                            <input type="email" class="form-control" name="email" id="email"/>
                                        </div>
                                        <input type="hidden" id="price" value="{{ $room->price }}">
                                        <input type="hidden" id="price2" value="{{ $room->price2 }}">
                                        @isset($child)
                                            <input type="hidden" id="pricec" class="pricec"
                                                   value="{{$child->price_extra }}">
                                            <input type="hidden" id="pricec2" class="pricec"
                                                   value="{{$child->price_extra2 }}">
                                            <input type="hidden" id="pricec3" class="pricec"
                                                   value="{{$child->price_extra3 }}">
                                        @endisset

                                        <div class="form-group">
                                            <label for="">@lang('main.sum')</label>
                                            <input type="text" id="sum" name="sum" readonly>
                                        </div>

                                        <input type="hidden" name="book_id" value="{{ $random }}">
                                        <input type="hidden" name="status" value="Reserved">


                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'comment'])
                                            <label for="">Комментарий</label>
                                            <textarea name="comment" rows="3"></textarea>
                                        </div>
                                        @csrf
                                        <button class="more" id="saveBtn">@lang('main.book')</button>
                                    </form>
                                </div>
                            </div>
                            {!! $room->__('description') !!}
                            <div class="list">
                                @if($room->bed != '')
                                    <p><i class="fa-light fa-bed"></i> @lang('main.bed'): {{$room->bed}}</p>
                                @endif
                                @empty($plan->food->title)

                                @else
                                    <p><i class="fa-light fa-mug-saucer"></i> {{$plan->food->title}}</p>
                                @endempty

                                @if($room->hotel->early_in != '')
                                    <p><i class="fa-light fa-calendar-days"></i> @lang('main.early')
                                        {{$room->hotel->early_in}}</p>
                                @endif
                                @if($room->hotel->early_out != '')
                                    <p><i class="fa-light fa-calendar-days"></i> @lang('main.late')
                                        {{$room->hotel->early_out}}</p>
                                @endif

                                @isset($rule)
                                    <p><i class="fa-regular fa-money-bill"></i> {{ $rule->title }}</p>
                                @endisset

                            </div>
                            <div class="servlisting">
                                <h5>@lang('main.amenities'):</h5>
                                <div class="row">
                                    @php
                                        $services = explode(', ', $room->hotel->amenity->services);
                                    @endphp
                                    @foreach($services as $service)
                                        <div class="col-md-4">
                                            <div class="item">
                                                <i class="fa-regular fa-check"></i> {{ $service }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="servlisting">
                                <h5>@lang('main.payments'):</h5>
                                <div class="row">
                                    @php
                                        $pays = explode(', ', $room->hotel->payment->payments);
                                    @endphp
                                    @foreach($pays as $pay)
                                        <div class="col-md-3">
                                            <div class="item">
                                                <i class="fa-light fa-credit-card"></i> {{ $pay }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="share">
                                <div class="descr">@lang('main.share')</div>
                                <script src="https://yastatic.net/share2/share.js"></script>
                                <div class="ya-share2" data-curtain data-shape="round"
                                     data-services="vkontakte,odnoklassniki,telegram,twitter,whatsapp,linkedin"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($related->isNotEmpty())
            <div class="page rooms related">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <h2>@lang('main.related')</h2>
                        </div>
                    </div>
                    @foreach($related as $room)
                        @include('layouts.card', compact('room'))
                    @endforeach
                </div>
            </div>
        @endif

        <script>
            $("#count, #countc, #date, #age1, #age2, #age3").change(function () {
                let price = $('#price').val();
                let price2 = $('#price2').val();
                let pricec = $('#pricec').val();
                let pricec2 = $('#pricec2').val();
                let pricec3 = $('#pricec3').val();

                let age1 = $('#age1').val();
                let age2 = $('#age2').val();
                let age3 = $('#age3').val();


                @isset($child)
                //age1
                if (age1 >= {{ $child->age_from }} && age1 <= {{ $child->age_to }}) {
                    pricec = pricec;
                }
                if (age1 >= {{ $child->age_from2 }} && age1 <= {{ $child->age_to2 }}) {
                    pricec = pricec2;
                }
                if (age1 >= {{ $child->age_from3 }} && age1 <= {{ $child->age_to3 }}) {
                    pricec = pricec3;
                }

                //age2
                if (age2 >= {{ $child->age_from }} && age3 <= {{ $child->age_to }}) {
                    pricec = pricec;
                }
                if (age2 >= {{ $child->age_from2 }} && age3 <= {{ $child->age_to2 }}) {
                    pricec = pricec2;
                }
                if (age2 >= {{ $child->age_from3 }} && age3 <= {{ $child->age_to3 }}) {
                    pricec = pricec3;
                }

                //age3
                if (age3 >= {{ $child->age_from }} && age3 <= {{ $child->age_to }}) {
                    pricec = pricec;
                }
                if (age3 >= {{ $child->age_from2 }} && age3 <= {{ $child->age_to2 }}) {
                    pricec = pricec2;
                }
                if (age3 >= {{ $child->age_from3 }} && age3 <= {{ $child->age_to3 }}) {
                    pricec = pricec3;
                }
                var countc = $('#countc').val();
                @else
                var countc = 1;
                pricec = 0;
                @endisset

                let count = $('#count').val();
                let start_d = $('#start_d').val();
                let end_d = $('#end_d').val();
                let date1 = new Date(start_d);
                let date2 = new Date(end_d);
                let days = (date2 - date1) / (1000 * 60 * 60 * 24);
                let sum = (price * days) + (pricec * countc * days);
                let sum2 = (price2 * days) + (pricec * countc * days);
                if (count == 2) {
                    $('#sum').val('$ ' + sum2);
                } else {
                    $('#sum').val('$ ' + sum);
                }
            });

        </script>

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
