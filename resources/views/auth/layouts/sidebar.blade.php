<div class="sidebar">
    <ul>
        @can('edit-hotel')
            @if( session()->has('hotel_id'))
                <li @routeactive('dashboard*') class="hotel-list">
                    <a href="{{route('dashboard')}}">@lang('admin.dashboard')</a>
                </li>
            @else
                <li @routeactive('console*') class="hotel-list">
                <a href="{{route('console')}}">Консоль</a>
                </li>
            @endif
            <li @routeactive('hotel*') class="hotel-list">
                <a href="{{route('hotels.index')}}">@lang('admin.hotel')</a>
            </li>
            @if( session()->has('hotel_id') )
                <li @routeactive('bookings.index') class="price-list">
                    <a href="{{route('bookcalendar.index')}}">@lang('admin.rates_and_availability')</a>
                </li>
                <li @routeactive('listbook*') @routeactive('userbook*') @routeactive('allbook*')ss="price-list">
                    <a href="{{route('listbooks.index')}}">@lang('admin.bookings')</a>
                </li>
                <li @routeactive('room*') @routeactive('rate*') @routeactive('cancel*') class="room-list">
                    <a href="{{route('rooms.index')}}">@lang('admin.rooms')</a>
                </li>
                <li @routeactive('bills.index')>
                    <a href="{{route('bills.index')}}">@lang('admin.bills')</a>
                </li>
            @endif
        @endcan
        @hasrole('Accountant')
            <li @routeactive('allbooks.index') class="price-list">
                <a href="{{route('allbooks.index')}}">@lang('admin.bookings')</a>
            </li>
            <li @routeactive('allbills.index')>
                <a href="{{route('allbills.index')}}">@lang('admin.bills')</a>
            </li>
        @endhasrole
        @hasrole('B2B')
            <li @routeactive('userbook*')>
                <a href="{{ route('userbooks.index')}}">@lang('admin.my_bookings')</a>
            </li>
            @else
                <li @routeactive('userbook*')><a href="{{ route('userbooks.index') }}">@lang('admin.my_bookings')</a></li>
        @endhasrole
            <li><a href="{{ route('profile.edit') }}">Профиль</a></li>
        <li style="margin-top: 60px"><a href="{{ route('logout') }}">@lang('admin.logout')</a></li>
        <li><a href="{{route('index')}}" target="_blank">@lang('admin.visit')</a></li>
    </ul>
</div>
