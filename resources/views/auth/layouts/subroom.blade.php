<ul class="btns">
    <li @routeactive('rate*')><a href="{{ route('rates.index') }}">@lang('admin.plans')</a></li>
    <li @routeactive('cancel*')><a href="{{ route('cancellations.index') }}">@lang('admin.cancel_fines')</a></li>
</ul>
