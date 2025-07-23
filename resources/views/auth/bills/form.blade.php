@extends('auth.layouts.master')

@isset($bill)
    @section('title', 'Редактировать ' . $bill->title)
@else
    @section('title', 'Добавить')
@endisset

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @isset($bill)
                        <h1>@lang('admin.edit_contract') {{ $bill->title }}</h1>
                    @else
                        <h1>@lang('admin.conclude_contract')</h1>
                    @endisset
                    <form method="post" enctype="multipart/form-data"
                          @isset($bill)
                              action="{{ route('bills.update', $bill) }}"
                          @else
                              action="{{ route('bills.store') }}"
                            @endisset
                    >
                        @isset($bill)
                            @method('PUT')
                        @endisset
                        @error('title')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="hidden" value="{{ $hotel }}" name="hotel_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="title" value="{{ old('title', isset($bill) ? $bill->title :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($bill) ? $bill->title_en :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Название банка</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', isset($bill) ? $bill->bank_name :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">ИНН</label>
                                    <input type="text" name="bank_inn" value="{{ old('bank_inn', isset($bill) ? $bill->bank_inn :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Р/с</label>
                                    <input type="text" name="bank_account" value="{{ old('bank_account', isset($bill) ? $bill->bank_account :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">БИК</label>
                                    <input type="text" name="bank_bic" value="{{ old('bank_bic', isset($bill) ? $bill->bank_bic :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Адрес</label>
                                    <input type="text" name="address" value="{{ old('address', isset($bill) ? $bill->address :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.file')</label>
                                    <input type="file" name="file1" id="pdf-upload" accept="application/pdf">
                                    @isset($bill->agreement)
                                        <iframe src="{{ Storage::url($bill->agreement) }}" frameborder="0" height="300" width="100%"></iframe>
                                    @endisset
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">@lang('admin.file') #2</label>
                                    <input type="file" name="file2">
                                    @isset($bill->rules)
                                        <iframe src="{{ Storage::url($bill->rules) }}" frameborder="0" height="300" width="100%"></iframe>
                                    @endisset
                                </div>
                            </div>
                            @can('edit-contact')
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="">@lang('admin.status')</label>
                                        <select name="status">
                                            @if(isset($bill))
                                                @if($bill->status == 1)
                                                    <option value="{{$bill->status}}">@lang('admin.active')</option>
                                                    <option value="0">@lang('admin.not_concluded')</option>
                                                @else
                                                    <option value="{{$bill->status}}">@lang('admin.not_concluded')</option>
                                                    <option value="1">@lang('admin.active')</option>
                                                @endif
                                            @else
                                                <option value="1">@lang('admin.active')</option>
                                                <option value="0">@lang('admin.not_concluded')</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            @endcan
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
