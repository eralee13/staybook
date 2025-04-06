<div class="container">
    <div class="row">
        <div class="col-8">
            <span style="font-size: 12px">{{ print_r(session('hotel_search'), true) }} {{$roomCount}}</span>
            <h1>Confirm</h1>
            <div class="col-12">
                {{-- <h3>{{$hotelLocal[$tmid]['title_en']}} {{$hotelLocal[$tmid]['rating']}}</h3> --}}
                <h5>Ваша поездка</h5>
                <br><br>
            </div>
            <div class="col-12">
                @if ( !isset($allotment) )
                <div class="alert alert-warning">Ошибка: Нет данных о бронировании! Попробуйте заново поискать!</div>
                @else
                <form action="">
                    {{-- <div class="col-12">
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
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <label for="">@lang('main.search-date')</label>
                        <input type="date" wire:model="checkin" id="start_d" name="start_d" />
                        <input type="date" wire:model="checkout" id="end_d" name="end_d" />
                    </div> --}}
                    <div class="row">
                        <div class="col-12 mt-5 mb-5">
                            <h3>{{ $hotelName }}</h3>
                            <div class="row">
                                <div class="col-6">
                                    @if( isset($hotelimg) )
                                        <img src="/storage/{{$hotelimg}}" alt="">
                                    @endif
                                </div>
                                <div class="col-6">

                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    @if ( isset($hotelcity) )<h4>City: {{ $hotelcity }}</h4>@endif
                                    @if ( isset($hoteladdress) )<h4>Address: {{ $hoteladdress }}</h4>@endif
                                </div>
                            </div>
                            @if ( isset($hotellat) && isset($hotellng) )
                            <div class="row">
                                <div class="col-12">
                                    <style>
                                        /* Фикс для серого экрана: задать размер контейнеру */
                                        #map {
                                            width: 100%;
                                            height: 500px;
                                        }
                                    </style>
    
                                    <!-- Подключение стилей Leaflet -->
                                    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    
                                    <div id="map"></div>
    
                                    <!-- Подключение скрипта Leaflet -->
                                    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    
                                    <script>
                                        var lat = {{ old('lat', isset($hotellat) ? $hotellat : '') }};  // Если нет данных, по умолчанию Москва
                                        var lng = {{ old('lng', isset($hotellng) ? $hotellng : '') }};
    
                                        var map = L.map('map').setView([lat, lng], 15);
    
                                        // Добавление слоя OpenStreetMap
                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                        }).addTo(map);
    
                                        var marker = null; // Переменная для хранения последнего маркера
    
                                        // Если есть начальные координаты, устанавливаем маркер
                                        if (lat && lng) {
                                            marker = L.marker([lat, lng]).addTo(map)
                                                .bindPopup('Широта: ' + lat.toFixed(6) + '<br>Долгота: ' + lng.toFixed(6))
                                                .openPopup();
                                        }
    
                                        // // Добавление масштаба
                                        L.control.scale().addTo(map);
    
                                        // Обработчик клика по карте
                                        map.on('click', function(e) {
                                            var lat = e.latlng.lat;  // Широта
                                            var lng = e.latlng.lng;  // Долгота
    
                                            // Удаление старого маркера, если он есть
                                            if (marker) {
                                                map.removeLayer(marker);
                                            }
    
                                            // Обновление значений в полях ввода
                                            // document.getElementById('lat').value = lat.toFixed(6);
                                            // document.getElementById('lng').value = lng.toFixed(6);
    
                                            // Добавление маркера на выбранную точку
                                            if (lat && lng) {
                                                marker = L.marker([lat, lng]).addTo(map)
                                                    .bindPopup('Широта: ' + lat.toFixed(6) + '<br>Долгота: ' + lng.toFixed(6))
                                                    .openPopup();
                                            }
                                        });
                                    </script>
                                </div>
                            </div>
                            @endif
                            <ul class="list-group mt-5">
                                <li class="list-group-item"><strong>Отель:</strong> {{ $hotelName }}</li>
                                <li class="list-group-item"><strong>Номер:</strong> {{ $roomName }}</li>
                                <li class="list-group-item"><strong>Тип номера:</strong> {{ $bedDesc }}</li>
                                <li class="list-group-item"><strong>Количество взрослых:</strong> {{ $adults }}</li>
                                <li class="list-group-item"><strong>Количество детей:</strong> {{ $child }}</li>    
                                <li class="list-group-item"><strong>Доступность номеров:</strong> {{ $allotment }}</li>
                                <li class="list-group-item"><strong>Заезд:</strong> {{ $checkin }} </li>
                                <li class="list-group-item"><strong>Выезд:</strong> {{ $checkout }} </li>
                                <li class="list-group-item"><strong>Цена:</strong> {{ $totalPrice }} {{ $currency }}</li>
                                <li class="list-group-item"><strong>Еда:</strong> {{ $meal }} </li>
                                <li class="list-group-item"><strong>Политика отмены:</strong> 
                                    @if($refundable == true)
                                        Бесплатная отмена действия до {{$cancelDate}}
                                        Сумма штрафа {{$cancelPolicy}}
                                    @else
                                        Отмена невозможно!
                                    @endif
                                </li>
                                <li class="list-group-item"><strong>Токен бронирования:</strong> {{ $token }}</li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <h3>ФИО гостя</h3>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="paxfname">firstname</label>
                                <input wire:model="paxfname" type="text" name="paxfname" required>
                                @error('paxfname') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="paxlname">lastname</label>
                                <input wire:model="paxlname" type="text" name="paxlname" required>
                                 @error('paxlname') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="form-group">
                                <label for="lastname">Email</label>
                                <input wire:model="email" type="email" name="email" required>
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input wire:model="phone" type="tel" name="phone"  id="phone" class="phone" required>
                                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <h3>Контактное лицо</h3>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="firstname">firstname</label>
                                <input wire:model="firstname" type="text" name="firstname" required>
                                @error('firstname') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="lastname">lastname</label>
                                <input wire:model="lastname" type="text" name="lastname" required>
                                 @error('lastname') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <textarea wire:model="specdesc" name="specdesc" id="specdesc" cols="30" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="col-4 offset-4">
                            <button wire:click="confirmBooking" type="button" class="more"  @disabled($orderCreated)>Забронировать</button>
                        </div>
                    </div>
                    <br>    
                        @if ($bookingSuccess)
                            <div class="alert alert-info mt-3">{{ $bookingSuccess }}</div>
                        @endif
                    
                    <br><br>
                </form>
                @endif
            </div>
        </div>
        <div class="col-4">
            <div class="row">
                <div class="col-12 price-detail">
                    <div class="row">
                        <div class="col-2"><img src="" alt=""></div>
                        <div class="col-10">
                            <h6></h6>
                        </div>
                    </div>
                    <hr>
                    <h5>Детализация цены</h5>
                    <div class="row">
                        <div class="col-6"></div>
                        <div class="col-6"></div>
                    </div>
                    <div class="row">
                        <div class="col-6">Налоги (12%)</div>
                        <div class="col-6 pull-right">{{ number_format($nds, 2) }} {{ $currency }}</div>
                    </div>
                    <div class="row">
                        <div class="col-6">Total ()</div>
                        <div class="col-6 pull-right">{{ number_format($totalPrice, 2) }} {{ $currency }}</div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">Total sum : </div>
                        <div class="col-6 pull-right">{{ number_format($totalSum, 2) }} {{ $currency }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>