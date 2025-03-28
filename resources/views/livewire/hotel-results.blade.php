
<div class="container">
    <div class="row">
        <div class="col-md-12"><h2>Hotel Search Results</h2></div>
            {{$checkin}} - {{$checkout}}
            {{ print_r(session('hotel_search'), true) }}
        <div class="col-md-12">
            <style>.search input{ width: auto; }</style>
            <div class="search homesearch d-xl-block d-lg-block d-none main-filter" id="main-filter">
                <form wire:submit.prevent="" class="row">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="accommodation_type">Размещение</label>
                                <select name="accommodation_type"  wire:model="accommodation_type">
                                    <option value="hotel">Hotel</option>
                                    <option value="villa">Villa</option>
                                    <option value="cottage">Cottage</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="resort">Resort</option>
                                    <option value="hostel">Hostel</option>
                                    <option value="guesthouse">Guesthouse</option>
                                    <option value="bungalow">Bungalow</option>
                                    <option value="motel">Motel</option>
                                    <option value="capsule">Capsule Hotel</option>
                                    <option value="chalet">Chalet</option>
                                    <option value="lodging">Lodging</option>
                                    <option value="inn">Inn</option>
                                    <option value="houseboat">Houseboat</option>
                                    <option value="glamping">Glamping</option>
                                </select>
                                
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="price">Цена от-до</label>
                                <input type="text" wire:model="pricemin" name="pricemax" wire:model="pricemin">
                                <input type="text" wire:model="pricemax" name="pricemax" wire:model="pricemax">
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="rating">@lang('main.search-rating')</label>
                                <select name="rating" id="rating" wire:model="rating">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
            
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="food">@lang('main.search-include')</label>
                                <select id="food_id" name="food_id" wire:model="food">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="RO">RO</option>
                                    <option value="BF">BF</option>
                                    <option value="HF">HF</option>
                                    <option value="AI">AI</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="check">@lang('main.search-early')</label>
                                <select name="early_in" id="early_in" wire:model="early_in">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="06:00">06:00</option>
                                    <option value="07:00">07:00</option>
                                    <option value="08:00">08:00</option>
                                    <option value="09:00">09:00</option>
                                    <option value="10:00">10:00</option>
                                    <option value="11:00">11:00</option>
                                    <option value="12:00">12:00</option>
                                    <option value="13:00">13:00</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="check">@lang('main.search-late')</label>
                                <select name="early_out" id="early_out" wire:model="early_out">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="15:00">15:00</option>
                                    <option value="16:00">16:00</option>
                                    <option value="17:00">17:00</option>
                                    <option value="18:00">18:00</option>
                                    <option value="19:00">19:00</option>
                                    <option value="20:00">20:00</option>
                                    <option value="21:00">21:00</option>
                                    <option value="22:00">22:00</option>
                                    <option value="23:00">23:00</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group check">
                                <input type="checkbox" id="cancelled" name="cancelled" wire:model="cancelled">
                                <label for="cancelled">@lang('main.cancelled')</label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group check">
                                <input type="checkbox" name="extra_place" id="extra_place" wire:model="extra_place">
                                <label for="extra_place">@lang('main.search-extra')</label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <button type="submit" class="more">@lang('main.search')</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            @if(isset($hotelDetail['Hotels']) && $hotelDetail['Hotels'])
                <div class="hotel-lists">
                    @foreach($hotelDetail['Hotels'] as $key => $hotel)
                        <div>
                            <div class="row">
                                <div class="col-md-8">
                                    <h3>{{ $hotelLocalData[$hotel['HotelCode']]['title_en'] ?? '' }}</h3>
                                    <div class="address">{{ $hotelLocalData[$hotel['HotelCode']]['address_en'] ?? ''}}</div>
                                    <a href="{{ route('hotel.rooms', ['hotelId' => $hotelLocalData[$hotel['HotelCode']]['id'], 'tmid' => $hotel['HotelCode']]) }}" class="bg-green-500 text-white px-4 py-2 rounded">
                                        Посмотреть номера
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <div class="min_price">от
                                        @php
                                        $result = array_reduce($hotel['RoomTypes'], function ($carry, $roomType) {
                                            foreach ($roomType['RateInfos'] as $rateInfo) {
                                                if ($carry === null || $rateInfo['TotalPrice'] < $carry['price']) {
                                                    $carry = [
                                                        'price' => $rateInfo['TotalPrice'],
                                                        'curr' => $rateInfo['CurrencyCode'],
                                                        'room_name' => $roomType['Name']
                                                        
                                                    ];
                                                }
                                            }
                                            return $carry;
                                        }, null);
                                        echo $result['price'] .' '. $result['curr'];
                                        @endphp
                                    </div>
                                </div>
                                <div class="row gallery">
                                    <div class="col-md-2">
                                        @if( $hotelLocalData[$hotel['HotelCode']]['image'] )
                                            <a href="/storage/{{ $hotelLocalData[$hotel['HotelCode']]['image'] ?? ''}}"><img
                                                src="/storage/{{ $hotelLocalData[$hotel['HotelCode']]['image'] ?? ''}}"
                                                alt=""></a>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        {{-- <div class="servlisting">
                                            <h5>Удобства:</h5>
                                            <div class="row">
                                                @if($hotelLocalData[$hotel['HotelCode']]['amenity']['services'])

                                                    @php
                                                        $services = explode(',', $hotelLocalData[$hotel['HotelCode']]['amenity']['services']);
                                                    @endphp
                                                
                                                    @foreach($services as $service)

                                                    <div class="col-md-4">
                                                        <div class="item">
                                                        <i class="fa-regular fa-check"></i> {{$service}}
                                                        </div>
                                                    </div>

                                                    @endforeach

                                                @endif
                                            </div>
                                            <p>Описание</p>
                                            <p>{{ $hotelLocalData[$hotel['HotelCode']]['description_en'] ?? ''}} <strong>{{ $hotelLocalData[$hotel['HotelCode']]['rating'] ?? ''}}</strong>.</p>
                                            <div class="phone"><span>Номер телефона:</span> <a href="tel:{{$hotelLocalData[$hotel['HotelCode']]['rating'] ?? ''}}">{{$hotelLocalData[$hotel['HotelCode']]['phone'] ?? ''}}
                                                    111</a></div>
                                            @if($hotelLocalData[$hotel['HotelCode']]['email'])
                                            <div class="email"><span>Email:</span> <a href="mailto:{{$hotelLocalData[$hotel['HotelCode']]['email'] ?? '#'}}">{{$hotelLocalData[$hotel['HotelCode']]['email'] ?? ''}}</a>
                                            </div>
                                            @endif
                                        </div> --}}
                                    </div>
                                </div>
                                <div class="row">
                                    {{-- <h2>Расположение</h2>
                                    <script src="https://maps.api.2gis.ru/2.0/loader.js"></script>
                                    <div id="map" style="width: 100%; height: 200px;"></div>
                                    <script>
                                        DG.then(function () {
                                            var map = DG.map('map', {
                                                center: [{{$hotelLocalData[$hotel['HotelCode']]['lat'] ?? ''}}, {{$hotelLocalData[$hotel['HotelCode']]['lng'] ?? ''}}],
                                                zoom: 14
                                            });
                    
                                            DG.marker([{{$hotelLocalData[$hotel['HotelCode']]['lat'] ?? ''}}, {{$hotelLocalData[$hotel['HotelCode']]['lng'] ?? ''}}], { scrollWheelZoom: false })
                                                .addTo(map)
                                                .bindLabel('{{$hotelLocalData[$hotel['HotelCode']]['title_en'] ?? ''}}', {
                                                    static: true
                                                });
                                        });
                                    </script> --}}
                                </div>
                            </div>
                        </div>
                        <hr>
                    @endforeach
                </div>
            @else
                <h3>По вашему запросу отелей не найдено!</h3>
            @endif
        </div>
    </div>
</div>