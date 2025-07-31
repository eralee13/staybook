@extends('layouts.head')

@section('title', 'Отмена брони')

@section('content')


    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.booking_cancellation')</h1>
                    <p>
                        @lang('main.cancellation_amount'): {{ round($book->cancel_penalty) }} {{ $book->currency }}
                    </p>
                    <form action="{{ route('cancel_confirm_etg') }}">
                        <div class="form-group">
                            <label for="">@lang('main.booking_number')</label>
                            <input type="text" readonly value="{{ $request->number }}" name="number">
                        </div>
                        <input type="hidden" name="amount" value="{{ $book->cancel_penalty }}">
                        <button class="more">@lang('main.cancel')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
