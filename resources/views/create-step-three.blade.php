@extends('layouts.master')

@section('title', 'Главная страница')

@section('content')
    <div class="page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <form action="{{ route('postCreateStepThree') }}" method="post" >
                        {{ csrf_field() }}
                        <div class="card">
                            <h3>Заполненные данные</h3>
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <td>Название отеля</td>
                                        <td>{{$hotel->title}}</td>
                                    </tr>
                                    <tr>
                                        <td>Тип отеля</td>
                                        <td>{{$hotel->type}}</td>
                                    </tr>
                                    <tr>
                                        <td>Город, Страна</td>
                                        <td>{{$hotel->city }}</td>
                                    </tr>
                                    <tr>
                                        <td>Адрес</td>
                                        <td>{{$hotel->address}}</td>
                                    </tr>
                                    <tr>
                                        <td>Адрес EN</td>
                                        <td>{{$hotel->address_en}}</td>
                                    </tr>
                                    <tr>
                                        <td>Описание</td>
                                        <td>{!! $hotel->description !!}</td>
                                    </tr>
                                    <tr>
                                        <td>Описание EN</td>
                                        <td>{!! $hotel->description_en !!}</td>
                                    </tr>
                                    <tr>
                                        <td>Кол-во комнат</td>
                                        <td>{{$hotel->count}}</td>
                                    </tr>
                                    <tr>
                                        <td>Время заезда</td>
                                        <td>{{ $hotel->checkin }}</td>
                                    </tr>
                                    <tr>
                                        <td>Время выезда
                                        </td>
                                        <td>{{ $hotel->checkout }}</td>
                                    </tr>
                                    <tr>
                                        <td>Ранний заезд</td>
                                        <td>{{ $hotel->early_in }}</td>
                                    </tr>
                                    <tr>
                                        <td>Поздний выезд</td>
                                        <td>{{ $hotel->early_out }}</td>
                                    </tr>
                                    <tr>
                                        <td>Номер телефона</td>
                                        <td>{{ $hotel->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>{{ $hotel->email }}</td>
                                    </tr>
                                    <tr>
                                        <td>Статус</td>
                                        <td>
                                            @if($hotel->status == 1)
                                                Включен
                                            @else
                                                Отключен
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="card-footer">
                                <div class="row" style="margin-top: 30px">
                                    <div class="col-md-6 text-left">
                                        <a href="{{ route('createStepTwo') }}" class="more btn btn-danger
                                    pull-right">Назад</a>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button type="submit" class="more btn btn-primary">Готово</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
