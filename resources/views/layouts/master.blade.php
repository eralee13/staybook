<!DOCTYPE html>
<html lang="ru">

<head>
    <link rel="icon" href="{{route('index')}}/img/favicon.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{route('index')}}/img/favicon.jpg">
    <!-- Template Basic Images End -->

    <!-- Custom Browsers Color Start -->
    <meta name="theme-color" content="#000">
    <!-- Custom Browsers Color End -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"
          rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/eliyantosarage/font-awesome-pro@main/fontawesome-pro-6.5.1-web/css/all.min.css"
          rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css"
          integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous"/>
    <link rel="stylesheet" href="{{route('index')}}/css/main.min.css">
    <link rel="stylesheet" href="{{route('index')}}/css/style.css?ver=1.1">


</head>

<body>


<header class="main">
    <div class="container">
        <div class="row">
            <div class="col-lg-2 col-md-3 col-4">
                <a href="{{route('index')}}">
                    <img src="{{route('index')}}/img/logo.svg" alt="">
                </a>
            </div>
            @auth
                <div class="col-lg-10 d-xl-block d-lg-block d-none">
                    <div class="wrap">
                        <div class="lang-wrap" id="lang">
                            <div class="currency">KGS</div>
                            <div class="lang">
                                <div class="lang-item">
                                    <a href="#">Русский <img src="{{route('index')}}/img/ru.svg" alt=""></a>
                                </div>
                            </div>
                            <div class="overwrap" id="over">
                                <ul class="tabs" id="tabs">
                                    <li class="current" data-tab="tab-1">Валюта</li>
                                    <li data-tab="tab-2">Язык</li>
                                </ul>
                                <div class="tab-content current" id="tab-1">
                                    <ul>
                                        <li>KGS Кыргызский сом</li>
                                        <li>RUB Российский рубль</li>
                                        <li class="current">USD Американский доллар</li>
                                    </ul>
                                </div>
                                <div class="tab-content" id="tab-2">
                                    <ul>
                                        <li><img src="{{route('index')}}/img/kg.svg" alt=""> Кыргыз тили</li>
                                        <li @if(session('locale')=='ru')
                                                current
                                                @endif><a href="{{ route('locale', 'ru') }}"><img
                                                        src="{{route('index')}}/img/ru.svg" alt=""> Русский</a></li>
                                        <li @if(session('locale')=='en')
                                                current
                                                @endif><a href="{{ route('locale', 'en') }}"><img
                                                        src="{{route('index')}}/img/en.svg" alt=""> English</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="menu-wrap">
                            <ul>
                                <li><a href="{{route('about')}}">О сервисе</a></li>
                                <li><a href="{{route('contactspage')}}">Контакты</a></li>
                            </ul>
                        </div>
                        <div class="auth">
                            <a href="{{ route('login') }}"><img src="{{route('index')}}/img/user_w.svg" alt="">
                                Войти</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-10 col-md-9 col-8 d-xl-none d-lg-none d-block">
                    <div class="wrap">
                        <div class="auth">
                            <a href="{{route('login')}}"><img src="{{route('index')}}/img/user_w.svg" alt=""> Войти</a>
                        </div>
                        <nav>
                            <a href="#" class="toggle-mnu d-xl-none d-lg-none"><span></span></a>
                            <ul>
                                <li><a href="{{route('about')}}">О сервисе</a></li>
                                <li><a href="{{route('contactspage')}}">Контакты</a></li>
                                <li><a href="#"><img src="{{route('index')}}/img/kg.svg" alt=""> Кыргыз тили </a></li>
                                <li @if(session('locale')=='ru')
                                        current
                                        @endif><a href="{{ route('locale', 'ru') }}"><img
                                                src="{{route('index')}}/img/ru.svg" alt=""> Русский</a></li>
                                <li @if(session('locale')=='en')
                                        current
                                        @endif><a href="{{ route('locale', 'en') }}"><img
                                                src="{{route('index')}}/img/en.svg" alt=""> English</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            @endauth
        </div>
        @auth
            <div class="row">
                <div class="col-md-12">
                    <h1>Остановитесь с удобством.
                        Работайте с выгодой.</h1>
                    <div class="type">
                        <div class="type-item current">
                            <a href="{{route('index')}}">Отели и номера</a>
                        </div>
                        <div class="type-item">
                            <a href="#">Трансфер</a>
                        </div>
                    </div>
                    <form action="{{ route('search_property') }}">
                        <div class="row">
                            <div class="col-lg-3 col-md-12">
                                <div class="form-group">
                                    <div class="label stay"><img src="{{route('index')}}/img/marker_out.svg" alt="">
                                    </div>
                                    <select name="city" id="address" required>
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
                                    <input type="text" id="date" class="date" required="">
                                    <input type="hidden" id="date" name="arrivalDate" value="{{ $now }}">
                                    <input type="hidden" id="end_d" name="departureDate" value="{{ $tomorrow }}">
                                </div>
                            </div>
                            {{--                            <div class="col-lg col-6">--}}
                            {{--                                <div class="form-group">--}}
                            {{--                                    <div class="label out"><img src="{{route('index')}}/img/marker_out.svg" alt=""> Выезд</div>--}}
                            {{--                                    <input type="date" id="date" name="departureDate" value="{{ $tomorrow }}">--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
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
		  <select>
			<option value="">-- возраст --</option>
			${Array.from({length: 19}, (_, age) => `<option name="age${age}" value="${age}">${age}</option>`).join('')}
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
                                        <select name="" id=""></select>
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
                                                            <input type="radio" value="RO">
                                                            <label for="">RO</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="BF">
                                                            <label for="">BF</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="HF">
                                                            <label for="">HF</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="FF">
                                                            <label for="">FF</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-md-4 col-4">
                                                        <div class="itemmm">
                                                            <input type="radio" value="AI">
                                                            <label for="">AI</label>
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
                                    <button class="more"><img src="{{route('index')}}/img/search.svg" alt=""> Найти
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
        @endauth
    </div>
</header>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            @if(session()->has('success'))
                <p class="alert alert-success">{{ session()->get('success') }}</p>
            @endif
            @if(session()->has('warning'))
                <p class="alert alert-warning">{{ session()->get('warning') }}</p>
            @endif
        </div>
    </div>
</div>

@yield('content')

<footer>
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-md-4">
                    <div class="footer-item">
                        <div class="logo">
                            <img src="{{ route('index') }}/img/logo_b.svg" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="footer-item">
                        <ul>
                            <li>г. Бишкек,
                                пр.Чынгыза Айтматова 91
                            </li>
                            <li><a href="tel:+996 227 225 227">+996 227 225 227</a></li>
                            <li><a href="https://instagram.com" target="_blank">Instagram</a></li>
                            <li><a href="https://wa.me/" target="_blank">WhatsApp</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="footer-item">
                        <ul>
                            <li><a href="{{ route('about') }}">О сервисе</a></li>
                            <li><a href="{{ route('about') }}">О компании</a></li>
                            <li><a href="#">Города и страны</a></li>
                            <li><a href="#">Блог</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="footer-item">
                        <ul>
                            <li><a href="#">Компаниям и сервисам</a></li>
                            <li><a href="#">Апартаментам</a></li>
                            <li><a href="#">Отелям и другим объектам</a></li>
                            <li><a href="#">Туроператорам и турагентам</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="footer-item">
                        <ul>
                            <li><a href="#">Правила и условия бронирования</a></li>
                            <li><a href="#">Политика конфиденциальности</a></li>
                            <li><a href="#">Юридическая информация</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="copy">@lang('main.copy') &copy; {{ date('Y') }} staybook.asia</div>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="{{ route('index') }}/js/scripts.min.js"></script>

@livewireScripts

</body>

</html>
