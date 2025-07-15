@extends('layouts.head')

@section('title', 'Подтверждение отмены брони')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    
                    @if($book->status == 'Cancelled')
                        <h1 data-aos="fade-up" data-aos-duration="2000">Ваша бронь отменена!</h1>
                        <div class="alert alert-success">Статус: @lang('main.' . $book->status)</div>
                    @else
                        <h1 data-aos="fade-up" data-aos-duration="2000">Ошибка, Бронь не был отменен! </h1>
                        <p>Попробуйте обновить страницу. Если отмена всё же не была произведена, обратитесь к вашему менеджеру по работе с клиентами!</p>
                        <div class="alert alert-danger">Статус: @lang('main.' . $book->status)</div>
                    @endif
                    <ul>
                        <li>Номер брони: {{ $book->id }}</li>
                        {{--                            <li>Дата отмены: {{ $cancel_date }}</li>--}}
                        <li>
                            @if(isset($cancelRule->is_refundable) && $cancelRule->is_refundable == true)
                                <td>Бесплатная отмена действует до {{ $cancelRule->end_date }} (UTC {{ $hotel->utc }}). Размер
                                    штрафа: {{ $book->cancel_penalty }} {{ $book->currency ?? '$' }}</td>
                            @else
                                Возможность бесплатной отмены отсутствует. Размер штрафа: {{ $book->cancel_penalty }} {{ $book->currency ?? 'CNY' }}
                            @endif
                        <li>Отель: {{ $hotel->title }}</li>
                        <li>Дата заеда/выезда: {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }} (UTC {{ $hotel->utc }})</li>
                        <li>Тип комнаты: {{ $room->title ?? ''}}</li>
                        <li>Тариф: {{ $rate->title ?? ''}}</li>
                        <li>Кол-во гостей: {{ $book->adult ?? ''}}</li>
                    </ul>

                    <a href="{{ route('index') }}" class="more btn">На главную</a>
                </div>
            </div>
        </div>
    </div>


@endsection