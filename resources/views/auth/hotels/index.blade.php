@extends('auth.layouts.master')

@section('title', __('admin.hotels'))

@section('content')

    <div class="page hotels">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <form>
                        <input type="text" name="search" id="search" placeholder="@lang('admin.search')"
                               class="form-control"
                               onfocus="this.value=''">
                    </form>
                </div>
                <div class="col-md-6">
                    <div class="add">
                        <a href="{{ route('hotels.create') }}" class="more">@lang('admin.add_hotel')</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="search_list"></div>
                    @admin
                    <div class="count">
                        @lang('admin.count_hotels'): {{ $chotel }}
                    </div>
                    @endadmin
                    <div class="table-wrap">
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>@lang('admin.title')</th>
                                <th>@lang('admin.address')</th>
                                @hasrole('Super Admin')
                                    <th>apiType</th>
                                @endhasrole
                                <th>@lang('admin.status')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            @foreach($hotels as $hotel)
                                <tr>
                                    <td>{{ $hotel->id }}</td>
                                    <td>{{ $hotel->__('title') }}</td>
                                    <td>{{ $hotel->__('address') ?? $hotel->address_en }}</td>
                                    @hasrole('Super Admin')
                                        <td>{{ $hotel->apiName }}</td>
                                    @endhasrole
                                    <td>
                                        @if($hotel->status === 1)
                                            <div class="alert alert-success">@lang('admin.active')</div>
                                        @else
                                            <div class="alert alert-danger">@lang('admin.disable')</div>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('hotels.destroy', $hotel) }}" method="post">
                                            <ul>
                                                @can('edit-contact')
                                                    <a href="{{ route('hotels.show', $hotel) }}" class="select-hotel"
                                                       data-hotel="{{ $hotel->id }}"><img
                                                                src="{{ route('index') }}/img/icons/eye.svg"
                                                                class="view" style="max-width: 25px"></a>
                                                @else
                                                    @if($hotel->status === 1)
                                                        <a href="{{ route('hotels.show', $hotel) }}"
                                                           class="select-hotel" data-hotel="{{ $hotel->id }}"><img
                                                                    src="{{ route('index') }}/img/icons/eye.svg"
                                                                    class="view" style="max-width: 25px"></a>
                                                    @endif
                                                @endif
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Do you want to delete this?');"><img
                                                            src="{{ route('index') }}/img/icons/trash.svg" alt="">
                                                </button>
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
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
            });
        });
    </script>

@endsection
