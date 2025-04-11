<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - StayBook</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <link rel="icon" href="{{route('index')}}/img/favicon.jpg">
    <link rel="apple-touch-icon" sizes="180x180" href="{{route('index')}}/img/favicon.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">
    <link
        href="https://cdn.jsdelivr.net/gh/eliyantosarage/font-awesome-pro@main/fontawesome-pro-6.5.1-web/css/all.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css"/>
    <link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.1/dist/sweetalert2.min.css
" rel="stylesheet">
    <link
        href="https://cdn.jsdelivr.net/gh/eliyantosarage/font-awesome-pro@main/fontawesome-pro-6.5.1-web/css/all.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="/css/main.min.css">
    <link rel="stylesheet" href="{{route('index')}}/css/admin.css">
    <link rel="stylesheet" href="{{route('index')}}/css/main.min.css">
    <link rel="stylesheet" href="{{route('index')}}/css/style.css">
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


@yield('content')


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<link rel="stylesheet" href="https://unpkg.com/@fullcalendar/core@4.3.1/main.min.css">
<link rel="stylesheet" href="https://unpkg.com/@fullcalendar/timeline@4.3.0/main.min.css">
<link rel="stylesheet" href="https://unpkg.com/@fullcalendar/resource-timeline@4.3.0/main.min.css">

<script src="https://unpkg.com/@fullcalendar/core@4.3.1/main.min.js"></script>
<script src="https://unpkg.com/@fullcalendar/timeline@4.3.0/main.min.js"></script>
<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
<script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script>
<script src="https://unpkg.com/@fullcalendar/interaction@4.3.0/main.min.js"></script>
<script src="https://unpkg.com/@fullcalendar/resource-common@4.3.1/main.min.js"></script>
<script src="https://unpkg.com/@fullcalendar/resource-timeline@4.3.0/main.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        let calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: ['resourceTimeline', 'interaction'],
            header: {
                left: 'today prev,next',
                center: 'title',
                right: ''
            },
            aspectRatio: 1.5,
            defaultView: 'resourceTimelineMonth',
            resourceAreaWidth: '50%',
            resourceGroupField: 'building',
            eventColor: '#ffabab',
            eventTextColor: 'red',
            timezone: 'Asian/Bishkek',
            locale: 'en',
            editable: true,
            events: [
                    @foreach($bookings as $booking)
                {
                    resourceId: '{{ $booking->room_id }}',
                    title: '{{ $booking->adult }}',
                    start: '{{ $booking->arrivalDate }}',
                    end: '{{ $booking->departureDate }}',
                    //category_id: '{{ $booking->category_id }}'
                },
                @endforeach
            ],
            resourceColumns: [
                {
                    labelText: 'Номер',
                    field: 'building'
                },
                {
                    labelText: 'Тариф',
                    field: 'title',
                },
                {
                    labelText: '@lang('admin.price')',
                    field: 'price'
                },
                {
                    labelText: 'ID',
                    field: 'cat'
                },

            ],
            resources: [
                    @foreach($categories as $category)
                    @php
                        $room = \App\Models\Room::where('id', $category->room_id)->first();
                    @endphp
                {
                    id: '{{$room->id}}', building: '{{ $room->title }}', title: '{{$category->title}}', price:
                        '$ ' + {{ $room->price }}, ca: '{{ $category->id }}'
                },
                @endforeach
            ],
            dateClick: function (info) {
                $("#room_id").val(info.resource.id);
                //$("#category_id").val(info.resource.cat);
                let arrival = info.startStr;
                let departure = info.endStr;
                $("#arrival").val(arrival);
                $("#departure").val(departure);
            },
            select: function (info) {
                $("#room_id").val(info.resource.id);
                //$("#category_id").val(info.resource.cat);
                let arrival = info.startStr;
                let departure = info.endStr;
                $("#arrival").val(arrival);
                $("#departure").val(departure);
                $("#show_modal").modal("show");
            },
            selectHelper: true,
            selectable: true,
            validRange: {
                start: '2024-12-31',
                end: '2030-12-31'
            },

        });

        calendar.render();

        $('#saveBtn').click(function () {
            // let room_id = $('#room_id option:selected').val();
            let title = $(".modal").find("#title").val();
            let phone = $(".modal").find("#phone").val();
            let email = $(".modal").find("#email").val();
        });
    });

</script>

<style>
    .fc-view-container {
        background-color: #ddf7d9;
    }

    .fc-no-scrollbars, .fc-rows colgroup {
        background-color: #fff;
    }

    .fc-timeline-event .fc-time {
        display: none;
    }
</style>

<link rel="stylesheet" href="{{ route('index') }}/css/daterangepicker.css">

<script src="{{ route('index') }}/js/daterangepicker.min.js"></script>

<script>
    $('#date').daterangepicker({
        "autoApply": true,
        "locale": {
            "format": "DD/MM/YYYY",
            "separator": " - ",
            "applyLabel": "Apply",
            "cancelLabel": "Cancel",
            "fromLabel": "From",
            "toLabel": "To",
            "customRangeLabel": "Custom",
            "weekLabel": "W",
            "daysOfWeek": [
                "Вс",
                "Пн",
                "Вт",
                "Ср",
                "Чт",
                "Пт",
                "Сб"
            ],
            "monthNames": [
                "Январь",
                "Февраль",
                "Март",
                "Апрель",
                "Maй",
                "Июнь",
                "Июль",
                "Август",
                "Сентябрь",
                "Октябрь",
                "Ноябрь",
                "Декабрь"
            ],
            "firstDay": 1
        },
        "startDate": new Date(),
        "endDate": moment(new Date(),).add(1, 'days'),
        "minDate": new Date(),
    }, function (start, end, label) {
        $('#arrival').val(start.format('YYYY-MM-DD'));
        $('#departure').val(end.format('YYYY-MM-DD'));

    });

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

</body>
</html>
