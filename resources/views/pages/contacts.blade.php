@extends('layouts.master')

@section('title', 'Контакты')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">{{ $page->__('title') }}</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>{{ $page->__('title') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page contacts">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2 col-md-12">
                    <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                    <div id="map" style="width: 100%; height: 450px;"></div>
                    <script>
                        DG.then(function () {
                            var map = DG.map('map', {
                                center: [42.839085, 74.584437],
                                zoom: 16
                            });

                            DG.marker([42.839085, 74.584437], { scrollWheelZoom: false })
                                .addTo(map)
                                .bindLabel('StayBook', {
                                    static: true
                                });
                        });
                    </script>
                    {!! $page->__('description') !!}
                    <form action="{{ route('contact_mail') }}" method="post">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" name="name" placeholder="@lang('main.name')">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="email" name="email" placeholder="Email" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <input type="text" name="phone" id="phone" placeholder="@lang('main.phone')"
                                           required>
                                    <div id="output"></div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <textarea name="message" rows="4" placeholder="@lang('main.message')"></textarea>
                                </div>
                                <div class="form-group check">
                                    <input type="checkbox" id="agree" required>
                                    <label for="agree">@lang('main.form_agree')</label>
                                </div>
                                @csrf
                                <button class="more" id="send">@lang('main.send')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .check input {
            width: auto;
            height: auto;
            display: inline-block;
        }
    </style>

@endsection
