@foreach($tmrates as $rate)
    @php
            $price = $rate->TotalPrice ?? 0;
            $totalPrice = number_format( ($price * 0.08) + $price , 2, '.', '');
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

                <div class="name">Правила отмены:
                    @if($rate->Refundable == true)
                        {{-- $rate->CancelPolicyInfos[0]->Amount --}}
                        Бесплатная отмена действует
                        до {{ $rate->CancelPolicyInfos[0]->From }} UTC {{$hotel->utc}}. 
                        Сумма аннуляции: {{ $rate->CancelPolicyInfos[0]->CurrencyCode }} {{ $totalPrice }} 
                    @else
                        Невозвратный тариф.
                    @endif
                    
                </div>
            </div>
            <div class="item price">{{ $rate->CurrencyCode }} {{ $totalPrice }}</div>
            <div class="nds">Все налоги включены</div>
                
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

                    <button class="more">Забронировать</button>
                </form>
            </div>
        </div>
    @endforeach