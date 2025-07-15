@extends('layouts.head')

@section('title', 'Бронь оформлена')

@section('content')

    @auth
    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @if(isset($res->errors))
                        @foreach ($res->errors as $error)
                            <div class="alert alert-danger">
                                <h5>{{ $error->code }}</h5>
                                <p style="margin-bottom: 0">{{ $error->message }}</p>
                            </div>
                        @endforeach
                    @else
                        @isset($res->booking)
                            <h1>@lang('main.congratulations')!</h1>
                            <ul>
                                <li>@lang('main.status'): {{ $res->booking->status }}</li>
                                <li>@lang('main.booking_number'): {{ $res->booking->number }}</li>
                                <li>ID @lang('main.hotel'): {{ $res->booking->propertyId }}</li>
                                @php
                                    $hotel = \App\Models\Hotel::where('exely_id', $res->booking->propertyId)->get()->first();
                                    $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                                    $cancel_time = \Carbon\Carbon::createFromDate($res->booking->cancellationPolicy->freeCancellationDeadlineLocal)->format('d.m.Y H:i');
                                    $cancel_utc = \Carbon\Carbon::createFromDate($res->booking->cancellationPolicy->freeCancellationDeadlineLocal)->format('P');
                                    $arrival = \Carbon\Carbon::createFromDate($res->booking->roomStays[0]->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                    $departure = \Carbon\Carbon::createFromDate($res->booking->roomStays[0]->stayDates->departureDateTime)->format('d.m.Y H:i');
                                    $utc   = \Carbon\Carbon::parse($res->booking->cancellationPolicy->freeCancellationDeadlineUtc);
                                    $local = \Carbon\Carbon::parse($res->booking->cancellationPolicy->freeCancellationDeadlineLocal . 'Z');
                                    $hours = $utc->diffInHours($local, false);
                                    $offset = sprintf('UTC%+03d:00', $hours);

                                    //currency
                                                                    $baseCancelPrice = round($res->booking->cancellationPolicy->penaltyAmount * config('services.main.coef') / 100 + $res->booking->cancellationPolicy->penaltyAmount, 0);
                                                                    $basePrice = round($res->booking->total->priceBeforeTax * config('services.main.coef') / 100 + $res->booking->total->priceBeforeTax, 0);

                                                                    $toCurrency = strtoupper($fxBase ?? 'USD');

                                                                    $fxRates = [
                                                                        'USD' => $fxRates['usd'] ?? 1,
                                                                        'RUB' => $fxRates['rub'] ?? 1,
                                                                        'KGS' => $fxRates['kgs'] ?? 1,
                                                                        'UZS' => $fxRates['uzs'] ?? 1,
                                                                    ];

                                                                    $symbols = [
                                                                        'USD' => '$',
                                                                        'RUB' => '₽',
                                                                        'KGS' => 'сом',
                                                                        'UZS' => 'сўм',
                                                                    ];

                                                                    $rateTo = $fxRates[$toCurrency] ?? 1;
                                                                    $convertedCancel = app(\App\Services\FXService::class)->convert($baseCancelPrice, $res->booking->currencyCode, $fxBase);
                                                                    $converted = app(\App\Services\FXService::class)->convert($basePrice, $res->booking->currencyCode, $fxBase);
                                                                    $symbol = $symbols[$toCurrency] ?? $toCurrency;
                                @endphp
                                <li>@lang('main.dates'): {{ $arrival }} - {{ $departure }} (UTC {{ $hotel_utc }})</li>
                                <li>
                                    @if($res->booking->cancellationPolicy->freeCancellationPossible == true)
                                        @lang('main.free_cancellation'): {{ $cancel_time }} ({{ $offset }}
                                        ) @lang('main.cancellation_amount')
                                        : {{ round($converted) }} {{ $symbol }}</li>
                                @else
                                    @lang('main.free_cancellation'). @lang('main.cancellation_amount')
                                    : {{ round($convertedCancel) }} {{ $symbol }}
                                @endif
                                <li>
                                    @lang('main.guest')
                                    : {{ $res->booking->customer->firstName }} {{ $res->booking->customer->lastName }}
                                    <ul>
                                        <li>@lang('main.phone'): {{ $res->booking->customer->contacts->phones[0]->phoneNumber }}</li>
                                        <li>Email: {{ $res->booking->customer->contacts->emails[0]->emailAddress }}</li>
                                        <li>@lang('main.message'): {{ $res->booking->customer->comment }}</li>
                                    </ul>
                                </li>
                            </ul>
                            <div class="bnt-wrap">
                                <form action="{{ route('cancel_calculate_exely') }}">
                                    <input type="hidden" name="number" value="{{ $res->booking->number }}">
                                    <input type="hidden" name="currency" value="{{ $symbol }}">
                                    @if($res->booking->cancellation == null)
                                        <input type="hidden" name="cancelTime"
                                               value="{{ $res->booking->cancellationPolicy->freeCancellationDeadlineUtc }}">
                                    @endif
                                    <button class="more">@lang('main.cancel_booking')</button>
                                </form>
                            </div>
                        @endisset
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
        @include('layouts.auth')
    @endauth

@endsection
