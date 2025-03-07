@extends('auth.layouts.master')

@section('title', __('admin.roles'))

@section('content')

<div class="page admin">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                @include('auth.layouts.sidebar')
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-7">
                        <h1>@lang('admin.roles')</h1>
                    </div>
                    <div class="col-md-5">
                        @can('create-role')
                        <a class="btn add" href="{{ route('roles.create') }}"><i class="fa-solid fa-plus"></i>
                            @lang('admin.add')</a>
                        @endcan
                    </div>
                </div>
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
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td>{{ $role->name }}</td>
                            <td>
                                <form action="{{ route('roles.destroy', $role->id) }}" method="post">
                                    <ul>
                                        <li><a href="{{ route('roles.show', $role->id) }}" class="btn view"><i class="fa-regular fa-eye"></i></a></li>
                                        @csrf
                                        @method('DELETE')
                                        @if ($role->name!='Super Admin')
                                            @can('edit-role')
                                                <li><a href="{{ route('roles.edit', $role->id) }}" class="btn edit"><i class="fa-regular fa-pen-to-square"></i></a></li>
                                            @endcan
                                            @can('delete-role')
                                                @if ($role->name!=Auth::user()->hasRole($role->name))
                                                    <button type="submit" class="btn delete" onclick="return confirm('Do ' +
                                                 'you want to delete this role?');"><i class="fa-regular
                                                 fa-trash"></i> </button>
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

                {{ $roles->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection