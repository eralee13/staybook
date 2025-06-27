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
        #policy1, #policy2, #policy3 {
            float: left;
            width: 25px;
            margin-right: 15px;
        }
        .policy1, .policy2, .policy3 {
            display: inline-block;
            width: calc(100% - 40px);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const radios = document.querySelectorAll('input[name="cancel_policy"]');

            function updateVisibility() {
                document.getElementById('policy-field1').classList.add('d-none');
                document.getElementById('policy-field2').classList.add('d-none');
                document.getElementById('policy-field3').classList.add('d-none');

                const selected = document.querySelector('input[name="cancel_policy"]:checked').value;

                if (selected === 'free_until_checkin') {

                    document.getElementById('policy-field1').classList.add('d-none');
                    document.getElementById('policy-field2').classList.add('d-none');
                    document.getElementById('policy-field3').classList.add('d-none');

                    // document.querySelector('[name="free_cancellation_days"]').value = '';
                    // document.querySelector('[name="penalty_type"]').value = '';
                    // document.querySelector('[name="penalty_amount"]').value = '';

                } else if (selected === 'free_then_penalty') {

                    document.getElementById('policy-field1').classList.remove('d-none');
                    document.getElementById('policy-field2').classList.remove('d-none');
                    document.getElementById('policy-field3').classList.remove('d-none');

                } else if (selected === 'non_refundable') {

                    document.getElementById('policy-field1').classList.add('d-none');
                    document.getElementById('policy-field2').classList.remove('d-none');
                    document.getElementById('policy-field3').classList.remove('d-none');

                    // document.querySelector('[name="free_cancellation_days"]').value = '';

                }
            }

            radios.forEach(radio => {
                radio.addEventListener('change', updateVisibility);
            });

            // вызвать при загрузке
            updateVisibility();
        });
    </script>

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
                                    @include('auth.layouts.error', ['fieldname' => 'penalty_type'])
                                    <label for="">Выберите тариф</label>
                                    <select name="rate_id" id="">
                                        @isset($cancellation)
                                            <option @if($cancellation->rate_id)
                                                        selected value="{{ $cancellation->rate_id }}">
                                                {{ $cancellation->rate->title }}</option>
                                        @else
                                            <option>@lang('admin.choose')</option>
                                        @endif
                                        @endisset
                                        @foreach($rates as $rate)
                                            <option value="{{ $rate->id }}">{{ $rate->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="container mt-4">
                                <h5 class="mb-3">Отмена и штрафы</h5>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="cancel_policy" id="policy1" value="free_until_checkin" checked>
                                    <label class="policy1 form-check-label" for="policy1">
                                        <strong>Бесплатная отмена вплоть до времени заезда</strong><br>
                                        <small class="text-muted">В случае отмены бронирования гостю вернётся полная стоимость или предоплата.</small>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="cancel_policy" id="policy2" value="free_then_penalty">
                                    <label class="policy2 form-check-label" for="policy2">
                                        <strong>Бесплатная отмена, а затем отмена со штрафом вплоть до времени заезда</strong><br>
                                        <small class="text-muted">
                                            В случае отмены до указанного времени, стоимость бронирования или предоплаты будет полностью возвращена гостю. 
                                            Если бронирование отменено позже указанного времени, вы сможете списать штраф.
                                        </small>
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="cancel_policy" id="policy3" value="non_refundable">
                                    <label class="policy3 form-check-label" for="policy3">
                                        <strong>Невозвратный тариф</strong><br>
                                        <small class="text-muted">В случае отмены бронирования с гостя будет удержана полная стоимость бронирования или предоплата.</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6"  id="policy-field1">
                                @include('auth.layouts.error', ['fieldname' => '>free_cancellation_days'])
                                <div class="form-group">
                                    <label for="">Количество дней до заезда</label>
                                    <input type="number" name="free_cancellation_days" value="{{ old('free_cancellation_days', isset($cancellation) ?
                                    $cancellation->free_cancellation_days :
                             null) }}">
                                </div>
                            </div>

                            <div class="col-md-6" id="policy-field2">
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
                                        <option value="night">Ночи</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6" id="policy-field3">
                                @include('auth.layouts.error', ['fieldname' => 'penalty_amount'])
                                <div class="form-group">
                                    <label for="">Сумма размера штрафа</label>
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
                    <br><br>
                </div>
            </div>
        </div>
    </div>

@endsection
