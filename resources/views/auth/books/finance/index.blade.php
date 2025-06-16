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
                        <div class="row align-items-center aic">
                            <div class="col-md-9">
                                <h1>@lang('admin.bookings')</h1>
                            </div>
                            <div class="col-md-3">
                                <div class="btn-wrap">
                                    <a class="btn add" href="{{ route('excel-books') }}">
                                      Выгрузить в Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                        <table>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.booking')</th>
                                <th>@lang('admin.guests')</th>
                                <th>@lang('admin.hotel')</th>
                                <th>@lang('admin.plans')</th>
                                <th>@lang('admin.dates_of_stay')</th>
                                <th>@lang('admin.price')</th>
{{--                                <th>@lang('admin.action')</th>--}}
                            </tr>
                            <tbody>
                            @foreach($books as $book)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="title"># {{ $book->id }}</div>
                                        {{--                                        <div class="stick">B2B</div>--}}
                                        <div class="date">@lang('admin.created') {{ $book->created_at }}</div>
                                    </td>
                                    <td>
                                        <div class="title">{{ $book->title }}</div>
                                        <div class="date">{{ $book->adult }} @lang('admin.adult')</div>
                                        @if($book->child > 0)
                                            <div class="date">{{ $book->child }} @lang('admin.child')</div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->orWhere('exely_id', $book->hotel_id)->first();
                                        @endphp
                                        {{ $hotel->__('title') }}
                                    </td>
                                    <td>
                                        @php
                                            $room = \App\Models\Room::where('id', $book->room_id)->orWhere('exely_id', $book->room_id)->first();
                                            $plan = \App\Models\Rate::where('room_id', $book->room_id)->first();
                                        @endphp
                                        @isset($room)
                                            <div class="title">{{ $room->__('title') }}</div>
                                        @endisset

                                        @isset($plan)
                                            <div class="title">{{ $plan->__('title') }}</div>
                                        @endisset
                                    </td>
                                    <td>{{ $book->showStartDate() }} - {{ $book->showEndDate() }}</td>
                                    <td>
                                        @if($book->sum != 1)
                                            <div class="title">{{ $book->sum }}
                                                @if($book->currency)
                                                    {{ $book->currency }}
                                                @else
                                                    $
                                                @endif
                                            </div>
                                        @else
                                            <div class="title">$ {{ $book->price }}</div>
                                        @endif
                                        <div class="status">
                                            @if($book->status == 'Reserved')
                                                <div class="alert alert-success">
                                                    {{ $book->status }}
                                                </div>
                                            @else
                                                <div class="alert alert-danger">
                                                    {{ $book->status }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
{{--                                    <td>--}}
{{--                                            <ul>--}}
{{--                                                <a href="{{ route('allbooks.show', $book)}}"><img src="{{ route('index') }}/img/icons/eye.svg" alt=""></a>--}}
{{--                                            </ul>--}}
{{--                                    </td>--}}
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {{ $books->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.bookings_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .alert{
            padding: 2px 5px;
            font-size: 10px;
            display: inline-block;
        }
    </style>



@endsection
