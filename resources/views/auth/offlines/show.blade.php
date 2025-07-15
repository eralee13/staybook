@extends('auth.layouts.master')

@section('title', 'Offline ' . $offline->name)

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9 modal-content">
                    <h1>@lang('admin.offlines') #{{ $offline->name }}</h1>
                    <div class="dashboard-item">
                        <div class="name">@lang('admin.booking_made_on') {{ $offline->created_at }}</div>
                    </div>
                    <div class="row wrap">
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.guests')</div>
                                {{ $offline->name }}<br>
                                {{ $offline->phone }}<br>
                                {{ $offline->email }}<br>
                                {{ $offline->message }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.count')</div>
                                <div class="div">{{ $offline->room_count }} @lang('admin.room'), {{ $offline->adult }} @lang('admin.adult')</div>
                                @if($offline->child > 0)
                                    <div>{{ $offline->child }} @lang('admin.child') ({{ implode(', ', json_decode($offline->childAges, true)) }} лет)</div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.dates_of_stay')</div>
                                {{ $offline->showStartDate() }} - {{ $offline->showEndDate() }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">@lang('admin.price')</div>
                                $ {{ $offline->min_price }} - {{ $offline->max_price }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">Вид питания</div>
                                {{ $offline->meal }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">Город</div>
                                {{ $offline->city }}
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">Тип</div>
                                {{ $offline->type }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">Рейтинг</div>
                                {{ $offline->rating }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">Тип размещения</div>
                                {{ $offline->accommodation }}
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="dashboard-item">
                                <div class="name">Тип номера</div>
                                {{ $offline->type_room }}
                            </div>
                        </div>
                        @isset($offline->file)
                            <div class="col-md-2">
                                <div class="dashboard-item">
                                    <div class="name">Файл</div>
                                    <a href="{{ Storage::url($offline->file) }}" target="_blank">Скачать файл</a>
                                </div>
                            </div>
                        @endisset
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection
