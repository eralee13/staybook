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
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/eliyantosarage/font-awesome-pro@main/fontawesome-pro-6.5.1-web/css/all.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" />
    <link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.1/dist/sweetalert2.min.css
" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/eliyantosarage/font-awesome-pro@main/fontawesome-pro-6.5.1-web/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/main.min.css">
    <link rel="stylesheet" href="{{route('index')}}/css/admin.css">
    <link rel="stylesheet" href="{{route('index')}}/css/main.min.css">
    <link rel="stylesheet" href="{{route('index')}}/css/style.css">
</head>
<body>

<header>
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                <div class="logo">
                    <a href="{{route('hotels.index')}}"><img src="{{ route('index') }}/img/logo.svg" alt="Stay
                    Book"></a>
                </div>
            </div>
            <div class="col-md-3">
                <form>
                    <select id="dynamic_select">
                        <option>@lang('main.choose')</option>
                        @foreach($hotels as $hotel)
                            <option @if($hotel->id == $hotel_id) selected @endif value="{{ route('hotels.show', $hotel)
                            }}" data-hotel="{{ $hotel->id }}">{{ $hotel->__('title')}}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="col-md-4">
                @if($hotel->status == 1)
                    <div class="status active"><i class="fa-solid fa-circle"></i> @lang('admin.active')</div>
                @else
                    <div class="status"><i class="fa-solid fa-circle"></i> @lang('admin.disable')</div>
                @endif
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
            </div>
            <div class="col-md-3">
                <div class="homelink">
                    <a href="{{route('index')}}" target="_blank"><i class="fas fa-house"></i> @lang('admin.visit')</a></a>
                </div>
            </div>
        </div>
    </div>
    <div class="head">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <nav>
                        <ul>
                            <li @routeactive('hotels.index')><a href="{{route('hotels.index')}}"><i class="fas
                            fa-hotel"></i> @lang('admin.hotels')</a></li>
                            <li @routeactive('bookings.index')><a href="{{route('bookings.index')}}"><i
                                        class="fa-regular
                            fa-tag"></i> @lang('admin.rates_and_availability')</a></li>
                            <li @routeactive('listbooks.index')><a href="{{route('listbooks.index')}}"><i
                                        class="fa-regular fa-tag"></i> @lang('admin.bookings')</a></li>
                            <li @routeactive('rooms.index')><a href="{{route('rooms.index')}}"><i class="fas
                            fa-booth-curtain"></i> @lang('admin.rooms')</a></li>
                            <li @routeactive('bills.index')><a href="{{route('bills.index')}}"><i class="fa-thin
                            fa-money-bills"></i> @lang('admin.bills')</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="col-md-4 person">
                    <a href="{{route('profile.edit')}}"><i class="fa-regular fa-address-card"></i>
                        @auth
                            @php
                                echo \Illuminate\Support\Facades\Auth::user()->name
                            @endphp
                        @else
                            @lang('admin.profile')
                        @endauth
                    </a>
                    <a href="{{route('logout')}}" class="delete"><i class="fa-regular fa-door-open"></i> @lang('admin.logout')</a>
                </div>
            </div>
        </div>
    </div>
</header>


@yield('content')

<footer>
    <div class="copy">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p>StayBook {{ date('Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</footer>

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
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var today = moment().day();
        let calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'resourceTimeline', 'interaction' ],
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
            selectable: true,
            events: [
                @foreach($bookings as $booking)
                {
                    resourceId: '{{ $booking->room_id }}',
                    title: '$ {{ $booking->price }}',
                    start: '{{ $booking->start_d }}',
                    end: '{{ $booking->end_d }}'
                },
                @endforeach
            ],
            resourceColumns: [
                {
                    labelText: '@lang('admin.room')',
                    field: 'building'
                },
                {
                    labelText: 'Тариф',
                    field: 'title'
                },
                {
                    labelText: '@lang('admin.price')',
                    field: 'price'
                },

            ],
            resources: [
                    @foreach($categories as $room)
                    @php
                        $r = \App\Models\Room::where('id', $room->room_id)->first();
                        //$plan = \App\Models\Category::where('room_id', $room->room_id)->first();
                    @endphp
                { id: '{{$r->id}}', building: '{{ $r->title }}', title: '{{$room->title}}', price:
                        '$ ' + {{ $r->price }} },
                @endforeach
            ],
            eventDrop: function(info) {
                $("#room_id").val(info.resource.id);
                let start_d = info.startStr;
                let end_d = info.endStr;
                $("#start_d").val(start_d);
                $("#end_d").val(end_d);
            },
            select: function(info) {
                $("#room_id").val(info.resource.id);
                let start_d = info.startStr;
                let end_d = info.endStr;
                $("#start_d").val(start_d);
                $("#end_d").val(end_d);
                $("#show_modal").modal("show");
            },
            selectHelper: true,
            validRange: {
                start: '2023-12-31',
                end: '2030-12-31'
            },

        });

        calendar.render();

        function changeStartDate(date) {
            calendar.gotoDate(date)
        }

        $('#saveBtn').click(function (){
           // let room_id = $('#room_id option:selected').val();
            let title = $(".modal").find("#title").val();
            let phone = $(".modal").find("#phone").val();
            let email = $(".modal").find("#email").val();
        });
    });

</script>

<style>
    .fc-view-container{
        background-color: #ddf7d9;
    }
    .fc-no-scrollbars, .fc-rows colgroup{
        background-color: #fff;
    }
    .fc-timeline-event .fc-time{
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
        "endDate": moment(new Date(),).add(1,'days'),
        "minDate": new Date(),
    }, function (start, end, label) {
        $('#start_d').val(start.format('YYYY-MM-DD'));
        $('#end_d').val(end.format('YYYY-MM-DD'));

    });

    $(function() {
        $('#dynamic_select').on('change', function() {
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
