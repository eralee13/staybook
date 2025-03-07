@extends('auth.layouts.master')

@isset($child)
    @section('title', __('admin.edit'))
@else
    @section('title', __('admin.add'))
@endisset

@section('content')


    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    @include('auth.layouts.subroom')
                    @isset($child)
                        <h1>@lang('admin.edit') {{ $child->title }}</h1>
                    @else
                        <h1>@lang('admin.add')</h1>
                    @endisset

                    <form method="post"
                          @isset($child)
                              action="{{ route('childs.update', $child) }}"
                          @else
                              action="{{ route('childs.store') }}"
                            @endisset
                    >
                        @isset($child)
                            @method('PUT')
                        @endisset
                        <input type="hidden" value="{{ $hotel }}" name="hotel_id">
                            <input type="hidden" value="1" name="status">
                        <div class="row">
                            <div class="col-md-12">
                                @include('auth.layouts.error', ['fieldname' => 'room_id'])
                                <div class="form-group">
                                    <label for="">@lang('admin.room')</label>
                                    <select name="room_id">
                                        @isset($child)
                                            <option value="{{ $child->room_id }}" @if($child->room_id)
                                                selected>
                                                {{ $child->room->title }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->__('title') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
{{--                            age1--}}
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Возраст</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    @include('auth.layouts.error', ['fieldname' => 'age_from'])
                                                    <select name="age_from" id="">
                                                        @isset($child)
                                                            <option value="{{ $child->age_from }}" @if($child->age_from)
                                                                selected>
                                                                {{ $child->age_from }}</option>
                                                        @endif
                                                        @endisset
                                                        <option value="0">@lang('admin.from') 0</option>
                                                        <option value="1">@lang('admin.from') 1</option>
                                                        <option value="2">@lang('admin.from') 2</option>
                                                        <option value="3">@lang('admin.from') 3</option>
                                                        <option value="4">@lang('admin.from') 4</option>
                                                        <option value="5">@lang('admin.from') 5</option>
                                                        <option value="6">@lang('admin.from') 6</option>
                                                        <option value="7">@lang('admin.from') 7</option>
                                                        <option value="8">@lang('admin.from') 8</option>
                                                        <option value="9">@lang('admin.from') 9</option>
                                                        <option value="10">@lang('admin.from') 10</option>
                                                        <option value="11">@lang('admin.from') 11</option>
                                                        <option value="12">@lang('admin.from') 12</option>
                                                        <option value="13">@lang('admin.from') 13</option>
                                                        <option value="14">@lang('admin.from') 14</option>
                                                        <option value="15">@lang('admin.from') 15</option>
                                                        <option value="16">@lang('admin.from') 16</option>
                                                        <option value="17">@lang('admin.from') 17</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    @include('auth.layouts.error', ['fieldname' => 'age_to'])
                                                    <select name="age_to" id="">
                                                        @isset($child)
                                                            <option value="{{ $child->age_to }}" @if($child->age_to)
                                                                selected>
                                                                {{ $child->age_to }}</option>
                                                        @endif
                                                        @endisset
                                                        <option value="1">@lang('admin.to') 1</option>
                                                        <option value="2">@lang('admin.to') 2</option>
                                                        <option value="3">@lang('admin.to') 3</option>
                                                        <option value="4">@lang('admin.to') 4</option>
                                                        <option value="5">@lang('admin.to') 5</option>
                                                        <option value="6">@lang('admin.to') 6</option>
                                                        <option value="7">@lang('admin.to') 7</option>
                                                        <option value="8">@lang('admin.to') 8</option>
                                                        <option value="9">@lang('admin.to') 9</option>
                                                        <option value="10">@lang('admin.to') 10</option>
                                                        <option value="11">@lang('admin.to') 11</option>
                                                        <option value="12">@lang('admin.to') 12</option>
                                                        <option value="13">@lang('admin.to') 13</option>
                                                        <option value="14">@lang('admin.to') 14</option>
                                                        <option value="15">@lang('admin.to') 15</option>
                                                        <option value="16">@lang('admin.to') 16</option>
                                                        <option value="17">@lang('admin.to') 17</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'without_place'])
                                        <div class="form-group">
                                            <label for="">Без предоставления места</label>
                                            <select name="without_place" id="">
                                                @isset($child)
                                                    <option @if($child->without_place)
                                                                selected>
                                                        {{ $child->without_place }}</option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endif
                                                @endisset
                                                <option value="1">1 @lang('admin.child')</option>
                                                <option value="2">2 @lang('admin.child')</option>
                                                <option value="3">3 @lang('admin.child')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'price_without'])
                                        <div class="form-group">
                                            <label for="">Стоимость</label>
                                            <input type="number" name="price_without" placeholder="0" value="{{ old('price_without', isset
                                    ($child) ? $child->price_without : null) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4"></div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'extra_place'])
                                        <div class="form-group">
                                            <label for="">На дополнительном месте</label>
                                            <select name="extra_place" id="">
                                                @isset($child)
                                                    <option value="{{ $child->extra_place }}" @if($child->extra_place)
                                                        selected>
                                                        {{ $child->extra_place }}</option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endif
                                                @endisset
                                                <option value="1">1 @lang('admin.child')</option>
                                                <option value="2">2 @lang('admin.child')</option>
                                                <option value="3">3 @lang('admin.child')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'price_extra'])
                                        <div class="form-group">
                                            <label for="">Стоимость</label>
                                            <input type="number" name="price_extra" placeholder="0" value="{{ old
                                    ('price_extra', isset
                                    ($child) ? $child->price_extra : null) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{--                            age2--}}
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Возраст</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    @include('auth.layouts.error', ['fieldname' => 'age_from2'])
                                                    <select name="age_from2" id="">
                                                        @isset($child)
                                                            <option value="{{ $child->age_from2 }}" @if ($child->age_from2)
                                                                selected>{{ $child->age_from2 }}</option>
                                                        @endif
                                                        @endisset
                                                        <option value="0">@lang('admin.from') 0</option>
                                                        <option value="1">@lang('admin.from') 1</option>
                                                        <option value="2">@lang('admin.from') 2</option>
                                                        <option value="3">@lang('admin.from') 3</option>
                                                        <option value="4">@lang('admin.from') 4</option>
                                                        <option value="5">@lang('admin.from') 5</option>
                                                        <option value="6">@lang('admin.from') 6</option>
                                                        <option value="7">@lang('admin.from') 7</option>
                                                        <option value="8">@lang('admin.from') 8</option>
                                                        <option value="9">@lang('admin.from') 9</option>
                                                        <option value="10">@lang('admin.from') 10</option>
                                                        <option value="11">@lang('admin.from') 11</option>
                                                        <option value="12">@lang('admin.from') 12</option>
                                                        <option value="13">@lang('admin.from') 13</option>
                                                        <option value="14">@lang('admin.from') 14</option>
                                                        <option value="15">@lang('admin.from') 15</option>
                                                        <option value="16">@lang('admin.from') 16</option>
                                                        <option value="17">@lang('admin.from') 17</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    @include('auth.layouts.error', ['fieldname' => 'age_to2'])
                                                    <select name="age_to2" id="">
                                                        @isset($child)
                                                            <option value="{{ $child->age_to2 }}" @if($child->age_to2)
                                                                selected>
                                                                {{ $child->age_to2 }}</option>
                                                        @endif
                                                        @endisset
                                                        <option value="1">@lang('admin.to') 1</option>
                                                        <option value="2">@lang('admin.to') 2</option>
                                                        <option value="3">@lang('admin.to') 3</option>
                                                        <option value="4">@lang('admin.to') 4</option>
                                                        <option value="5">@lang('admin.to') 5</option>
                                                        <option value="6">@lang('admin.to') 6</option>
                                                        <option value="7">@lang('admin.to') 7</option>
                                                        <option value="8">@lang('admin.to') 8</option>
                                                        <option value="9">@lang('admin.to') 9</option>
                                                        <option value="10">@lang('admin.to') 10</option>
                                                        <option value="11">@lang('admin.to') 11</option>
                                                        <option value="12">@lang('admin.to') 12</option>
                                                        <option value="13">@lang('admin.to') 13</option>
                                                        <option value="14">@lang('admin.to') 14</option>
                                                        <option value="15">@lang('admin.to') 15</option>
                                                        <option value="16">@lang('admin.to') 16</option>
                                                        <option value="17">@lang('admin.to') 17</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'without_place2'])
                                        <div class="form-group">
                                            <label for="">Без предоставления места</label>
                                            <select name="without_place2" id="">
                                                @isset($child)
                                                    <option @if($child->without_place2)
                                                                selected>
                                                        {{ $child->without_place2 }}</option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endif
                                                @endisset
                                                <option value="1">1 @lang('admin.child')</option>
                                                <option value="2">2 @lang('admin.child')</option>
                                                <option value="3">3 @lang('admin.child')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'price_without2'])
                                        <div class="form-group">
                                            <label for="">Стоимость</label>
                                            <input type="number" name="price_without2" placeholder="0" value="{{ old
                                            ('price_without2', isset
                                    ($child) ? $child->price_without2 : null) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4"></div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'extra_place2'])
                                        <div class="form-group">
                                            <label for="">На дополнительном месте</label>
                                            <select name="extra_place2" id="">
                                                @isset($child)
                                                    <option value="{{ $child->extra_place2 }}" @if($child->extra_place)
                                                        selected>
                                                        {{ $child->extra_place2 }}</option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endif
                                                @endisset
                                                <option value="1">1 @lang('admin.child')</option>
                                                <option value="2">2 @lang('admin.child')</option>
                                                <option value="3">3 @lang('admin.child')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'price_extra2'])
                                        <div class="form-group">
                                            <label for="">Стоимость</label>
                                            <input type="number" name="price_extra2" placeholder="0" value="{{ old
                                    ('price_extra2', isset
                                    ($child) ? $child->price_extra2 : null) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{--                            age3--}}
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Возраст</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    @include('auth.layouts.error', ['fieldname' => 'age_from3'])
                                                    <select name="age_from3" id="">
                                                        @isset($child)
                                                            <option value="{{ $child->age_from3 }}" @if($child->age_from3)
                                                                selected>{{ $child->age_from3 }}</option>
                                                        @endif
                                                        @endisset
                                                        <option value="0">@lang('admin.from') 0</option>
                                                        <option value="1">@lang('admin.from') 1</option>
                                                        <option value="2">@lang('admin.from') 2</option>
                                                        <option value="3">@lang('admin.from') 3</option>
                                                        <option value="4">@lang('admin.from') 4</option>
                                                        <option value="5">@lang('admin.from') 5</option>
                                                        <option value="6">@lang('admin.from') 6</option>
                                                        <option value="7">@lang('admin.from') 7</option>
                                                        <option value="8">@lang('admin.from') 8</option>
                                                        <option value="9">@lang('admin.from') 9</option>
                                                        <option value="10">@lang('admin.from') 10</option>
                                                        <option value="11">@lang('admin.from') 11</option>
                                                        <option value="12">@lang('admin.from') 12</option>
                                                        <option value="13">@lang('admin.from') 13</option>
                                                        <option value="14">@lang('admin.from') 14</option>
                                                        <option value="15">@lang('admin.from') 15</option>
                                                        <option value="16">@lang('admin.from') 16</option>
                                                        <option value="17">@lang('admin.from') 17</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    @include('auth.layouts.error', ['fieldname' => 'age_to3'])
                                                    <select name="age_to3" id="">
                                                        @isset($child)
                                                            <option value="{{ $child->age_to }}" @if($child->age_to3)
                                                                selected>
                                                                {{ $child->age_to3 }}</option>
                                                        @endif
                                                        @endisset
                                                        <option value="1">@lang('admin.to') 1</option>
                                                        <option value="2">@lang('admin.to') 2</option>
                                                        <option value="3">@lang('admin.to') 3</option>
                                                        <option value="4">@lang('admin.to') 4</option>
                                                        <option value="5">@lang('admin.to') 5</option>
                                                        <option value="6">@lang('admin.to') 6</option>
                                                        <option value="7">@lang('admin.to') 7</option>
                                                        <option value="8">@lang('admin.to') 8</option>
                                                        <option value="9">@lang('admin.to') 9</option>
                                                        <option value="10">@lang('admin.to') 10</option>
                                                        <option value="11">@lang('admin.to') 11</option>
                                                        <option value="12">@lang('admin.to') 12</option>
                                                        <option value="13">@lang('admin.to') 13</option>
                                                        <option value="14">@lang('admin.to') 14</option>
                                                        <option value="15">@lang('admin.to') 15</option>
                                                        <option value="16">@lang('admin.to') 16</option>
                                                        <option value="17">@lang('admin.to') 17</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'without_place3'])
                                        <div class="form-group">
                                            <label for="">Без предоставления места</label>
                                            <select name="without_place3" id="">
                                                @isset($child)
                                                    <option @if($child->without_place3)
                                                                selected>
                                                        {{ $child->without_place3 }}</option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endif
                                                @endisset
                                                <option value="1">1 @lang('admin.child')</option>
                                                <option value="2">2 @lang('admin.child')</option>
                                                <option value="3">3 @lang('admin.child')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'price_without'])
                                        <div class="form-group">
                                            <label for="">Стоимость</label>
                                            <input type="number" name="price_without3" placeholder="0" value="{{ old
                                            ('price_without3', isset
                                    ($child) ? $child->price_without3 : null) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4"></div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'extra_place'])
                                        <div class="form-group">
                                            <label for="">На дополнительном месте</label>
                                            <select name="extra_place3" id="">
                                                @isset($child)
                                                    <option value="{{ $child->extra_place3 }}" @if($child->extra_place3)
                                                        selected>
                                                        {{ $child->extra_place3 }}</option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endif
                                                @endisset
                                                <option value="1">1 @lang('admin.child')</option>
                                                <option value="2">2 @lang('admin.child')</option>
                                                <option value="3">3 @lang('admin.child')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @include('auth.layouts.error', ['fieldname' => 'price_extra'])
                                        <div class="form-group">
                                            <label for="">Стоимость</label>
                                            <input type="number" name="price_extra3" placeholder="0" value="{{ old
                                    ('price_extra3', isset
                                    ($child) ? $child->price_extra3 : null) }}">
                                        </div>
                                    </div>
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
