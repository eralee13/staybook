@php use Carbon\Carbon; @endphp
<div class="container">
    {{-- {{ print_r(session('hotel_search'), true) }} --}}
    <form wire:submit.prevent="searchHotels">
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

 <!-- Вывод найденных отелей -->
 {{-- @if($hotels)
 <div class="mt-4">
     <h2 class="text-lg font-semibold">Найденные отели</h2>
     <ul class="space-y-4">
         @foreach($hotels as $hotel)
             <li class="p-4 border rounded-lg shadow-md">
                 <div class="flex items-center justify-between">
                     <div>
                         <h3 class="text-lg font-bold">{{ $hotel['title'] }}</h3>
                         <p class="text-sm text-gray-600">{{ $hotel['address_en'] }}, {{ $hotel['city'] }}</p>
                         <p class="text-sm">Рейтинг: ⭐ {{ $hotel['rating'] }}</p>
                     </div>
                     <button wire:click="loadRooms({{ $hotel['id'] }})"
                             class="bg-green-500 text-white px-3 py-1 rounded">
                         Посмотреть комнаты
                     </button>
                 </div>
             </li>
         @endforeach
     </ul>
 </div>
@endif

<!-- Вывод доступных комнат -->
@if($rooms)
 <div class="mt-6">
     <h2 class="text-lg font-semibold">Доступные комнаты</h2>
     <ul class="space-y-4">
         @foreach($rooms as $room)
             <li class="p-4 border rounded-lg shadow-md">
                 <h3 class="text-md font-bold">{{ $room['title'] }}</h3>
                 <p class="text-sm text-gray-600">{{ $room['description_en'] }}</p>
                 <p class="text-md font-semibold">Цена: ${{ $room['price'] }}</p>
             </li>
         @endforeach
     </ul>
 </div>
@endif --}}
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<script>
    $(function() {
        const locale = "{{ $locale }}";

        // локализация для разных языков
        const localeSettings = {
            en: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Apply',
                cancelLabel: 'Cancel',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                weekLabel: 'W',
                daysOfWeek: moment.weekdaysMin(),
                monthNames: moment.months(),
                firstDay: 1
            },
            ru: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Применить',
                cancelLabel: 'Отмена',
                fromLabel: 'С',
                toLabel: 'По',
                customRangeLabel: 'Свой',
                weekLabel: 'Н',
                customRangeLabel: 'Выбрать вручную',
                daysOfWeek: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                    'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                firstDay: 1,

            }
        };

        $('#daterange').daterangepicker({
            autoUpdateInput: false,
            autoApply: true,
            startDate: "{{ $now }}",
            endDate: "{{ $tomorrow }}",
            locale: localeSettings[locale] || localeSettings['en'],
        });
    });

    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        let range = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
        $(this).val(range);

        // Передать значение в Livewire
    @this.set('dateRange', range);
        // Livewire.emit('updateDateRange', range);
    });

    // $('#daterange').on('cancel.daterangepicker', function () {
    //     $(this).val('');
    //     @this.set('dateRange', ''); // Очистка значения в Livewire
    // });

</script>