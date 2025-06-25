@extends('auth.layouts.master')

@section('title', __('admin.rooms'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <div class="row align-items-center aic">
                        <div class="col-md-7">
                            <h6>{{ $hotel->title }}</h6>
                            <h1>@lang('admin.rooms')</h1>
                        </div>
                        <div class="col-md-5">
                            <div class="btn-wrap">
                                <a class="btn add" href="{{ route('rooms.create') }}"><i class="fa-solid
                                fa-plus"></i> @lang('admin.add_room')</a>
                            </div>
                        </div>
                    </div>

                    @if($rooms->isNotEmpty())
                        @include('auth.layouts.subroom')
                        <table class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.image')</th>
                                <th>@lang('admin.title')</th>
                                <th>@lang('admin.area')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rooms as $room)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    @php
                                        $image = \App\Models\Image::where('room_id', $room->id)->orderBy('id', 'DESC')->first();
                                    @endphp
                                    <td>
                                        @if ($image)
                                            <img src="{{ Storage::url($image->image) }}" alt="{{ $room->__('title') }}" width="100px">
                                        @else
                                            <img src="{{ route('index') }}/img/noimage.png" alt="" width="100px">
                                        @endif
                                    </td>
                                    <td>{{ $room->__('title') }}</td>
                                    <td>{{ $room->area }} м<sup>2</sup></td>
                                    <td>
                                        <form action="{{ route('rooms.destroy', $room) }}" method="post">
                                            <ul>
{{--                                                <li><a href="{{ route('rooms.show', $room)--}}
{{--                                            }}"><img src="{{ route('index') }}/img/icons/eye.svg" alt=""></a></li>--}}
                                                <li><a href="{{ route('rooms.edit', $room)
                                            }}"><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></a></li>
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Do you want to delete this?');"><img src="{{ route('index') }}/img/icons/trash.svg" alt=""></button>
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $rooms->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.rooms_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"
            integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" crossorigin="anonymous"></script>

    <script>
        function submit_post_filter() {
            let appUrl = {!! json_encode(url('/admin/')) !!};
            let s_query = $('#s_query').val();
            let show_item_at_once = $('#show_item_at_once').val();
            let ch_status = $('#ch_status').val();
            console.log(show_item_at_once);

            if (s_query != '') {
                s_query = s_query;
            } else {
                s_query = '0';
            }

            if (show_item_at_once != '0') {
                show_item_at_once = show_item_at_once;
            } else if (show_item_at_once == '0') {
                show_item_at_once = 0;
            } else {
                show_item_at_once = 'all';
            }

            if (ch_status != '') {
                ch_status = ch_status;
            } else {
                ch_status = '3';
            }


            window.location.href = appUrl + '/rooms/' + ch_status + '/' + show_item_at_once + '/' +
                s_query;
        }

        $(document).ready(function () {
            $('#filter_btn').click(function () {
                submit_post_filter();
            });

        });
    </script>

@endsection
