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

<body>
<header>
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                <div class="logo">
                    <a href="{{route('hotels.index')}}"><img src="{{ route('index') }}/img/logo.svg"
                                                             alt="Stay Book"></a>
                </div>
            </div>
            @can('edit-hotel')
                @empty($hotels)

                @else
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
                @empty($hotel)
                    @else
                @if($hotel->status == 1)
                <div class="status active"><i class="fa-solid fa-circle"></i> @lang('admin.active')</div>
                    @else
                    <div class="status"><i class="fa-solid fa-circle"></i> @lang('admin.disable')</div>
                @endif
                @endempty
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

                @endempty
            @endcan
            <div class="col-md-3">
                <div class="homelink">
                    <a href="{{route('index')}}" target="_blank"><i class="fas fa-house"></i> @lang('admin.visit')
                    </a>
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
                            @can('edit-hotel')
                                <li @routeactive('hotels.index')><a href="{{route('hotels.index')}}"><i class="fas
                                fa-hotel"></i> @lang('admin.hotels')</a></li>
                                <li @routeactive('bookings.index')><a href="{{route('bookings.index')}}"><i
                                            class="fa-regular
                                fa-tag"></i> @lang('admin.rates_and_availability')</a></li>
                                <li @routeactive('listbooks.index')><a href="{{route('listbooks.index')}}"><i
                                            class="fa-regular fa-tag"></i> @lang('admin.bookings')</a></li>
                                <li @routeactive('rooms.index')><a href="{{route('rooms.index')}}"><i class="fas
                                fa-booth-curtain"></i> @lang('admin.rooms')</a></li>
                            @endcan
                            @can('edit-bill')
                                <li @routeactive('bills.index')><a href="{{route('bills.index')}}"><i class="fa-thin
                            fa-money-bills"></i> @lang('admin.bills')</a></li>
                            @endcan
                        </ul>
                    </nav>
                </div>
                @auth
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
                @endauth
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
    @can('edit-page')
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
    <div class="copy">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p>@lang('admin.all_rights') &copy; {{ date('Y') }} StayBook</p>
                </div>
            </div>
        </div>
    </div>
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

    $(document).ready(function () {
        $('#country').selectize({
            sortField: 'text'
        });
    });
</script>

</body>
</html>

