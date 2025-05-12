@extends('auth.layouts.master')

@isset($rate)
    @section('title', __('admin.edit') . ' ' . $rate->title)
@else
    @section('title', __('admin.add'))
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-2">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-10">
                    @include('auth.layouts.subroom')
                    @isset($rate)
                        <h1>@lang('admin.edit') {{ $rate->title }}</h1>
                    @else
                        <h1>@lang('admin.add')</h1>
                    @endisset
                    <form method="post"
                          @isset($rate)
                              action="{{ route('rates.update', $rate) }}"
                          @else
                              action="{{ route('rates.store') }}"
                            @endisset
                    >
                        @isset($rate)
                            @method('PUT')
                        @endisset
                        <input type="hidden" value="{{ $hotel }}" name="hotel_id">
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="title" value="{{ old('title', isset($rate) ?
                                    $rate->title : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title_en'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($rate) ?
                                $rate->title_en : null) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'room_id'])
                                <div class="form-group">
                                    <label for="">Категория номера</label>
                                    <select name="room_id">
                                        @isset($rate)
                                            <option value="{{ $rate->room_id }}"
                                                    selected>{{ $rate->room->title }}</option>
                                        @else
                                            <option value="">@lang('admin.choose')</option>
                                        @endisset
                                        @foreach($rooms as $room)
                                            @isset($rate)
                                                @if($rate->room_id != $room->id)
                                                    <option value="{{ $room->id }}">{{ $room->title }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $room->id }}">{{ $room->title }}</option>
                                            @endisset
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'meal_id'])
                                <div class="form-group">
                                    <label for="">@lang('admin.food')</label>
                                    <select name="meal_id" id="">
                                        @isset($rate)
                                            <option value="{{ $rate->meal_id }}" selected>
                                                {{ $rate->meal->code }}</option>
                                        @else
                                            <option value="">@lang('admin.choose')</option>
                                        @endisset
                                        @foreach($meals as $meal)
                                            @isset($rate)
                                                @if($rate->meal_id != $meal->id)
                                                    <option value="{{ $meal->id }}">{{ $meal->code }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $meal->id }}">{{ $meal->code }}</option>
                                            @endisset
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'bed_type'])
                                    <label for="bed">@lang('admin.bed')</label>
                                    <select name="bed_type" id="bed">
                                        @isset($rate)
                                            <option value="{{ $rate->bed_type }}" selected>
                                                {{ $rate->bed_type }}</option>
                                        @else
                                            <option value="">@lang('admin.choose')</option>
                                        @endisset
                                        <option value="Single">Single</option>
                                        <option value="Double">Double</option>
                                        <option value="Twin">Twin</option>
                                        <option value="Triple">Triple</option>
                                        <option value="Quadruple">Quadruple</option>
                                        <option value="King Size">King Size</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'availability'])
                                    <label for="">Доступно</label>
                                    <input type="number" name="availability" value="{{ old('availability', isset($rate) ?
                                $rate->availability : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'price'])
                                    <label for="">Стоимость за 1 взрослого</label>
                                    <input type="number" name="price" value="{{ old('price', isset($rate) ?
                                $rate->price : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'price2'])
                                    <label for="">Стоимость за 2 взрослого</label>
                                    <input type="number" name="price2" value="{{ old('price2', isset($rate) ?
                                $rate->price2 : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'adult'])
                                    <label for="">Кол-во взрослых</label>
                                    <input type="number" name="adult" value="{{ old('adult', isset($rate) ?
                                $rate->adult : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'child'])
                                    <label for="">Кол-во детей</label>
                                    <input type="number" name="child" value="{{ old('child', isset($rate) ?
                                $rate->child : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="hidden" name="children_allowed" value="0">
                                    @include('auth.layouts.error', ['fieldname' => 'child'])
                                    @isset($rate)
                                        <input type="checkbox" name="children_allowed" value="1"
                                               {{ $rate->children_allowed ? 'checked' : '' }} id="children_allowed">
                                    @else
                                        <input type="checkbox" name="children_allowed" value="1" id="children_allowed">
                                    @endisset
                                    <label for="children_allowed">Можно ли заселять с детьми</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'free_child_age'])
                                    <label for="">Возраст ребёнка, при котором бесплатное проживание*</label>
                                    <input type="number" name="free_children_age" value="{{ old('free_children_age', isset($rate) ?
                                $rate->free_children_age : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'child_extra_fee'])
                                    <label for="">Стоимость доплаты за ребенка</label>
                                    <input type="number" name="child_extra_fee" value="{{ old('child_extra_fee', isset($rate) ?
                                $rate->child_extra_fee : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'child_extra_fee'])
                                    <label for="">Политика отмены</label>
                                    <select name="cancellation_rule_id">
                                        @isset($rate)
                                            <option value="{{ $rate->cancellation_rule_id }}"
                                                    selected>{{ $rate->cancellationRule->title }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endisset
                                        @foreach($cancellations as $cancel)
                                            @isset($rate)
                                                @if($rate->cancellation_rule_id != $cancel->id)
                                                    <option value="{{ $cancel->id }}">{{ $cancel->title }}</option>
                                                @endif
                                            @else
                                                <option value="{{ $cancel->id }}">{{ $cancel->title }}</option>
                                            @endisset
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
