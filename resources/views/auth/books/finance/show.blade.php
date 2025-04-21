@extends('auth.layouts.master')


@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <h1>Бронь на имя {{ $book->title }}</h1>
                    <table class="table">
                        <tr>
                            <th>Номер брони</th>
                            <td>{{ $book->id }}</td>
                        </tr>
                        <tr>
                            <th>Отель</th>
                            <td>{{ $hotel->title }}</td>
                        </tr>
                        <tr>
                            <th>Номер</th>
                            <td>{{ $room->title }}</td>
                        </tr>
                        <tr>
                            <th>Номер телефона</th>
                            <td><a href="tel:{{ $book->phone }}">{{ $book->phone }}</a></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><a href="mailto:{{ $book->email }}">{{ $book->email }}</a></td>
                        </tr>
                        <tr>
                            <th>Количество Взрослых</th>
                            <td>{{ $book->count }}</td>
                        </tr>
                        <tr>
                            <th>Количество Детей</th>
                            <td>{{ $book->countc }}</td>
                        </tr>
                        <tr>
                            <th>Стоимость</th>
                            <td>{{ $book->sum }}</td>
                        </tr>
                        <tr>
                            <th>Дата заезда</th>
                            <td>{{ $book->start_d }}</td>
                        </tr>
                        <tr>
                            <th>Дата выезда</th>
                            <td>{{ $book->end_d }}</td>
                        </tr>
                        <tr>
                            <th>Комментарий</th>
                            <td>{{ $book->comment }}</td>
                        </tr>
                        <tr>
                            <th>Статус</th>
                            <td>
                                @if($book->status == 'Забронирован')
                                    <div class="more success">{{ $book->status }}</div>
                                @endif
                            </td>
                        </tr>


                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .success{
            padding: 5px 20px;
            background-color: green;
            color: #fff;
            display: inline-block;
        }
    </style>

@endsection
