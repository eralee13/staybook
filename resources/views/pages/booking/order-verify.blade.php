@extends('layouts.master')

@section('title', 'Подтверждение заказа')

@section('content')

    <style>
        body{
            font-family: Unbounded, sans-serif !important;
            background-color: rgba(246, 246, 246, 1) !important;
        }
        .page{ padding-bottom: 60px; }
    </style>

    @auth
        @php
            $hotel = \App\Models\Hotel::where('id', $request->propertyId)->firstOrFail();
            $room  = \App\Models\Room::findOrFail($request->room_id);
            $rate  = \App\Models\Rate::findOrFail($request->rate_id);

            $coef = (float) (config('services.main.coef') ?? 0.92);

            // Политика отмены может отсутствовать — НЕ падаем
            $cancel = \App\Models\CancellationRule::where('rate_id', $rate->id)->first(); // запасной вариант
            $hotelTz   = $hotel->timezone ?: 'UTC';
            $hotel_utc = \Carbon\Carbon::now($hotelTz)->format('P');
            $timezone  = \Carbon\Carbon::now($hotelTz)->format('P');

            $arrivalCarbon   = \Carbon\Carbon::parse($request->arrivalDate, $hotelTz)->timezone($hotelTz);
            $departureCarbon = \Carbon\Carbon::parse($request->departureDate, $hotelTz)->timezone($hotelTz);

            $arrival   = $arrivalCarbon->format('d.m.Y');
            $departure = $departureCarbon->format('d.m.Y');

            $freeDate = $arrivalCarbon->format('d.m.Y H:i');

            // Крайняя дата бесплатной отмены (если есть правило)
            $cancelCutoffCarbon = $cancel
                ? \Carbon\Carbon::parse($request->arrivalDate, $hotelTz)->subDays((int)($cancel->free_cancellation_days ?? 0))
                : null;
            $cancelDate = $request->cancelDate;

            // Имена взрослых
            $names = [];
            for ($i = 1; $i <= 8; $i++) {
                $field = 'title' . $i;
                if ($request->filled($field)) $names[] = $request->$field;
            }

            // Имена детей
            $ch_names = [];
            for ($i = 1; $i <= 8; $i++) {
                $field = 'child_name' . $i;
                if ($request->filled($field)) $ch_names[] = $request->$field;
            }

            // Возраста детей для hidden (если пришли массивом/CSV)
            $childAges = is_array($request->childAges)
                ? $request->childAges
                : (empty($request->childAges) ? [] : explode(',', (string)$request->childAges));
        @endphp

        <div class="page order">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12">
                        <h1><img src="{{ route('index') }}/img/arrow-left.svg" alt=""> @lang('main.order_confirmation')</h1>

                        <div class="order-item">
                            <p><span>@lang('main.hotel')</span>: {{ $hotel->__('title') }}</p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.room')</span>: {{ $room->__('title') }}</p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.rate')</span>: {{ $rate->__('title') }}</p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.count_room')</span>: {{ $request->roomCount }}</p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.count_adult')</span>: {{ $request->adult }}</p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.count_child')</span>: {{ $request->child ?? 0 }}</p>
                        </div>
                        <div class="order-item">
                            <p>
                                <span>@lang('main.dates')</span>:
                                {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }}
                                (UTC {{ $hotel_utc }})
                            </p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.price')</span>: {{ $request->sum }} {{ $request->currency ?? '$' }}</p>
                        </div>

                        <div class="order-item">
                            <p>
                                {{ $request->cancelText }} {{ $request->cancelPrice }} {{ $request->currency }}
                            </p>
                        </div>

                        <div class="order-item">
                            <p><span>@lang('main.full_name')</span>: {{ implode(', ', $names) }}</p>
                        </div>

                        @if(!empty($ch_names))
                            <div class="order-item">
                                <p><span>@lang('main.full_name') @lang('main.child')</span>: {{ implode(', ', $ch_names) }}</p>
                            </div>
                        @endif

                        <div class="order-item">
                            <p><span>@lang('main.phone')</span>: {{ $request->phone }}</p>
                        </div>
                        <div class="order-item">
                            <p><span>Email</span>: {{ $request->email }}</p>
                        </div>
                        @if($request->checkin_request == 1)
                            <div class="order-item">
                                <p><span>@lang('main.late_checkin')</span>: {{ $request->checkin_time }}</p>
                            </div>
                        @endif
                        @if($request->checkout_request == 1)
                            <div class="order-item">
                                <p><span>@lang('main.late_checkout')</span>: {{ $request->checkout_time }}</p>
                            </div>
                        @endif
                        @if($request->comment)
                            <div class="order-item">
                                <p><span>@lang('main.message')</span>: {{ $request->comment }}</p>
                            </div>
                        @endif

                        <div class="btn-wrap">
                            <form action="{{ route('book_reserve') }}" method="get">
                                <input type="hidden" name="hotel_id" value="{{ $request->propertyId }}">
                                <input type="hidden" name="sum" value="{{ $request->sum }}">
                                <input type="hidden" name="price" value="{{ $request->price }}">
                                <input type="hidden" name="cancellation_id" value="{{ $request->cancellation_id }}">
                                <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                                <input type="hidden" name="currency" value="{{ $request->currency }}">
                                <input type="hidden" name="source_sym" value="{{ $request->source_sym }}">
                                <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                                <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                                <input type="hidden" name="room_id" value="{{ $request->room_id }}">
                                <input type="hidden" name="rate_id" value="{{ $request->rate_id }}">
                                <input type="hidden" name="title" value="{{ implode(', ', $names) }}">
                                @if(!empty($ch_names))
                                    <input type="hidden" name="child_name" value="{{ implode(', ', $ch_names) }}">
                                @endif
                                <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">
                                <input type="hidden" name="adult" value="{{ $request->adult }}">
                                <input type="hidden" name="child" value="{{ $request->child }}">


                                @foreach($childAges as $age)
                                    <input type="hidden" name="childAges[]" value="{{ $age }}">
                                @endforeach


                                <input type="hidden" name="comment" value="{{ $request->comment }}">
                                <input type="hidden" name="phone" value="{{ $request->phone }}">
                                <input type="hidden" name="email" value="{{ $request->email }}">

                                @if($request->checkin_request)
                                    <input type="hidden" name="checkin_request" value="{{ $request->checkin_request }}">
                                    <input type="hidden" name="checkin_time" value="{{ $request->checkin_time }}">
                                @endif
                                @if($request->checkout_request)
                                    <input type="hidden" name="checkout_request" value="{{ $request->checkout_request }}">
                                    <input type="hidden" name="checkout_time" value="{{ $request->checkout_time }}">
                                @endif

                                @hasrole('Demo')
                                <div class="alert alert-danger">Доступ ограничен</div>
                                @else
                                    <button class="more">@lang('main.confirm')</button>
                                    @endhasrole
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Timeout Modal --}}
        <div class="modal fade" id="timeoutModal" tabindex="-1" aria-labelledby="timeoutLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content text-center">
                    @if(app()->getLocale() == 'ru')
                        <div class="modal-header"><h5 class="modal-title" id="timeoutLabel">Время истекло</h5></div>
                        <div class="modal-body">Время бронирования истекло. Вы будете перенаправлены на поиск.</div>
                    @else
                        <div class="modal-header"><h5 class="modal-title" id="timeoutLabel">Time has expired</h5></div>
                        <div class="modal-body">The booking time has expired. You will be redirected to the search page.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bootstrap 5 (без jQuery) --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            setTimeout(function () {
                const modal = new bootstrap.Modal(document.getElementById('timeoutModal'));
                modal.show();
                setTimeout(function () {
                    window.location.href = "{{ route('index') }}";
                }, 4000);
            }, 600000);
        </script>

    @else
        @include('layouts.auth')
    @endauth

@endsection
