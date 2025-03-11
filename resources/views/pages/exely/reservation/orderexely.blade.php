@extends('layouts.master')

@section('title', 'Забронировать')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Забронировать</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Забронировать</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="date">{{ $request->arrivalDate }} - {{ $request->departureDate }}</div>
                    <div class="hotel">Hotel ID: {{ $request->propertyId }}</div>
                    <h4>{{ $request->hotel }}</h4>
                    <div class="price">Price: {{ $request->price }}</div>

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
                                    <input type="text" class="form-control" name="name" value="test"/>
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
                                    <label for="">@lang('main.sum') $</label>
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

@endsection
