<!DOCTYPE html>
<html lang="ru">

<head>
    <title>@yield('title') - StayBook</title>
    <meta name="description" content="Staybook- прямая связь с отелями Центральной Азии и Кавказа для B2B-партнёров">
    <link rel="icon" href="{{route('index')}}/img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{route('index')}}/img/favicon.png">
    <!-- Template Basic Images End -->

    <!-- Custom Browsers Color Start -->
    <meta name="theme-color" content="#000">
    <!-- Custom Browsers Color End -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@200..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{route('index')}}/css/main.min.css?ver=1.6">
    <link rel="stylesheet" href="{{route('index')}}/css/style.css?ver=1.1">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JWCJ1YQVHX"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-JWCJ1YQVHX');
    </script>


</head>

<body>
{{--<div id="preloader">--}}
{{--    <div class="loader"></div>--}}
{{--</div>--}}

<div class="main-wrap">
    <div class="overlay"></div>
    <header>
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-4">
                    <div class="logo">
                        <a href="{{route('index')}}">
                            <img src="{{route('index')}}/img/logo.svg" alt="">
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 d-xl-block d-lg-block d-none">
                    <div class="menu-wrap">
                        <ul>
                            <li><a href="{{route('service')}}">@lang('main.about_service')</a></li>
                            <li><a href="{{route('contactspage')}}">@lang('main.contacts')</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-5 d-xl-block d-lg-block d-none">
                    <div class="wrap">
                        <div class="lang-wrap" id="lang">
                            <div class="currency">{{ $fxBase }}</div>
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
                                    <li class="current" data-tab="tab-1">@lang('main.currency')</li>
                                    <li data-tab="tab-2">@lang('main.language')</li>
                                </ul>
                                <div class="tab-content current" id="tab-1">
                                    <ul class="currency-switcher">
                                        @foreach(['USD', 'KGS', 'RUB'] as $ccy)
                                            <li>
                                                <a class="{{ $fxBase === $ccy ? 'current' : '' }}"
                                                   href="{{ route('currency.switch', $ccy) }}">
                                                    @if($ccy === 'USD')
                                                        USD
                                                    @elseif($ccy === 'KGS')
                                                        KGS
                                                    @else
                                                        RUB
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="tab-content" id="tab-2">
                                    <ul>
                                        <li @if(session('locale')=='ru')
                                                current
                                                @endif><a href="{{ route('locale', 'ru') }}">RU</a></li>
                                        <li @if(session('locale')=='en')
                                                current
                                                @endif><a href="{{ route('locale', 'en') }}">EN</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="auth">
                            @auth
                                <a href="{{ route('profile.edit') }}">{{ \Illuminate\Support\Facades\Auth::user()->name }}</a>
                            @else
                                <a href="{{ route('login') }}">@lang('main.login')</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-10 col-md-9 col-8 d-xl-none d-lg-none d-block">
                    <div class="wrap">
                        <div class="auth">
                            @auth
                                <a href="{{ route('profile.edit') }}">{{ \Illuminate\Support\Facades\Auth::user()->name }}</a>
                            @else
                                <a href="{{ route('login') }}">@lang('main.login')</a>
                            @endif
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
                                <li>{{ $contacts['address'] ?? '' }}</li>
                                <li><a href="tel:{{ $contacts['phone'] ?? '' }}">{{ $contacts['phone'] ?? '' }}</a></li>
                                <li><a href="mailto:{{ $contacts['email'] ?? '' }}">{{ $contacts['email'] ?? '' }}</a></li>
                                <li><a href="{{ $contacts['instagram'] ?? '' }}" target="_blank">Instagram</a></li>
                                <li><a href="https://wa.me/{{ $contacts['whatsapp'] ?? '' }}" target="_blank">WhatsApp</a>
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
                                <li>{{ $contacts['address'] ?? '' }}</li>
                                <li><a href="tel:{{ $contacts['phone'] ?? '' }}">{{ $contacts['phone'] ?? '' }}</a></li>
                                <li><a href="mailto:{{ $contacts['email'] ?? '' }}">{{ $contacts['email'] ?? '' }}</a></li>
                                <li><a href="{{ $contacts['instagram'] ?? '' }}" target="_blank">Instagram</a></li>
                                <li><a href="https://wa.me/{{ $contacts['whatsapp'] ?? '' }}" target="_blank">WhatsApp</a>
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
                    <div class="col-6">
                        <a href="{{ route('privacy') }}">@lang('main.privacy')</a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('legal') }}">@lang('main.legal')</a>
                    </div>
                    <div class="col-12">
                        <p>@lang('main.copy') &copy; {{ date('Y') }} staybook.asia</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>


    <script src="{{ route('index') }}/js/scripts.min.js?ver=1.2"></script>

    {{--<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>--}}
        <script>
            $(function () {
            let start = moment($('#arrivalDate').val(), 'YYYY-MM-DD', true);
            let end   = moment($('#departureDate').val(), 'YYYY-MM-DD', true);

            if (!start.isValid()) start = moment().startOf('day');
            if (!end.isValid() || end.isSameOrBefore(start)) end = start.clone().add(1, 'day');

            let selectingDeparture = false;

            const picker = $('#arrivalDisplay').daterangepicker({
            singleDatePicker: true,         // <— теперь выбирается только одна дата!
            autoApply: true,
            autoUpdateInput: false,
            minDate: moment().startOf('day'),
            locale: {
            format: "DD.MM.YYYY",
            applyLabel: "Применить",
            cancelLabel: "Отмена",
            fromLabel: "С",
            toLabel: "По",
            customRangeLabel: "Произвольно",
            weekLabel: "W",
            daysOfWeek: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
            monthNames: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
            firstDay: 1
        }
        }, function (chosenDate) {
            if (selectingDeparture) {
            const checkin = moment($('#arrivalDate').val(), 'YYYY-MM-DD');
            let checkout = chosenDate.clone().startOf('day');

            // не позволяем выезду быть раньше въезда
            if (checkout.isSameOrBefore(checkin, 'day')) {
            checkout = checkin.clone().add(1, 'day');
        }
            $('#departureDate').val(checkout.format('YYYY-MM-DD'));
            $('#departureDisplay').val(checkout.format('DD.MM.YYYY'));
        } else {
            const checkin = chosenDate.clone().startOf('day');
            const checkout = checkin.clone().add(1, 'day');
            $('#arrivalDate').val(checkin.format('YYYY-MM-DD'));
            $('#departureDate').val(checkout.format('YYYY-MM-DD'));
            $('#arrivalDisplay').val(checkin.format('DD.MM.YYYY'));
            $('#departureDisplay').val(checkout.format('DD.MM.YYYY'));
        }
            selectingDeparture = false;
        });

            // начальные значения
            $('#arrivalDisplay').val(start.format('DD.MM.YYYY'));
            $('#departureDisplay').val(end.format('DD.MM.YYYY'));

            // открытие календаря по клику на выезд
            $('#departureDisplay').on('focus click', function () {
            selectingDeparture = true;
            picker.data('daterangepicker').setStartDate(moment($('#departureDate').val(), 'YYYY-MM-DD'));
            picker.data('daterangepicker').show();
        });
        });
    </script>

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () {
                (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return;
                }
            }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(103165450, "init", {
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            webvisor: true
        });
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/103165450" style="position:absolute; left:-9999px;" alt=""/></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->

</body>

</html>
