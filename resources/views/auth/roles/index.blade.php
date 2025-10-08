@extends('auth.layouts.master')

@section('title', __('admin.roles'))

@section('content')

<div class="page admin">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                @include('auth.layouts.sidebar')
            </div>
            <div class="col-md-9">
                <div class="row aic">
                    <div class="col-md-9">
                        <h1>@lang('admin.roles')</h1>
                    </div>
                    <div class="col-md-3">
                        @can('create-role')
                        <a class="btn add" href="{{ route('roles.create') }}" style="display: block; text-align: center">@lang('admin.add')</a>
                        @endcan
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">@lang('admin.name')</th>
                            <th scope="col" style="width: 250px;">@lang('admin.action')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $role->name }}</td>
                                <td>
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="post">
                                        <ul>
                                            <li><a href="{{ route('roles.show', $role->id) }}"><img src="{{ route('index') }}/img/icons/eye.svg" alt=""></a></li>
                                            @csrf
                                            @method('DELETE')
                                            @if ($role->name!='Super Admin')
                                                @can('edit-role')
                                                    <li><a href="{{ route('roles.edit', $role->id) }}"><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></a></li>
                                                @endcan
                                                @can('delete-role')
                                                    @if ($role->name!=Auth::user()->hasRole($role->name))
                                                        <button type="submit" onclick="return confirm('Do ' +
                                                 'you want to delete this role?');"><img src="{{ route('index') }}/img/icons/trash.svg" alt=""></button>
                                                    @endif
                                                @endcan
                                            @endif
                                        </ul>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <td colspan="3">
                        <span class="text-danger">
                            <strong>No Role Found!</strong>
                        </span>
                            </td>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $roles->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection