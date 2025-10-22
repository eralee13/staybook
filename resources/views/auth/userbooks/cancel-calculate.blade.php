@extends('layouts.master')

@section('title', 'Отмена брони')

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-lg-6 col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">@lang('main.booking_cancellation')</h1>
                    @php
                        $timezone = $hotel->timezone ?? config('app.timezone');
                        $hotel = \App\Models\Hotel::find($book->hotel_id);
                        $hotelTz = $hotel->timezone ?? 'UTC';
                        $hotel_utc = \Carbon\Carbon::now($hotelTz)->format('P');
                        $cancel = \App\Models\CancellationRule::find($book->cancellation_id)
                                  ?: (\App\Models\CancellationRule::where('rate_id', $book->rate_id ?? null)->first());
                        $freeDate = \Carbon\Carbon::parse($book->arrivalDate)->format('d.m.Y H:i');
                        if($cancel != null){
                            $cancelDate = \Carbon\Carbon::parse($book->arrivalDate)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
                        }
                        $cancelTimeCarbon = !empty($request->cancelTime)
                            ? \Carbon\Carbon::parse($request->cancelTime, $hotelTz)
                            : null;
                    @endphp
                    @if($cancel->cancel_policy === 'free_until_checkin')
                        <div class="value">
                            @lang('main.free_cancellation') {{ $freeDate }}
                            UTC {{ $timezone }}
                        </div>
                    @elseif($cancel->cancel_policy === 'free_then_penalty')
                        @if(now()->lte($cancelDate))
                            <div class="value">
                                @lang('main.free_cancellation') {{ $cancelDate }}
                                UTC {{ $timezone }}
                            </div>
                        @else
                            <div class="value">
                                @lang('main.cancellation_is_not_avaialble'). @lang('main.cancellation_amount')
                                : {{ $book->cancel_penalty }} {{ $book->currency }}
                            </div>
                        @endif
                        <div class="value">
                            @lang('main.cancellation_amount')
                            : {{ $book->cancel_penalty }} {{ $book->currency }}
                        </div>
                    @else
                        <div class="value">
                            @lang('main.cancellation_amount')
                            : {{ $book->cancel_penalty }} {{ $book->currency }}
                        </div>
                    @endif

                    <form action="{{ route('cancel_confirm') }}" style="margin-top: 30px">
                        <div class="form-group">
                            <label>@lang('main.booking_number')</label>
                            <input type="text" value="{{ $book->book_token }}" name="number">
                        </div>
                        <input type="hidden" name="amount" value="{{ $book->cancel_penalty }}">
                        {{-- передаём ту же cancelTime, но лучше ISO, если бэкенд ожидает точное сравнение --}}
                        @if($cancelTimeCarbon)
                            <input type="hidden" name="cancelTime" value="{{ $cancelTimeCarbon->toIso8601String() }}">
                        @endif
                        <button class="more">@lang('main.cancel')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
