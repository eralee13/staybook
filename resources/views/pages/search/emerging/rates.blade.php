@foreach($rates as $rate)
    @php
            $price = $rate['payment_options']['payment_types'][0]['amount'] ?? 0;
            $totalPrice = number_format( ($price * 0.08) + $price , 2, '.', '');
            $rooms = $request->input('rooms', []);
            $payment = $rate['payment_options']['payment_types'][0];

            if($payment['cancellation_penalties']['free_cancellation_before'] == true){

                $pay_end_date = Carbon\Carbon::createFromDate($payment['cancellation_penalties']['policies'][0]['end_at'])->format('d.m.Y H:i:s');

                $penaltPrice = $payment['cancellation_penalties']['policies'][1]['amount_charge'] ?? 0;
                $penaltyPrice = number_format( ( (float)$penaltPrice  * 0.08) + (float)$penaltPrice, 2, '.', '');

            }else{
                $pay_end_date = '';
                $penaltyPrice = '';
            }
    @endphp

        <div class="tariffs-item">
            @isset($rate)
                <h5>{{ $rate['room_name'] }}</h5>
            @endisset
            
            <div class="item bed">
                <div class="name">{{ $rate['room_data_trans']['bedding_type'] ?? $rate['room_data_trans']['main_room_type'] }}</div>
            </div>

                <div class="item meal">
                        <div class="name">{{ $rate['meal'] ?? 'No breakfast' }}</div>
                </div>
            
            <div class="item cancel">
                <div class="name">Правила отмены:

                    @if($payment['cancellation_penalties']['free_cancellation_before'] == true)
                        Бесплатная отмена действует
                        до {{ $pay_end_date }} UTC {{$hotel->utc}}. <br>
                        Сумма аннуляции: {{ $payment['currency_code'] }} {{ $payment['cancellation_penalties']['policies'][0]['amount_charge'] }}. <br>
                        Иначе штраф: {{ $payment['currency_code'] }} {{ $penaltyPrice }}.
                    @else
                        Невозвратный тариф.
                    @endif
                    
                </div>
            </div>
            <div class="item price">{{ $payment['currency_code'] }} {{ $totalPrice }}</div>
            <div class="nds">Все налоги включены</div>
                
            <div class="btn-wrap">

                <form action="{{ route('order_etg') }}">
                    <input type="hidden" name="arrivalDate" value="{{ $request->arrivalDate }}">
                    <input type="hidden" name="departureDate" value="{{ $request->departureDate }}">
                        @foreach ($rooms as $i => $room)
                            <input type="hidden" name="rooms[{{ $i }}][adults]" value="{{ $room['adults'] }}">
                            
                            @if (isset($room['childAges']))
                                @foreach ($room['childAges'] as $a => $age)
                                    <input type="hidden" name="rooms[{{ $i }}][childAges][]" value="{{ $age }}">
                                @endforeach
                            @endif
                        @endforeach
                    <input type="hidden" name="meal_id" value="{{ $rate['meal'] ?? null }}">
                    <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                    <input type="hidden" name="rate_name" value="{{ $rate['room_name'] }}">
                    <input type="hidden" name="room_name" value="{{ $rate['room_data_trans']['main_name'] }}">
                    <input type="hidden" name="book_hash" value="{{ $rate['book_hash'] }}">
                    <input type="hidden" name="match_hash" value="{{ $rate['match_hash'] }}">
                    <input type="hidden" name="refundable" value="{{ $payment['cancellation_penalties']['free_cancellation_before'] }}">
                    <input type="hidden" name="cancelDate"
                            value="{{ $pay_end_date }}">
                    <input type="hidden" name="cancelPriceAnullation"
                            value="{{ $payment['cancellation_penalties']['policies'][0]['amount_charge'] }}">
                    <input type="hidden" name="cancelPrice"
                            value="{{ $penaltyPrice }}">
                    <input type="hidden" name="price" value="{{ $price }}">
                    <input type="hidden" name="totalPrice" value="{{ $totalPrice }}">
                    <input type="hidden" name="currency" 
                            value="{{ $rate['payment_options']['payment_types'][0]['currency_code'] }}">
                    <input type="hidden" name="utc"  value="{{ $hotel->utc }}">
                    <input type="hidden" name="etgimage"  value="{{ $tmimage }}">

                    <button class="more">Забронировать</button>
                </form>
            </div>
        </div>
    @endforeach