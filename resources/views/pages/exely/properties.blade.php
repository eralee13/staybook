@extends('layouts.master')

@section('title', 'Properties')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Properties</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Properties</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page property">
        <div class="container">
            <div class="row">
                <div class="col-md-2">
                    @include('pages.exely.sidebar')
                </div>
                <div class="col-md-10">
                    <div class="row">
                        @foreach($properties as $property)
                            <div class="col-lg-4">
                                <div class="property-item">
                                    <img src="{{ $property->images[0]->url }}" alt="">
                                    <h4>{{ $property->name}}</h4>
                                    <p>{{ Str::limit($property->description, 100) }}</p>
                                    <p>Stars: {{ $property->stars }}</p>
                                    <div class="address">{{ $property->contactInfo->address->addressLine }}</div>
                                    <div class="btn-wrap" style="margin-top: 20px">
                                        <a href="{{ route('property', $property->id) }}" class="more">Read more</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
