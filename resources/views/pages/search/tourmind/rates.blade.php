@php
 $meals = \App\Models\Meal::pluck('title', 'id');
//  $sortedRates = collect($tmrates)->sortBy('TotalPrice')->values();
@endphp
@foreach($tmrates as $rate)
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
                        
                        Бесплатная отмена действует
                        до {{ $rate->CancelPolicyInfos[0]->From }} UTC {{$hotel->utc}}. 
                        Сумма аннуляции: {{ $rate->CancelPolicyInfos[0]->CurrencyCode }} {{ $rate->CancelPolicyInfos[0]->Amount }} 
                    @else
                        Невозвратный тариф.
                    @endif
                    
                </div>
            </div>
            <div class="item price">{{ $rate->CurrencyCode }} {{ $rate->TotalPrice }}</div>
            <div class="nds">Все налоги включены</div>
            
            <div class="btn-wrap">
                <form action="{{ route('order', 1) }}">
                    <input type="hidden" name="arrivalDate"
                            value="{{ $request->arrivalDate }}">
                    <input type="hidden" name="departureDate"
                            value="{{ $request->departureDate }}">
                    <input type="hidden" name="adult"
                            value="{{ $request->adult }}">
                    <input type="hidden" name="child"
                            value="{{ $request->child }}">
                    <input type="hidden" name="childAges[]"
                            value="{{ implode(',', $request->childAges) }}">
                    <input type="hidden" name="meal_id"
                            value="{{ $meals[$rate->MealInfo->MealType] ?? null }}">
                    <input type="hidden" name="hotel_id"
                            value="{{ $hotel->id }}">
                    <input type="hidden" name="rate_name"
                            value="{{ $rate->bedTypeDesc }}">
                    <input type="hidden" name="room_name"
                            value="{{ $rate->Name }}">
                    <input type="hidden" name="RoomTypeCode"
                            value="{{ $tmroom->RoomTypeCode }}">
                    <input type="hidden" name="rate_code"
                            value="{{ $rate->RateCode }}">
                    <input type="hidden" name="refundable"
                            value="{{ $rate->Refundable }}">
                    <input type="hidden" name="cancelDate"
                            value="{{ isset($rate->CancelPolicyInfos[0]->From) ? $rate->CancelPolicyInfos[0]->From : '' }}">
                    <input type="hidden" name="cancelPrice"
                            value="{{ isset($rate->CancelPolicyInfos[0]->Amount) ? $rate->CancelPolicyInfos[0]->Amount : '' }}">
                    <input type="hidden" name="price"
                            value="{{ $rate->TotalPrice }}">
                    <input type="hidden" name="currency" 
                            value="{{ $rate->CurrencyCode }}">
                    <input type="hidden" name="utc" 
                            value="{{ $hotel->utc }}">
                    <input type="hidden" name="tmimage" 
                            value="{{ $tmimage }}">
                    <input type="hidden" name="api_name" 
                            value="tourmind">

                    <button class="more">Забронировать</button>
                </form>
            </div>
        </div>
    @endforeach