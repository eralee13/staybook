
<div class="container">
    <div class="row">
        <div class="col-md-12"><h2>Hotel Search Results</h2>
            
            {{-- {{ print_r(session('hotel_search'), true) }} --}}
        
        </div>
            
        <div class="col-md-12">
            <style>.search input{ width: auto; } form label{width: 100%; } form input, form select{width: auto}</style>
            <div class="search homesearch d-xl-block d-lg-block d-none main-filter" id="main-filter">
                <form wire:submit.prevent="filterHotels" class="row">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="city">@lang('main.search-title')</label>
                                <select name="city" id="city" wire:model="city">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="Kyiv">Киев</option>
                                    {{-- @foreach($hotels as $hotel)
                                        <option value="{{ $hotel->id }}" data-address="{{ $hotel->__('address')
                                    }}">{{ $hotel->title_en }} ({{ $hotel->title}})</option>
                                    @endforeach --}}
                                    {{-- @foreach($properties as $property)
                                        <option value="{{ $property->id }}" data-address="{{ $property->contactInfo->address->addressLine }}">{{ $property->name }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                
            
                                {{-- 
                                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                                <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                                <label for="">@lang('main.search-date')</label>
                                <input type="text" id="date_range" wire:model.lazy="dateRange" placeholder="Выберите даты">
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        flatpickr("#date_range", {
                                            mode: "range", // Включаем выбор диапазона
                                            dateFormat: "Y-m-d", // Формат даты (YYYY-MM-DD)
                                            minDate: "today", // Запрещаем выбор прошедших дат
                                            locale: "ru", // Поддержка русского языка
                                            onClose: function(selectedDates, dateStr) {
                                                @this.set('dateRange', dateStr); // Передаем данные в Livewire
                                            }
                                        });
                                    });
                                </script> --}}
                                
                                <label for="">@lang('main.search-date')</label>
                                <input type="text" wire:model="dateRange" id="date_range" class="date" placeholder="Выберите дату">
                                <input type="hidden" wire:model="checkin" id="start_d" name="start_d" />
                                <input type="hidden" wire:model="checkout" id="end_d" name="end_d" />
            
                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        // $('#date_range').daterangepicker({
                                        //     autoUpdateInput: false,
                                        //     locale: {
                                        //         format: 'YYYY-MM-DD',
                                        //         applyLabel: 'Применить',
                                        //         cancelLabel: 'Очистить',
                                        //         fromLabel: 'От',
                                        //         toLabel: 'До',
                                        //         customRangeLabel: 'Выбрать вручную',
                                        //         daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                                        //         monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 
                                        //                      'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                                        //         firstDay: 1
                                        //     }
                                        // });
            
                                
            
                                        $('#date_range').on('apply.daterangepicker', function (ev, picker) {
                                            let dateRange = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
                                            $(this).val(dateRange);
                                            @this.set('dateRange', dateRange); // Передача в Livewire
                                        });
                                
                                        $('#date_range').on('cancel.daterangepicker', function () {
                                            $(this).val('');
                                            @this.set('dateRange', ''); // Очистка значения в Livewire
                                        });
                                    });
                                </script>
                                
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="">@lang('main.search-adult')</label>
                                <select name="adults" id="adults" wire:model="adults">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>    
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                                @error('adults')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group" style="position: relative">
                                <label for="">@lang('main.search-child')</label>
                                <select name="countc" onchange="ageCheck(this);" wire:model="child">
                                    <option value="">@lang('main.choose')</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                                <select name="age1" id="age1" class="age" wire:model="childrenage">
                                    <option value="0">@lang('main.choose')</option>
                                    <option value="1">1 год</option>
                                    <option value="2">2 года</option>
                                    <option value="3">3 года</option>
                                    <option value="4">4 года</option>
                                    <option value="5">5 лет</option>
                                    <option value="6">6 лет</option>
                                    <option value="7">7 лет</option>
                                    <option value="8">8 лет</option>
                                    <option value="9">9 лет</option>
                                    <option value="10">10 лет</option>
                                    <option value="11">11 лет</option>
                                    <option value="12">12 лет</option>
                                    <option value="13">13 лет</option>
                                    <option value="14">14 лет</option>
                                    <option value="15">15 лет</option>
                                    <option value="16">16 лет</option>
                                    <option value="17">17 лет</option>
                                </select>
                                <select name="age2" id="age2" class="age" wire:model="childrenage2">
                                    <option value="0">@lang('main.choose')</option>
                                    <option value="1">1 год</option>
                                    <option value="2">2 года</option>
                                    <option value="3">3 года</option>
                                    <option value="4">4 года</option>
                                    <option value="5">5 лет</option>
                                    <option value="6">6 лет</option>
                                    <option value="7">7 лет</option>
                                    <option value="8">8 лет</option>
                                    <option value="9">9 лет</option>
                                    <option value="10">10 лет</option>
                                    <option value="11">11 лет</option>
                                    <option value="12">12 лет</option>
                                    <option value="13">13 лет</option>
                                    <option value="14">14 лет</option>
                                    <option value="15">15 лет</option>
                                    <option value="16">16 лет</option>
                                    <option value="17">17 лет</option>
                                </select>
                                <select name="age3" id="age3" class="age" wire:model="childrenage3">
                                    <option value="0">@lang('main.choose')</option>
                                    <option value="1">1 год</option>
                                    <option value="2">2 года</option>
                                    <option value="3">3 года</option>
                                    <option value="4">4 года</option>
                                    <option value="5">5 лет</option>
                                    <option value="6">6 лет</option>
                                    <option value="7">7 лет</option>
                                    <option value="8">8 лет</option>
                                    <option value="9">9 лет</option>
                                    <option value="10">10 лет</option>
                                    <option value="11">11 лет</option>
                                    <option value="12">12 лет</option>
                                    <option value="13">13 лет</option>
                                    <option value="14">14 лет</option>
                                    <option value="15">15 лет</option>
                                    <option value="16">16 лет</option>
                                    <option value="17">17 лет</option>
                                </select>
                                <script>
                                    function ageCheck(that) {
                                        if (that.value == 1) {
                                            document.getElementById("age1").style.display = "inline-block";
                                            document.getElementById("age2").style.display = "none";
                                            document.getElementById("age3").style.display = "none";
                                        }
                                        else if (that.value == 2) {
                                            document.getElementById("age1").style.display = "inline-block";
                                            document.getElementById("age2").style.display = "inline-block";
                                            document.getElementById("age3").style.display = "none";
                                        }
                                        else if (that.value == 3) {
                                            document.getElementById("age1").style.display = "inline-block";
                                            document.getElementById("age2").style.display = "inline-block";
                                            document.getElementById("age3").style.display = "inline-block";
                                        }
                                        else {
                                            document.getElementById("age1").style.display = "none";
                                            document.getElementById("age2").style.display = "none";
                                            document.getElementById("age3").style.display = "none";
                                        }
                                    }
                                </script>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="">@lang('main.search-roomCount')</label>
                                <select name="roomCount" id="roomCount" wire:model="roomCount">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                    </div>
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
                                <input type="text" wire:model="pricemin" name="pricemax" wire:model="pricemin" style="width: 45%; float: left; margin-right: 10px;">
                                <input type="text" wire:model="pricemax" name="pricemax" wire:model="pricemax" style="width: 45%; float: left; margin-right: 10px;" >
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
                                    <option value="1">BF</option>
                                    <option value="2">RO</option>
                                    <option value="3">HF</option>
                                    <option value="4">AI</option>
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
                                <button type="submit" class="more" >@lang('main.search')</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <br>    {{$pricemin}} - {{$pricemax}}
            @if ($bookingSuccess)
                <div class="alert alert-info mt-3">{{ $bookingSuccess }}</div>
            @endif
        </div>
        <div class="col-md-6">
            @if( isset($hotelDetail['Hotels']) )
                <div class="hotel-lists">
                    @foreach($hotelDetail['Hotels'] as $key => $hotel)
                        <div>
                            <div class="row">
                                <div class="col-md-8">
                                    <h3>{{ $hotel['localData']['title_en'] ?? '' }} &#9733; {{ $hotel['localData']['rating'] ?? '' }}</h3>
                                    <div class="address">{{ $hotel['localData']['address_en'] ?? ''}}</div>
                                    <a href="{{ route('hotel.rooms', ['hotelId' => $hotel['localData']['id'], 'tmid' => $hotel['HotelCode']]) }}" class="btn btn-success px-4 py-2 rounded">
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
        <div class="col-6"></div>
    </div>
</div>