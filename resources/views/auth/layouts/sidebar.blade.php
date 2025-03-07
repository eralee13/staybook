<div class="sidebar">
    @php
        if($hotel == null){
            $hotel = 14;
        }
    @endphp
    <ul>
        <li @routeactive('hotel*')><a href="{{ route('hotels.show', $hotel)}}"><i class="fas fa-gauge"></i>
            @lang('admin.information')</a></li>
        <li @routeactive('servic*')><a href="{{ route('services.index')}}"><i class="fa-regular
            fa-bell-concierge"></i> @lang('admin.services')</a></li>
        <li @routeactive('payment*')>
        <a href="{{ route('payments.index')}}"><i class="fa-regular fa-money-bill"></i> @lang('admin.payment')</a>
        </li>
    </ul>
</div>
