@extends('layouts.head')

@section('title', 'Подтверждение заказа')

@section('content')

    @auth
        <div class="page order">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12">
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
                        <h1>@lang('main.order_confirmation')</h1>
                        <table>
                            <tr>
                                <td>@lang('main.hotel'):</td>
                                <td>{{ $hotel->__('title') }}</td>
                            </tr>
                            <tr>
                                <td>@lang('main.room'):</td>
                                <td>{{ $room->__('title') }}</td>
                            </tr>
                            <tr>
                                <td>@lang('main.rate'):</td>
                                <td>{{ $rate->__('title') }}</td>
                            </tr>
                            <tr>
                                <td>@lang('main.count_room')</td>
                                <td>{{ $request->roomCount }}</td>
                            </tr>
                            <tr>
                                <td>@lang('main.count_adult'):</td>
                                <td>{{ $request->adult }}</td>
                            </tr>
                            <tr>
                                <td>@lang('main.count_child'):</td>
                                <td>{{ $request->child ?? 0 }}</td>
                            </tr>
                            <tr>
                                <td>@lang('main.dates'):</td>
                                <td>{{ $arrival }} {{ $hotel->checkin }} - {{ $departure }} {{ $hotel->checkout }}
                                    (UTC {{ $hotel_utc }})
                                </td>
                            </tr>
                            <tr>
                                <td>@lang('main.price'):</td>
                                <td>{{ $request->sum }} {{ $request->currency ?? '$' }}</td>
                            </tr>
                            <tr>
                                <td>@lang('main.cancellation_policy'):</td>
                                @if($cancel->cancel_policy === 'free_until_checkin')
                                    <td>@lang('main.free_cancellation') {{ $freeDate }}
                                        UTC {{ $timezone }}</td>

                                @elseif($cancel->cancel_policy === 'free_then_penalty')
                                    @if(now()->lte($cancelDate))
                                        <td> @lang('main.free_cancellation') {{ $cancelDate }}
                                            UTC {{ $timezone }}
                                    @else
                                        <td>@lang('main.cancellation_is_not_avaialble')
                                            .
                                    @endif
                                    @lang('main.cancellation_amount')
                                    : {{ $request->cancelPrice }} {{ $request->currency }}</td>
                                @else
                                    <td>@lang('main.cancellation_amount')
                                        : {{ $request->cancelPrice }} {{ $request->currency }}
                                    </td>
                                @endif
                            </tr>
                            <tr>
                                <td>@lang('main.full_name'):</td>
                                <td>
                                    @php
                                        $names = [];
                                        for ($i = 1; $i <= 8; $i++) {
                                            $field = 'title' . $i;
                                            if ($request->filled($field)) {
                                                $names[] = $request->$field;
                                            }
                                        }
                                    @endphp
                                    <div class="name">{{ implode(', ', $names) }}</div>
                                </td>
                            </tr>
                            @if($request->child_name1)
                                <tr>
                                    <td>@lang('main.full_name') @lang('main.child'):</td>
                                    <td>
                                        @php
                                            $ch_names = [];
                                            for ($i = 1; $i <= 8; $i++) {
                                                $field = 'child_name' . $i;
                                                if ($request->filled($field)) {
                                                    $ch_names[] = $request->$field;
                                                }
                                            }
                                        @endphp
                                        <div class="name">{{ implode(', ', $ch_names) }}</div>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td>@lang('main.phone'):</td>
                                <td>
                                    <div class="name">{{ $request->phone }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>Email:</td>
                                <td>
                                    <div class="name">{{ $request->email }}</div>
                                </td>
                            </tr>
                            @if($request->checkin_request == 1)
                                <tr>
                                    <td>@lang('main.late_checkin')</td>
                                    <td>{{ $request->checkin_time }}</td>
                                </tr>
                            @endif
                            @if($request->checkout_request == 1)
                                <tr>
                                    <td>@lang('main.late_checkout')</td>
                                    <td>{{ $request->checkout_time }}</td>
                                </tr>
                            @endif
                            @if($request->comment)
                                <tr>
                                    <td>@lang('main.message'):</td>
                                    <td>{{ $request->comment }}</td>
                                </tr>
                            @endif
                        </table>

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
                                <input type="hidden" name="child_name" value="{{ implode(', ', $ch_names) }}">
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
                                <input type="hidden" name="checkin_request" value="{{ $request->checkin_request }}">
                                <input type="hidden" name="checkout_request" value="{{ $request->checkout_request }}">
                                <input type="hidden" name="checkin_time" value="{{ $request->checkin_time }}">
                                <input type="hidden" name="checkout_time" value="{{ $request->checkout_time }}">
                                <button class="more">@lang('main.confirm')</button>
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