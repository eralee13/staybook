<ul class="btns">
{{--    <li @routeactive('categ*')><a href="{{ route('categoryRooms.index') }}">@lang('admin.categoryRooms')</a></li>--}}
    <li @routeactive('rate*')><a href="{{ route('rates.index') }}">@lang('admin.plans')</a></li>
{{--    @can('edit-meal')--}}
{{--        <li @routeactive('meal*')><a href="{{ route('meals.index') }}">@lang('admin.meals')</a></li>--}}
{{--    @endcan--}}
    <li @routeactive('cancel*')><a href="{{ route('cancellations.index') }}">Отмена и штрафы</a></li>
</ul>
