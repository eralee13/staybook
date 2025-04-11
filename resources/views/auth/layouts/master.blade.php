<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <!-- <base href="/"> -->

    <title>@yield('title') - StayBook</title>
    <meta name="description" content="">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Template Basic Images Start -->
    <meta property="og:image" content="path/to/image.jpg">
    <link rel="icon" href="{{route('index')}}/img/favicon.jpg">
    <link rel="apple-touch-icon" sizes="180x180" href="{{route('index')}}/img/favicon.jpg">
    <!-- Template Basic Images End -->

    <!-- Custom Browser Color Start -->
    <meta name="theme-color" content="#000">
    <!-- Custom Browsers Color End -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/eliyantosarage/font-awesome-pro@main/fontawesome-pro-6.5.1-web/css/all.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="{{route('index')}}/css/main.min.css">
    <link rel="stylesheet" href="{{route('index')}}/css/admin.css">
    <link href="{{route('index')}}/css/print.css" rel="stylesheet" media="print" type="text/css">
</head>

<body class="admin">
<header>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="logo">
                    <a href="{{route('hotels.index')}}"><img src="{{route('index')}}/img/logo.svg" alt=""></a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="profile">
                    <a href="{{ route('profile.edit') }}">Профиль</a>
                </div>
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
                                    <li  @if(session('locale')=='ru')
                                             current
                                            @endif><a href="{{ route('locale', 'ru') }}"><img src="{{route('index')}}/img/ru.svg" alt=""> Русский</a></li>
                                    <li  @if(session('locale')=='en')
                                             current
                                            @endif><a href="{{ route('locale', 'en') }}"><img src="{{route('index')}}/img/en.svg" alt=""> English</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="homelink">
                    <a href="{{route('index')}}" target="_blank">Перейти на сайт</a>
                </div>
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
    @can('edit-contact')
    <div class="bottom">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ul>
                        <li @routeactive('pages.index')><a href="{{ route('pages.index')}}"><i class="fas
            fa-page"></i> @lang('admin.pages')</a></li>
                        <li @routeactive('users.index')><a href="{{ route('users.index')}}"><i class="fa-solid fa-user"></i> @lang('admin.users')</a></li>
                        <li @routeactive('roles.index')><a href="{{ route('roles.index')}}"><i class="fa-solid fa-mask"></i> @lang('admin.roles')</a></li>
                        <li @routeactive('permissions.index')><a href="{{ route('permissions.index')}}"><i class="fa-solid fa-lock"></i> @lang('admin.permissions')</a></li>
                        <li @routeactive('contacts.index')><a href="{{ route('contacts.index')}}"><i class="fas
            fa-address-book"></i> @lang('admin.contacts')</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endcan
</footer>


<script src="{{ route('index') }}/js/scripts.min.js"></script>
<script>
    $(function() {
        $('#dynamic_select').on('change', function() {
            let url = $(this).val();
            if (url) {
                window.location = url;
            }
            return false;
        });
    });

    // $(document).ready(function () {
    //     $('#country').selectize({
    //         sortField: 'text'
    //     });
    // });
</script>

</body>
</html>

