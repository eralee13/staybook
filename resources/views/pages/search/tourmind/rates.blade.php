@foreach($tmrates as $rate)
    @php
            $price = $rate->TotalPrice ?? 0;
            $totalPrice = number_format( ($price / config('services.main.coef')) , 2, '.', '');
            $basePrice = $totalPrice;

            $toCurrency = strtoupper($fxBase ?? 'USD');

            $symbols = [
                'USD' => '$',
                'RUB' => '₽',
                'KGS' => 'сом',
                'UZS' => 'сўм',
            ];

            $converted = app(\App\Services\FXService::class)->convert($basePrice, $rate->CurrencyCode, $fxBase);
            $symbol = $symbols[$toCurrency] ?? $toCurrency;

            $cancelCurrency = $rate->CancelPolicyInfos[0]->CurrencyCode;
            $cancelAmount = $rate->CancelPolicyInfos[0]->Amount;
            $cancelAmount = number_format(($cancelAmount / config('services.main.coef')), 2, '.', '');
            $cancelConverted = app(\App\Services\FXService::class)->convert($cancelAmount, $rate->CurrencyCode, $fxBase);
            $cancelSymbol = $symbols[$cancelCurrency] ?? $toCurrency;
        @endphp

        <div class="tariffs-item">
           
            @isset($rate)
                <h5>{{ $rate->Name }}</h5>
            @endisset
            
            <div class="item bed">
                <div class="name">{{ $rate->bedTypeDesc }}</div>
            </div>

                <div class="item meal">
                        <div class="name">{{ $meals[$rate->MealInfo->MealType] ?? 'No breakfast' }}</div>
                </div>
            
            <div class="item cancel">

                <div class="name">@lang('main.cancellation_policy'):
                    @if($rate->Refundable == true)
                        {{-- $rate->CancelPolicyInfos[0]->Amount --}}
                        @lang('main.free_cancellation') {{ $rate->CancelPolicyInfos[0]->From }} UTC {{$hotel->utc}}. 
                        @lang('main.cancellation_amount_tm'): {{ round($cancelConverted) }} {{ $cancelSymbol }} 
                    @else
                        @lang('main.non_refundable')
                    @endif
                    
                </div>
            </div>
            <div class="item price">
                    {{ round($converted) }} {{ $symbol }}
            </div>
            <div class="nds">@lang('main.all_taxes_included')</div>
                
            <div class="btn-wrap">

                <form action="{{ route('order_tm') }}">
                    <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                    <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                    <input type="hidden" name="adult" value="{{ $request->adult }}">
                    <input type="hidden" name="child" value="{{ $request->child }}">
                    <input type="hidden" name="roomCount" value="{{ $request->roomCount }}">
                        @if( isset($request->childAges))
                            @foreach($request->childAges as $age)
                                <input type="hidden" name="childAges[]"
                                    value="{{ $age }}">
                            @endforeach
                        @endif
                    <input type="hidden" name="meal_id" value="{{ $meals[$rate->MealInfo->MealType] ?? null }}">
                    <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                    <input type="hidden" name="rate_name" value="{{ $rate->bedTypeDesc }}">
                    <input type="hidden" name="room_name" value="{{ $rate->Name }}">
                    <input type="hidden" name="RoomTypeCode" value="{{ $tmroom->RoomTypeCode }}">
                    <input type="hidden" name="rate_code" value="{{ $rate->RateCode }}">
                    <input type="hidden" name="refundable" value="{{ $rate->Refundable }}">
                    <input type="hidden" name="cancelDate"
                            value="{{ isset($rate->CancelPolicyInfos[0]->From) ? $rate->CancelPolicyInfos[0]->From : '' }}">
                    <input type="hidden" name="cancelPrice"
                            value="{{ isset($rate->CancelPolicyInfos[0]->Amount) ? $rate->CancelPolicyInfos[0]->Amount : '' }}">
                    <input type="hidden" name="price" value="{{ $rate->TotalPrice }}">
                    <input type="hidden" name="totalPrice" value="{{ $totalPrice }}">
                    <input type="hidden" name="currency" 
                            value="{{ $rate->CurrencyCode }}">
                    <input type="hidden" name="utc"  value="{{ $hotel->utc }}">
                    <input type="hidden" name="tmimage"  value="{{ $tmimage }}">
                    {{-- <input type="hidden" name="api_name" 
                            value="tourmind"> --}}

                    <button class="more" id="order">@lang('main.book')</button>
                </form>
            </div>
        </div>
    @endforeach