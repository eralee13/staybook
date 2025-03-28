<div class="container">
    <span style="font-size: 12px">{{ print_r(session('hotel_search'), true) }}</span>
    <div class="row">
        @if( isset($rooms) )
        <div class="col-12"><h3>{{$hotelLocal[$tmid]['title_en']}} {{$hotelLocal[$tmid]['rating']}}</h3></div>

        @foreach($rooms['Hotels'][0]['RoomTypes'] as $room)
            <div class="col-5">
                <img src="" alt="">
            </div>
            <div class="col-6">
                <h5>{{$room['Name']}}</h5>
                {{-- <span>Время заезда: {{$room['RateInfos'][0]['CancelPolicyInfos'][0]['From']}}</span>
                <span>Время выезда: {{$room['RateInfos'][0]['CancelPolicyInfos'][0]['To']}}</span> --}}
                <ul>
                    <li>Категория: {{$room['BedTypeDesc']}}</li>
                    <li>Количество взрослых: </li>
                    <li>Доступность номеров: {{$room['RateInfos'][0]['Allotment']}}</li>
                    <li>Политика отмены: 
                        @if($room['RateInfos'][0]['Refundable'] == true)
                            Бесплатная отмена действия до {{$room['RateInfos'][0]['CancelPolicyInfos'][0]['To']}}
                            Сумма штрафа {{$room['RateInfos'][0]['CancelPolicyInfos'][0]['Amount']}} {{$room['RateInfos'][0]['CancelPolicyInfos'][0]['CurrencyCode']}}
                        @else
                            Отмена невозможно!
                        @endif
                    </li>
                </ul>

                <div class="btn-wrap">
                    <form action="{{ route('bookingform') }}" method="GET">
                        <input type="hidden" name="hotel" value="{{ $hotelId }}">
                        <input type="hidden" name="tmid" value="{{ $tmid }}">
                        <input type="hidden" name="ratecode" value="{{$room['RateInfos'][0]['RateCode']}}">
                        {{-- <input type="hidden" name="checkIn" value="{{ $hotel['CheckIn'] }}">
                        <input type="hidden" name="checkOut" value="{{ $hotel['CheckOut'] }}"> --}}
                        <input type="hidden" name="total_price" value="{{ $room['RateInfos'][0]['TotalPrice'] }}">
                        <input type="hidden" name="currency" value="{{ $room['RateInfos'][0]['CurrencyCode'] }}">
                        <input type="hidden" name="token" value="{{ 'swt' . Str::random(40) }}">
                        
                        <button type="submit" class="btn btn-primary more" >Забронировать</button>
                    </form>

                </div>
                <br><br>
            </div>
            <div class="col-1">
                <div class="pull-right">
                    <span>{{$room['RateInfos'][0]['TotalPrice']}}</span>
                    <span>{{$room['RateInfos'][0]['CurrencyCode']}}</span>
                </div>
            </div>
        @endforeach

        @endif
    </div>
</div>
