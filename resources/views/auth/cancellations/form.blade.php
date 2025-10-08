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
            const penaltyTypeSelect = document.querySelector('select[name="penalty_type"]');
            const penaltyNightsBlock = document.getElementById('penalty-nights-block');
            const penaltyAmountBlock = document.getElementById('penalty-amount-block');

            function updateVisibility() {
                const field1 = document.getElementById('policy-field1');
                const field2 = document.getElementById('policy-field2');
                const field3 = document.getElementById('policy-field3');

                if (field1) field1.classList.add('d-none');
                if (field2) field2.classList.add('d-none');
                if (field3) field3.classList.add('d-none');

                const selectedRadio = document.querySelector('input[name="cancel_policy"]:checked');
                const selected = selectedRadio ? selectedRadio.value : null;

                if (selected === 'free_then_penalty') {
                    if (field1) field1.classList.remove('d-none');
                    if (field2) field2.classList.remove('d-none');
                    if (field3) field3.classList.remove('d-none');
                } else if (selected === 'non_refundable') {
                    if (field2) field2.classList.remove('d-none');
                    if (field3) field3.classList.remove('d-none');
                }

                updatePenaltyNightsField();
            }

            function updatePenaltyNightsField() {
                const showNights = penaltyTypeSelect && penaltyTypeSelect.value === 'night';

                if (penaltyNightsBlock) {
                    penaltyNightsBlock.classList.toggle('d-none', !showNights);
                }

                if (penaltyAmountBlock) {
                    penaltyAmountBlock.classList.toggle('d-none', showNights);
                }
            }

            if (penaltyTypeSelect) {
                penaltyTypeSelect.addEventListener('change', updatePenaltyNightsField);
            }

            radios.forEach(radio => {
                radio.addEventListener('change', updateVisibility);
            });

            // инициализация при загрузке
            updateVisibility();
        });
    </script>

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
                            <a href="{{ route('cancellations.index') }}"
                               @routeactive('cancel*')>@lang('admin.cancel_fines')</a>
                        </div>
                    </div>
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
                        <input type="hidden" value="{{ $hotel }}" name="hotel_id">
                        <div class="row">
                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'title'])
                                <div class="form-group">
                                    <label for="">@lang('admin.title')</label>
                                    <input type="text" name="title" value="{{ old('title', isset($cancellation) ?
                                    $cancellation->title :
                             null) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'penalty_type'])
                                    <label for="">@lang('admin.choose') @lang('admin.rate')</label>
                                    <div class="custom-select">
                                        <select name="rate_id" id="">
                                            @isset($cancellation)
                                                @if($cancellation->rate_id && $cancellation->rate)
                                                    <option selected value="{{ $cancellation->rate_id }}">
                                                        {{ $cancellation->rate->__('title') }}
                                                    </option>
                                                @else
                                                    <option>@lang('admin.choose')</option>
                                                @endif
                                            @endisset
                                            @foreach($rates as $rate)
                                                @php
                                                    $canc = \App\Models\CancellationRule::where('rate_id', $rate->id)->first();
                                                @endphp
                                                <option value="{{ $rate->id }}">{{ $rate?->__('title') }}
                                                    - {{ $canc?->__('title') ?? ''}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="container mt-4">
                                <h5 class="mb-3">@lang('admin.cancel_fines')</h5>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="cancel_policy" id="policy1"
                                           value="free_until_checkin"
                                           {{ old('cancel_policy', $cancellation->cancel_policy ?? '') === 'free_until_checkin' ? 'checked' : '' }} checked>
                                    <label class="policy1 form-check-label" for="policy1">
                                        <strong>@lang('admin.free_until_checkin')</strong><br>
                                        <small class="text-muted">
                                            @if(app()->getLocale() == 'ru')
                                                В случае отмены бронирования гостю вернётся полная стоимость или
                                                предоплата.
                                            @else
                                                In case of cancellation, the guest will be refunded either the full
                                                amount or the prepayment.
                                            @endif
                                        </small>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="cancel_policy" id="policy2"
                                           value="free_then_penalty"
                                            {{ old('cancel_policy', $cancellation->cancel_policy ?? '') === 'free_then_penalty' ? 'checked' : '' }}>
                                    <label class="policy2 form-check-label" for="policy2">
                                        <strong>@lang('admin.free_then_penalty')</strong><br>
                                        <small class="text-muted">
                                            @if(app()->getLocale() == 'ru')
                                                В случае отмены до указанного времени, стоимость бронирования или
                                                предоплаты будет полностью возвращена гостю.
                                                Если бронирование отменено позже указанного времени, вы сможете списать
                                                штраф.
                                            @else
                                                If the cancellation is made before the specified time, the full booking
                                                amount or prepayment will be refunded to the guest.
                                                If the cancellation is made after the specified time, you may charge a
                                                penalty.
                                            @endif
                                        </small>
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="cancel_policy"
                                           id="policy3" {{ old('cancel_policy', $cancellation->cancel_policy ?? '') === 'non_refundable' ? 'checked' : '' }}>
                                    <label class="policy3 form-check-label" for="policy3">
                                        <strong>@lang('admin.non_refundable')</strong><br>
                                        <small class="text-muted">
                                            @if(app()->getLocale() == 'ru')
                                                В случае отмены бронирования с гостя будет удержана полная стоимость
                                                бронирования или предоплата.
                                            @else
                                                In case of cancellation, the full booking amount or prepayment will be
                                                charged to the guest.
                                            @endif
                                        </small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6" id="policy-field1">
                                @include('auth.layouts.error', ['fieldname' => '>free_cancellation_days'])
                                <div class="form-group">
                                    <label for="">@lang('admin.before_checkin')</label>
                                    <input type="number" name="free_cancellation_days" value="{{ old('free_cancellation_days', isset($cancellation) ?
                                    $cancellation->free_cancellation_days :
                             null) }}">
                                </div>
                            </div>

                            <div class="col-md-6" id="policy-field2">
                                <div class="form-group">
                                    @include('auth.layouts.error', ['fieldname' => 'penalty_type'])
                                    @php
                                        $hot = \App\Models\Hotel::where('id', $hotel)->first();
                                    @endphp
                                    <label for="">@lang('admin.type_fine')</label>
                                    <div class="custom-select">
                                        <select name="penalty_type" id="">
                                            @isset($cancellation)
                                                <option @if($cancellation->penalty_type)
                                                            selected>
                                                    {{ $cancellation->penalty_type }}</option>
                                            @else
                                                <option>@lang('admin.choose')</option>
                                            @endif
                                            @endisset
                                            <option value="fixed">@lang('admin.fixed_amount')</option>
                                            <option value="percent">@lang('admin.percent_from_total')</option>
                                            <option value="night">@lang('admin.number_nights')</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 d-none" id="penalty-nights-block">
                                <div class="form-group">
                                    <label for="penalty_nights">@lang('admin.number_nights') ({{ $hot->currency }}
                                        )</label>
                                    <input type="number" name="penalty_nights" class="form-control"
                                           value="{{ old('penalty_nights', $cancellation->penalty_nights ?? '') }}">
                                </div>
                            </div>

                            <div class="col-md-6" id="penalty-amount-block">
                                @include('auth.layouts.error', ['fieldname' => 'penalty_amount'])
                                <div class="form-group">
                                    <label for="">@lang('admin.penalty_fee') ({{ $hot->currency }})</label>
                                    <input type="number" name="penalty_amount" value="{{ old('penalty_amount', isset($cancellation) ?
            $cancellation->penalty_amount : null) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                @include('auth.layouts.error', ['fieldname' => 'description'])
                                <div class="form-group">
                                    <label for="">@lang('admin.cancel_descr')</label>
                                    <textarea name="description" rows="3">{{ old('description', isset($cancellation) ?
                                    $cancellation->description : null) }}</textarea>
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

@endsection
