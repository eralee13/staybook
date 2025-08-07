@php use Illuminate\Support\Facades\Http; @endphp
@extends('layouts.head')

@section('title', 'Подтверждение заказа')

@section('content')

    @auth
        <div class="page order">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        @if(isset($order->errors))
                            @foreach ($order->errors as $error)
                                <div class="alert alert-danger">
                                    <h5>{{ $error->code }}</h5>
                                    <p style="margin-bottom: 0">{{ $error->message }}</p>
                                </div>
                            @endforeach
                        @else
                            @if($order == null)
                                <div class="alert alert-danger">@lang('main.complete')! <a
                                            href="{{ route('index') }}">@lang('main.please_try_again')</a>!
                                </div>
                            @else
                                @if($order->booking != null)
                                    @php
                                        $hotel = \App\Models\Hotel::where('exely_id', $order->booking->propertyId)->get()->first();
                                        $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                                        $cancelPossible = $order->booking->cancellationPolicy;
                                        if($cancelPossible->freeCancellationPossible == true) {
                                            $cancelLocal = \Carbon\Carbon::createFromDate($cancelPossible->freeCancellationDeadlineLocal)->format('d.m.Y H:i');
                                            $cancel_utc = \Carbon\Carbon::createFromDate($cancelPossible->freeCancellationDeadlineLocal)->format('P');
                                        }

                                        $utc   = \Carbon\Carbon::parse($cancelPossible->freeCancellationDeadlineUtc);
                                        $local = \Carbon\Carbon::parse($cancelPossible->freeCancellationDeadlineLocal . 'Z');
                                        // сколько часов между ними (signed)
                                        $hours = $utc->diffInHours($local, false);
                                        // формат UTC±HH:00
                                        $offset = sprintf('UTC%+03d:00', $hours);
                                    @endphp
                                    <h1>@lang('main.order_confirmation')</h1>

                                    <table>
                                        <tr>
                                            <td>@lang('main.hotel'):</td>
                                            <td>{{ $hotel->__('title') }}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('main.price'):</td>
                                            <td>{{ $request->brut_price }} {{ $request->currency }}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('main.cancellation_policy'):</td>
                                            @if($cancelPossible->freeCancellationPossible == true)
                                                <td>@lang('main.free_cancellation') {{ $cancelLocal }} ({{ $offset }}).
                                                    @lang('main.cancellation_amount')
                                                    : {{ $request->cancel_brut_price }} {{ $request->currency }}</td>
                                            @else
                                                <td>@lang('main.cancellation_amount')
                                                    : {{ $request->cancel_brut_price }} {{ $request->currency }}</td>
                                            @endif
                                        </tr>

                                        @foreach($order->booking->roomStays as $room)
                                            <tr>
                                                <td>@lang('main.full_name'):</td>
                                                <td>
                                                    @foreach($room->guests as $guest)
                                                        <div class="name">{{ $guest->firstName }}</div>
                                                    @endforeach
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.full_name') @lang('main.child'):</td>
                                                <td>
                                                    @foreach($room->guests as $guest)
                                                        <div class="name">{{ $guest->middleName }}</div>
                                                    @endforeach
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.dates'):</td>
                                                @php
                                                    $arrival = \Carbon\Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                                    $departure = \Carbon\Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                                                @endphp
                                                <td>{{ $arrival }} - {{ $departure }}
                                                    @if($order->booking->cancellationPolicy->freeCancellationDeadlineLocal == null)
                                                        (UTC {{ $hotel_utc }})
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.rate'):</td>
                                                <td>{{ $room->ratePlan->name }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.count_adult'):</td>
                                                <td>{{ $room->guestCount->adultCount }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.count_child'):</td>
                                                @php
                                                    $childAgesInput = (array) $request->input('childAges', []);
                                                $childAges = collect($childAgesInput)
                                                    ->flatMap(fn($ageString) => explode(',', $ageString)) // "2,4" → ["2", "4"]
                                                    ->map(fn($age) => (int) trim($age))                   // убираем пробелы и делаем числа
                                                    ->filter(fn($age) => $age > 0)                        // убираем пустые/нулевые
                                                    ->values()                                            // пересобираем индексы
                                                    ->toArray();

                                                $count = count($childAges);
                                                @endphp
                                                <td>{{ $childCount }} @if($count > 0)
                                                        (@lang('main.age'): {{ implode(', ', $childAges) }}
                                                    @endif</td>
                                                {{--                                    <td>{{ implode(',', explode($order->booking->roomStays[0]->guestCount->childAges)) }}</td>--}}
                                                {{--                                    <td>{{ count($order->booking->roomStays[0]->guestCount->guestCount->childAges) }}</td>--}}
                                            </tr>
                                            <tr>
                                                <td>@lang('main.room'):</td>
                                                <td>{{ $room->roomType->name }}</td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td>@lang('main.message'):</td>
                                            <td>{{ $order->booking->customer->comment }}</td>
                                        </tr>
                                    </table>

                                    <div class="btn-wrap">
                                        <form action="{{ route('book_reserve_exely') }}" method="get">
                                            <input type="hidden" name="propertyId"
                                                   value="{{ $order->booking->propertyId }}">
                                            <input type="hidden" name="hotel_id" value="{{ $request->hotel_id }}">
                                            <input type="hidden" name="brut_price" value="{{ $request->brut_price }}">
                                            <input type="hidden" name="net_price" value="{{ $request->net_price }}">
                                            <input type="hidden" name="cancel_brut_price"
                                                   value="{{ round($request->cancel_brut_price) }}">
                                            <input type="hidden" name="cancel_net_price"
                                                   value="{{ $request->cancel_net_price }}">
                                            <input type="hidden" name="currency" value="{{ $request->currency }}">
                                            <input type="hidden" name="source_sym" value="{{ $request->source_sym }}">
                                            <input type="hidden" name="arrivalDate"
                                                   value="{{ $order->booking->roomStays[0]->stayDates->arrivalDateTime }}">
                                            <input type="hidden" name="departureDate"
                                                   value="{{ $order->booking->roomStays[0]->stayDates->departureDateTime }}">
                                            <input type="hidden" name="ratePlanId"
                                                   value="{{ $order->booking->roomStays[0]->ratePlan->id }}">
                                            <input type="hidden" name="roomTypeId"
                                                   value="{{ $order->booking->roomStays[0]->roomType->id }}">
                                            <input type="hidden" name="roomCode"
                                                   value="{{ $order->booking->roomStays[0]->roomType->placements[0]->code }}">
                                            <input type="hidden" name="firstName"
                                                   value="{{ $order->booking->roomStays[0]->guests[0]->firstName }}">
                                            <input type="hidden" name="middleName"
                                                   value="{{ $order->booking->roomStays[0]->guests[0]->middleName }}">
                                            <input type="hidden" name="sex" value="Male">
                                            <input type="hidden" name="citizenship" value="KGS">
                                            <input type="hidden" name="placements"
                                                   value="{{ json_encode($order->booking->roomStays[0]->roomType->placements) }}">
                                            <input type="hidden" name="adultCount"
                                                   value="{{ $order->booking->roomStays[0]->guestCount->adultCount }}">
                                            <input type="hidden" name="child" value="{{ $childCount }}">
                                            <input type="hidden" name="childAges"
                                                   value="{{ implode(', ', $childAges) }}">
                                            <input type="hidden" name="createBookingToken"
                                                   value="{{ $order->booking->createBookingToken }}">
                                            <input type="hidden" name="checkSum"
                                                   value="{{ $order->booking->roomStays[0]->checksum }}">
                                            <input type="hidden" name="comment"
                                                   value="{{ $order->booking->customer->comment }}">
                                            <input type="hidden" name="phone"
                                                   value="{{ $order->booking->customer->contacts->phones[0]->phoneNumber }}">
                                            <input type="hidden" name="email"
                                                   value="{{ $order->booking->customer->contacts->emails[0]->emailAddress }}">
                                            @hasrole('Demo')
                                            <div class="alert alert-danger">Доступ ограничен</div>
                                            @else
                                                <button class="more">@lang('main.confirm')</button>
                                                @endhasrole
                                        </form>
                                    </div>
                                @else
                                    <div class="alert alert-warning">Уважаемый посетитель! Данные по бронированию были
                                        изменены.
                                        Мы можем вам предложить альтернативный вариант либо вы можете заново выполнить
                                        <a href="{{ route('index') }}">поиск проживания</a></div>
                                    <table>
                                        <tr>
                                            <td>Отель:</td>
                                            @php
                                                $hotel = \App\Models\Hotel::where('exely_id', $order->alternativeBooking->propertyId)->get()->first();
                                                $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                                            //currency
                                                $baseCancelPrice = round($order->alternativeBooking->cancellationPolicy->penaltyAmount * config('services.main.coef') / 100 + $order->alternativeBooking->cancellationPolicy->penaltyAmount, 0);
                                                $basePrice = round($order->alternativeBooking->total->priceBeforeTax * config('services.main.coef') / 100 + $order->alternativeBooking->total->priceBeforeTax, 0);
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
                                                                    $convertedCancel = app(\App\Services\FXService::class)->convert($baseCancelPrice, $order->alternativeBooking->currencyCode, $fxBase);
                                                                    $converted = app(\App\Services\FXService::class)->convert($basePrice, $order->alternativeBooking->currencyCode, $fxBase);
                                                                    $symbol = $symbols[$toCurrency] ?? $toCurrency;

                                            @endphp
                                            <td>{{ $hotel->__('title') }}</td>
                                        </tr>
                                        <tr>
                                            <td>@lang('main.price'):</td>
                                            <td>{{ round($request->brut_price)  }} {{ $request->currency }}</td>
                                        </tr>
                                        <tr>
                                            @php
                                                $cancelPossible = $order->alternativeBooking->cancellationPolicy;
                                                if($cancelPossible->freeCancellationPossible == true) {
                                                    $cancelLocal = \Carbon\Carbon::createFromDate($cancelPossible->freeCancellationDeadlineLocal)->format('d.m.Y H:i');
                                                    $cancel_utc = \Carbon\Carbon::createFromDate($cancelPossible->freeCancellationDeadlineLocal)->format('P');
                                                }

                                                $utc   = \Carbon\Carbon::parse($cancelPossible->freeCancellationDeadlineUtc);
                                        $local = \Carbon\Carbon::parse($cancelPossible->freeCancellationDeadlineLocal . 'Z');

                                        // сколько часов между ними (signed)
                                        $hours = $utc->diffInHours($local, false);

                                        // формат UTC±HH:00
                                        $offset = sprintf('UTC%+03d:00', $hours);
                                            @endphp
                                            <td>@lang('main.cancellation_policy'):</td>
                                            @if($cancelPossible->freeCancellationPossible == true)
                                                <td>@lang('main.free_cancellation') {{ $cancelLocal }} ({{ $offset }}).
                                                    @lang('main.cancellation_amount')
                                                    : {{ round($convertedCancel) }} {{ $symbol }}</td>
                                            @else
                                                <td>@lang('main.free_cancellation'). @lang('main.cancellation_amount')
                                                    : {{ round($convertedCancel) }} {{ $symbol }}</td>
                                            @endif
                                        </tr>
                                        @foreach($order->alternativeBooking->roomStays as $room)
                                            @php
                                                $arrival = \Carbon\Carbon::createFromDate($room->stayDates->arrivalDateTime)->format('d.m.Y H:i');
                                                $departure = \Carbon\Carbon::createFromDate($room->stayDates->departureDateTime)->format('d.m.Y H:i');
                                            @endphp
                                            <tr>
                                                <td>@lang('main.rate'):</td>
                                                <td>{{ $room->ratePlan->name }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.check-in/check-out'):</td>
                                                <td>{{ $arrival }} - {{ $departure }} (UTC {{ $hotel_utc }})
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.count_adult'):</td>
                                                <td>{{ $room->guestCount->adultCount }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.count_child'):</td>
                                                {{--                                        {{ implode(',', $room->guestCount->childAges) }}--}}
                                                <td>{{ $childCount }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.room'):</td>
                                                <td>{{ $room->roomType->name }}</td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.full_name'):</td>
                                                <td>
                                                    <ul>
                                                        @foreach($room->guests as $guest)
                                                            <li>{{ $guest->firstName }}</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>@lang('main.full_name') @lang('main.child'):</td>
                                                <td>
                                                    <ul>
                                                        @foreach($room->guests as $guest)
                                                            <li>{{ $guest->middleName }}</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td>@lang('main.message'):</td>
                                            <td>{{ $order->alternativeBooking->customer->comment }}</td>
                                        </tr>
                                    </table>
                                    <div class="btn-wrap">
                                        <form action="{{ route('book_reserve_exely') }}" method="get">
                                            <input type="hidden" name="propertyId"
                                                   value="{{ $order->alternativeBooking->propertyId }}">
                                            <input type="hidden" name="brut_price"
                                                   value="{{ round($converted) }}">
                                            {{--                            <input type="hidden" name="taxes" value="{{ $order->booking->total->taxes }}">--}}
                                            <input type="hidden" name="cancel_brut_price"
                                                   value="{{ round($convertedCancel) }}">
                                            <input type="hidden" name="propertyId"
                                                   value="{{ $order->alternativeBooking->propertyId }}">
                                            <input type="hidden" name="arrivalDate"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->stayDates->arrivalDateTime }}">
                                            <input type="hidden" name="departureDate"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->stayDates->departureDateTime }}">
                                            <input type="hidden" name="ratePlanId"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->ratePlan->id }}">
                                            <input type="hidden" name="roomTypeId"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->roomType->id }}">
                                            <input type="hidden" name="roomCode"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->roomType->placements[0]->code }}">
                                            <input type="hidden" name="firstName"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->guests[0]->firstName }}">
                                            <input type="hidden" name="middleName"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->guests[0]->middleName }}">
                                            <input type="hidden" name="sex" value="Male">
                                            <input type="hidden" name="citizenship" value="KGS">
                                            <input type="hidden" name="placements"
                                                   value="{{ json_encode($order->alternativeBooking->roomStays[0]->roomType->placements) }}">
                                            <input type="hidden" name="adultCount"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->guestCount->adultCount }}">
                                            @if (request()->filled('childAges'))
                                                <input type="hidden" name="childAges[]"
                                                       value="{{ implode(',',  $order->alternativeBooking->roomStays[0]->guestCount->childAges)  }}">
                                            @endif
                                            <input type="hidden" name="createBookingToken"
                                                   value="{{ $order->alternativeBooking->createBookingToken }}">
                                            <input type="hidden" name="checkSum"
                                                   value="{{ $order->alternativeBooking->roomStays[0]->checksum }}">
                                            <input type="hidden" name="comment"
                                                   value="{{ $order->alternativeBooking->customer->comment }}">
                                            <input type="hidden" name="phone"
                                                   value="{{ $order->alternativeBooking->customer->contacts->phones[0]->phoneNumber }}">
                                            <input type="hidden" name="email"
                                                   value="{{ $order->alternativeBooking->customer->contacts->emails[0]->emailAddress }}">
                                            @hasrole('Demo')
                                                <div class="alert alert-danger">Доступ ограничен</div>
                                            @else
                                                <button class="more">@lang('main.confirm')</button>
                                            @endhasrole
                                        </form>
                                    </div>
                                @endif
                            @endif

                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="timeoutModal" tabindex="-1" aria-labelledby="timeoutLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                    @if(app()->getLocale() == 'ru')
                        <div class="modal-header">
                            <h5 class="modal-title" id="timeoutLabel">Время истекло</h5>
                        </div>
                        <div class="modal-body">
                            Время бронирования истекло. Вы будете перенаправлены на поиск.
                        </div>
                    @else
                        <div class="modal-header">
                            <h5 class="modal-title" id="timeoutLabel">Time has expired</h5>
                        </div>
                        <div class="modal-body">
                            The booking time has expired. You will be redirected to the search page.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            setTimeout(function () {
                $('#timeoutModal').modal('show');
                setTimeout(function () {
                    window.location.href = "{{ route('index') }}";
                }, 4000);
            }, 600000);
        </script>
    @else
        @include('layouts.auth')
    @endauth

@endsection
