<div class="sidebar">
    <ul>
        <li @routeactive('bill*')><a href="{{ route('bills.index')}}"><i class="fas fa-gauge"></i>
            @lang('admin.information')</a></li>
        @can('edit-book')
            <li @routeactive('listbooks.index')><a href="{{route('listbooks.index')}}"><i class="fa-regular
            fa-bell-concierge"></i> @lang('admin.bookings')</a></li>
            <li @routeactive('servic*')><a href="{{ route('services.index')}}"><i class="fa-regular
            fa-bell-concierge"></i> @lang('admin.services')</a></li>
            <li @routeactive('payment*')>
            <a href="{{ route('payments.index')}}"><i class="fa-regular fa-money-bill"></i> @lang('admin.payment')</a>
            </li>
        @endcan
    </ul>
</div>
