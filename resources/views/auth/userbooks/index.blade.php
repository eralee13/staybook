@extends('auth.layouts.master')

@section('title', __('admin.bookings'))

@section('content')

    <div class="page admin bookings">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @if($books->isNotEmpty())
                        <h1>@lang('admin.my_bookings')</h1>
                        <ul class="tabs">
                            <li class="current"
                            ">Все</li>
                            <li>Будущие</li>
                            <li>Активные</li>
                            <li>Завершенные</li>
                            <li>Отмененные</li>
                        </ul>

                        {{--                            <tr>--}}
                        {{--                                <th>#</th>--}}
                        {{--                                <th>@lang('admin.booking')</th>--}}
                        {{--                                <th>@lang('admin.guests')</th>--}}
                        {{--                                <th>@lang('admin.plans')</th>--}}
                        {{--                                <th>@lang('admin.dates_of_stay')</th>--}}
                        {{--                                <th>@lang('admin.price')</th>--}}
                        {{--                                <th>@lang('admin.status')</th>--}}
                        {{--                                <th>@lang('admin.action')</th>--}}
                        {{--                            </tr>--}}
                        @foreach($books as $book)
                            <table>
                                <tbody>
                                <tr>
                                    <td>
                                        <div class="title">Заказ:</div>
                                        <div class="value"># {{ $book->id }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Статус:</div>
                                        <div class="value" style="color: green">{{ $book->status }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Гость:</div>
                                        <div class="value">{{ $book->title }}</div>
                                        {{--                                        <div class="count">{{ $book->count }} @lang('admin.adult')</div>--}}
                                        {{--                                        @if($book->countc > 0)--}}
                                        {{--                                            <div class="count">{{ $book->countc }} @lang('admin.child')</div>--}}
                                        {{--                                        @endif--}}
                                    </td>
                                    <td>
                                        <div class="title">Кол-во гостей:</div>
                                        <div class="value">{{ $book->adult }} взрос.</div>
                                        {{--                                        <div class="count">{{ $book->count }} @lang('admin.adult')</div>--}}
                                        {{--                                        @if($book->countc > 0)--}}
                                        {{--                                            <div class="count">{{ $book->countc }} @lang('admin.child')</div>--}}
                                        {{--                                        @endif--}}
                                    </td>
                                    <td>
                                        <div class="title">К оплате</div>
                                        <div class="value">{{ $book->sum }} $</div>
                                    </td>
                                    <td>
                                        <div class="title">Дата создания:</div>
                                        @php
                                            $date = \Carbon\Carbon::createFromDate($book->created_at)->format('d.m.Y H:i')
                                        @endphp
                                        <div class="value">{{ $date }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="title">Заезд:</div>
                                        <div class="value">{{ $book->showStartDate() }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Выезд:</div>
                                        <div class="value">{{ $book->showEndDate() }}</div>
                                    </td>
                                    @php
                                        $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
                                    @endphp
                                    <td>
                                        <div class="title">Отель</div>
                                        <div class="value">{{ $hotel->title }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Город</div>
                                        <div class="value">Бишкек</div>
                                    </td>
                                    <td>
                                        <div class="title">Бесплатная отмена до</div>
                                        <div class="value">10.04.2025</div>
                                    </td>
                                    <td>
                                        <form action="{{ route('userbooks.cancel', $book) }}" method="post">
                                            <ul>
                                                <a href="{{ route('userbooks.show', $book)}}" class="more"><i
                                                            class="fa-regular fa-eye"></i></a>
                                                @csrf
                                                @if($book->status == 'Reserved')
                                                    <button class="btn delete"
                                                            onclick="return confirm('Do you want to cancel this?');"><i
                                                                class="fa-solid fa-xmark"></i></button>
                                                @endif
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        @endforeach
                    @else
                        <h2 style="text-align: center">@lang('admin.bookings_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <style>
        ul.tabs {
            padding-left: 0;
            text-align: left;
        }

        ul.tabs li.current {
            background-color: #0a58ca;
            color: #fff;
        }

        table {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        table tbody {
            margin-bottom: 20px;
        }

        table tr {
            background-color: #fff;
        }

        table td, table th {
            padding: 20px;
            border-color: #f5f5f5;
        }

        .title {
            font-size: 14px;
            opacity: .6;
        }

        .value {
            font-size: 20px;
        }
    </style>

@endsection
