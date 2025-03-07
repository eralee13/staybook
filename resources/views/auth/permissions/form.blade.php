@extends('auth.layouts.master')

@isset($permission)
    @section('title', 'Edit ' . $permission->title)
@else
    @section('title', 'Add permission')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    @isset($permission)
                        <h1>@lang('admin.edit') {{ $permission->title }}</h1>
                    @else
                        <h1>@lang('admin.add')</h1>
                    @endisset
                    <form method="post" enctype="multipart/form-data"
                          @isset($permission)
                              action="{{ route('permissions.update', $permission) }}"
                          @else
                              action="{{ route('permissions.store') }}"
                            @endisset
                    >
                        @isset($permission)
                            @method('PUT')
                        @endisset
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="name" value="{{ old('name', isset($permission) ?
                                    $permission->name : null) }}">
                                    <input type="hidden" name="guard_name" value="web">
                                </div>
                            </div>
                        </div>
                        @csrf
                        <button class="more">@lang('admin.send')</button>
                        <a href="{{url()->previous()}}" class="btn delete cancel">@lang('admin.cancel')</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .admin label {
            display: inline-block;
        }
    </style>

@endsection
