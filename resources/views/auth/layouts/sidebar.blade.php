<div class="sidebar">
    @php
        if($hotel == null){
            $hotel = 14;
        }
    @endphp
    <ul>
        @can('edit-hotel')
            <li @routeactive('hotel*')><a href="{{ route('hotels.show', $hotel)}}"><i class="fas fa-gauge"></i>
                @lang('admin.information')</a></li>
            <li @routeactive('servic*')><a href="{{ route('amenities.index')}}"><i class="fa-regular
            fa-bell-concierge"></i> @lang('admin.amenities')</a></li>
            <li @routeactive('payment*')>
            <a href="{{ route('payments.index')}}"><i class="fa-regular fa-money-bill"></i> @lang('admin.payment')</a>
            </li>
        @endcan
        <li @routeactive(
        'userbook*')>
        <a href="{{ route('userbooks.index')}}"><i class="fa-regular fa-money-bill"></i> @lang('admin.bookings')</a>
        </li>
    </ul>
</div>
