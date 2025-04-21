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
                                    $room = \App\Models\Room::where('id', $book->room_id)->orWhere('exely_id', $book->room_id)->first();
                                    $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->orWhere('exely_id', $book->hotel_id)->first();
                                @endphp

                                @isset($room->image)
                                    @if(\Illuminate\Support\Facades\Storage::exists($room->image))
                                        <div class="img"><img src="{{ Storage::url($room->image) }}"></div>
                                    @else
                                        <div class="img"><img src="{{ route('index') }}/img/{{$room->image}}"></div>
                                    @endif
                                @endisset
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.hotel')</div>
                                <div class="wrap">
                                    {{ $hotel->title }}
                                    <div class="name" style="margin-top: 20px">@lang('admin.room')</div>
                                    @isset($room) {{ $room->title }}@endisset <br>
{{--                                    <div class="name">Тариф:</div> {{ $category->title }}--}}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                                    {{ $book->status }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @isset($hotel)
                        <div class="row">
                            <div class="col-md-6">
                                @php
                                    $lat = old('lat', isset($hotel->lat) ? $hotel->lat : 42.8746);
                                    $lng = old('lng', isset($hotel->lng) ? $hotel->lng : 74.6120);
                                    $zoom = 12;
                                    $width = 500;
                                    $height = 180;

                                    // Генерируем URL статического изображения карты
                                    $mapUrl = "https://static-maps.yandex.ru/1.x/?ll=$lng,$lat&size={$width},{$height}&z=$zoom&l=map&pt=$lng,$lat,pm2rdl";

                                @endphp

                                @if($lat && $lng)
                                    <img src="{{ $mapUrl }}" alt="Карта" style="width:100%; height: 180px; max-width:480px;">
                                @else
                                    <p>Координаты карты не указаны</p>
                                @endif
                            </div>
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>

@endsection
