@extends('layouts.head')

@section('title', 'Подтверждение заказа')

@section('content')

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
                        $timezone = \Carbon\Carbon::parse($hotel->timezone)->format('P');
                        $cancel = \App\Models\CancellationRule::where('id', $request->cancellation_id)->firstOrFail();
                        $cancelDate = \Carbon\Carbon::parse($request->arrivalDate)->subDays($cancel->free_cancellation_days)->format('d.m.Y H:i');
                        $freeDate = \Carbon\Carbon::parse($request->arrivalDate)->format('d.m.Y H:i');
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
                            <td>{{ $order->booking->total->priceBeforeTax ?? $request->price }} {{ $order->booking->currencyCode ?? '$' }}</td>
                        </tr>
                        <tr>
                            <td>@lang('main.cancellation_policy'):</td>
                            @if($cancel->cancel_policy === 'free_until_checkin')
                                <td>@lang('main.free_cancellation') {{ $freeDate }}
                                    UTC {{ $timezone }}</td>

                            @elseif($cancel->cancel_policy === 'free_then_penalty')
                                @if(now()->lte($cancelDate))
                                   <td> @lang('main.free_cancellation') {{ $cancelDate }}
                                       UTC {{ $timezone }}</td>
                                @else
                                    <td>@lang('main.cancellation_is_not_avaialble').</td>
                                @endif
                                @lang('main.cancellation_amount')
                                :
                                @if($cancel->penalty_type === 'fixed')
                                    ${{ $cancelPrice = round($cancel->penalty_amount) }}
                                @elseif($cancel->penalty_type === 'night')
                                    ${{ $cancelPrice = round($cancel->penalty_nights * $rate->price) }}
                                @else
                                    ${{ $cancelPrice = round(($sum * $cancel->penalty_amount) / 100) }}
                                @endif

                            @else
                                <td>@lang('main.cancellation_amount')
                                    :
                                    @if($cancel->penalty_type === 'fixed')
                                        ${{ $cancelPrice = round($cancel->penalty_amount) }}
                                    @elseif($cancel->penalty_type === 'night')
                                        ${{ $cancelPrice = round($cancel->penalty_nights * $rate->price) }}
                                    @else
                                        ${{ $cancelPrice = round(($sum * $cancel->penalty_amount) / 100) }}
                                    @endif</td>
                            @endif
                        </tr>
                        <tr>
                            <td>@lang('main.full_name'):</td>
                            <td>
                                <div class="name">{{ $request->name }}</div>
                            </td>
                        </tr>
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
                        <tr>
                            <td>@lang('main.message'):</td>
                            <td>{{ $request->comment }}</td>
                        </tr>
                    </table>

                    <div class="btn-wrap">
                        <form action="{{ route('book_reserve') }}" method="get">
                            <input type="hidden" name="propertyId"
                                   value="{{ $request->propertyId }}">
                            <input type="hidden" name="total"
                                   value="{{ $request->price }}">
                            <input type="hidden" name="cancellation_id" value="{{ $request->cancellation_id }}">
                            <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                            <input type="hidden" name="arrivalDate"
                                   value="{{ $request->arrivalDate }}">
                            <input type="hidden" name="departureDate"
                                   value="{{ $request->departureDate }}">
                            <input type="hidden" name="rate_id"
                                   value="{{ $request->rate_id }}">
                            <input type="hidden" name="roomTypeId"
                                   value="{{ $request->room_id }}">
                            <input type="hidden" name="firstName"
                                   value="{{ $request->name }}">
                            <input type="hidden" name="lastName"
                                   value="{{ $request->name }}">
                            <input type="hidden" name="sex" value="Male">
                            <input type="hidden" name="citizenship" value="KGS">
                            <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">
                            <input type="hidden" name="adult" value="{{ $request->adult }}">
                            <input type="hidden" name="child" value="{{ $request->child }}">
                            <input type="hidden" name="childAges[]"
                                   value="{{ implode(', ', $request->childAges ?? []) }}">
                            <input type="hidden" name="comment"
                                   value="{{ $order->booking->bookingComments[0] ?? $request->comment }}">
                            <input type="hidden" name="phone"
                                   value="{{ $order->booking->customer->contacts->phones[0]->phoneNumber ?? $request->phone  }}">
                            <input type="hidden" name="email"
                                   value="{{ $order->booking->customer->contacts->emails[0]->emailAddress?? $request->email }}">
                            <button class="more">@lang('main.confirm')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection