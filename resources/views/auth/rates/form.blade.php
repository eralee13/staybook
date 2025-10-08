@extends('auth.layouts.master')

@isset($rate)
    @section('title', __('admin.edit') . ' ' . $rate->title)
@else
    @section('title', __('admin.add'))
@endisset

@section('content')

    <div class="page admin">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <div class="row room_list_btn" style="margin-bottom: 20px">
                        <div class="col-md-4">
                            <a href="{{ route('rates.index') }}" @routeactive('rate*')>@lang('admin.plans')</a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('cancellations.index') }}" @routeactive('cancel*')>@lang('admin.cancel_fines')</a>
                        </div>
                    </div>
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
                                    <label for="">@lang('admin.category_room')</label>
                                    <div class="custom-select">
                                        <select name="room_id">
                                            @isset($rate)
                                                <option value="{{ $rate->room_id }}">{{ $rate->room->__('title') }}</option>
                                            @else
                                                <option value="">@lang('admin.choose')</option>
                                                @foreach($rooms as $room)
                                                    <option value="{{ $room->id }}">{{ $room->__('title') }}</option>
                                                @endforeach
                                            @endisset
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'meal_id'])
                                <div class="form-group">
                                    <label for="">@lang('admin.food')</label>
                                    <div class="custom-select">
                                        <select name="meal_id" id="">
                                            @isset($rate->meal_id)
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
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'bed_type'])
                                    <label for="bed">@lang('admin.bed')</label>
                                    <div class="custom-select">
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
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'availability'])
                                    <label for="">@lang('admin.availability')</label>
                                    <input type="number" name="availability" value="{{ old('availability', isset($rate) ?
                                $rate->availability : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'price'])
                                    @php
                                        $hot = \App\Models\Hotel::where('id', $hotel)->first();
                                    @endphp
                                    <label for="">@lang('admin.price_for') 1 @lang('main.adult') ({{ $hot->currency }})</label>
                                    <input type="number" name="price" value="{{ old('price', isset($rate) ?
                                $rate->price : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'price2'])
                                    <label for="">@lang('admin.price_for') 2 @lang('main.adult') ({{ $hot->currency }})</label>
                                    <input type="number" name="price2" value="{{ old('price2', isset($rate) ?
                                $rate->price2 : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'price3'])
                                    <label for="">@lang('admin.price_for') 3 @lang('main.adult') ({{ $hot->currency }})</label>
                                    <input type="number" name="price3" value="{{ old('price3', isset($rate) ?
                                $rate->price3 : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'price4'])
                                    <label for="">@lang('admin.price_for') 4 @lang('main.adult') ({{ $hot->currency }})</label>
                                    <input type="number" name="price4" value="{{ old('price4', isset($rate) ?
                                $rate->price4 : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'adult'])
                                    <label for="">@lang('admin.count_adult')</label>
                                    <input type="number" name="adult" value="{{ old('adult', isset($rate) ?
                                $rate->adult : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'child'])
                                    <label for="">@lang('admin.count_child')</label>
                                    <input type="number" name="child" value="{{ old('child', isset($rate) ?
                                $rate->child : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="children_allowed" class="option">
                                    @include('auth.layouts.error', ['fieldname' => 'child'])
                                    @isset($rate)
                                        <input type="checkbox" name="children_allowed" value="1"
                                               {{ $rate->children_allowed ? 'checked' : '' }} id="children_allowed" >
                                    @else
                                        <input type="checkbox" name="children_allowed" value="1" id="children_allowed">
                                    @endisset
                                    <span class="box" aria-hidden="true">
                                                            <svg viewBox="0 0 24 24" role="presentation"
                                                                 focusable="false">
                                                              <path d="M9.2 17.6c-.4 0-.8-.2-1.1-.5l-3.9-4a1.6 1.6 0 1 1 2.2-2.2l2.8 2.9 6.6-7a1.6 1.6 0 1 1 2.4 2.1l-7.7 8.2c-.3.3-.7.5-1.3.5z"/>
                                                            </svg>
                                                          </span>
                                    <span class="name">@lang('admin.child_possible')</span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'free_child_age'])
                                    <label for="">@lang('admin.child_age_free')</label>
                                    <input type="number" name="free_children_age" value="{{ old('free_children_age', isset($rate) ?
                                $rate->free_children_age : null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'child_extra_fee'])
                                    <label for="">@lang('admin.child_pay')</label>
                                    <input type="number" name="child_extra_fee" value="{{ old('child_extra_fee', isset($rate) ?
                                $rate->child_extra_fee : null) }}">
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @if(app()->getLocale() == 'ru')
                                    <h4>Ограничения бронирования</h4>
                                    <label for="">Вы можете открыть или закрыть продажи тарифа в определённое время.
                                        Отсчёт
                                        идёт от 00:00 (начала суток) предполагаемого дня заезда.
                                    </label>
                                @else
                                    <h4>Booking restrictions</h4>
                                    <label for="">You can open or close sales of the tariff at a certain time. The
                                        countdown starts from 00:00 (beginning of the day) of the expected day of
                                        arrival.
                                    </label>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'open_time'])
                                        <label for="">@lang('admin.book_open')</label>
                                        <input type="text" name="open_time">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'close_time'])
                                        <label for="">@lang('admin.book_close')</label>
                                        <input type="text" name="close_time">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <button class="more">@lang('admin.send')</button>
                            </div>
                            <div class="col-md-6">
                                <a href="{{url()->previous()}}" class="btn delete cancel">@lang('admin.cancel')</a>
                            </div>
                        </div>
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
