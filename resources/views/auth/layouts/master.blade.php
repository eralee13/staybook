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
    <link rel="icon" href="{{route('index')}}/img/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{route('index')}}/img/favicon.png">
    <!-- Template Basic Images End -->

    <!-- Custom Browser Color Start -->
    <meta name="theme-color" content="#000">
    <!-- Custom Browsers Color End -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@200..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{route('index')}}/css/main.min.css?ver=1.2">
    <link rel="stylesheet" href="{{route('index')}}/css/admin.css?ver=1.2">
    <link href="{{route('index')}}/css/print.css" rel="stylesheet" media="print" type="text/css">
</head>

<body class="admin">
<header>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="logo">
                    <a href="{{ route('profile.edit') }}"><img src="{{route('index')}}/img/logo.svg" alt=""></a>
                </div>
            </div>
            <div class="col-md-9 d-xl-block d-lg-block d-none">
                <div class="homelink">
                    <a href="{{route('index')}}" target="_blank">@lang('admin.visit')</a>
                </div>
                <div class="wrap">
                    <div class="lang-wrap" id="lang">
                        <div class="lang">
                            <div class="lang-item">
                                @if(app()->getLocale() == 'ru')
                                    <a href="#">RU</a>
                                @else
                                    <a href="#">EN</a>
                                @endif
                            </div>
                        </div>
                        <div class="overwrap" id="over">
                            <ul class="tabs" id="tabs">
                                <li class="current" data-tab="tab-1">@lang('admin.language')</li>
                            </ul>
                            <div class="tab-content current" id="tab-1">
                                <ul>
                                    {{--                                    <li><img src="{{route('index')}}/img/kg.svg" alt=""> Кыргыз тили</li>--}}
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
                </div>
                <div class="profile">
                    <a href="{{ route('profile.edit') }}">{{ \Illuminate\Support\Facades\Auth::user()->name }}</a>
                </div>
            </div>
            <div class="col-md-9 col-6 d-xl-none d-lg-none d-block">
                <div class="profile">
                    <a href="{{ route('profile.edit') }}">{{ \Illuminate\Support\Facades\Auth::user()->name }}</a>
                </div>
                <nav>
                    <a href="#" class="toggle-mnu d-xl-none d-lg-none"><span></span></a>
                    <ul>
                        <li><a href="{{route('service')}}">@lang('main.about_service')</a></li>
                        <li><a href="{{route('contactspage')}}">@lang('main.contacts')</a></li>
                        <hr>
                        <li @if(session('locale')=='ru')
                                current
                                @endif><a href="{{ route('locale', 'ru') }}">Русский</a></li>
                        <li @if(session('locale')=='en')
                                current
                                @endif><a href="{{ route('locale', 'en') }}">English</a></li>
                    </ul>
                </nav>
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
    <div class="footer d-xl-block d-lg-block d-none">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <div class="logo">
                            <img src="{{ route('index') }}/img/logo_foot.svg" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <ul>
                            <li>{{ $contacts->first()->__('address') }}</li>
                            <li><a href="tel:{{ $contacts->first()->phone }}">{{ $contacts->first()->phone }}</a></li>
                            <li><a href="mailto:{{ $contacts->first()->email }}">{{ $contacts->first()->email }}</a></li>
                            <li><a href="{{ $contacts->first()->instagram }}" target="_blank">Instagram</a></li>
                            <li><a href="https://wa.me/{{ $contacts->first()->whatsapp }}" target="_blank">WhatsApp</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <ul>
                            <li><a href="{{ route('service') }}">@lang('main.about_service')</a></li>
                            <li><a href="{{ route('about') }}">@lang('main.about_company')</a></li>
                            <li><a href="#">@lang('main.blog')</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <ul>
                            <li><a href="{{ route('companies') }}">@lang('main.companies_services')</a></li>
                            <li><a href="{{ route('apartments') }}">@lang('main.apartments')</a></li>
                            <li><a href="{{ route('objects') }}">@lang('main.hotels_properties')</a></li>
                            <li><a href="{{ route('objects') }}">@lang('main.tour_operators')</a></li>
                            <li><a href="{{ route('rules') }}">@lang('main.booking_terms')</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row copy">
                <div class="col-lg-4 col-md-4">
                    <p>@lang('main.copy') &copy; {{ date('Y') }} staybook.asia</p>
                </div>
                <div class="col-lg-4 col-md-4 center">
                    <a href="{{ route('privacy') }}">@lang('main.privacy')</a>
                </div>
                <div class="col-lg-4 col-md-4 right">
                    <a href="{{ route('legal') }}">@lang('main.legal')</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer d-xl-none d-lg-none d-block">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <div class="logo">
                            <img src="{{ route('index') }}/img/logo_foot.svg" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="footer-item">
                        <ul>
                            <li>{{ $contacts->first()->__('address') }}</li>
                            <li><a href="tel:{{ $contacts->first()->phone }}">{{ $contacts->first()->phone }}</a></li>
                            <li><a href="mailto:{{ $contacts->first()->email }}">{{ $contacts->first()->email }}</a></li>
                            <li><a href="{{ $contacts->first()->instagram }}" target="_blank">Instagram</a></li>
                            <li><a href="https://wa.me/{{ $contacts->first()->whatsapp }}" target="_blank">WhatsApp</a>
                            </li>
                            <li><a href="{{ route('service') }}">@lang('main.about_service')</a></li>
                            <li><a href="{{ route('about') }}">@lang('main.about_company')</a></li>
                            <li><a href="#">@lang('main.blog')</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-6">
                    <div class="footer-item">
                        <ul>
                            <li><a href="{{ route('companies') }}">@lang('main.companies_services')</a></li>
                            <li><a href="{{ route('apartments') }}">@lang('main.apartments')</a></li>
                            <li><a href="{{ route('objects') }}">@lang('main.hotels_properties')</a></li>
                            <li><a href="{{ route('objects') }}">@lang('main.tour_operators')</a></li>
                            <li><a href="{{ route('rules') }}">@lang('main.booking_terms')</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row copy">
                <div class="col-lg-6">
                    <a href="{{ route('privacy') }}">@lang('main.privacy')</a>
                </div>
                <div class="col-lg-6">
                    <a href="{{ route('legal') }}">@lang('main.legal')</a>
                </div>
                <div class="col-12">
                    <p>@lang('main.copy') &copy; {{ date('Y') }} staybook.asia</p>
                </div>
            </div>
        </div>
    </div>
</footer>


<script src="{{ route('index') }}/js/scripts.min.js"></script>
<script>
    $(function () {
        $('#dynamic_select').on('change', function () {
            let url = $(this).val();
            if (url) {
                window.location = url;
            }
            return false;
        });
    });

</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('#cityhot').select2({
            placeholder: "@lang('admin.choose')",
            allowClear: true
        });
        $('#timezone').select2({
            placeholder: "@lang('admin.choose')",
            allowClear: true
        });
        $('#type').select2({
            placeholder: "@lang('admin.choose')",
            allowClear: true
        });
    });
</script>

</body>
</html>

