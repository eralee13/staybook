<ul class="btns">
    <li @routeactive('cats*')><a href="{{ route('cats.index') }}">@lang('admin.cats')</a></li>
    <li @routeactive('categ*')><a href="{{ route('categories.index') }}">@lang('admin.plans_and_rules')</a></li>
    @can('edit-food')
        <li @routeactive('food*')><a href="{{ route('foods.index') }}">@lang('admin.foods')</a></li>
    @endcan
    <li @routeactive('child*')><a href="{{ route('childs.index') }}">@lang('admin.childs')</a></li>
</ul>
