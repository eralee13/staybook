@extends('layouts.master')

@section('title', 'Отели')

@section('content')
    @auth
        <div class="page places hotels">
            <div class="container">
                <div class="row">
                    <h1>@lang('main.hotels')</h1>
                    @foreach($hotels as $hotel)
                        <div class="col-lg-4 col-md-6 col-6">
                            <div class="places-item">
                                <div class="img-wrap">
                                    <a href="{{ route('hotel', $hotel->code) }}">
                                        @if($hotel->image)
                                            <img src="{{ Storage::url($hotel->image) }}" alt="">
                                        @else
                                            <img src="{{ route('index')}}/img/noimage.png" alt="">
                                        @endif
                                    </a>
                                </div>
                                <div class="text-wrap">
                                    <div class="row">
                                        <div class="col-md-10 col-8">
                                            <div class="address">{{ $hotel->city }}</div>
                                        </div>
                                        <div class="col-md-2 col-4">
                                            @if($hotel->rating)
                                                <div class="rating"><img src="{{ route('index') }}/img/star.svg"
                                                                         alt=""> {{ $hotel->rating }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <h5>{{ $hotel->title }}</h5>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="paginate">
                            {{ $hotels->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('layouts.auth')
    @endauth

@endsection
