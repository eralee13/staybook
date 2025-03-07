@extends('auth.layouts.master')

@section('title', __('admin.childs'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @include('auth.layouts.subroom')
                    <div class="row align-items-center aic">
                        <div class="col-md-7">
                            <h1>@lang('admin.childs')</h1>
                        </div>
                        <div class="col-md-5">
                            <div class="btn-wrap">
                                <a class="btn add" href="{{ route('childs.create') }}"><i class="fa-solid
                                fa-plus"></i> @lang('admin.add')</a>
                            </div>
                        </div>
                    </div>

                    @if($childs->isNotEmpty())
                        <table class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.title')</th>
                                <th>@lang('admin.child_type')</th>
                                <th>@lang('admin.child_count')</th>
                                <th>@lang('admin.price')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($childs as $child)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $child->room->__('title') }}</td>
                                    <td>
                                        <ul class="list">
                                            <li>Без предоставления места</li>
                                            <li>На дополнительном месте</li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul class="list">
                                            <li>{{ $child->without_place }}</li>
                                            <li>{{ $child->extra_place }}</li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul class="list">
                                            <li>
                                                @if($child->price_without == null)
                                                    $ 0
                                                @else
                                                    {{ $child->price_without }}
                                                @endif
                                            </li>
                                            <li>
                                                @if($child->price_extra == null)
                                                    $ 0
                                                @else
                                                    $ {{ $child->price_extra }}
                                                @endif
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <form action="{{ route('childs.destroy', $child) }}" method="post">
                                            <ul>
                                                <li><a class="btn edit" href="{{ route('childs.edit', $child)
                                            }}"><i class="fa-regular fa-pen-to-square"></i></a></li>
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Do you want to delete this?');"
                                                        class="btn delete"><i class="fa-regular fa-trash"></i></button>
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $childs->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.childs_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

<style>
    .admin table ul.list li{
        display: block;
    }
</style>
