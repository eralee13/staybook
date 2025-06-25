@extends('layouts.master')

@section('title', 'Отмена бронирования')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @if(isset($calc->errors))
                        @foreach ($calc->errors as $error)
                            <div class="alert alert-danger">
                                <h5>{{ $error->code }}</h5>
                                <p style="margin-bottom: 0">{{ $error->message }}</p>
                            </div>
                        @endforeach
                    @else
                        <h1>@lang('main.booking_cancellation')</h1>
                        <p>@lang('main.cancellation_amount'): {{ $calc->penaltyAmount }} {{ $request->currency }}</p>
                        <form action="{{ route('cancel_confirm_exely') }}">
                            <div class="form-group">
                                <label for="">@lang('main.booking_number')</label>
                                <input type="text" value="{{ $request->number }}" name="number">
                            </div>
                            <input type="hidden" name="amount" value="{{ $calc->penaltyAmount }}">
                            <button class="more">@lang('main.cancel_booking')</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
