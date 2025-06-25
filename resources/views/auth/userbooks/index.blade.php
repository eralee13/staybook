@extends('auth.layouts.master')

@section('title', __('admin.bookings'))

@section('content')

    <div class="page admin bookings">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @if($books->isNotEmpty())
                        <h1>@lang('admin.my_bookings')</h1>
                        @foreach($books as $book)
                            <table>
                                <tbody>
                                <tr>
                                    <td>
                                        <div class="title">Заказ:</div>
                                        <div class="value"># {{ $book->id }}</div>
                                    </td>
                                    @if($book->api_type == 'exely')
                                        <td>
                                            <div class="title"># заказа Exelly</div>
                                            <div class="value">{{ $book->book_token }}</div>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="title">Статус:</div>
                                        @if($book->status == 'Reserved')
                                            <div class="value" style="color: green">{{ $book->status }}</div>
                                        @else
                                            <div class="value" style="color: red">{{ $book->status }}</div>
                                        @endif
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
                                    </td>
                                    <td>
                                        <div class="title">К оплате</div>
                                        <div class="value">{{ $book->sum }} {{ $book->currency ?? '$' }}</div>
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
                                        $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->orWhere('exely_id', $book->hotel_id)->first();
                                    @endphp
                                    <td>
                                        <div class="title">Отель</div>
                                        <div class="value">{{ $hotel->title }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Город</div>
                                        <div class="value">{{ $hotel->city }}</div>
                                    </td>
                                    @php
                                        $cancel = \App\Models\CancellationRule::where('id', $book->cancellation_id)->first();
                                    @endphp
                                    @if($cancel)
                                        <td>
                                            <div class="title">Правило аннуляции</div>
                                            @if($cancel->is_refundable == true)
                                                <div class="value">@lang('main.cancellation_amount')
                                                    : {{ $book->cancel_penalty }} {{ $book->currency ?? '$' }}
                                                </div>
                                            @else
                                                <div class="value">@lang('main.cancellation_is_not_avaialble')
                                                    . @lang('main.cancellation_amount')
                                                    : {{ $book->cancel_penalty }} {{ $book->currency ?? '$' }}</div>
                                            @endif
                                        </td>
                                    @else
                                        <td></td>
                                    @endif
                                    <td>
                                        @if($book->api_type == 'local')
                                            <form action="{{ route('userbooks.cancel_calculate', $book) }}"
                                                  method="post">
                                                <ul>
                                                    <a href="{{ route('userbooks.show', $book)}}"><img
                                                                src="{{ route('index') }}/img/icons/eye.svg" alt=""></a>
                                                    @csrf
                                                    @if($book->status == 'Reserved')
                                                        <button class="btn delete"
                                                                onclick="return confirm('Do you want to cancel this?');">
                                                            <i class="fa-solid fa-xmark"></i></button>
                                                    @endif
                                                </ul>
                                            </form>
                                        @else
                                            <form action="{{ route('userbooks.cancel_calculate_exely', $book) }}"
                                                  method="post">
                                                <ul>
                                                    <a href="{{ route('userbooks.show', $book)}}"><img
                                                                src="{{ route('index') }}/img/icons/eye.svg" alt=""></a>
                                                    @csrf
                                                    @if($book->status == 'Reserved')
                                                        <button class="btn delete"
                                                                onclick="return confirm('Do you want to cancel this?');">
                                                            <i class="fa-solid fa-xmark"></i></button>
                                                    @endif
                                                </ul>
                                            </form>
                                        @endif
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
            font-size: 12px;
            opacity: .6;
        }

        .value {
            font-size: 14px;
        }
    </style>

@endsection
