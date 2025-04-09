<div class="container">
    {{-- <span style="font-size: 12px">{{ print_r(session('hotel_search'), true) }}</span> --}}
    <div class="row">
        <br>    
            @if ($bookingSuccess)
                <div class="alert alert-info mt-3">{{ $bookingSuccess }}</div>
            @endif

        @if( isset($rooms) )
        <div class="col-12"><br><h3>{{$hotelLocal[$tmid]['title_en']}} 	&#9733; {{$hotelLocal[$tmid]['rating']}}</h3></div>

        @foreach($rooms['Hotels'][0]['RoomTypes'] as $room)
            <div class="col-5">
                <div class="row">
                    @if( isset($images) )
                        @foreach(\Illuminate\Support\Arr::random($images, min(3, count($images))) as $image)
                        <div class="col-4">
                            <img src="/storage/{{$image}}" alt="">
                        </div>
                        @endforeach
                    @endif
                </div>
                
            </div>
            <div class="col-6">
                <h5>Номер: {{$room['Name']}}</h5>
                {{-- <span>Время заезда: {{$room['RateInfos'][0]['CancelPolicyInfos'][0]['From']}}</span>
                <span>Время выезда: {{$room['RateInfos'][0]['CancelPolicyInfos'][0]['To']}}</span> --}}
                <ul>
                    <li>Тип номера: {{$room['BedTypeDesc']}}</li>
                    <li>Количество взрослых: {{ session('hotel_search')['adults'] ?? '' }}</li>
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
                        <input type="hidden" name="hotelId" value="{{$hotelId}}">
                        <input type="hidden" name="tmid" value="{{$tmid}}">
                        <input type="hidden" name="hotelName" value="{{$hotelLocal[$tmid]['title_en']}} &#9733; {{$hotelLocal[$tmid]['rating']}}">
                        <input type="hidden" name="roomName" value="{{$room['Name']}}">
                        <input type="hidden" name="RoomTypeCode" value="{{$room['RoomTypeCode']}}">
                        <input type="hidden" name="bedDesc" value="{{$room['BedTypeDesc']}}">
                        <input type="hidden" name="allotment" value="{{$room['RateInfos'][0]['Allotment'] ?? 0}}">
                        <input type="hidden" name="Refundable" value="{{$room['RateInfos'][0]['Refundable'] ?? ''}}">
                        <input type="hidden" name="cancelPolicy" value="{{$room['RateInfos'][0]['CancelPolicyInfos'][0]['Amount'] ?? ''}} {{$room['RateInfos'][0]['CancelPolicyInfos'][0]['CurrencyCode'] ?? ''}}">
                        <input type="hidden" name="cancelDate" id="cancelDate" value="{{$room['RateInfos'][0]['CancelPolicyInfos'][0]['To'] ?? ''}}">
                        <input type="hidden" name="ratecode" value="{{$room['RateInfos'][0]['RateCode']}}">
                        <input type="hidden" name="totalPrice" value="{{ $room['RateInfos'][0]['TotalPrice'] }}">
                        <input type="hidden" name="currency" value="{{ $room['RateInfos'][0]['CurrencyCode'] }}">
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        <button type="submit" class="btn btn-primary more" >Забронировать</button>
                    </form>

                </div>
                <br><br>
            </div>
            <div class="col-1">
                <div class="pull-right">
                    <span>{{$room['RateInfos'][0]['TotalPrice'] }}</span>
                    <span>{{$room['RateInfos'][0]['CurrencyCode']}}</span>
                </div>
            </div>
        @endforeach

        @endif
    </div>
</div>
