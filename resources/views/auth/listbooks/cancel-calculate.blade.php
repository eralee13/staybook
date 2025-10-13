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
                        // Мягкие выборки (не падаем, если нет записи)
                        $hotel = \App\Models\Hotel::find($book->hotel_id);
                        $hotelTz = $hotel->timezone ?? 'UTC';
                        $hotel_utc = \Carbon\Carbon::now($hotelTz)->format('P');

                        // Политика отмены: по id, иначе (если есть) по тарифу брони
                        $cancel = \App\Models\CancellationRule::find($book->cancellation_id)
                                  ?: (\App\Models\CancellationRule::where('rate_id', $book->rate_id ?? null)->first());

                        // cancelTime может прийти как строка — нормализуем
                        $cancelTimeCarbon = !empty($request->cancelTime)
                            ? \Carbon\Carbon::parse($request->cancelTime, $hotelTz)
                            : null;
                    @endphp

                    @if($cancel && $cancel->is_refundable)
                        <p>
                            @if($cancelTimeCarbon && now($hotelTz)->lte($cancelTimeCarbon))
                                @lang('main.free_cancellation') {{ $cancelTimeCarbon->format('d.m.Y H:i') }} (UTC {{ $hotel_utc }}).
                            @endif
                            @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $book->currency }}
                        </p>
                    @elseif($cancel && !$cancel->is_refundable)
                        <p>
                            @lang('main.cancellation_is_not_avaialble').
                            @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $book->currency }}
                        </p>
                    @else
                        {{-- Политика не найдена: показываем безопасное сообщение --}}
                        <p>
                            @lang('main.cancellation_rule_not_found').
                            @lang('main.cancellation_amount'): {{ $book->cancel_penalty }} {{ $book->currency }}
                        </p>
                    @endif

                    <form action="{{ route('cancel_confirm') }}">
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
