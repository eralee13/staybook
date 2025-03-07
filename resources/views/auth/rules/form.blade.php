@extends('auth.layouts.master')

@isset($rule)
    @section('title', __('admin.edit') . ' ' . $rule->title)
@else
    @section('title', __('admin.add'))
@endisset

@section('content')

    <style>
        .admin label {
            display: inline-block;
        }
        #block1, #block2{
            display: none;
        }
    </style>

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @include('auth.layouts.subroom')
                    @isset($rule)
                        <h1>@lang('admin.edit') {{ $rule->title }}</h1>
                    @else
                        <h1>@lang('admin.add')</h1>
                    @endisset
                    <form method="post"
                          @isset($rule)
                              action="{{ route('rules.update', $rule) }}"
                          @else
                              action="{{ route('rules.store') }}"
                            @endisset
                    >
                        @isset($rule)
                            @method('PUT')
                        @endisset
                        <input type="hidden" value="{{ $hotel }}" name="hotel_id">
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title'])
                                <div class="form-group">
                                    <label for="">Название правила для отеля</label>
                                    <input type="text" name="title" value="{{ old('title', isset($rule) ?
                                    $rule->title :
                             null) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title_en'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title') EN</label>
                                    <input type="text" name="title_en" value="{{ old('title_en', isset($rule) ?
                                $rule->title_en :
                             null) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'size'])
                                <div class="form-group">
                                    <label for="">Указать размер штрафа при аннуляции</label>
                                    <select name="size" onchange="ageCheck(this);">
                                        @isset($rule)
                                            <option value="{{ $rule->size }}" @if($rule->size)
                                                        selected>
                                                @if($rule->size == 1)
                                                    Процент от первых суток
                                                @elseif($rule->size == 2)
                                                    Процент от всей суммы бронирования
                                                @else
                                                    Без штрафа
                                                @endif
                                            </option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        <option value="0">Без штрафа</option>
                                        <option value="1">Процент от первых суток</option>
                                        <option value="2">Процент от всей суммы бронирования</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div id="block1" @isset($rule)style="@if($rule->size == 1) display: block
                                    @endif"@endisset>
                                        <div class="form-group">
                                            <label for="">Процент от первых суток</label>
                                            <input type="number" name="percent_day" value="{{ old('percent_day', isset
                                            ($rule) ?
                                    $rule->percent_day :  null) }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="">За сколько дней</label>
                                            <input type="number" name="date_day" value="{{ old('date_day', isset
                                            ($rule) ? $rule->date_day :  null) }}">
                                        </div>
                                    </div>
                                    <div id="block2" @isset($rule) style="@if($rule->size == 2) display: block @endif"
                                         @endisset>
                                        <div class="form-group">
                                            <label for="">Процент от всей суммы бронирования</label>
                                            <input type="number" name="percent_book" value="{{ old('percent_book', isset
                                            ($rule) ? $rule->percent_book :  null) }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="">За сколько дней</label>
                                            <input type="number" name="date_book" value="{{ old('date_book', isset
                                            ($rule) ? $rule->date_book :  null) }}">
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

    <script>
        function ageCheck(that) {
            if (that.value == 1) {
                document.getElementById("block1").style.display = "block";
                document.getElementById("block2").style.display = "none";
            } else if (that.value == 2) {
                document.getElementById("block1").style.display = "none";
                document.getElementById("block2").style.display = "block";
            } else {
                document.getElementById("block1").style.display = "none";
                document.getElementById("block2").style.display = "none";
            }
        }
    </script>



@endsection
