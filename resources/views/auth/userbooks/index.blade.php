@extends('auth.layouts.master')

@section('title', __('admin.bookings'))

@section('content')

    <div class="page admin bookings">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @if($books->isNotEmpty())
                        <h1>@lang('admin.bookings')</h1>
                        <table>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.booking')</th>
                                <th>@lang('admin.guests')</th>
                                <th>@lang('admin.plans')</th>
                                <th>@lang('admin.dates_of_stay')</th>
                                <th>@lang('admin.price')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            <tbody>
                            @foreach($books as $book)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="title"># {{ $book->id }}</div>
                                        <div class="stick">B2B</div>
                                        <div class="date">@lang('admin.created') {{ $book->created_at }}</div>
                                    </td>
                                    <td>
                                        <div class="title">{{ $book->title }} {{ $book->title2 }}</div>
                                        <div class="count">{{ $book->adult }} @lang('admin.adult')</div>
                                        @if($book->child > 0)
                                            <div class="count">{{ $book->child }} @lang('admin.child')</div>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $room = \App\Models\Room::where('id', $book->room_id)->first();
                                            $plan = \App\Models\Rate::where('room_id', $book->room_id)->first();
                                        @endphp
                                        <div class="title">{{ $room->__('title_en') }}</div>
                                        <br>
                                        @isset($plan)
                                            <div class="title">{{ $plan->__('desc_en') }}</div>
                                        @endisset
                                    </td>
                                    <td>{{ $book->showStartDate() }} - {{ $book->showEndDate() }}</td>
                                    <td>
                                        @if($book->sum != 1)
                                            <div class="title">{{ $book->sum }}</div>
                                        @else
                                            <div class="title">$ {{ $book->price }}</div>
                                        @endif
                                        <div class="status"><i class="fa-regular fa-money-bill"></i> @lang('main.paid')
                                        </div>
                                    </td>
                                    <td>
                                        <form action="{{ route('userbooks.cancel', $book) }}" method="post">
                                            <ul>
                                                <a href="{{ route('userbooks.show', $book)}}" class="more"><i
                                                        class="fa-regular fa-eye"></i></a>
                                                @csrf
                                                <input type="hidden" id="api_type" name="api_type" value="{{$book->api_type}}">
                                                <button class="btn delete"
                                                        onclick="return confirm('Do you want to cancel this?');">@lang('admin.cancel')</button>
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $books->links() }}
                    @else
                        <h2 style="text-align: center">@lang('admin.bookings_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
