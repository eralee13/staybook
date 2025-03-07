@extends('layouts.master')

@section('title', 'Главная страница')

@section('content')
    <div class="page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <h3>Шаг №2</h3>
                    <form action="{{ route('postCreateStepTwo') }}" method="POST">
                        @csrf
                        <div class="card">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'description'])
                                        <label for="">@lang('admin.description')</label>
                                        <textarea name="description" id="editor" rows="3">{{ old('description', isset($product) ?
                            $product->description : null) }}</textarea>
                                        <script
                                            src="https://cdn.tiny.cloud/1/yxonqgmruy7kchzsv4uizqanbapq2uta96cs0p4y91ov9iod/tinymce/6/tinymce.min.js"
                                            referrerpolicy="origin"></script>
                                        <script
                                            src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
                                        <script>
                                            ClassicEditor
                                                .create(document.querySelector('#editor'))
                                                .catch(error => {
                                                    console.error(error);
                                                });
                                            ClassicEditor
                                                .create(document.querySelector('#editor1'))
                                                .catch(error => {
                                                    console.error(error);
                                                });
                                        </script>
                                    </div>
                                    <div class="form-group">
                                        @include('auth.layouts.error', ['fieldname' => 'description_en'])
                                        <label for="">@lang('admin.description') EN</label>
                                        <textarea name="description_en" id="editor1" rows="3">{{ old('description_en', isset($product) ?
                            $product->description_en : null) }}</textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        @include('auth.layouts.error', ['fieldname' => 'count'])
                                        <div class="form-group">
                                            <label for="">@lang('admin.number_of_room')</label>
                                            <input type="number" name="count" value="{{ old('count', isset($product) ?
                                    $product->count : null) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'checkin'])
                                            <label for="">@lang('admin.checkin')</label>
                                            <select name="checkin" id="">
                                                @isset($product)
                                                    <option @if($product->checkin)
                                                                selected>
                                                        {{ $product->checkin }}</option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endif
                                                @endisset
                                                <option value="13:00">13:00</option>
                                                <option value="14:00">14:00</option>
                                                <option value="15:00">15:00</option>
                                                <option value="16:00">16:00</option>
                                                <option value="17:00">17:00</option>
                                                <option value="18:00">18:00</option>
                                                <option value="19:00">19:00</option>
                                                <option value="20:00">20:00</option>
                                                <option value="21:00">21:00</option>
                                                <option value="22:00">22:00</option>
                                                <option value="23:00">23:00</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'checkout'])
                                            <label for="">@lang('admin.checkout')</label>
                                            <select name="checkout" id="">
                                                @isset($product)
                                                    <option @if($product->checkout)
                                                                selected>
                                                        {{ $product->checkout }}</option>
                                                @else
                                                    <option>Выбрать</option>
                                                @endif
                                                @endisset
                                                <option value="01:00">01:00</option>
                                                <option value="02:00">02:00</option>
                                                <option value="03:00">03:00</option>
                                                <option value="04:00">04:00</option>
                                                <option value="05:00">05:00</option>
                                                <option value="06:00">06:00</option>
                                                <option value="07:00">07:00</option>
                                                <option value="08:00">08:00</option>
                                                <option value="09:00">09:00</option>
                                                <option value="10:00">10:00</option>
                                                <option value="11:00">11:00</option>
                                                <option value="12:00">12:00</option>
                                                <option value="13:00">13:00</option>
                                                <option value="14:00">14:00</option>
                                                <option value="15:00">15:00</option>
                                                <option value="16:00">16:00</option>
                                                <option value="17:00">17:00</option>
                                                <option value="18:00">18:00</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'early_in'])
                                            <label for="early_in">@lang('admin.early_checkin')</label>
                                            <select name="early_in" id="early_in">
                                                @isset($product)
                                                    <option @if($product->early_in)
                                                                selected>
                                                        {{ $product->early_in }}</option>
                                                @else
                                                    <option>Choose</option>
                                                @endif
                                                @endisset
                                                <option value="06:00">06:00</option>
                                                <option value="07:00">07:00</option>
                                                <option value="08:00">08:00</option>
                                                <option value="09:00">09:00</option>
                                                <option value="10:00">10:00</option>
                                                <option value="11:00">11:00</option>
                                                <option value="12:00">12:00</option>
                                                <option value="13:00">13:00</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'early_out'])
                                            <label for="early_out">@lang('admin.late_checkout')</label>
                                            <select name="early_out" id="early_out">
                                                @isset($product)
                                                    <option @if($product->early_out)
                                                                selected>
                                                        {{ $product->early_out }}</option>
                                                @else
                                                    <option>Choose</option>
                                                @endif
                                                @endisset
                                                <option value="15:00">15:00</option>
                                                <option value="16:00">16:00</option>
                                                <option value="17:00">17:00</option>
                                                <option value="18:00">18:00</option>
                                                <option value="19:00">19:00</option>
                                                <option value="20:00">20:00</option>
                                                <option value="21:00">21:00</option>
                                                <option value="22:00">22:00</option>
                                                <option value="23:00">23:00</option>
                                            </select>
                                        </div>
                                    </div>


                                    <div class="col-md-6">
                                        <div class="form-group">
                                            @include('auth.layouts.error', ['fieldname' => 'rating'])
                                            <label for="rating">@lang('admin.rating')</label>
                                            <select name="rating" id="rating">
                                                <option>@lang('admin.choose')</option>
                                                <option value="2" @if(old('rating') == '2') selected @endif>2</option>
                                                <option value="3" @if(old('rating') == '3') selected @endif>3</option>
                                                <option value="4" @if(old('rating') == '4') selected @endif>4</option>
                                                <option value="5" @if(old('rating') == '5') selected @endif>5</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        @include('auth.layouts.error', ['fieldname' => 'phone'])
                                        <div class="form-group">
                                            <label for="">@lang('admin.phone_number')</label>
                                            <input type="tel" id="phone" name="phone" class="phone" value="{{ old('phone', isset
                                    ($product) ?
                                    $product->phone :
                             null) }}">
                                            <div id="output" class="output"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        @include('auth.layouts.error', ['fieldname' => 'email'])
                                        <div class="form-group">
                                            <label for="">Email</label>
                                            <input type="email" name="email" value="{{ old('email', isset($product) ? $product->email :
                             null) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        @include('auth.layouts.error', ['fieldname' => 'top'])
                                        <div class="form-group">
                                            <label for="">TOP (order)</label>
                                            <input type="number" name="top" value="{{ old('top', isset($product) ?
                                    $product->top : null) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">@lang('admin.status')</label>
                                            <select name="status">
                                                @if(isset($product))
                                                    @if($product->status == 1)
                                                        <option value="1">@lang('admin.active')</option>
                                                        <option value="0">@lang('admin.disable')</option>
                                                    @else
                                                        <option value="0">@lang('admin.disable')</option>
                                                        <option value="1">@lang('admin.active')</option>
                                                    @endif
                                                @else
                                                    <option value="1">@lang('admin.active')</option>
                                                    <option value="0">@lang('admin.disable')</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row" style="margin-top: 20px">
                                <div class="col-md-6 text-left">
                                    <a href="{{ route('createStepOne') }}" class="more btn btn-danger
                                    pull-right">Назад</a>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button type="submit" class="more btn btn-primary">Далее</button>
                                </div>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>
@endsection
