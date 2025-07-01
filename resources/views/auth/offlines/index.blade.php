@extends('auth.layouts.master')

@section('title', __('admin.offlines'))

@section('content')

    <div class="page admin offlineings">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @if($offlines->isNotEmpty())
                        <h1>@lang('admin.offlines')</h1>
                        @foreach($offlines as $offline)
                            <table>
                                <tbody>
                                <tr>
                                    <td>
                                        <div class="title">ФИО:</div>
                                        <div class="value">{{ $offline->name }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Номер телефона:</div>
                                        <div class="value">{{ $offline->phone }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Email:</div>
                                        <div class="value">{{ $offline->email }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Комменатрий:</div>
                                        <div class="value">{{ $offline->message }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Кол-во:</div>
                                        <div class="value">{{ $offline->room_count }} ном. {{ $offline->adult }} взрос. {{ $offline->child }} дет. ({{ implode(', ', json_decode($offline->childAges, true)) }} лет)</div>
                                    </td>
                                    <td>
                                        <div class="title">Дата создания:</div>
                                        @php
                                            $date = \Carbon\Carbon::createFromDate($offline->created_at)->format('d.m.Y H:i')
                                        @endphp
                                        <div class="value">{{ $date }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="title">Даты заезда:</div>
                                        <div class="value">{{ $offline->showStartDate() }} - {{ $offline->showEndDate() }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Стоимость:</div>
                                        <div class="value">$ {{ $offline->min_price }} - {{ $offline->max_price }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Город</div>
                                        <div class="value">{{ $offline->city }} <div class="alert alert-warning">{{ $offline->meal }}</div>
                                    </td>
                                    <td>
                                        <div class="title">Тип</div>
                                        <div class="value">{{ $offline->type }}, {{ $offline->accommodation }}, {{ $offline->type_room }}</div></div>
                                    </td>
                                    <td>
                                        <div class="title">Рейтинг</div>
                                        <div class="value">{{ $offline->rating }}</div>
                                    </td>
                                    <td>
                                        <a href="{{ route('offlines.show', $offline)}}"><img
                                                    src="{{ route('index') }}/img/icons/eye.svg" alt=""></a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        @endforeach
                    @else
                        <h2 style="text-align: center">@lang('admin.offlineings_not_found')</h2>
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
