@extends('layouts.filter_mini')

@section('title', 'Отели')

@section('content')

    <div class="page places about">
        <div class="container">
            <div class="row">
                <h1>@lang('main.hotels')</h1>
                @foreach($hotels as $hotel)
                    <div class="col-lg-4 col-md-6">
                        <div class="places-item">
                                    <span class="img-wrap">
                                        @if($hotel->image)
                                            <img src="{{ Storage::url($hotel->image) }}" alt="">
                                        @else
                                            <img src="{{ route('index')}}/img/noimage.png" alt="">
                                        @endif
                                    </span>
                            <div class="text-wrap">
                                <div class="address">{{ $hotel->city }}</div>
                                <h5>{{ $hotel->title }}</h5>
                                @if($hotel->rating)
                                    <div class="rating"><img src="{{ route('index') }}/img/star.svg"
                                                             alt=""> {{ $hotel->rating }}</div>
                                @endif
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

@endsection
