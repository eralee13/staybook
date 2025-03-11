@extends('auth.layouts.master')

@section('title', 'Бронь ' . $book->title)

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-12 modal-content">
                    <h1>@lang('admin.booking') #{{ $book->id }}</h1>
                    <div class="print">
                        <a href="javascript:window.print();"><i class="fa-regular fa-print"></i>
                            @lang('admin.print')</a>
                    </div>
                    <div class="download">
                        <a href="{{ route('pdf', $book->id) }}"><i class="fa-regular fa-download"></i> @lang('admin.download')</a>
                    </div>
                    <div class="row wrap">
                        <div class="dashboard-item">
                            <div class="name">@lang('admin.booking_made_on') {{ $book->created_at }}</div>
                        </div>
{{--                        <div class="col-md-4">--}}
{{--                            <div class="dashboard-item">--}}
{{--                                <div class="name">ID</div>--}}
{{--                                <span># {{ $book->id }}</span>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                        <div class="col-md-4">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.guests')</div>
                                {{ $book->title }}<br>
                                @isset($book->title2)
                                    {{ $book->title2 }}<br>
                                @endisset
                                @isset($book->titlec1)
                                    {{ $book->titlec1 }} - ({{$book->age1}})<br>
                                @endisset
                                @isset($book->titlec2)
                                    {{ $book->titlec2 }} - ({{$book->age2}})<br>
                                @endisset
                                @isset($book->titlec3)
                                    {{ $book->titlec3 }} - ({{$book->age3}})
                                @endisset
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.count')</div>
                                <div>{{ $book->adult }} @lang('admin.adult')</div>
                                @if($book->child > 0)
                                    <div>{{ $book->child }} @lang('admin.child')</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.phone')</div>
                                <div>{{ $book->phone }}</div>
                            </div>
                            <div class="dashboard-item">
                                <div class="name">Email</div>
                                <div>{{ $book->email }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dashboard-item">
                                @php
                                    //$category = \App\Models\Rate::where('room_id', $book->room_id)->firstOrFail();
                                    $room = \App\Models\Room::where('id', $book->room_id)->first();
                                @endphp
                                <div class="img"><img src="{{ Storage::url($room->image) }}"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.hotel')</div>
                                <div class="wrap">
                                    {{ $room->hotel->title }}
                                    <div class="name" style="margin-top: 20px">@lang('admin.room')</div>
                                    {{ $room->title }} <br>
{{--                                    <div class="name">Тариф:</div> {{ $category->title }}--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">

                            <img src="https://maps.googleapis.com/maps/api/staticmap?center=Berkeley,CA&amp;zoom=13&amp;size=400x400&amp;key=AIzaSyA3kg7YWugGl1lTXmAmaBGPNhDW9pEh5bo&amp;signature=45D4gqkHrzXqD1o0ucV_geljI6A=" alt="">

                            <div class="dashboard-item">
                                <div class="name">@lang('admin.dates_of_stay')</div>
                                {{ $book->showStartDate() }} - {{ $book->showEndDate() }}
                            </div>
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.price')</div>
                                @if($book->sum != 1)
                                    <div class="title">$ {{ $book->sum }}</div>
                                @else
                                    <div class="title">$ {{ $book->price }}</div>
                                @endif
                            </div>
                            <div class="dashboard-item">
                                <div class="name" style="margin-top: 20px">@lang('admin.status')</div>
                                <div class="status"><i class="fa-regular fa-money-bill"></i>
                                    @lang('main.paid')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                            <div id="map" style="width: 100%; height: 300px;"></div>
                            <script>
                                DG.then(function () {
                                    var map = DG.map('map', {
                                        center: [{{$room->hotel->lat}}, {{$room->hotel->lng}}],
                                        zoom: 14
                                    });

                                    DG.marker([{{$room->hotel->lat}}, {{$room->hotel->lng}}], {scrollWheelZoom:
                                            false})
                                        .addTo(map)
                                        .bindLabel('{{$room->hotel->__('title')}}', {
                                            static: true
                                        });
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
