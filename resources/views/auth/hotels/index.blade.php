@extends('auth.layouts.hotelhead')

@section('title', __('admin.hotels'))

@section('content')

    <div class="page hotels">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <form>
                        <input type="text" name="search" id="search" placeholder="@lang('admin.search')"
                               class="form-control"
                               onfocus="this.value=''">
                    </form>

                </div>
                <div class="col-md-3">
                    <div class="add">
                        <a href="{{ route('hotels.create') }}" class="more"><i class="fa-regular fa-plus"></i>
                            @lang('admin.add_hotel')</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="add">
                        <a href="{{ route('hotel.create') }}" class="more add">Воспользоваться помощником</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="search_list"></div>
                    @admin
                        Кол-во отелей: {{ $chotel->count() }}
                    @endadmin
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>@lang('admin.title')</th>
                            <th>@lang('admin.address')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                        @foreach($hotels as $hotel)
                            <tr>
                                <td>{{ $hotel->id }}</td>
                                <td>{{ $hotel->__('title') }}</td>
                                <td>{{ $hotel->__('address') }}</td>
                                <td>
                                    <form action="{{ route('hotels.destroy', $hotel) }}" method="post">
                                        <ul>
                                            <a href="{{ route('hotels.show', $hotel) }}" class="select-hotel" data-hotel="{{ $hotel->id }}"><img src="{{ route('index') }}/img/icons/eye.svg" alt=""></a>
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="return confirm('Do you want to delete this?');"><img src="{{ route('index') }}/img/icons/trash.svg" alt=""></a></button>
                                        </ul>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>


                    {{ $hotels->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#search').on('keyup', function () {
                var query = $(this).val();
                $.ajax({
                    url: "search",
                    type: "GET",
                    data: {'search': query},
                    success: function (data) {
                        $('#search_list').html(data);
                    }
                });
                //end of ajax call
            });
        });
    </script>

@endsection
