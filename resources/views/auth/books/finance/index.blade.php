@extends('auth.layouts.master')

@section('title', __('admin.bookings'))

@section('content')

    <div class="page admin bookings">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-9">
                                <label for="month">Фильтр по месяцу</label>
                                <select name="month" id="month" class="form-control" onchange="this.form.submit()">
                                    <option value="">Все месяцы</option>
                                    @foreach(range(1, 12) as $m)
                                        @php
                                            $date = \Carbon\Carbon::createFromDate(null, $m, 1);
                                        @endphp
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                            {{ $date->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="" style="color: transparent">1</label>
                                <div class="btn-wrap">
                                    <a class="btn add" style="display: block; text-align: center;" href="{{ route('excel-books') }}">
                                        @lang('admin.export_excel')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    @if($books->isNotEmpty())
                        <div class="table-wrap">
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
                                    @php
                                        $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->orWhere('exely_id', $book->hotel_id)->first();
                                        $room = \App\Models\Room::where('id', $book->room_id)->orWhere('exely_id', $book->room_id)->first();
                                        $plan = \App\Models\Rate::where('room_id', $book->room_id)->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="title"># {{ $book->id }}</div>
                                            {{--                                        <div class="stick">B2B</div>--}}
                                            <div class="date">@lang('admin.created') {{ $book->created_at }}</div>
                                        </td>
                                        <td>
                                            <div class="title">{{ $book->title1 }}</div>
                                            <div class="date">{{ $book->adult }} @lang('admin.adult')</div>
                                            @if($book->child > 0)
                                                <div class="date">{{ $book->child }} @lang('admin.child')</div>
                                            @endif
                                        </td>
                                        @isset($hotel)
                                        <td>
                                            {{ $hotel->__('title') }}
                                        </td>
                                        @else
                                            <td></td>
                                        @endisset
                                        <td>
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
                                                    <span style="color: var(--green)">{{ $book->status }}</span>
                                                @else
                                                    <span style="color: #CA6561">{{ $book->status }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
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
