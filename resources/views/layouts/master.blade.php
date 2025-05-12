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
<div id="preloader">
    <div class="loader"></div>
</div>

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"
        integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>

<script>
    $(document).ready(function () {
        $('#city').selectize({
            sortField: 'text'
        });
    });
</script>

@livewireScripts

</body>

</html>
