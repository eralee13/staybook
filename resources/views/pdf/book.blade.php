<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<head>
    <title>StayBook</title>
</head>
<body>

<style>
    body {
        font-family: DejaVu Sans
    }

    table {
        width: 100%;
    }

    table td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    table a {
        text-decoration: none;
        color: #000;
    }

    .address {
        font-size: 14px;
    }

    .stick {
        background-color: orange;
        color: #333;
        display: inline-block;
        padding: 2px 5px;
        font-size: 12px;
        border-radius: 5px;
    }

    .pay {
        color: green;
        opacity: .8;
    }

    .descr {
        font-size: 12px;
    }
</style>

@php
    $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->orWhere('exely_id', $book->hotel_id)->firstOrFail();
    $contacts = \App\Models\Contact::first();
    $room = \App\Models\Room::where('id', $book->room_id)->orWhere('exely_id', $book->room_id)->firstOrFail();
    //$category = \App\Models\Rate::where('room_id', $book->room_id)->firstOrFail();
@endphp

<div class="page admin">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <table>
                    <tr>
                        <td>
                            <div class="logo"><img src="{{ public_path("img/logo_b.svg") }}" width="120px"
                                                   alt="Logo"></div>
                        </td>
                        <td>
                            <div class="phone">
                                <a href="tel:{{ $contacts->phone }}">{{ $contacts->phone }}</a><br>
                                <a href="tel:{{ $contacts->phone2 }}">{{ $contacts->phone2 }}</a><br>
                            </div>
                            <div class="address">{{ $contacts->address }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td>{{ $book->id }}</td>
                    </tr>
                    <tr>
                        <td>Booking made on</td>
                        <td>{{ $book->created_at }}</td>
                    </tr>
                    <tr>
                        <td>Rate</td>
                        <td>
                            <div class="stick">B2B</div>
                        </td>
                    </tr>
                    <tr>
                        <td>Guest</td>
                        <td>
                            {{ $book->title }} ({{ $book->adult }} @lang('admin.adult'))<br>
                            @isset($book->title2)
                                {{ $book->title2 }}<br>
                            @endisset
                            {{ $book->child }} - @lang('admin.child')
                        </td>
                    </tr>
                    <tr>
                        <td>Check In</td>
                        <td>{{ $book->showStartDate() }} from {{ $hotel->checkin }}</td>
                    </tr>
                    <tr>
                        <td>Check Out</td>
                        <td>{{ $book->showEndDate() }} until {{ $hotel->checkout }}</td>
                    </tr>
                    <tr>
                        <td>

                        </td>
                        <td>{{ $room->title }}<br>
                        </td>
                    </tr>
{{--                    <tr>--}}
{{--                        <td>Bedding</td>--}}
{{--                        <td>{{ $room->bed }}</td>--}}
{{--                    </tr>--}}
                    <tr>
                        <td colspan="2">
                            {{ $hotel->title }}<br>
                            {{ $hotel->address }}
                            <div class="descr">{!! $hotel->description !!}</div>
                            @php
                                $lat = old('lat', isset($hotel->lat) ? $hotel->lat : 42.8746);
                                $lng = old('lng', isset($hotel->lng) ? $hotel->lng : 74.6120);
                                $zoom = 15;
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
                        </td>

                    </tr>
                    <tr>
                        <td>Accommodation price</td>
                        <td>{{ $book->sum }} {{ $book->currency }}</td>
                    </tr>
                    <tr>
                        <td>Payment type</td>
                        <td>
                            <div class="pay">{{ $book->status }}</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{--        <div class="row">--}}
        {{--            <div class="col-md-12 modal-content">--}}

        {{--                <div class="date">Дата создания: {{ $date }}</div>--}}
        {{--                <h1>Бронь #{{ $book->id }}</h1>--}}

        {{--                <div class="row wrap">--}}
        {{--                    <div class="col-md-6">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            <div class="name">ID</div>--}}
        {{--                            <span># {{ $book->id }}</span>--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                    <div class="col-md-6">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            <div class="name">ФИО</div>--}}
        {{--                            {{ $book->title }} <br>--}}
        {{--                            @isset($book->title2)--}}
        {{--                                {{ $book->title2 }}<br>--}}
        {{--                            @endisset--}}
        {{--                            @isset($book->titlec1)--}}
        {{--                                {{ $book->titlec1 }} - ({{$book->age1}})<br>--}}
        {{--                            @endisset--}}
        {{--                            @isset($book->titlec2)--}}
        {{--                                {{ $book->titlec2 }} - ({{$book->age2}})<br>--}}
        {{--                            @endisset--}}
        {{--                            @isset($book->titlec3)--}}
        {{--                                {{ $book->titlec3 }} - ({{$book->age3}})--}}
        {{--                            @endisset--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                    <div class="col-md-6">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            <div class="name">Кол-во</div>--}}
        {{--                            <div>{{ $book->count }} взрос.</div>--}}
        {{--                            @if($book->countc > 0)--}}
        {{--                                <div>{{ $book->countc }} дет.</div>--}}
        {{--                            @endif--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                    <div class="col-md-6">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            <div class="name">Дата заезда/выезда</div>--}}
        {{--                            {{ $book->showStartDate() }} - {{ $book->showEndDate() }}--}}
        {{--                        </div>--}}
        {{--                    </div>--}}

        {{--                    <div class="col-md-4">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            @php--}}
        {{--                                $room = \App\Models\Room::where('id', $book->room_id)->firstOrFail();--}}
        {{--                            @endphp--}}
        {{--                            <div class="img" style="margin-top: 40px"><img src="{{ storage_path('app/public/'--}}
        {{--                            .$room->image) }}"--}}
        {{--                                alt="Logo" style="width: 200px; height: 150px;"></div>--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                    <div class="col-md-4">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            <div class="name">Отель</div>--}}
        {{--                            <div class="title">{{ $room->hotel->title }}</div>--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                    <div class="col-md-4">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            <div class="name">Номер</div>--}}
        {{--                            <div class="title">{{ $room->title }}</div>--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                    <div class="col-md-6">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            <div class="name">Стоимость</div>--}}
        {{--                            <div class="title">{{ $book->sum }}</div>--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                    <div class="col-md-6">--}}
        {{--                        <div class="dashboard-item">--}}
        {{--                            <div class="name">Статус</div>--}}
        {{--                            <div class="status" style="color: green"><i class="fa-regular fa-money-bill"></i>--}}
        {{--                                Оплачено--}}
        {{--                            </div>--}}
        {{--                        </div>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        </div>--}}
    </div>
</div>

</body>
</html>
