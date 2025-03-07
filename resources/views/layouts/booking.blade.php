<!DOCTYPE html>
<html lang="ru">

<head>

    <meta charset="utf-8">
    <!-- <base href="/"> -->

    <title>@yield('title') - SilkWayTravel</title>
    <meta name="description" content="">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Template Basic Images Start -->
    <meta property="og:image" content="path/to/image.jpg">
    <link rel="icon" href="{{route('index')}}/img/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="{{route('index')}}/img/favicon.png">
    <!-- Template Basic Images End -->

    <!-- Custom Browsers Color Start -->
    <meta name="theme-color" content="#000">
    <!-- Custom Browsers Color End -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{route('index')}}/css/main.min.css">
    <link rel="stylesheet" href="{{route('index')}}/css/style.css">

</head>

<body>
<header>
    <div class="container">
        <div class="row">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="logo">
                    <a href="{{route('homepage')}}"><img src="{{ url('/') }}/img/logo.png" alt=""></a>
                </div>
            </div>
            <div class="col-lg-10 col-md-8 col-6">
                <ul class="lang d-xl-none d-lg-none d-inline-block">
                    <li class="
                            @if(session('locale')=='ru')
                                current
                            @endif
                            "><a href="{{ route('locale', 'ru') }}">RU</a></li>
                    <li class="
                            @if(session('locale')=='en')
                                current
                            @endif
                            "><a href="{{ route('locale', 'en') }}">EN</a></li>
                </ul>
                <nav>
                    <a href="#" class="toggle-mnu d-xl-none d-lg-none"><span></span></a>
                    <ul>
                        <li @routeactive(
                        'homepage')><a href="{{route('homepage')}}">@lang('main.home')</a></li>
                        <li @routeactive(
                        'hotels')><a href="{{route('hotels')}}">@lang('main.hotels')</a></li>
                        <li @routeactive(
                        'allrooms')><a href="{{route('allrooms')}}">@lang('main.rooms')</a></li>
                        <li @routeactive(
                        'about')><a href="{{route('about')}}">@lang('main.about')</a></li>
                        <li @routeactive(
                        'contactspage')><a href="{{route('contactspage')}}">@lang('main.contacts')
                        </a></li>
                    </ul>
                    <ul class="lang d-xl-inline-block d-lg-inline-block d-none">
                        <li class="
                            @if(session('locale')=='ru')
                                current
                            @endif
                            "><a href="{{ route('locale', 'ru') }}">RU</a></li>
                        <li class="
                            @if(session('locale')=='en')
                                current
                            @endif
                            "><a href="{{ route('locale', 'en') }}">EN</a></li>
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
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-md-6">
                    <div class="footer-item">
                        <div class="logo"><img src="{{ url('/') }}/img/logo.png" alt=""></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <h4>@lang('main.hotels')</h4>
                        <ul>
                            @foreach($hotels as $hotel)
                                <li><a href="{{ route('hotel', $hotel->code) }}">{{ $hotel->__('title')
                                }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <h4>@lang('main.navigation')</h4>
                        <ul>
                            <li @routeactive(
                            'hotels')><a href="{{route('hotels')}}">@lang('main.hotels')</a></li>
                            <li @routeactive(
                            'allrooms')><a href="{{route('allrooms')}}">@lang('main.rooms')</a></li>
                            <li @routeactive(
                            'about')><a href="{{route('about')}}">@lang('main.about')</a></li>
                            <li @routeactive(
                            'contactspage')><a href="{{route('contactspage')}}">@lang('main.contacts')
                            </a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="footer-item">
                        <h4>@lang('main.contacts')</h4>
                        <ul>
                            <li>{{ $contacts->first()->__('address') }}</li>
                            <li><a href="tel:{{ $contacts->first()->phone }}">{{ $contacts->first()->phone }}</a></li>
                            <li><a href="tel:{{ $contacts->first()->phone2 }}">{{ $contacts->first()->phone2 }}</a></li>
                        </ul>
                        <ul class="soc">
                            <li><a href="{{ $contacts->first()->instagram }}" target="_blank"><img src="{{route('index')}}/img/instagram.svg" alt=""></a>
                            </li>
                            <li><a href="https://wa.me/{{ $contacts->first()->whatsapp }}" target="_blank"><img src="{{route('index')}}/img/whatsapp.svg" alt=""></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copy">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p>@lang('main.copy') &copy; {{ date('Y') }} silkwaytravel.kg</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/intlTelInput.min.js"></script>
<script src='{{route('index')}}/js/ru.js'></script>

<style>
    .fc-day-grid-event .fc-time{
        display: none;
    }
    tr:first-child>td>.fc-day-grid-event{
        height: 75px;
        margin: 0;
    }
</style>
<script>
    $("nav").find('.toggle-mnu').click(function () {
        $(this).toggleClass("on");
        $("nav ul").slideToggle().toggleClass("active");
        $("body").toggleClass("active");
        return false;
    });

    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let events = @json($events);
        let date = new Date();
        let today = date.setDate(date.getDate() - 1);
        $('#calendar').fullCalendar({
            header: {
                left: "prev,next today",
                center: "title",
                right: "month"
            },
            eventColor: '#0163b4',
            eventTextColor: '#fff',
            timezone: 'Asian/Bishkek',
            @if(session('locale')=='ru')
                locale: 'ru',
            @else
                locale: 'en',
            @endif
            //eventOverlap: false,
            selectOverlap: false,
            events: events,
            selectable: true,
            longPressDelay: 0,
            selectHelper: true,
            validRange: {
                start: today,
                end: '2024-12-31'
            },
            select: function(start, end) {
                let start_d = $.fullCalendar.formatDate(start, "Y-MM-DD");
                let end_d = $.fullCalendar.formatDate(end, "Y-MM-DD");
                $("#start_d").val(start_d);
                $("#end_d").val(end_d);
                $("#show_modal").modal("show");

                $("#count, #countc").change(function(){
                    let price = $('#price').text();
                    let pricec = $('#pricec').text();
                    let count = $('#count').val();
                    let countc = $('#countc').val();
                    let start_d = $.fullCalendar.formatDate(start, "Y-MM-DD");
                    let end_d = $.fullCalendar.formatDate(end, "Y-MM-DD");
                    let st = new Date(start_d);
                    let en = new Date(end_d);
                    let millisecondsPerDay = 1000 * 60 * 60 * 24;
                    let millisBetween = en.getTime() - st.getTime();
                    let days = millisBetween / millisecondsPerDay;
                    let sum = (price * count * days) + (pricec * countc * days);
                    $('#sum').val(sum + ' сом');
                });
                
                $('#saveBtn').click(function (){
                    let room_id = $(".modal").find("#room_id").val();
                    let title = $(".modal").find("#title").val();
                    let phone = $(".modal").find("#phone").val();
                    let email = $(".modal").find("#email").val();
                    let comment = $(".modal").find("#comment").val();
                    let count = $(".modal").find("#count").val();
                });
            },
            editable: false,
            eventRender: function (event, element) {
                element.find('.fc-title').append("<br/>" + "<span class='busy'>@lang('main.busy')</span>");
            }
        });
    });

    const input = document.querySelector("#phone");
    const output = document.querySelector("#output");

    const iti = window.intlTelInput(input, {
        nationalMode: true,
        initialCountry: 'kg',
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.1.1/build/js/utils.js" // just for formatting/placeholders etc
    });

    const handleChange = () => {
        let text;
        if (input.value) {
            text = iti.isValidNumber()
                ? "Действительный номер " + iti.getNumber()
                : "Неверный номер - попробуйте еще раз";
        } else {
            text = "Пожалуйста, введите действительный номер";
        }
        if (iti.isValidNumber()) {
            output.classList.add("agree");
            document.getElementById("saveBtn").disabled = false;
        } else {
            output.classList.remove("agree");
            document.getElementById("saveBtn").disabled = true;
        }
        const textNode = document.createTextNode(text);
        output.innerHTML = "";
        output.appendChild(textNode);
    };

    input.addEventListener('change', handleChange);
    input.addEventListener('keyup', handleChange);
</script>
</body>
</html>

