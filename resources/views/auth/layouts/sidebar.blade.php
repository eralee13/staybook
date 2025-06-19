<div class="sidebar">
{{--    @php--}}
{{--        if($hotel == null){--}}
{{--            $hotel = 14;--}}
{{--        }--}}
{{--    @endphp--}}
    <ul>
        @can('edit-hotel')
            <li @routeactive('dashboard*') class="hotel-list"><a href="{{route('dashboard')}}"><img src="{{ route('index') }}/img/icons/home.svg" alt=""> Консоль</a></li>
                <li @routeactive(
                'hotel*') class="hotel-list"><a href="{{route('hotels.index')}}"><img src="{{ route('index') }}/img/icons/home.svg" alt=""> @lang('admin.hotels')</a></li>
                <li @routeactive(
                'bookings.index') class="price-list"><a href="{{route('bookcalendar.index')}}"><img src="{{ route('index') }}/img/money.svg" alt=""> @lang('admin.rates_and_availability')</a></li>
                <li @routeactive(
                'listbooks.index') class="price-list"><a href="{{route('listbooks.index')}}"><img src="{{ route('index') }}/img/money.svg" alt=""> @lang('admin.bookings')</a></li>
                <li @routeactive(
                'rooms.index') class="room-list"><a href="{{route('rooms.index')}}"><img src="{{ route('index') }}/img/icons/bed.svg" alt=""> @lang('admin.rooms')</a></li>
               <li @routeactive(
                'bills.index')><a href="{{route('bills.index')}}"><img src="{{ route('index') }}/img/icons/file.svg" alt=""> @lang('admin.bills')</a></li>

{{--            <li @routeactive('servic*')><a href="{{ route('amenities.index')}}"><i class="fa-regular--}}
{{--            fa-bell-concierge"></i> @lang('admin.amenities')</a></li>--}}
{{--            <li @routeactive('payment*')>--}}
{{--            <a href="{{ route('payments.index')}}"><i class="fa-regular fa-money-bill"></i> @lang('admin.payment')</a>--}}
{{--            </li>--}}
        @endcan
            @hasrole('Accountant')
            <li @routeactive(
                'allbooks.index') class="price-list"><a href="{{route('allbooks.index')}}"><img src="{{ route('index') }}/img/money.svg" alt=""> Все бронирования</a></li>
            <li @routeactive(
            'allbills.index')><a href="{{route('allbills.index')}}"><img src="{{ route('index') }}/img/icons/file.svg" alt=""> Все счета</a></li>
            @endhasrole
{{--            @hasrole('B2B')--}}
{{--            <li @routeactive('userbook*')>--}}
{{--        <a href="{{ route('userbooks.index')}}"><i class="fa-regular fa-money-bill"></i> @lang('admin.my_bookings')</a>--}}
{{--        </li>--}}
{{--            @endhasrole--}}
            <li @routeactive('userbook*')>
            <a href="{{ route('userbooks.index')}}"><i class="fa-regular fa-money-bill"></i> @lang('admin.my_bookings')</a>
            </li>

        <li><a href="{{ route('logout') }}">Выйти из системы</a></li>
    </ul>
</div>
