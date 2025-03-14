@extends('layouts.master')

@section('title', 'Забронировать')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Забронировать номер</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Забронировать номер</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 col-md-12">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="{{ $hotel->object()->images[0]->url }}" alt="">
                            <h5 style="margin-top: 10px; text-align: center">{{ $hotel->object()->name }}</h5>
                        </div>
                        <div class="col-md-8">
                            <div class="date">{{ $arrival }} - {{ $departure }}</div>
                            <h4>{{ $request->hotel }}</h4>
                            <div class="price">Цена: {{ $request->price }} {{ $request->currency }}</div>

                            <form action="{{ route('res_bookings_verify') }}">
                                <input type="hidden" name="propertyId" value="{{ $request->propertyId }}">
                                <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                                <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                                <input type="hidden" name="ratePlanId" value="{{ $request->ratePlanId }}">
                                <input type="hidden" name="roomTypeId" value="{{ $request->roomTypeId }}">
                                <input type="hidden" name="roomType" value="{{ $request->roomType }}">
                                <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">
                                <input type="hidden" name="roomCode" value="{{ $request->roomCode }}">
                                <input type="hidden" name="placementCode" value="{{ $request->placementCode }}">
                                <input type="hidden" name="guestCount" value="{{ $request->guestCount }}">
                                <input type="hidden" name="checkSum" value="{{ $request->checkSum }}">
                                <input type="hidden" name="servicesId" value="{{ $request->servicesId }}">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="col-xs-4" for="title">ФИО</label>
                                            <input type="text" class="form-control" name="name" value="Test Name"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Номер телефона</label>
                                            <input type="text" name="phone" value="+996500500500">
                                            <div id="output"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'email'])
                                            <label class="col-xs-4" for="email">Email</label>
                                            <input type="email" class="form-control" name="email" id="email"
                                                   value="test@mail.com"/>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">@lang('main.sum') ({{ $request->currency }})</label>
                                            <input type="text" id="sum" name="price" value="{{ $request->price }}"
                                                   readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'comment'])
                                        <label for="">Комментарий</label>
                                        <textarea name="comment" rows="3">Test message</textarea>
                                    </div>
                                    <button class="more" id="saveBtn">@lang('main.book')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
