@section('title', 'Создание продукта')

{{-- // Мультиформа создание отеля с номерами и тарифами --}}

<div class="page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1>Добро пожаловать в Stay Book</h1>
                <p>Расскажите нам о вашей недвижимости</p>
                @if (session()->has('message'))
                    <div class="alert alert-success">{{ session('message') }}</div>
                @endif

                <h3>Шаг {{ $step }}</h3>

                @if($hotelError)
                    <div class="alert alert-danger">{{ $hotelError}}</div>
                @endif

                @if($hotelSuccess)
                    <div class="alert alert-success">{{ $hotelSuccess}}</div>
                @endif

                @if ($step == 1)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="" class="form-label">@lang('admin.title')</label>
                                <input type="text" wire:model="title" class="form-control">
                                @error('title')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="">@lang('admin.title') EN</label>
                                <input type="text" wire:model="title_en" class="form-control">
                                @error('title_en')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="type">Тип недвижимости</label>
                                <select wire:model="type" class="form-control">
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

                            <div class="form-group">
                                <label for="city">Город</label>
                                @php
                                    $cities  = \App\Models\City::all();
                                    $timezones = DateTimeZone::listIdentifiers();
                                @endphp
                                <select wire:model="city" id="cityhot">
                                    <option value="">Выбрать</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->title }}">{{ $city->title }}</option>
                                    @endforeach
                                </select>
                                @error('city')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="address">Адрес</label>
                                <input wire:model="address" type="text" class="form-control" name="address"/>
                                @error('address')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="address">Адрес EN</label>
                                <input wire:model="address_en" type="text" class="form-control" name="address_en"/>
                                @error('address_en')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">Часовой пояс</label>
                                <select wire:model="timezone" id="timezone">
                                    <option value="">Выбрать</option>
                                    @foreach($timezones as $timezone)
                                        <option value="{{ $timezone }}">{{ $timezone }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lat">Широта</label>
                                        <input wire:model="lat" type="text" class="form-control" id="lat">
                                        @error('lat')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lng">Долгота</label>
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
                            <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>

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
                                map.on('click', function (e) {
                                    var lat = e.latlng.lat;  // Широта
                                    var lng = e.latlng.lng;  // Долгота

                                    // Удаление старого маркера, если он есть
                                    if (marker) {
                                        map.removeLayer(marker);
                                    }

                                @this.set('lat', lat.toFixed(6))
                                    ;
                                @this.set('lng', lng.toFixed(6))
                                    ;

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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Описание отеля</label>
                                <textarea class="form-control" wire:model="description" id="editor" rows="3"></textarea>
                                @error('description')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- <script src="https://cdn.tiny.cloud/1/yxonqgmruy7kchzsv4uizqanbapq2uta96cs0p4y91ov9iod/tinymce/6/tinymce.min.js"
                                    referrerpolicy="origin"></script>
                            <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
                            <script>
                                ClassicEditor
                                    .create(document.querySelector('#editor'))
                                    .catch(error => {
                                        console.error(error);
                                    });
                                ClassicEditor
                                    .create(document.querySelector('#editor1'))
                                    .catch(error => {
                                        console.error(error);
                                    });
                            </script> --}}

                            <div class="form-group">
                                <label for="">Время заезда</label>
                                <select wire:model="checkin">
                                    <option value="">Выбрать</option>
                                    <option value="13:00">13:00</option>
                                    <option value="14:00">14:00</option>
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
                                @error('checkin')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
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
                                    <input type="hidden" :value="JSON.stringify(data)" wire:model="services">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="" class="form-label">Email</label>
                                <input type="email" wire:model="email" class="form-control">

                                @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="">@lang('main.rating')</label>
                                <select wire:model="rating" class="form-control" id="rating">
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                                @error('rating')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                <label for="">Описание EN</label>
                                <textarea class="form-control" wire:model="description_en" rows="3"></textarea>
                                @error('description_en')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="">Время выезда</label>
                                <select wire:model="checkout">
                                    <option value="">Выбрать</option>
                                    <option value="01:00">01:00</option>
                                    <option value="02:00">02:00</option>
                                    <option value="03:00">03:00</option>
                                    <option value="04:00">04:00</option>
                                    <option value="05:00">05:00</option>
                                    <option value="06:00">06:00</option>
                                    <option value="07:00">07:00</option>
                                    <option value="08:00">08:00</option>
                                    <option value="09:00">09:00</option>
                                    <option value="10:00">10:00</option>
                                    <option value="11:00">11:00</option>
                                    <option value="12:00">12:00</option>
                                    <option value="13:00">13:00</option>
                                    <option value="14:00">14:00</option>
                                    <option value="15:00">15:00</option>
                                    <option value="16:00">16:00</option>
                                    <option value="17:00">17:00</option>
                                    <option value="18:00">18:00</option>
                                </select>
                                @error('checkout')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
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
                                    <input type="hidden" :value="JSON.stringify(data)" wire:model="services_en">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="" class="form-label">@lang('main.phone')</label>
                                <input type="tel" id="phone" wire:model="phone" class="phone form-control">
                                @error('phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="form-group"
                                 @can('edit-contact')
                                     style="display: block;"
                                 @else
                                     style="display: none;"
                                    @endcan
                            >
                                <label for="status" class="form-label">@lang('main.status')</label>
                                <select wire:model="status">
                                    <option value="1">Активен</option>
                                    <option value="0" selected>Отключен</option>
                                </select>
                                @error('status')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="col-12">

                            <div class="form-group">
                                <label for="hotel_images" class="form-label">Изображения</label>
                                <input type="file" wire:model="hotel_images" multiple class="form-control"/>
                                @error('hotel_images')
                                <div class="alert alert-danger">{{ $message }}</div> @enderror

                                <div class="mt-2 row gap-2">
                                    @foreach ($hotel_images as $photo)
                                        <div class="col-2">
                                            <img src="{{ $photo->temporaryUrl() }}"
                                                 class="h-24 w-full object-cover rounded border"/>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>
                @endif

                <div class="mt-4 d-flex justify-content-center gap-2">
                    @if ($step > 1)
                        <button wire:click="prevStep" class="more cancel">Назад</button>
                    @endif

                    @if ($step < 2)
                        <button wire:click="nextStep" class="more">Далее</button>
                    @else
                        <button wire:click="submit" class="more">Завершить</button>
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

                <style>
                    input[type="checkbox"] {
                        display: inline-block;
                        width: auto;
                        height: auto;
                        margin-right: 5px;
                    }

                    .more {
                        margin: 0 20px;
                    }
                </style>
                <style>
                    .admin label {
                        display: inline-block;
                    }

                    #policy1, #policy2, #policy3 {
                        float: left;
                        width: 25px;
                        margin-right: 15px;
                    }

                    .policy1, .policy2, .policy3 {
                        display: inline-block;
                        width: calc(100% - 40px);
                    }
                </style>

            </div>
        </div>
    </div>
</div>


{{-- <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#cityhot').select2({
            placeholder: "Выберите город",
            allowClear: true
        });
        $('#timezone').select2({
            placeholder: "Выберите часовой пояс",
            allowClear: true
        });
    });
</script> --}}