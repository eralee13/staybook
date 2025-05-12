@extends('layouts.master')

@section('title', 'Главная страница')

@section('content')
    @auth
        <div class="main-filter">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h1>Остановитесь с удобством.
                            Работайте с выгодой.</h1>
                        <div class="type">
                            <div class="type-item current">
                                <a href="{{route('search')}}">Отели и номера</a>
                            </div>
                            <div class="type-item">
                                <a href="#">Трансфер</a>
                            </div>
                        </div>

                        <form action="{{ route('search') }}" method="GET">
                            <div class="row">
                                <div class="col-lg-3 col-md-12">
                                    <div class="form-group">
                                        <div class="label stay"><img src="{{route('index')}}/img/marker_out.svg" alt="">
                                        </div>
                                        <select name="city" id="city">
                                            @foreach($cities as $city)
                                                <option value="{{ $city->title }}">{{ $city->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg col-6">
                                    <div class="form-group">
                                        <div class="label in"><img src="{{route('index')}}/img/marker_in.svg" alt=""> Заезд
                                        </div>
                                        <input type="text" id="date" class="date">
                                        <input type="hidden" id="arrivalDate" name="arrivalDate" value="{{ now()->format('Y-m-d') }}">
                                        <input type="hidden" id="departureDate" name="departureDate" value="{{ $tomorrow }}">
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
                                                    <input type="hidden" name="adult" id="adult" value="1">
                                                </div>

                                                <!-- Дети -->
                                                <div class="counter count-item">
                                                    <label>Дети:</label>
                                                    <a class="minus" onclick="changeCount('child', -1)">-</a>
                                                    <span id="child-count">0</span>
                                                    <a class="plus" onclick="changeCount('child', 1)">+</a>
                                                    <input type="hidden" name="child" id="child">
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
		  <select name="childAges[]">
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
                                                                <input type="radio" name="rating" value="1">
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
                                                                <input type="radio" name="rating" value="2">
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
                                                                <input type="radio" name="rating" value="3">
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
                                                                <input type="radio" name="rating" value="4">
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
                                                                <input type="radio" name="rating" value="5">
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
                                                    <div class="row justify-content-center">
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="1">
                                                                <label for="">RO</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="2">
                                                                <label for="">BB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="3">
                                                                <label for="">HB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="4">
                                                                <label for="">FB</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-md-4 col-4">
                                                            <div class="itemmm">
                                                                <input type="radio" name="meal_id" value="5">
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

                        <div class="property-list">
                            <div class="property-list-item">
                                <a href="#">
                                    <img src="{{route('index')}}/img/hotel.svg" alt="">
                                    <div class="name">Отели</div>
                                </a>
                            </div>
                            <div class="property-list-item">
                                <a href="#">
                                    <img src="{{route('index')}}/img/rooms.svg" alt="">
                                    <div class="name">Номера</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="places">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Места в Кыргызстане</h2>
                    </div>
                </div>
                <div class="row">
                    @foreach($hotels as $hotel)
                        <div class="col-lg-4 col-md-6">
                            <div class="places-item">
                                    <span class="img-wrap">
                                        <img src="{{ Storage::url($hotel->image) }}" alt="">
                                    </span>
                                <div class="text-wrap">
                                    <div class="address">{{ $hotel->city }}</div>
                                    <h5>{{ $hotel->title }}</h5>
                                    <div class="rating"><img src="{{ route('index') }}/img/star.svg"
                                                             alt=""> {{ $hotel->rating }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-wrap">
                            <a href="{{ route('hotels') }}">Показать больше</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="places popular">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Популярные направления</h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place1.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place2.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place3.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="places-item">
                            <a href="#"><img src="img/place1.jpg" alt=""></a>
                            <div class="text-wrap">
                                <div class="address">Бишкек, Кыргызстан</div>
                                <div class="rating"><img src="img/star.svg" alt=""> 4,76</div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-wrap">
                            <a href="{{ route('hotels') }}">Показать больше</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('layouts.auth')
    @endauth
@endsection
