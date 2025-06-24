@extends('layouts.head')

@section('title', 'Отмена брони')

@section('content')


    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12">
<<<<<<< HEAD
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.booking_cancellation')</h1>
                    @php
                        $cancel = \App\Models\CancellationRule::where('id', $book->cancellation_id)->first();
                        $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->first();
                        $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                    @endphp
                    @if($cancel->is_refundable == true)
                        <p>
                            @if(now()->lte($request->cancelTime))
                                @lang('main.free_cancellation') {{ $request->cancelTime }} (UTC {{ $hotel_utc }}
                                ).
                            @endif
                            @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $request->currency }}</p>
                    @else
                        <p>@lang('main.free_cancellation'). @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $request->currency }}</p>
                    @endif
=======
                    <h1 data-aos="fade-up" data-aos-duration="2000">Отмена брони</h1>
                    <p>
                        Штраф за отмену составляет: {{ $book->cancel_penalty }} {{ $book->currency }}
                    </p>
>>>>>>> origin/eralast
                    <form action="{{ route('cancel_confirm') }}">
                        <div class="form-group">
                            <label for="">@lang('main.booking_number')</label>
                            <input type="text" value="{{ $request->number }}" name="number">
                        </div>
                        <input type="hidden" name="amount" value="{{ $book->cancel_penalty }}">
                        <button class="more">@lang('main.cancel')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection
