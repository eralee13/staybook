@extends('layouts.master')

@section('title', 'Отели')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.hotels')</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>@lang('main.hotels')</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @auth

    <div class="page hotels">
        <div class="container">
            <div class="row">
                @foreach($hotels as $hotel)
                    <div class="col-lg-4 col-md-6 col-6" data-aos="zoom-in" data-aos-duration="2000">
                        <div class="hotels-item" style="background-image: url({{ Storage::url($hotel->image) }})">
                            <a href="{{ route('hotel', $hotel->code) }}">
                                <div class="overlay"></div>
                                <div class="text-wrap">
                                    <h4>{{ $hotel->__('title') }}</h4>
                                    <div class="address">{{ $hotel->__('address') }}</div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
                    @foreach($properties as $property)
                        <div class="col-lg-4 col-md-6 col-6" data-aos="zoom-in" data-aos-duration="2000">
                            <div class="hotels-item" style="background-image: url({{ $property->images[0]->url }})">
                                <a href="{{ route('property', $property->id) }}">
                                    <div class="overlay"></div>
                                    <div class="text-wrap">
                                        <h4>{{ $property->name}}</h4>
                                        <div class="address">{{ $property->contactInfo->address->addressLine }}</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
            </div>
            <div class="row">
                <div class="col-md-12">
                    {{ $hotels->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

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
