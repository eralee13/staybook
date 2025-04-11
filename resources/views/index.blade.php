@extends('layouts.master')

@section('title', 'Главная страница')

@section('content')
    @auth
        <div class="places">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Места в Кыргызстане</h2>
                    </div>
                </div>
                <div class="row">
                    @foreach($hotels as $hotel)
                        <div class="col-lg-4 col-md-6">
                            <div class="places-item">
                                    <span class="img-wrap">
                                        <img src="{{ Storage::url($hotel->image) }}" alt="">
                                    </span>
                                <div class="text-wrap">
                                    <div class="address">{{ $hotel->city }}</div>
                                    <h5>{{ $hotel->title }}</h5>
                                    <div class="rating"><img src="{{ route('index') }}/img/star.svg" alt=""> 4,76</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-wrap">
                            <a href="{{ route('hotels') }}">Показать больше</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="places popular">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Популярные направления</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place1.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place2.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place3.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place1.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-wrap">
                            <a href="{{ route('hotels') }}">Показать больше</a>
                        </div>
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
