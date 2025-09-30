@extends('layouts.master')

@section('title', 'Подтверждение заказа')

@section('content')

    <style>
        body{
            font-family: Unbounded,sans-serif !important;
            background-color: rgba(246, 246, 246, 1) !important;
        }
        .page{
            padding-bottom: 60px;
        }
    </style>

    @auth
        <div class="page order">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 col-md-12">
                        @php
                            $hotel = \App\Models\Hotel::where('id', $request->propertyId)->first();
                            $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
                            $arrival = \Carbon\Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
                            $departure = \Carbon\Carbon::createFromDate($request->departureDate)->format('d.m.Y');
                            $room = \App\Models\Room::where('id', $request->room_id)->firstOrFail();
                            $rate = \App\Models\Rate::where('id', $request->rate_id)->firstOrFail();
                            $cancelPossible = \App\Models\CancellationRule::where('rate_id', $rate->id)->firstOrFail();
                            $freeDate = \Carbon\Carbon::parse($request->arrivalDate)->format('d.m.Y H:i');
                            $cancel = \App\Models\CancellationRule::where('id', $request->cancellation_id)->firstOrFail();
                            $cancelDate = \Carbon\Carbon::parse($request->arrivalDate)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
                            $timezone = \Carbon\Carbon::parse($hotel->timezone)->format('P');
                        @endphp
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
                            <p><span>@lang('main.dates')</span>: {{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }}
                                (UTC {{ $hotel_utc }})</p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.price')</span>: {{ $request->sum }} {{ $request->currency ?? '$' }}</p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.cancellation_policy')</span>: @if($cancel->cancel_policy === 'free_until_checkin')
                                   @lang('main.free_cancellation') {{ $freeDate }}
                                        UTC {{ $timezone }}

                                @elseif($cancel->cancel_policy === 'free_then_penalty')
                                    @if(now()->lte($cancelDate))
                                        @lang('main.free_cancellation') {{ $cancelDate }}
                                            UTC {{ $timezone }}
                                    @else
                                        @lang('main.cancellation_is_not_avaialble')
                                            .
                                            @endif
                                            {{ $request->cancelPrice }} {{ $request->currency }}
                                        @else
                                            {{ $request->cancelPrice }} {{ $request->currency }}
                                        @endif
                            </p>
                        </div>
                        <div class="order-item">
                            <p><span>@lang('main.full_name')</span>: @php
                                $names = [];
                                for ($i = 1; $i <= 8; $i++) {
                                    $field = 'title' . $i;
                                    if ($request->filled($field)) {
                                        $names[] = $request->$field;
                                    }
                                }
                            @endphp
                                {{ implode(', ', $names) }}</p>
                        </div>
                        @if($request->child_name1)
                        <div class="order-item">
                            <p><span>@lang('main.full_name') @lang('main.child')</span>:
                                @php
                                    $ch_names = [];
                                    for ($i = 1; $i <= 8; $i++) {
                                        $field = 'child_name' . $i;
                                        if ($request->filled($field)) {
                                            $ch_names[] = $request->$field;
                                        }
                                    }
                                @endphp
                                {{ implode(', ', $ch_names) }}
                            </p>
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
                                <input type="hidden" name="cancelPriceSource" value="{{ $request->cancelPriceSource }}">
                                <input type="hidden" name="currency" value="{{ $request->currency }}">
                                <input type="hidden" name="source_sym" value="{{ $request->source_sym }}">
                                <input type="hidden" name="arrivalDate"
                                       value="{{ $request->arrivalDate }}">
                                <input type="hidden" name="departureDate"
                                       value="{{ $request->departureDate }}">
                                <input type="hidden" name="room_id"
                                       value="{{ $request->room_id }}">
                                <input type="hidden" name="rate_id"
                                       value="{{ $request->rate_id }}">
                                <input type="hidden" name="title" value="{{ implode(', ', $names) }}">
                                @if($request->child_name1)
                                    <input type="hidden" name="child_name" value="{{ implode(', ', $ch_names) }}">
                                @endif
                                <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">
                                <input type="hidden" name="adult" value="{{ $request->adult }}">
                                <input type="hidden" name="child" value="{{ $request->child }}">
                                <input type="hidden" name="childAges[]"
                                       value="{{ implode(', ', $request->childAges ?? []) }}">
                                <input type="hidden" name="comment"
                                       value="{{ $request->comment }}">
                                <input type="hidden" name="phone"
                                       value="{{ $request->phone  }}">
                                <input type="hidden" name="email"
                                       value="{{ $request->email }}">
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