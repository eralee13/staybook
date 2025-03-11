<ul class="btns">
    <li @routeactive('categ*')><a href="{{ route('categoryRooms.index') }}">@lang('admin.categoryRooms')</a></li>
    <li @routeactive('rate*')><a href="{{ route('rates.index') }}">@lang('admin.plans_and_rules')</a></li>
    @can('edit-food')
        <li @routeactive('meal*')><a href="{{ route('meals.index') }}">@lang('admin.meals')</a></li>
    @endcan
    <li @routeactive('accommodation*')><a href="{{ route('accommodations.index') }}">@lang('admin.accommodations')</a></li>
</ul>
