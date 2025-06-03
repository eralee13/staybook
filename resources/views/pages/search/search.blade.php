@php use Carbon\Carbon;use Illuminate\Support\Facades\Http; @endphp
@extends('layouts.filter_mini')

@section('title', 'Поиск')

@section('content')

    @auth
        <div class="main-filter" style="padding-bottom: 40px">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('search') }}">
                            <div class="row">
                                <div class="col-lg-3 col-md-12">
                                    <div class="form-group">
                                        <div class="label stay"><img src="{{ route('index') }}/img/marker_out.svg"
                                                                     alt="">
                                        </div>
                                        <select name="city" id="address" required>
                                            <option value="{{ $request->city }}">{{ $request->city }}</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->title }}">{{ $city->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg col-6">
                                    <div class="form-group">
                                        <div class="label in"><img src="{{ route('index') }}/img/marker_in.svg" alt="">
                                            Заезд
                                        </div>
                                        <input type="text" id="date" class="date" required="">
                                        <input type="hidden" id="arrivalDate" name="arrivalDate"
                                               value="{{ $request->arrivalDate }}">
                                        <input type="hidden" id="departureDate" name="departureDate"
                                               value="{{ $request->departureDate }}">
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
                                                    <span id="adult-count">{{ $request->adult ?? 1 }}</span>
                                                    <a class="plus" onclick="changeCount('adult', 1)">+</a>
                                                    <input type="hidden" name="adult" id="adult"
                                                           value="{{ $request->adult ?? 1 }}">
                                                </div>

                                                <!-- Дети -->
                                                <div class="counter count-item">
                                                    <label>Дети:</label>
                                                    <a class="minus" onclick="changeCount('child', -1)">-</a>
                                                    <span id="child-count">{{ $request->child ?? 0 }}</span>
                                                    <a class="plus" onclick="changeCount('child', 1)">+</a>
                                                    <input type="hidden" name="childAges[]" id="child">
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
		  <select name="age${i + 1}">
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
                                            <div class="label filter"><img src="{{route('index')}}/img/setting.svg"
                                                                           alt="">
                                                Фильтры
                                            </div>
                                            <div class="filter-wrap" id="filter-wrap">
                                                <div class="closebtn" id="closebtn"><img
                                                            src="{{route('index')}}/img/close.svg" alt=""></div>
                                                <h5>Фильтры</h5>
                                                <div class="form-group">
                                                    <div class="name">Рейтинг</div>
                                                    <div class="row justify-content-center">
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" id="1" name="rating" value="1"
                                                                       @if($request->rating == 1) checked @endif>
                                                                <div class="img @if($request->rating == 1) active @endif">
                                                                    <label for="1">1</label>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="2" id="2"
                                                                       @if($request->rating == 2) checked @endif>
                                                                <div class="img @if($request->rating == 2) active @endif">
                                                                    <label for="2">2</label>
                                                                    <div class="img-wrap">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                        <img src="{{route('index')}}/img/icons/rate.svg"
                                                                             alt="">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="3" id="3"
                                                                       @if($request->rating == 3) checked @endif>
                                                                <div class="img @if($request->rating == 3) active @endif">
                                                                    <label for="3">3</label>
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
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="4" id="4"
                                                                       @if($request->rating == 4) checked @endif>
                                                                <div class="img @if($request->rating == 4) active @endif">
                                                                    <label for="4">4</label>
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
                                                        <div class="col-lg col-md-4">
                                                            <div class="item">
                                                                <input type="radio" name="rating" value="5" id="5"
                                                                       @if($request->rating == 5) checked @endif>
                                                                <div class="img @if($request->rating == 5) active @endif">
                                                                    <label for="5">5</label>
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
                                                                <input type="checkbox" value="early_in">
                                                                <label for="">Ранний заезд</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-6">
                                                            <div class="itemm">
                                                                <input type="checkbox" value="late_out">
                                                                <label for="">Поздний выезд</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="line"></div>
                                                <div class="form-group" id="meal">
                                                    <div class="name">Виды питания</div>
                                                    <div class="row">
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 1) active @endif">
                                                                <input type="radio" value="1" id="ro" name="meal_id"
                                                                       @if($request->meal_id == 1) checked @endif">
                                                                <label for="ro">RO</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 2) active @endif">
                                                                <input type="radio" value="2" id="bb" name="meal_id"
                                                                       @if($request->meal_id == 2) checked @endif>
                                                                <label for="bb">BB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 3) active @endif">
                                                                <input type="radio" id="hb" value="3" name="meal_id"
                                                                       @if($request->meal_id == 3) checked @endif>
                                                                <label for="hb">HB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 4) active @endif">
                                                                <input type="radio" id="fb" value="4" name="meal_id"
                                                                       @if($request->meal_id == 4) checked @endif>
                                                                <label for="fb">FB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm @if($request->meal_id == 5) active @endif">
                                                                <input type="radio" id="ai" value="5" name="meal_id"
                                                                       @if($request->meal_id == 5) checked @endif>
                                                                <label for="ai">AI</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button class="more">Найти</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg col-12">
                                    <div class="form-group">
                                        <button class="more"><img src="{{ route('index') }}/img/search.svg" alt="">
                                            Найти
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="page search">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        @if($results != null)
                            @if(isset($results->errors))
                                @foreach ($results->errors as $error)
                                    <div class="alert alert-danger">
                                        <h5>{{ $error->code }}</h5>
                                        <p style="margin-bottom: 0">{{ $error->message }}</p>
                                    </div>
                                @endforeach
                            @else
                                @if($results->roomStays != null)
                                    @foreach($results->roomStays as $room)
                                        @php
                                            $hotel = \App\Models\Hotel::where('exely_id', $room->propertyId)->get()->first();
                                            $roomf = \App\Models\Room::where('hotel_id', $hotel->exely_id)->get()->first();
                                            $amenities = explode(',', $roomf->amenities);
                                            $iconMap = [
                                                'wi-fi'             => 'wifi.svg',
                                                'интернет'          => 'wifi.svg',
                                                'Доступ в интернет' => 'wifi.svg',
                                                'чайный набор'      => 'tea.svg',
                                                'Питание включено'  => 'meal.svg',
                                                'минеральная вода'  => 'water.svg',
                                                'сауна'             => 'sauna.svg',
                                                'сейф'              => 'safe.svg',
                                                'Двуспальная кровать' => 'bed2.svg',
                                                'Гладильные принадлежности' => 'iron.svg',
                                                'Ванная комната' => 'bath.svg',
                                                'Сауна' => 'sauna.svg',
                                                'Сейф' => 'safe.svg',
                                                'Минибар' => 'minibar.svg',
                                                'Кондиционер' => 'cond.svg',
                                                'Туалетные принадлежности' => 'toilet.svg',
                                                'Душ' => 'shower.svg',
                                                'Звукоизоляция' => 'sound.svg',
                                                'Фен' => 'dry.svg',
                                                'Постельное бельё' => 'bed_sheets.svg',
                                                'Халат' => 'robe.svg',
                                                'Шкаф' => 'close.svg',
                                                'Телефон' => 'phone_hotel.svg',
                                                'Отопление' => 'heating.svg',
                                                'Письменный стол' => 'table.svg',
                                                'Минеральная вода' => 'water.svg',
                                            ];
                                        @endphp
                                        <div class="search-item">
                                            <div class="row">
                                                <div class="col-md-5 order-xl-1 order-lg-1 order-1">
                                                    <div class="img-wrap">
                                                        <img src="{{ Storage::url($hotel->image ?? '') }}" alt="">
                                                    </div>
                                                </div>
                                                <div class="col-md-5 order-xl-2 order-lg-2 order-3">
                                                    <h4>{{ $hotel->title }}</h4>
                                                    <div class="amenities">
                                                        @foreach($amenities as $amenity)
                                                            @php
                                                                $iconFile = 'check.svg';
                                                                foreach ($iconMap as $keyword => $filename) {
                                                                    if (mb_stripos($amenity, $keyword) !== false) {
                                                                        $iconFile = $filename;
                                                                        break;
                                                                    }
                                                                }
                                                            @endphp
                                                            <div class="amenities-item">
                                                                <img src="{{ asset('img/icons/' . $iconFile) }}"
                                                                     alt="{{ $amenity }}">
                                                                <div class="name">{{ $amenity }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="btn-wrap">
                                                        <div class="btn-wrap">
                                                            <form action="{{ route('hotel_exely', $room->roomType->id) }}">
                                                                <input type="hidden" name="propertyId"
                                                                       value="{{ $room->propertyId }}">
                                                                <input type="hidden" name="arrivalDate"
                                                                       value="{{ $request->arrivalDate }}">
                                                                <input type="hidden" name="departureDate"
                                                                       value="{{ $request->departureDate }}">

                                                                <input type="hidden" name="adultCount"
                                                                       value="{{ $room->guestCount->adultCount }}">
                                                                @php
                                                                    $array_child = [];
                                                                @endphp
                                                                @foreach($room->guestCount->childAges as $child)
                                                                    @php
                                                                        $array_child[] = $child
                                                                    @endphp
                                                                @endforeach
                                                                <input type="hidden" name="childAges[]" value="{{ implode(', ', $array_child) }}">
                                                                <input type="hidden" name="ratePlanId"
                                                                       value="{{ $room->ratePlan->id }}">
                                                                <input type="hidden" name="roomTypeId"
                                                                       value="{{ $room->roomType->id }}">
                                                                @foreach($room->roomType->placements as $type)
                                                                    <input type="hidden" name="roomType"
                                                                           value="{{ $type->kind }}">
                                                                    <input type="hidden" name="roomCount"
                                                                           value="{{ $type->count }}">
                                                                    <input type="hidden" name="roomCode"
                                                                           value="{{ $type->code }}">
                                                                    <input type="hidden" name="minAge"
                                                                           value="{{ $type->minAge }}">
                                                                    <input type="hidden" name="maxAge"
                                                                           value="{{ $type->maxAge }}">
                                                                @endforeach

                                                                <input type="hidden" name="checkSum"
                                                                       value="{{ $room->checksum }}">
                                                                @foreach($room->includedServices as $serv)
                                                                    <input type="hidden" name="servicesId"
                                                                           value="{{ $serv->id }}">
                                                                @endforeach

                                                                {{-- <input type="hidden" name="servicesQuantity" value="{{  }}">--}}
                                                                <input type="hidden" name="hotel"
                                                                       value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="hotel_id"
                                                                       value="{{ $room->propertyId }}">
                                                                <input type="hidden" name="room_id"
                                                                       value="{{ $room->roomType->id }}">
                                                                <input type="hidden" name="title"
                                                                       value="{{ $room->fullPlacementsName }}">
                                                                <input type="hidden" name="price"
                                                                       value="{{ $room->total->priceBeforeTax }}">
                                                                <button class="more">Выбрать номера</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2 order-xl-3 order-lg-3 order-2">
                                                    <div class="price">
                                                        от {{ $room->total->priceBeforeTax }} {{ $room->currencyCode }}</div>
                                                    <div class="night">ночь</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-danger">Поиск не дал результатов. Пожалуйста <a
                                                href="{{ route('properties') }}">попробуйте снова</a></div>
                                @endif
                            @endif
                        @else
                            <!-- local hotels-->
                            @foreach($hotels as $hotel)
                                @php
                                    $items = \App\Models\Amenity::where('hotel_id', $hotel->id)->get()->first();
                                    $images = \App\Models\Image::where('hotel_id', $hotel->id)->get();
                                    $amenities = explode(',', $items->services);
                                    $items  = array_slice($amenities, 0, 8);
                                    $iconMap = [
                                        'wi-fi'             => 'wifi.svg',
                                        'интернет'          => 'wifi.svg',
                                        'Доступ в интернет' => 'wifi.svg',
                                        'чайный набор'      => 'tea.svg',
                                        'Питание включено'  => 'meal.svg',
                                        'минеральная вода'  => 'water.svg',
                                        'сауна'             => 'sauna.svg',
                                        'сейф'              => 'safe.svg',
                                        'Двуспальная кровать' => 'bed2.svg',
                                        'Гладильные принадлежности' => 'iron.svg',
                                        'Ванная комната' => 'bath.svg',
                                        'Сауна' => 'sauna.svg',
                                        'Сейф' => 'safe.svg',
                                        'Минибар' => 'minibar.svg',
                                        'Кондиционер' => 'cond.svg',
                                        'Туалетные принадлежности' => 'toilet.svg',
                                        'Душ' => 'shower.svg',
                                        'Звукоизоляция' => 'sound.svg',
                                        'Фен' => 'dry.svg',
                                        'Постельное бельё' => 'bed_sheets.svg',
                                        'Халат' => 'robe.svg',
                                        'Шкаф' => 'close.svg',
                                        'Телефон' => 'phone_hotel.svg',
                                        'Отопление' => 'heating.svg',
                                        'Письменный стол' => 'table.svg',
                                        'Минеральная вода' => 'water.svg',
                                    ];
                                @endphp
                                <div class="search-item">
                                    <div class="row">
                                        <div class="col-md-5 order-xl-1 order-lg-1 order-1">
                                            <div class="img-wrap">
                                                @if($images->isNotEmpty())
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="main">
                                                                <img src="{{ Storage::url($hotel->image) }}"
                                                                     alt="">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            @foreach($images as $file)
                                                                <div class="primary">
                                                                    <img src="{{ Storage::url($file->image) }}"
                                                                         alt="">
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <img src="{{ Storage::url($hotel->image) }}" alt="">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-5 order-xl-2 order-lg-2 order-3">
                                            <h4>{{ $hotel->title }}</h4>
                                            <div class="amenities">
                                                @foreach($items as $amenity)
                                                    @php
                                                        $iconFile = 'check.svg';
                                                        foreach ($iconMap as $keyword => $filename) {
                                                            if (mb_stripos($amenity, $keyword) !== false) {
                                                                $iconFile = $filename;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="amenities-item">
                                                        <img src="{{ asset('img/icons/' . $iconFile) }}"
                                                             alt="{{ $amenity }}">
                                                        <div class="name">{{ $amenity }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="btn-wrap">
                                                <div class="btn-wrap">
                                                    <form action="{{ route('hotel', $hotel->code) }}">
                                                        <input type="hidden" name="arrivalDate"
                                                               value="{{ $request->arrivalDate }}">
                                                        <input type="hidden" name="departureDate"
                                                               value="{{ $request->departureDate }}">
                                                        @php
                                                            $totalAdults   = 0;
                                                            $totalChildren = 0;
                                                            if (!empty($request->rooms) && is_array($request->rooms)) {
                                                                foreach ($request->rooms as $room) {
                                                                    // Добавляем взрослых
                                                                    $totalAdults += (int) ($room['adults'] ?? 0);

                                                                    // Считаем детей в этой комнате
                                                                    $totalChildren += isset($room['childAges']) && is_array($room['childAges'])
                                                                                      ? count($room['childAges'])
                                                                                      : 0;
                                                                }
                                                            }

                                                            // Вычисляем количество ночей
                                                $arr    = Carbon::parse($request->arrivalDate);
                                                $dep    = Carbon::parse($request->departureDate);
                                                $nights = $arr->diffInDays($dep);
                                                $totalPrice = 0;
                                                // Получаем самый дешевый тариф по отелю (например, для всех комнат одинаковый)
                                                $rate = \App\Models\Rate::where('hotel_id', $hotel->id)
                                                         ->orderBy('price', 'asc')
                                                         ->first();

                                                if ($rate) {
                                                    $сhildAges = [];

                                                        $price_child = 0;

                                                        if (!empty($request->rooms) && is_array($request->rooms)) {
                                                            foreach ($request->rooms as $room) {
                                                                // Если в этой комнате задан массив childAges — перебираем и добавляем
                                                                if (!empty($room['childAges']) && is_array($room['childAges'])) {
                                                                    foreach ($room['childAges'] as $age) {
                                                                        $childAges[] = $age;
                                                                        $age = (int) $age;
                                                                        if ($age >= $rate->free_children_age) {
                                                                            $price_child += $rate->child_extra_fee;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }


                                                        // Вычисляем стоимость для этой комнаты:
                                                        // если взрослых >= 2, используем price2, иначе – price
                                                        if ($totalAdults >= 2) {
                                                            $price = ($rate->price2 + $price_child) * 1 * $nights;
                                                        } else {
                                                            $price = ($rate->price + $price_child) * 1 * $nights;
                                                        }
                                                }
                                                        @endphp
                                                        <input type="hidden" name="adult" value="{{ $totalAdults }}">
                                                        <input type="hidden" name="child" value="{{ $totalChildren }}">
                                                        <input type="hidden" name="childAges[]"
                                                               value="{{ implode(', ', $childAges) }}">
                                                        <input type="hidden" name="meal_id"
                                                               value="{{ $request->meal_id }}">
                                                        <button class="more">Показать все номера</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-2 order-xl-3 order-lg-3 order-2">
                                            <div class="price">от {{ number_format($price, 0, '.', ' ') }} $</div>
                                            <div class="night">ночь</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>
        </div>

    @else
        @include('layouts.auth')
    @endauth

@endsection
