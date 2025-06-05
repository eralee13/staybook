
<div>
    <div class="main-filter">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <form wire:submit.prevent="filterHotels">
                        <div class="row">
                            <div class="col-lg-3 col-md-12">
                                <div class="form-group">
                                    <div class="label stay"><img src="{{route('index')}}/img/marker_out.svg" alt="">
                                    </div>
                                    <select name="city" id="city" wire:model="city">
                                        @php
                                            $cities = \App\Models\City::all();
                                            $now = \Carbon\Carbon::now()->format('Y-m-d');
                                            $tomorrow = \Carbon\Carbon::tomorrow()->format('Y-m-d');
                                        @endphp
                                        @foreach($cities as $city)
                                            <option value="{{ $city->exely_id }}">{{ $city->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg col-6">
                                <div class="form-group">
                                    <div class="label in"><img src="{{route('index')}}/img/marker_in.svg" alt=""> Заезд
                                    </div>
                                    <input type="text" wire:model.lazy="dateRange" id="daterange" class="date">
                                    <input type="hidden" wire:model="checkin" id="start_d" name="start_d" value="{{ $now }}">
                                    <input type="hidden" wire:model="checkout" id="end_d" name="end_d" value="{{ $tomorrow }}">
                                </div>
                            </div>
                            <div class="col-lg col-6">
                                <div id="count_person">
                                    <div class="form-group">
                                        <div class="label guest"><img src="{{route('index')}}/img/user.svg" alt="">
                                        </div>
                                        <input type="text" value="Кол-во гостей">
                                        <div id="count-wrap" class="count-wrap">
                                            <!-- Взрослые -->
                                            <div class="counter count-item">
                                                <label>Взрослые:</label>
                                                <a class="minus" onclick="changeCount('adult', -1)">-</a>
                                                <span id="adult-count">1</span>
                                                <a class="plus" onclick="changeCount('adult', 1)">+</a>
                                                <input wire:model="adult" type="hidden" name="adult" id="adult" value="1">
                                            </div>

                                            <!-- Дети -->
                                            <div class="counter count-item">
                                                <label>Дети:</label>
                                                <a class="minus" onclick="changeCount('child', -1)">-</a>
                                                <span id="child-count">0</span>
                                                <a class="plus" onclick="changeCount('child', 1)">+</a>
                                                <input wire:model="child" type="hidden" name="childAges[]" id="child">
                                            </div>

                                            <!-- Возраст детей -->
                                            <div id="children-ages"></div>

                                            <script>
                                                let adultCount = 0;
                                                let childCount = 0;
                                                const maxAdults = 8;
                                                const maxChildren = 3;

                                                function changeCount(type, delta) {
                                                    if (type === 'adult') {
                                                        adultCount = Math.max(1, Math.min(maxAdults, adultCount + delta));
                                                        document.getElementById('adult-count').innerText = adultCount;
                                                        document.getElementById('adult').value = adultCount;
                                                    } else if (type === 'child') {
                                                        const newCount = childCount + delta;
                                                        if (newCount >= 0 && newCount <= maxChildren) {
                                                            childCount = newCount;
                                                            document.getElementById('child-count').innerText = childCount;
                                                            document.getElementById('child').value = childCount;
                                                            renderChildAgeSelectors();
                                                        }
                                                    }
                                                }

                                                function renderChildAgeSelectors() {
                                                    const container = document.getElementById('children-ages');
                                                    container.innerHTML = '';

                                                    for (let i = 0; i < childCount; i++) {
                                                        const div = document.createElement('div');
                                                        div.className = 'child-block';
                                                                                                        div.innerHTML = `
                                                        <label>Возраст ребёнка ${i + 1}:</label>
                                                        <select name="age${i + 1}" wire:model="childrenage${i + 1}">
                                                            <option value="">-- возраст --</option>
                                                            ${Array.from({length: 19}, (_, age) => `<option value="${age}">${age}</option>`).join('')}
                                                        </select>
                                                        `;
                                                        container.appendChild(div);
                                                    }
                                                }
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <label for="price">Цена от-до</label>
                                    <input type="text" wire:model="pricemin" name="pricemax" wire:model="pricemin" style="width: 45%; float: left; margin-right: 10px;">
                                    <input type="text" wire:model="pricemax" name="pricemax" wire:model="pricemax" style="width: 45%; float: left; margin-right: 10px;" >
                                </div>
                            </div>

                            <div class="col-lg col-6 extra">
                                <div class="form-group">
                                    <div id="filter">
                                        <div class="label filter"><img src="{{route('index')}}/img/setting.svg" alt="">
                                            Фильтры
                                        </div>
                                        <div class="filter-wrap" id="filter-wrap">
                                            <div class="closebtn" id="closebtn"><img
                                                        src="{{route('index')}}/img/close.svg" alt=""></div>
                                            <h5>Фильтры</h5>
                                            <div class="form-group">
                                                <div class="name">Рейтинг</div>
                                                <div class="row justify-content-center">
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="item">
                                                            <input type="radio" name="rating" value="1" wire:model="rating">
                                                            <div class="img">
                                                                <div class="num">1</div>
                                                                <div class="img-wrap">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="item">
                                                            <input type="radio" name="rating" value="2" wire:model="rating">
                                                            <div class="img">
                                                                <div class="num">2</div>
                                                                <div class="img-wrap">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="item">
                                                            <input type="radio" name="rating" value="3" wire:model="rating">
                                                            <div class="img">
                                                                <div class="num">3</div>
                                                                <div class="img-wrap">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-6">
                                                        <div class="item">
                                                            <input type="radio" name="rating" value="4" wire:model="rating">
                                                            <div class="img">
                                                                <div class="num">4</div>
                                                                <div class="img-wrap">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-6">
                                                        <div class="item">
                                                            <input type="radio" name="rating" value="5" wire:model="rating">
                                                            <div class="img">
                                                                <div class="num">5</div>
                                                                <div class="img-wrap">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                    <img src="{{route('index')}}/img/icons/rate.svg"
                                                                         alt="">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="line"></div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <div class="apart-item">
                                                            <img src="{{route('index')}}/img/hotelb.svg" alt="">
                                                            <h6>Отели</h6>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="line"></div>
                                            <div class="name">Прибытие</div>
                                            <div class="form-group" id="income">
                                                <div class="row">
                                                    <div class="col-md-6 col-6">
                                                        <div class="itemm">
                                                            <input type="checkbox" value="early_in" wire:model="early_in">
                                                            <label for="">Ранний заезд</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-6">
                                                        <div class="itemm">
                                                            <input type="checkbox" value="late_out" wire:model="late_out">
                                                            <label for="">Поздний выезд</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="line"></div>
                                            <div class="form-group" id="meal">
                                                <div class="name">Виды питания</div>
                                                <div class="row justify-content-center">
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="RO" wire:model="meal">
                                                            <label for="">RO</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="BF" wire:model="meal">
                                                            <label for="">BF</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="HF" wire:model="meal">
                                                            <label for="">HF</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="FF" wire:model="meal">
                                                            <label for="">FF</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="AI" wire:model="meal">
                                                            <label for="">AI</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{--                                            <button type="submit" class="more">Найти</button>--}}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-lg col-12">
                                <div class="form-group">
                                    <button type="submit" class="more"><img src="{{route('index')}}/img/search.svg" alt=""> Найти
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @if($pricemin || $pricemax)
                        <h3>Цены от: {{$pricemin}} - {{$pricemax}}</h3>
                    @endif
                    @if ($bookingSuccess)
                        <div class="alert alert-info mt-3">{{ $bookingSuccess }}</div>
                    @endif

                    @if( isset($hotelDetail['Hotels']) )
                        <div class="hotel-lists">
                            @foreach($hotelDetail['Hotels'] as $key => $hotel)
                                <div>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h3>{{ $hotel['localData']['title_en'] ?? '' }} &#9733; {{ $hotel['localData']['rating'] ?? '0' }}</h3>
                                            <div class="address">{{ $hotel['localData']['address_en'] ?? ''}}</div>
                                            <a href="{{ route('hotel.rooms', ['hotelId' => $hotel['localData']['id'], 'tmid' => $hotel['HotelCode']]) }}" class="bg-green-500 text-white px-4 py-2 rounded">
                                                Посмотреть номера
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="min_price">от
                                                @foreach ($hotel['RoomTypes'] as $roomType)
                                                    @if (!empty($roomType['RateInfos']))
                                                        @php
                                                            $lowestRate = reset($roomType['RateInfos']);
                                                        @endphp
                                                        {{ $lowestRate['TotalPrice'] }} {{ $lowestRate['CurrencyCode'] }}
                                                    @endif

                                                    {{-- Refundable - <pre>{{ print_r($roomType['RateInfos'], 1) }}</pre> --}}
                                                @endforeach
                                                {{-- @php
                                                $result = array_reduce($hotel['RoomTypes'], function ($carry, $roomType) {
                                                    if( isset($roomType['RateInfos']) ){
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
                                                    }
                                                }, null);
                                                echo $result['price'] .' '. $result['curr'];
                                                @endphp --}}
                                            </div>
                                        </div>
                                        <div class="row gallery">
                                            <div class="col-md-2">
                                                @if( $hotelLocalData[$hotel['HotelCode']]['image'] )
                                                    <a href="/storage/{{ $hotel['localData']['image'] ?? ''}}"><img
                                                                src="/storage/{{ $hotel['localData']['image'] ?? ''}}"
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
                                                        center: [{{$hotel['localData']['lat'] ?? ''}}, {{$hotel['localData']['lng'] ?? ''}}],
                                                        zoom: 14
                                                    });

                                                    DG.marker([{{$hotel['localData']['lat'] ?? ''}}, {{$hotel['localData']['lng'] ?? ''}}], { scrollWheelZoom: false })
                                                        .addTo(map)
                                                        .bindLabel('{{$hotel['localData']['title_en'] ?? ''}}', {
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
    </div>
</div>

