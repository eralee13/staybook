@extends('auth.layouts.master')

@section('title', __('admin.permissions'))

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
                            <h1>@lang('admin.permissions')</h1>
                        </div>
                        <div class="col-md-5">
                            <div class="btn-wrap">
                                <a class="btn add" href="{{ route('permissions.create') }}"><i class="fa-solid
                                fa-plus"></i> @lang('admin.add')</a>
                            </div>
                        </div>
                    </div>
                    @if($permissions->isNotEmpty())
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('admin.title')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($permissions as $permission)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $permission->name }}</td>
                                <td>
                                    <form action="{{ route('permissions.destroy', $permission) }}" method="post">
                                        <ul>
                                            <li><a href="{{ route('permissions.edit', $permission)
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
                    {{ $permissions->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.permissions_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
