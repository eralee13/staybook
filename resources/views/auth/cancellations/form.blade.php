@extends('auth.layouts.master')

@isset($cancellation)
    @section('title', __('admin.edit') . ' ' . $cancellation->title)
@else
    @section('title', __('admin.add'))
@endisset

@section('content')

    <style>
        .admin label {
            display: inline-block;
        }
    </style>

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-2">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-10">
                    @include('auth.layouts.subroom')
                    @isset($cancellation)
                        <h1>@lang('admin.edit') {{ $cancellation->title }}</h1>
                    @else
                        <h1>@lang('admin.add')</h1>
                    @endisset
                    <form method="post"
                          @isset($cancellation)
                              action="{{ route('cancellations.update', $cancellation) }}"
                          @else
                              action="{{ route('cancellations.store') }}"
                            @endisset
                    >
                        @isset($cancellation)
                            @method('PUT')
                        @endisset
                        <input type="hidden" value="{{ $hotel_id }}" name="hotel_id">
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title'])
                                <div class="form-group">
                                    <label for="">Название правила</label>
                                    <input type="text" name="title" value="{{ old('title', isset($cancellation) ?
                                    $cancellation->title :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="hidden" name="is_refundable" value="0">
                                    @isset($cancellation)
                                        <input type="checkbox" name="is_refundable" value="1"
                                               {{ $cancellation->is_refundable ? 'checked' : '' }} id="is_refundable">
                                    @else
                                        <input type="checkbox" name="is_refundable" value="1" id="is_refundable">
                                    @endisset
                                    <label for="is_refundable">Разрешить отмену</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => '>free_cancellation_days'])
                                <div class="form-group">
                                    <label for="">Количество дней до заезда для бесплатной отмены</label>
                                    <input type="number" name="free_cancellation_days" value="{{ old('free_cancellation_days', isset($cancellation) ?
                                    $cancellation->free_cancellation_days :
                             null) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'penalty_type'])
                                    <label for="">Тип штрафа</label>
                                    <select name="penalty_type" id="">
                                        @isset($cancellation)
                                            <option @if($cancellation->penalty_type)
                                                        selected>
                                                {{ $cancellation->penalty_type }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        <option value="fixed">Фиксированная сумма</option>
                                        <option value="percent">Процент от стоимости</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'penalty_amount'])
                                <div class="form-group">
                                    <label for="">Размер штрафа</label>
                                    <input type="number" name="penalty_amount" value="{{ old('penalty_amount', isset($cancellation) ?
                                    $cancellation->penalty_amount : null) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'description'])
                                <div class="form-group">
                                    <label for="">Описание правил отмены</label>
                                    <textarea name="description" rows="3">{{ old('description', isset($cancellation) ?
                                    $cancellation->description : null) }}</textarea>
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

@endsection
