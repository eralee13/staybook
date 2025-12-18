@extends('auth.layouts.master')

@section('title', __('admin.cancellations'))

@section('content')

    <div class="page admin">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @include('auth.layouts.subroom')
                    <div class="row">
                        <div class="col-md-12">
                            <h1>@lang('admin.cancel_fines')</h1>
                        </div>
                    </div>
                    <div class="row room_list_btn">
                        <div class="col-md-4">
                            <a href="{{ route('rates.index') }}" @routeactive('rate*')>@lang('admin.plans')</a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('cancellations.index') }}" @routeactive('cancel*')>@lang('admin.cancel_fines')</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn add" href="{{ route('cancellations.create') }}">@lang('admin.add')</a>
                        </div>
                    </div>

                    @if($rules->isNotEmpty())
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>@lang('admin.room')</th>
                                    <th>@lang('admin.title')</th>
                                    <th>@lang('admin.type_fine')</th>
                                    <th>@lang('admin.rate')</th>
                                    <th>@lang('admin.action')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($rules as $cancellation)
                                    @php
                                        $rate = \App\Models\Rate::find($cancellation->rate_id);
                                        $room = $rate ? \App\Models\Room::find($rate->room_id) : null;
                                    @endphp
                                    <tr>
                                        <td>{{ optional($room)->__('title') }}</td>
                                        <td>{{ optional($rate)->__('title') }}</td>
                                        <td>
                                            @if($cancellation->cancel_policy === 'free_until_checkin')
                                                @lang('admin.free_until_checkin')
                                            @elseif($cancellation->cancel_policy === 'free_then_penalty')
                                                @lang('admin.free_then_penalty')
                                            @else
                                                @lang('admin.non_refundable')
                                            @endif
                                        </td>
                                        <td>{{ optional($cancellation->rate)->__('title') }}</td>
                                        <td>
                                            <form action="{{ route('cancellations.destroy', $cancellation) }}" method="post">
                                                <ul>
                                                    <li><a href="{{ route('cancellations.edit', $cancellation)
                                            }}"><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></a></li>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="return confirm('Do you want to delete this?');"><img src="{{ route('index') }}/img/icons/trash.svg" alt=""></a></button>
                                                </ul>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $rules->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.rules_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
