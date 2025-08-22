@extends('layouts.head')

@section('title', 'Отмена брони')

@section('content')


    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.booking_cancellation')</h1>
                    <p>
                        @if(isset($cancelRule->is_refundable) && $cancelRule->is_refundable == true)
                                <td>@lang('main.free_cancellation') {{ $cancelRule->end_date }} (UTC {{ $hotel->utc }}). <br>
                                    @lang('main.cancellation_amount_tm'): {{ round($book->cancel_penalty) }} {{ $book->currency ?? '$' }}</td>
                        @else
                            @lang('main.non_refundable'): {{ round($book->cancel_penalty) }} {{ $book->currency ?? 'USD' }}
                        @endif
                    </p>
                    <form action="{{ route('cancel_confirm_etg') }}">
                        <div class="form-group">
                            <label for="">@lang('main.booking_number')</label>
                            <input type="text" readonly value="{{ $book->book_token }}" name="number">
                        </div>
                        <input type="hidden" name="amount" value="{{ $book->cancel_penalty }}">
                        <button class="more">@lang('main.cancel')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
