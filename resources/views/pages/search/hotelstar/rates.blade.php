@foreach($rates as $rate)
    @php
        $coef = config('app.main_coef');    
        $price = $rate['price'] ?? 0;
        $totalPrice = number_format( ($price / $coef ) , 2, '.', '');
        $rooms = $request->input('rooms', []);
        $payment = $rate['cancel_conditions'];

        if( !empty($payment['free_cancellation_before'])){

            $pay_end_date = Carbon\Carbon::createFromDate($payment['free_cancellation_before'])->format('d.m.Y H:i:s');

        }else{
            $pay_end_date = '';
        }

            // находим free
            $fullPolicy = collect($payment['policies'])
                            ->where('type', '!=', 'free')
                            ->first();

            // берем сумму
            $fullAmount = $fullPolicy['penalty']['amount'] ?? 0;
            $penaltyCurr = $fullPolicy['penalty']['currency'] ?? $rate['currency'];
            $penaltPrice = $fullAmount;
            $penaltyPrice = number_format( ( (float)$penaltPrice  / $coef), 2, '.', '');

        $toCurrency = strtoupper($fxBase ?? 'USD');

        $symbols = [
            'USD' => '$',
            'RUB' => '₽',
            'KGS' => 'сом',
            'UZS' => 'сўм',
        ];

        $converted = app(\App\Services\FXService::class)->convert($totalPrice, $rate['currency'], $fxBase);
        $symbol = $symbols[$toCurrency] ?? $toCurrency;
        $cancelConverted = app(\App\Services\FXService::class)->convert($penaltyPrice, $penaltyCurr, $fxBase);
        $meal = collect($rate['meals'])->firstWhere('included', true);
    @endphp
    
        <div class="tariffs-item">
            @isset($rate)
                <h5>{{ $rate['room_name'] }}</h5>
            @endisset
            
            <div class="item bed">
                <div class="name">
                    {{-- {{ $rate['room_data_trans']['bedding_type'] ?? $rate['room_data_trans']['main_room_type'] }} --}}
                </div>
            </div>

                <div class="item meal">
                    <div class="name">
                        @if( isset($rate['meals'][0]['name']) )
                            @foreach($rate['meals'] as $mealOption)
                                @if($mealOption['included'] == true)
                                    {{ $mealOption['name'] }}
                                @endif
                            @endforeach
                        @else
                            {{ __('main.no_meal') }}
                        @endif
                    </div>
                </div>
            
            <div class="item cancel">
                <div class="name">@lang('main.cancellation_policy'):

                    @if( !empty($payment['free_cancellation_before']) )
                        @lang('main.free_cancellation') {{ $pay_end_date }} UTC {{$hotel->utc}}. <br>
                        @lang('main.cancellation_amount_tm'):  {{ round($cancelConverted ) }} {{ $symbol }}
                    @else
                        @lang('main.non_refundable')
                    @endif
                    
                </div>
            </div>
            <div class="item price"> {{ round($converted) }} {{ $symbol }}</div>
            <div class="nds">@lang('main.all_taxes_included')</div>
            <div class="nds_not_included" style="color: red; font-size: 13px;">
                @lang('main.all_taxes_excluded')</div>
                <span style="font-size: 13px;">@lang('main.pay_at_hotel')</span><br>

                    {{-- @foreach($payment['tax_data']['taxes'] as $tax)
                        @if($tax['included_by_supplier'] == false)
                            <span style="font-size: 13px;"><strong>
                            проверка: если ключ это строка и есть перевод
                            @if(is_string($tax['name']) && Lang::has('main.'.$tax['name']))
                                @lang('main.'.$tax['name'])
                            @else
                                {{ $tax['name'] }}
                            @endif : </strong>
                            {{ $tax['amount']}} {{ $tax['currency_code'] }}</span><br>
                        @endif
                    @endforeach --}}

            <div class="btn-wrap">
                <form action="{{ route('order_hs') }}">
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
                    <input type="hidden" name="meal_id" value="{{ $meal['name'] ?? null }}">
                    <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                    <input type="hidden" name="room_id" value="{{ $rate['room_id'] }}">
                    <input type="hidden" name="room_name" value="{{ $rate['room_name'] }}">
                    <input type="hidden" name="rate_name" value="{{ $rate['room_name'] }}">
                    <input type="hidden" name="city" value="{{ $request->city }}">
                    
                    {{-- <input type="hidden" name="bedTypeDesc" value="{{ $rate['room_data_trans']['bedding_type'] }}"> --}}
                    <input type="hidden" name="hash" value="{{ $rate['hash'] }}">
                    <input type="hidden" name="provider_id" value="{{ $rate['provider_id'] }}">
                    {{-- <input type="hidden" name="match_hash" value="{{ $rate['match_hash'] }}"> --}}
                    <input type="hidden" name="refundable" value="{{ $payment['free_cancellation_before'] }}">
                    <input type="hidden" name="cancelDate"
                            value="{{ $pay_end_date }}">
                    <input type="hidden" name="cancelPriceAnullation"
                            value="{{ $penaltyPrice }}">
                    <input type="hidden" name="cancelPrice"
                            value="{{ $cancelConverted }}">
                    <input type="hidden" name="price" value="{{ $price }}">
                    <input type="hidden" name="totalPrice" value="{{ $converted }}">
                    <input type="hidden" name="currency" 
                            value="{{ $fxBase ?? $rate['currency'] }}">
                    <input type="hidden" name="utc"  value="{{ $hotel->utc }}">
                    <input type="hidden" name="etgimage"  value="{{ $tmimage }}">
                    <input type="hidden" name="increase_percent">
            
                    <button class="more" id="order">@lang('main.book')</button>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        // document.getElementById('order').addEventListener('click', function() {
            localStorage.removeItem('booking_hs_secondsLeft'); // Очистить данные
        // });
    </script>