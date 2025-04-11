@extends('layouts.filter_mini')

@section('title', 'Отели')

@section('content')
    @auth
        <div class="places hotels">
            <div class="container">
                <div class="row aic">
                    <div class="col-md-8">
                        <h1>Отели</h1>
                    </div>
                    {{--                    <div class="col-md-4">--}}
                    {{--                        <div class="other-wrap">--}}
                    {{--                            <a href="">Другой</a>--}}
                    {{--                        </div>--}}
                    {{--                    </div>--}}
                </div>
                <div class="row">
                    @foreach($hotels as $hotel)
                        <div class="col-lg-4 col-md-6">
                            <div class="places-item">
                                    <span class="img-wrap">
                                        <img src="{{ Storage::url($hotel->image) }}" alt="">
                                    </span>
                                <div class="text-wrap">
                                    <h5>{{ $hotel->title }}</h5>
                                    <div class="address">{{ $hotel->address }}</div>
                                    {{--                                <div class="price">от 36 000 сом</div>--}}
                                </div>
                            </div>
                        </div>
                    @endforeach

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
                            <div class="descr">@lang('main.need_auth') <a
                                        href="{{ route('login') }}">@lang('main.auth')</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endauth
@endsection
