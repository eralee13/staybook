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
{{--                        <form>--}}
{{--                            <div class="form-group">--}}
{{--                                <input type="text" name="search" id="search" placeholder="@lang('admin.search')"--}}
{{--                                       class="form-control"--}}
{{--                                       onfocus="this.value=''">--}}
{{--                            </div>--}}
{{--                        </form>--}}

                        <h1>@lang('admin.bookings')</h1>
                        <div id="search_list"></div>
                        <table>
                            <tr>
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
                                            $room = \App\Models\Room::where('id', $book->room_id)->first();
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
                                                <span style="color: green">{{ $book->status }}</span>
                                            @else
                                                <span style="color: red">{{ $book->status }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>

                                            <ul>
                                                <a href="{{ route('listbooks.show', $book)}}"><img src="{{ route('index') }}/img/icons/eye.svg" alt=""></a>

                                            </ul>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <h2 style="text-align: center">@lang('admin.bookings_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#search').on('keyup', function () {
                let query = $(this).val();
                $.ajax({
                    url: "searchbook",
                    type: "GET",
                    data: {'search': query},
                    success: function (data) {
                        $('#search_list').html(data);
                    }
                });
            });
        });
    </script>

@endsection
