@extends('auth.layouts.master')

@section('title', __('admin.offlines'))

@section('content')

    <div class="page admin offlineings">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @if($offlines->isNotEmpty())
                        <h1>@lang('admin.offlines')</h1>
                        @foreach($offlines as $offline)
                            <div class="table-wrap">
                                <table>
                                    <tbody>
                                    <tr>
                                        <td style="border-top: none"><b>ФИО:</b></td>
                                        <td style="border-top: none">{{ $offline->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Номер телефона:</b></td>
                                        <td>{{ $offline->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Email:</b></td>
                                        <td>{{ $offline->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Комментарий:</b></td>
                                        <td>{{ $offline->message }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Кол-во:</b></td>
                                        <td>{{ $offline->room_count }} ном. {{ $offline->adult }}
                                            взрос. {{ $offline->child }} дет.
                                            ({{ implode(', ', json_decode($offline->childAges, true)) }} лет)
                                        </td>
                                    </tr>
                                    @php
                                        $date = \Carbon\Carbon::createFromDate($offline->created_at)->format('d.m.Y H:i')
                                    @endphp
                                    <tr>
                                        <td><b>Дата создания:</b></td>
                                        <td>{{ $date }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Даты заезда:</b></td>
                                        <td>{{ $offline->showStartDate() }} - {{ $offline->showEndDate() }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Стоимость:</b></td>
                                        <td>$ {{ $offline->min_price }} - {{ $offline->max_price }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Город:</b></td>
                                        <td>{{ $offline->city }}</td>
                                    </tr>
                                    @isset($offline->meal)
                                        <tr>
                                            <td><b>Тип питания:</b></td>
                                            <td>{{ $offline->meal }}</td>
                                        </tr>
                                    @endisset
                                    <tr>
                                        <td><b>Тип:</b></td>
                                        <td>{{ $offline->type }}, {{ $offline->accommodation }}
                                            , {{ $offline->type_room }}
                                            /td>
                                    </tr>
                                    <tr>
                                        <td><b>Рейтинг:</b></td>
                                        <td>{{ $offline->rating }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Управление</b></td>
                                        <td>
                                            <a href="{{ route('offlines.show', $offline)}}"><img
                                                        src="{{ route('index') }}/img/icons/eye.svg"
                                                        style="max-width: 24px"></a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @else
                        <h2 style="text-align: center">@lang('admin.offlineings_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
