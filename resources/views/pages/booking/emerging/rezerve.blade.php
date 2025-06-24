@extends('layouts.head')

@section('title', 'Бронирование')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                    @if($message == 'Бронирование успешно создано!' || $message == 'Этот бронь уже существует!')
                        <h1>Поздравляем!</h1>

                            <div class="alert alert-primary" role="alert">
                                <strong>{{ $message }}</strong>
                            </div>
                    @else
                        <h1>Ошибка бронирования</h1>

                            <div class="alert alert-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </div>
                    @endif
    
                    <ul>
                        <li>Статус: @lang('main.' . $book->status ?? $message)</li>
                        <li>Номер брони: {{ $book->id ?? ''}}</li>
                        <li>ID отеля: {{ $request->hotel_id ?? ''}}</li>
                        <li>
                            Даты: {{ Carbon\Carbon::createFromDate($request->arrivalDate)->format('d.m.Y') }} {{$hotel->checkin ?? ''}} 
                            - {{ Carbon\Carbon::createFromDate($request->departureDate)->format('d.m.Y') }} {{ $hotel->checkout ?? ''}}
                            (UTC {{ $request->utc }})
                        </li>
                        <li>
                            @if($request->refundable == true)
                                
                                    Бесплатная отмена действует до {{ \Carbon\Carbon::parse($request->cancelDate)->format('d.m.Y') }} 
                                    (UTC {{ $request->utc }})!
                                    
                                    Иначе размер штрафа: {{ $book->cancel_penalty }} {{ $request->currency ?? '$' }}
                            @else
                                    Невозвратный тариф.
                            @endif
                        <li>
                            Заказчик: {{ $request->name ? $book->title : '' }}
                            <ul>
                                <li>Номер
                                    телефона: {{ $request->phone ?? '' }}</li>
                                <li>
                                    Email: {{ $request->email ?? '' }}</li>
                                <li>Комментарий: {{ $request->comment ?? '' }}</li>
                            </ul>
                        </li>
                    </ul>
                    @if( isset($book->id) ) 
                        <div class="bnt-wrap">
                            <form action="{{ route('cancel_calculate_tm', $book->id) }}">
                                <input type="hidden" name="number" value="{{ $book->book_token }}">
                                <button class="more">Отменить бронь</button>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <style>
        .page #map {
            margin-top: 20px;
        }

        .page i {
            color: darkblue;
        }

        .page form {
            margin-top: 50px;
        }

        .page form button {
            width: auto;
            padding: 10px 30px;
            margin-left: 10px;
        }
    </style>

@endsection
