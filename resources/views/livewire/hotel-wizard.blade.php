@section('title', 'Создание продукта')

    <div class="page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <h1>Добро пожаловать в Stay Book</h1>
                    <p>Расскажите нам о вашей недвижимости</p>
                    @if (session()->has('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                        {{$step}} - {{ $hotel_id }}

                        @if($hotelError)
                            <div class="alert alert-danger">{{ $hotelError}}</div>
                        @endif

                        @if($hotelSuccess)
                            <div class="alert alert-success">{{ $hotelSuccess}}</div>
                        @endif
                                 
                        @if ($step == 1)

                        <div class="row">
                            <div class="col-md-6">
                                
                                <div class="mb-3">
                                    <label for=""class="form-label">@lang('main.hotel_title')</label>
                                    <input type="text" wire:model.defer="title" class="form-control">

                                    @error('title')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="">@lang('main.hotel_title_en')</label>
                                    <input type="text" wire:model.defer="title_en" class="form-control">

                                    @error('title_en')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="type">Тип недвижимости</label>
                                    <select wire:model.defer="type" class="form-control">
                                        <option value="">Выбрать</option>
                                        <option value="Отель" @if(old('type') == 'Отель') selected @endif>Отель</option>
                                        <option value="Апарт отель" @if(old('type') == 'Апарт отель') selected @endif>
                                            Апарт отель
                                        </option>
                                        <option value="Гостевой дом" @if(old('type') == 'Гостевой дом') selected @endif>
                                            Гостевой дом
                                        </option>
                                    </select>

                                    @error('type')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="city">Город, Страна</label>
                                    <input wire:model.defer="city" type="text" 
                                        class="form-control"/>

                                    @error('type')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address">Адрес</label>
                                    <input wire:model.defer="address" type="text" class="form-control" name="address"/>

                                    @error('type')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address">Адрес EN</label>
                                    <input wire:model.defer="address_en" type="text" class="form-control" name="address_en"/>

                                    @error('type')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="row">

                                    <div class="col-md-6">

                                        <div class="mb-3">
                                            <label for="lat">@lang('main.lat')</label>
                                            <input wire:model="lat" type="text" class="form-control" id="lat" >
                                            @error('lat')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <div class="mb-3">
                                            <label for="lng">@lang('main.lng')</label>
                                            <input wire:model="lng" type="text" class="form-control" id="lng">
                                            @error('lng')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                    </div>
                                </div>
                                
                                <style>
                                    /* Фикс для серого экрана: задать размер контейнеру */
                                    #map {
                                        width: 100%;
                                        height: 450px;
                                    }
                                </style>

                                <!-- Подключение стилей Leaflet -->
                                <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

                                <div wire:ignore id="map" style="height: 450px;"></div>

                                <!-- Подключение скрипта Leaflet -->
                                <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
                                <script>
                                    let mapInitialized = false;
                                    var lat = {{ old('lat', isset($hotel) ? $hotel->lat : 42.8746) }};  // Если нет данных, по умолчанию Москва
                                    var lng = {{ old('lng', isset($hotel) ? $hotel->lng : 74.6120) }};

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

                                    // Добавление масштаба
                                    L.control.scale().addTo(map);

                                    // Обработчик клика по карте
                                    map.on('click', function(e) {
                                        var lat = e.latlng.lat;  // Широта
                                        var lng = e.latlng.lng;  // Долгота

                                        // Удаление старого маркера, если он есть
                                        if (marker) {
                                            map.removeLayer(marker);
                                        }

                                        @this.set('lat', lat.toFixed(6));
                                        @this.set('lng', lng.toFixed(6));

                                        // Добавление маркера на выбранную точку
                                        if (lat && lng) {
                                            marker = L.marker([lat, lng]).addTo(map)
                                                .bindPopup('Широта: ' + lat.toFixed(6) + '<br>Долгота: ' + lng.toFixed(6))
                                                .openPopup();
                                        }

                                        mapInitialized = true;
                                    });

                                    document.addEventListener('livewire:load', function () {
                                        Livewire.hook('message.processed', (message, component) => {
                                            if (document.getElementById('map')) {
                                                // Если карта уже создана — обновить размеры
                                                if (mapInitialized) {

                                                    setTimeout(() => {
                                                        const lat = parseFloat(document.getElementById('lat').value || 42.87);
                                                        const lng = parseFloat(document.getElementById('lng').value || 74.59);
                                                        initLeafletMap(lat, lng);
                                                        map.invalidateSize();
                                                    }, 300);

                                                } else {
                                                    setTimeout(() => {
                                                        initLeafletMap();
                                                    }, 300);
                                                }
                                            }
                                        });
                                    });
                                </script>
                            </div>
                        
                        </div>

                        @elseif ($step == 2)
                        
                        <div class="row">

                            <h3>Продолжение отеля</h3>

                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label for="">@lang('main.description')</label>
                                    <textarea class="form-control" wire:model.defer="description" rows="3"></textarea>

                                        @error('description')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="">@lang('main.time_checkin')</label>
                                    <input type="time" wire:model.defer="checkin" class="form-control">
                                    @error('checkin')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    {{-- Услуги (русский) --}}
                                    <div x-data="serviceInputServices('services')" class="mb-3">
                                        <label>Услуги</label>
                                        <div class="form-control d-flex flex-wrap" @click="$refs.input.focus()">
                                            <template x-for="(item, index) in data" :key="index">
                                                <span class="badge bg-success me-1 mb-1">
                                                    <span x-text="item"></span>
                                                    <button type="button" class="btn-close btn-close-white btn-sm ms-1"
                                                            @click="remove(index)"></button>
                                                </span>
                                            </template>

                                            <input type="text"
                                                x-ref="input"
                                                x-model="inputValue"
                                                @keydown.enter.prevent="add"
                                                @keydown.tab.prevent="add"
                                                @keydown.space.prevent="add"
                                                class="border-0 flex-grow-1"
                                                placeholder="Введите услуги и нажмите Enter"
                                                style="min-width: 100px; outline: none;">
                                        </div>
                                        <input type="hidden" :value="JSON.stringify(data)" wire:model.defer="services">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for=""class="form-label">@lang('main.email')</label>
                                    <input type="email" wire:model.defer="email" class="form-control">

                                    @error('email')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="">@lang('main.rating')</label>
                                    <select wire:model.defer="rating" class="form-control" id="rating">
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                </div>
                                
                            </div>
                            <div class="col-md-6">
                            
                                <div class="mb-3">
                                    <label for="">@lang('main.description_en')</label>
                                    <textarea class="form-control" wire:model.defer="description_en" rows="3"></textarea>

                                        @error('description_en')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="">@lang('main.time_checkout')</label>
                                    <input type="time" wire:model.defer="checkout" class="form-control">
                                    @error('checkout')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    {{-- Услуги EN --}}
                                    <div x-data="serviceInputServicesEn('services_en')" class="mb-3">
                                        <label>Services EN</label>
                                        <div class="form-control d-flex flex-wrap" @click="$refs.input.focus()">
                                            <template x-for="(item, index) in data" :key="index">
                                                <span class="badge bg-primary me-1 mb-1">
                                                    <span x-text="item"></span>
                                                    <button type="button" class="btn-close btn-close-white btn-sm ms-1"
                                                            @click="remove(index)"></button>
                                                </span>
                                            </template>

                                            <input type="text"
                                                x-ref="input"
                                                x-model="inputValue"
                                                @keydown.enter.prevent="add"
                                                @keydown.tab.prevent="add"
                                                @keydown.space.prevent="add"
                                                class="border-0 flex-grow-1"
                                                placeholder="Enter services"
                                                style="min-width: 100px; outline: none;">
                                        </div>
                                        <input type="hidden" :value="JSON.stringify(data)" wire:model.defer="services_en">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for=""class="form-label">@lang('main.phone')</label>
                                    <input type="tel" wire:model.defer="phone" class="phone form-control">

                                    @error('phone')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <div class="col-12">

                                <div class="mb-3">
                                    <label for="" class="form-label">@lang('main.hotel_images')</label>
                                    <input type="file" wire:model="hotel_images" multiple class="form-control"/>
                                    @error('hotel_images.*') <span class="text-red-500">{{ $message }}</span> @enderror

                                    <div class="mt-2 row gap-2">
                                        @foreach ($hotel_images as $photo)
                                            <div class="col-2">
                                                <img src="{{ $photo->temporaryUrl() }}" class="h-24 w-full object-cover rounded border" />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>

                        @elseif ($step == 3)

                        <div class="row step-3 mt-3">
                            <h3>Общая информация номера</h3>

                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label for="">@lang('main.room_desc')</label>
                                    <textarea class="form-control" wire:model.defer="room_desc" rows="3"></textarea>
                                        @error('room_desc')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                </div>

                                <div class="mb-3">
                                    {{-- Услуги --}}
                                    <div x-data="serviceRoomInputServices('room_services')" class="mb-3">
                                        <label>@lang('main.room_services')</label>
                                        <div class="form-control d-flex flex-wrap" @click="$refs.input.focus()">
                                            <template x-for="(item, index) in data" :key="index">
                                                <span class="badge bg-primary me-1 mb-1">
                                                    <span x-text="item"></span>
                                                    <button type="button" class="btn-close btn-close-white btn-sm ms-1"
                                                            @click="remove(index)"></button>
                                                </span>
                                            </template>

                                            <input type="text"
                                                x-ref="input"
                                                x-model="inputValue"
                                                @keydown.enter.prevent="add"
                                                @keydown.tab.prevent="add"
                                                @keydown.space.prevent="add"
                                                class="border-0 flex-grow-1"
                                                placeholder="@lang('main.enter_services')"
                                                style="min-width: 100px; outline: none;">
                                        </div>
                                        <input type="hidden" :value="JSON.stringify(data)" wire:model.defer="room_services">
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label for="">@lang('main.room_desc_en')</label>
                                    <textarea class="form-control" wire:model.defer="room_desc_en" rows="3"></textarea>
                                        @error('room_desc_en')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                </div>

                                <div class="mb-3">
                                    {{-- Услуги EN --}}
                                    <div x-data="serviceRoomInputServicesEn('room_services_en')" class="mb-3">
                                        <label>@lang('main.room_services_en')</label>
                                        <div class="form-control d-flex flex-wrap" @click="$refs.input.focus()">
                                            <template x-for="(item, index) in data" :key="index">
                                                <span class="badge bg-primary me-1 mb-1">
                                                    <span x-text="item"></span>
                                                    <button type="button" class="btn-close btn-close-white btn-sm ms-1"
                                                            @click="remove(index)"></button>
                                                </span>
                                            </template>

                                            <input type="text"
                                                x-ref="input"
                                                x-model="inputValue"
                                                @keydown.enter.prevent="add"
                                                @keydown.tab.prevent="add"
                                                @keydown.space.prevent="add"
                                                class="border-0 flex-grow-1"
                                                placeholder="@lang('main.enter_services_en')"
                                                style="min-width: 100px; outline: none;">
                                        </div>
                                        <input type="hidden" :value="JSON.stringify(data)" wire:model.defer="room_services_en">
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="" class="form-label">@lang('main.room_images')</label>
                                    <input type="file" wire:model="room_images" multiple class="form-control"/>
                                    @error('room_images.*') <span class="text-red-500">{{ $message }}</span> @enderror

                                    <div class="mt-2 row">
                                        @foreach ($room_images as $photo)
                                        <div class="col-2">
                                            <img src="{{ $photo->temporaryUrl() }}" class="h-24 w-full object-cover rounded border" />
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <h3 class="mt-3">Добавить номер</h3>

                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label for="">@lang('main.room_name')</label>
                                    <input type="text" wire:model.defer="room_name" class="form-control">
                                    @error('room_name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <div class="col-md-6">

                                <div class="mb-3">
                                    <label for="">@lang('main.room_name_en')</label>
                                    <input type="text" wire:model.defer="room_name_en" class="form-control">
                                    @error('room_name_en')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <div class="col-md-12">

                                @if($roomError)
                                    <div class="alert alert-danger">{{ $roomError }}</div>
                                @endif

                                <ul>
                                    @foreach($rooms as $index => $room)
                                        <li>{{ $room['name'] ? $room['name'] . ' -' : '' }} {{ $room['name_en'] }}</li>
                                    @endforeach
                                </ul>

                                <div class="mt-4 d-flex justify-content-end gap-2">
                                    <button wire:click="addRoom" class="btn btn-primary ml-5">@lang('main.add_room')</button>
                                </div>
                            </div>
                        </div>
                        
                        @elseif ($step == 4)

                            <div class="row">
                                <h3>Добавить тарифы</h3>

                                <div class="col-md-3">
                                    <label for="" class="form-label">@lang('main.rooms')</label>

                                    <select wire:model="selected_room" class="form-control">
                                        <option value="">@lang('main.select_room')</option>
                                        @foreach($rooms as $index => $room)
                                            <option value="{{ $room['id'] }}">{{ $room['name_en'] }}</option>
                                        @endforeach
                                    </select>

                                    @error('selected_room')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="" class="form-label">@lang('main.rate_name')</label>

                                    <input type="text" wire:model.defer="rate_name" class="form-control">

                                    @error('rate_name')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-3">
                                    <label for="" class="form-label">@lang('main.rate_name_en')</label>

                                    <input type="text" wire:model.defer="rate_name_en"  class="form-control">

                                    @error('rate_name_en')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label for="" class="form-label">@lang('main.meal')</label>

                                    <select wire:model="meal" class="form-control">
                                        <option value="">@lang('main.select_meal')</option>
                                        @foreach($meals as $key => $meal)
                                            <option value="{{ $meal['id'] }}">{{ $meal['title'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-12 mt-3">
                                    @if($rateError)
                                        <div class="alert alert-danger">{{ $rateError }}</div>
                                    @endif

                                    <ul>
                                        @foreach($rates as $rate)
                                            <li>{{ $rooms[$rate['room_id']]['name'] }} → {{ $rate['name'] }}</li>
                                        @endforeach
                                    </ul>

                                    <div class="mt-4 d-flex justify-content-end gap-2">
                                        <button wire:click="addRate" class="btn btn-primary ml-5">@lang('main.add_rate')</button>
                                    </div>
                                </div>

                            </div>

                        @elseif ($step == 5)
                        
                            <div class="row">

                                <h3>Добавить политику отмены</h3>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="" class="form-label">@lang('main.rate')</label>
    
                                        <select wire:model="selected_rate" class="form-control">
                                            <option value="">@lang('main.select_rate')</option>
                                            @foreach($rates as $index => $rate)
                                                <option value="{{ $rate['id'] }}">{{ $rate['name_en'] }}</option>
                                            @endforeach
                                        </select>
    
                                        @error('selected_rate')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
    
                                    <div class="col-md-4">
                                        <label for="" class="form-label">@lang('main.rule_name')</label>
    
                                        <input type="text" wire:model.defer="rule_name" class="form-control">
    
                                        @error('rule_name')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label for="" class="form-label">@lang('main.rule_name_en')</label>
    
                                        <input type="text" wire:model.defer="rule_name_en" class="form-control">
    
                                        @error('rule_name_en')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        @if($ruleError)
                                            <div class="alert alert-danger">{{ $ruleError }}</div>
                                        @endif
    
                                        <ul>
                                            @foreach($rules as $rule)
                                                <li>{{ $rates[$rule['rate_id']]['name'] }} → {{ $rule['name'] }}</li>
                                            @endforeach
                                        </ul>
    
                                        <div class="mt-4 d-flex justify-content-end gap-2">
                                            <button wire:click="addRule" class="btn btn-primary ml-5">@lang('main.add_rule')</button>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        @endif

                        <div class="mt-4 d-flex justify-content-center gap-2">
                            @if ($step > 1)
                                <button wire:click="prevStep" class="btn btn-secondary">Назад</button>
                            @endif
                    
                            @if ($step < 5)
                                <button wire:click="nextStep" class="btn btn-primary ml-5">Далее</button>
                            @else
                                <a href="{{ route('index') }}" class="btn btn-success">Завершить</a>
                            @endif
                            <br><br>
                        </div>
                    
                        <script>
                            function serviceInputServices() {
                                return {
                                    data: @entangle('services'),
                                    inputValue: '',
                                    add() {
                                        const value = this.inputValue.trim().replace(/[,]+$/, '');
                                        if (value && !this.data.includes(value)) {
                                            this.data.push(value);
                                        }
                                        this.inputValue = '';
                                    },
                                    remove(index) {
                                        this.data.splice(index, 1);
                                    }
                                }
                            }
                        
                            function serviceInputServicesEn() {
                                return {
                                    data: @entangle('services_en'),
                                    inputValue: '',
                                    add() {
                                        const value = this.inputValue.trim().replace(/[,]+$/, '');
                                        if (value && !this.data.includes(value)) {
                                            this.data.push(value);
                                        }
                                        this.inputValue = '';
                                    },
                                    remove(index) {
                                        this.data.splice(index, 1);
                                    }
                                }
                            }
                            
                            function serviceRoomInputServices() {
                                return {
                                    data: @entangle('room_services'),
                                    inputValue: '',
                                    add() {
                                        const value = this.inputValue.trim().replace(/[,]+$/, '');
                                        if (value && !this.data.includes(value)) {
                                            this.data.push(value);
                                        }
                                        this.inputValue = '';
                                    },
                                    remove(index) {
                                        this.data.splice(index, 1);
                                    }
                                }
                            }
                            
                            function serviceRoomInputServicesEn() {
                                return {
                                    data: @entangle('room_services_en'),
                                    inputValue: '',
                                    add() {
                                        const value = this.inputValue.trim().replace(/[,]+$/, '');
                                        if (value && !this.data.includes(value)) {
                                            this.data.push(value);
                                        }
                                        this.inputValue = '';
                                    },
                                    remove(index) {
                                        this.data.splice(index, 1);
                                    }
                                }
                            }
                        </script>

                </div>
            </div>
        </div>
    </div>
