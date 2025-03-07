@extends('auth.layouts.master')

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @isset($role)
                        <h1>@lang('admin.edit') {{ $role->name }}</h1>
                    @else
                        <h1>@lang('admin.add')</h1>
                    @endisset
                        <form method="post" enctype="multipart/form-data"
                              @isset($role)
                                  action="{{ route('roles.update', $role) }}"
                              @else
                                  action="{{ route('roles.store') }}"
                                @endisset
                        >
                            @csrf
                            @isset($role)
                                @method('PUT')
                            @endisset
                            @include('auth.layouts.error', ['fieldname' => 'name'])
                            <div class="form-group">
                                <label for="">@lang('admin.title')</label>
                                <input type="text" name="name" value="{{ old('name', isset($role) ? $role->name :
                                null) }}">
                            </div>

                            <div class="form-group">
                                <label for="">@lang('admin.permissions')</label>
                                <select multiple aria-label="Permissions" id="permissions" name="permissions[]"
                                        style="height: 210px;">
                                    @forelse ($permissions as $permission)
                                        <option value="{{ $permission->id }}" {{ in_array($permission->id, $rolePermissions ?? []) ? 'selected' : '' }}>
                                            {{ $permission->name }}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                            <button class="more">@lang('admin.send')</button>
                            <a href="{{url()->previous()}}" class="btn delete cancel">@lang('admin.cancel')</a>
                        </form>
                </div>
            </div>
        </div>
    </div>

    
@endsection