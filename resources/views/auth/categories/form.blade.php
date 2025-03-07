@extends('auth.layouts.master')

@isset($category)
    @section('title', __('admin.edit') . ' ' . $category->title)
@else
    @section('title', __('admin.add'))
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @include('auth.layouts.subroom')
                    @isset($category)
                        <h1>@lang('admin.edit') {{ $category->title }}</h1>
                    @else
                        <h1>@lang('admin.add')</h1>
                    @endisset
                    <form method="post"
                          @isset($category)
                              action="{{ route('categories.update', $category) }}"
                          @else
                              action="{{ route('categories.store') }}"
                            @endisset
                    >
                        @isset($category)
                            @method('PUT')
                        @endisset
                        <input type="hidden" value="{{ $hotel }}" name="hotel_id">
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="title" value="{{ old('title', isset($category) ?
                                    $category->title :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title_en'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($category) ?
                                $category->title_en :
                             null) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'room_id'])
                                <div class="form-group">
                                    <label for="">Какие номера можно забронировать по этому тарифу?
                                    </label>
                                    <select name="room_id[]" multiple style="height: auto">
                                        @isset($categories)
                                            @foreach($categories as $cat)
                                                @php
                                                    $room = \App\Models\Room::where('id', $cat)->first();
                                                @endphp
                                                    <option value="{{ $room->id }}" selected>{{ $room->__('title') }}</option>
                                            @endforeach
                                        @endisset
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->__('title') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'food_id'])
                                <div class="form-group">
                                    <label for="">@lang('admin.food')</label>
                                    <select name="food_id" id="">
                                        @isset($category)
                                            <option value="{{ $category->food_id }}" @if($category->food_id)
                                                        selected>
                                                {{ $category->food->__('title') }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        @foreach($foods as $food)
                                            <option value="{{ $food->id }}">{{ $food->__('title') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'rule_id'])
                                <div class="form-group">
                                    <label for="">@lang('admin.rule')</label>
                                    <select name="rule_id" id="">
                                        @isset($category->rule_id)
                                            <option value="{{ $select_rule->id }}">{{ $select_rule->__('title')
                                            }}</option>
                                        @endisset
                                        @foreach($rules as $rule)
                                            <option value="{{ $rule->id }}">{{ $rule->__('title') }}</option>
                                        @endforeach
                                    </select>
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
