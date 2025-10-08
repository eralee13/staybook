@extends('auth.layouts.master')

@section('title', 'Бронь ' . $book->title)

@section('content')

    @php
        $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
        $room = \App\Models\Room::where('id', $book->room_id)->orWhere('exely_id', $book->room_id)->first();
        $img = \App\Models\Image::where('room_id', $book->room_id)->first();
        $rate = \App\Models\Rate::where('id', $book->rate_id)->first();
        $lat = old('lat', isset($room->hotel->lat) ? $room->hotel->lat : 42.8746);
        $lng = old('lng', isset($room->hotel->lng) ? $room->hotel->lng : 74.6120);
        $zoom = 15;
        $width = 600;
        $height = 380;
        $mapUrl = "https://static-maps.yandex.ru/1.x/?ll=$lng,$lat&size={$width},{$height}&z=$zoom&l=map&pt=$lng,$lat,pm2rdl";
    @endphp

    <div class="page admin">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <h1>@lang('admin.booking') #{{ $book->id }}</h1>
                    <div class="row list_btn">
                        <div class="col-md-6">
                            <div class="print">
                                <a href="javascript:window.print();" class="more">@lang('admin.print')</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="download">
                                <a href="{{ route('pdf', $book->id) }}" class="more">@lang('admin.download')</a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="list-item">
                                <h6>@lang('admin.booking_made_on') {{ $book->created_at }}</h6>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="list-item">
                                @if(!empty($img->image))
                                    <div class="img"><img src="{{ Storage::url($img->image) }}"></div>
                                @else
                                    <div class="img"><img src="{{ route('index') }}/img/noimage.png" alt=""></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-wrap">
                                <table>
                                    <tr>
                                        <td style="border-top: none"><b>@lang('admin.hotel')</b></td>
                                        <td style="border-top: none">{{ $hotel->title ??  $hotel->title_en ?? ''}} <br>
                                    </tr>
                                    @isset($room)
                                        <tr>
                                            <td><b>@lang('admin.room')</b></td>
                                            <td>{{ $room->__('title') ?? ''}}</td>
                                        </tr>
                                    @endisset
                                    @if(!empty($rate))
                                        <tr>
                                            <td><b>@lang('admin.rate')</b></td>
                                            <td>{{ $rate->__('title') ?? ''}}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><b>@lang('admin.dates_of_stay')</b></td>
                                        <td>{{ $book->showStartDate() }} - {{ $book->showEndDate() }}</td>
                                    </tr>
                                    @if($book->checkin_request == 1)
                                        <tr>
                                            <td><b>@lang('admin.late_checkin')</b></td>
                                            <td>{{ \Carbon\Carbon::createFromDate($book->checkin_time)->format('H:i') }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td><b>Token</b></td>
                                        <td># {{ $book->book_token }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>@lang('admin.guests')</b></td>
                                        <td>{{ $book->title }}<br>
                                            {{ $book->child_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>@lang('admin.count')</b></td>
                                        <td>{{ $book->adult }} @lang('admin.adult')
                                            @if($book->child > 0)
                                                <div>{{ $book->child }} @lang('admin.child')
                                                    (возраст: {{$book->childages}})
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><b>@lang('admin.phone')</b></td>
                                        <td>{{ $book->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Email</b></td>
                                        <td>{{ $book->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>@lang('admin.price')</b></td>
                                        <td>@if($book->sum != 1)
                                                {{ $book->sum }} {{ $book->currency }}
                                            @else
                                                {{ $book->price }} {{ $book->currency }}
                                            @endif</td>
                                    </tr>
                                    @php
                                        if(isset($rate)){
                                            $cancelPossible = \App\Models\CancellationRule::where('rate_id', $rate->id)->first();
                                            $freeDate = \Carbon\Carbon::parse($book->arrivalDate)->format('d.m.Y H:i');
                                            $cancel = \App\Models\CancellationRule::where('id', $book->cancellation_id)->first();
                                            if($cancel != null){
                                                $cancelDate = \Carbon\Carbon::parse($book->arrivalDate)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
                                            }
                                        }
                                        $timezone = \Carbon\Carbon::parse($hotel->timezone)->format('P');
                                    @endphp
                                    @isset($cancel)
                                        <tr>
                                            <td><b>@lang('main.cancellation_policy')</b></td>
                                            <td>@if($cancel->cancel_policy === 'free_until_checkin')
                                                    @lang('main.free_cancellation') {{ $freeDate }}
                                                    UTC {{ $timezone }}

                                                @elseif($cancel->cancel_policy === 'free_then_penalty')
                                                    @if(now()->lte($cancelDate))
                                                        @lang('main.free_cancellation') {{ $cancelDate }}
                                                        UTC {{ $timezone }}
                                                    @else
                                                        @lang('main.cancellation_is_not_avaialble')
                                                        .
                                                    @endif
                                                    @lang('main.cancellation_amount')
                                                    : {{ $book->cancel_penalty }} {{ $book->currency }}
                                                @else
                                                    @lang('main.cancellation_amount')
                                                    : {{ $book->cancel_penalty }} {{ $book->currency }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endisset
                                    <tr>
                                        <td><b>@lang('admin.status')</b></td>
                                        <td>{{ $book->status }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="list-item">
                                @if($lat && $lng)
                                    <img src="{{ $mapUrl }}" alt="Карта" style="border: none; width: 100%">
                                @else
                                    <p>Координаты карты не указаны</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
