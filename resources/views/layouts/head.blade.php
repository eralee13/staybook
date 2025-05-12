<!DOCTYPE html>
<html lang="ru">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" href="{{route('index')}}/img/favicon.jpg">
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


    <script src="{{route('index')}}/js/scripts.min.js"></script>


</head>

<body>

<div id="preloader">
    <div class="loader"></div>
</div>

<header class="is_order">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-2 col-md-3 col-4">
                <a href="{{ route('index') }}">
                    <img src="{{ route('index') }}/img/logo_b.svg" alt="">
                </a>
            </div>
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

</body>

</html>
