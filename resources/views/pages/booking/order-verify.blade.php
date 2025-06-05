@php use Illuminate\Support\Facades\Http; @endphp
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
                        $cancelPossible = \App\Models\CancellationRule::where('id', $rate->cancellation_rule_id)->firstOrFail();
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
                            <td>@lang('main.count_adult'):</td>
                            <td>{{ $room->guestCount->adultCount ?? $request->adult }}</td>
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
                            @if($cancelPossible->is_refundable == true)
                                <td>
                                    @if(now()->lte($request->cancelDate))
                                       @lang('main.free_cancellation') {{ $request->cancelDate }} (UTC {{ $hotel_utc }}
                                        ).
                                    @endif
                                    @lang('main.cancellation_amount'): {{ $request->cancelPrice }} {{ $order->booking->currencyCode ?? '$' }}</td>
                            @else
                                <td>@lang('main.cancellation_is_not_avaialble'). @lang('main.cancellation_amount'): {{ $cancelPossible->penaltyAmount ?? $request->cancelPrice }} {{ $order->booking->currencyCode ?? '$' }}</td>
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
                            <td>{{ $order->booking->bookingComments[0] ?? $request->comment }}</td>
                        </tr>
                    </table>

                    <div class="btn-wrap">
                        <form action="{{ route('book_reserve') }}" method="get">
                            <input type="hidden" name="propertyId"
                                   value="{{ $order->booking->propertyId ?? $request->propertyId }}">
                            <input type="hidden" name="total"
                                   value="{{ $order->booking->total->priceBeforeTax ?? $request->price }}">
                            <input type="hidden" name="cancellation_id" value="{{ $request->cancellation_id }}">
                            <input type="hidden" name="cancelPrice" value="{{ $request->cancelPrice }}">
                            <input type="hidden" name="arrivalDate"
                                   value="{{ $order->booking->roomStays[0]->stayDates->arrivalDateTime ?? $request->arrivalDate }}">
                            <input type="hidden" name="departureDate"
                                   value="{{ $order->booking->roomStays[0]->stayDates->departureDateTime ?? $request->departureDate }}">
                            <input type="hidden" name="ratePlanId"
                                   value="{{ $order->booking->roomStays[0]->ratePlan->id ?? $request->rate_id }}">
                            <input type="hidden" name="roomTypeId"
                                   value="{{ $order->booking->roomStays[0]->roomType->id ?? $request->room_id }}">
                            <input type="hidden" name="roomCode"
                                   value="{{ $order->booking->roomStays[0]->roomType->placements[0]->code ?? '' }}">
                            <input type="hidden" name="firstName"
                                   value="{{ $order->booking->roomStays[0]->guests[0]->firstName ?? $request->name }}">
                            <input type="hidden" name="lastName"
                                   value="{{ $order->booking->roomStays[0]->guests[0]->lastName ?? $request->name }}">
                            <input type="hidden" name="sex" value="Male">
                            <input type="hidden" name="citizenship" value="KGS">
                            {{--                            <input type="hidden" name="placements"--}}
                            {{--                                   value="{{ json_encode($order->booking->roomStays[0]->roomType->placements) ?? '' }}">--}}
                            <input type="hidden" name="adultCount"
                                   value="{{ $order->booking->roomStays[0]->guestCount->adultCount ?? $request->adult }}">
                            <input type="hidden" name="childAges[]"
                                   value="{{ implode(', ', $request->childAges ?? []) }}">

                            {{--                            <input type="hidden" name="createBookingToken"--}}
                            {{--                                   value="{{ $order->booking->createBookingToken ?? '' }}">--}}
                            {{--                            <input type="hidden" name="checkSum"--}}
                            {{--                                   value="{{ $order->booking->roomStays[0]->checksum ?? '' }}">--}}
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

    <style>
        .page #map {
            margin-top: 20px;
        }

        .page i {
            color: darkblue;
        }

        .page form {
            margin-top: 50px;
        }

        .page form button {
            width: auto;
            padding: 10px 30px;
            margin-left: 10px;
        }
    </style>

@endsection
