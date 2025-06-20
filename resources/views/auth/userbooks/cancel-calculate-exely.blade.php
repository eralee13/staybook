@extends('layouts.head')

@section('title', 'Отмена брони')

@section('content')


    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
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
                        <form action="{{ route('userbooks.cancel_confirm') }}">
                            <div class="form-group">
                                <label for="">@lang('main.booking_number')</label>
                                <input type="text" value="{{ $book->book_token }}" name="number">
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
