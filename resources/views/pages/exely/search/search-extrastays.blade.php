@extends('layouts.master')

@section('title', 'Search Extra Stay')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Search Extra Stay</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Search Extra Stays</li>
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
                        @isset($stays->errors)
                            @foreach($stays->errors as $service) @endforeach
                            <h2>{{ $service->code }}</h2>
                            <p>{{ $service->message }}</p>
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
