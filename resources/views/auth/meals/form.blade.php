@extends('auth.layouts.master')

@isset($meal)
    @section('title', __('admin.edit') . ' ' . $meal->title)
@else
    @section('title', __('admin.add'))
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    @include('auth.layouts.subroom')
                    @isset($meal)
                        <h1>@lang('admin.edit') {{ $meal->title }}</h1>
                    @else
                        <h1>@lang('admin.add')</h1>
                    @endisset
                    <form method="post"
                          @isset($meal)
                              action="{{ route('meals.update', $meal) }}"
                          @else
                              action="{{ route('meals.store') }}"
                        @endisset
                    >
                        @isset($meal)
                            @method('PUT')
                        @endisset
                        <input type="hidden" value="{{ $hotel }}" name="hotel_id">
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="title" value="{{ old('title', isset($meal) ?
                                    $meal->title :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title_en'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($meal) ?
                                $meal->title_en : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'price'])
                                <div class="form-group">
                                    <label for="">@lang('admin.price') $</label>
                                    <input type="number" name="price" value="{{ old('price', isset($meal) ?
                                $meal->price : null) }}">
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
