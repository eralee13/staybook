@extends('auth.layouts.master')

@section('title', __('admin.plans_and_rules'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    @include('auth.layouts.subroom')
                    <h1>@lang('admin.plans_and_rules')</h1>
                    @if($rules->isNotEmpty())
                        <h3>@lang('admin.rules')</h3>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.title')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rules as $rule)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $rule->__('title') }}</td>
                                    <td>
                                        <form action="{{ route('rules.destroy', $rule) }}" method="post">
                                            <ul>
                                                <li><a class="btn edit" href="{{ route('rules.edit', $rule)
                                            }}"><i class="fa-regular fa-pen-to-square"></i></a></li>
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Do you want to delete this?');"
                                                        class="btn delete"><i class="fa-regular fa-trash"></i></button>
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $rules->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.rules_not_found')</h2>
                    @endif
                    <div class="btn-wrap" style="margin-top: 20px">
                        <a class="btn add" href="{{ route('rules.create') }}"><i class="fa-solid
                                fa-plus"></i> @lang('admin.add')</a>
                    </div>

                    @if($rates->isNotEmpty())
                        <h3>@lang('admin.plans')</h3>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.title')</th>
                                <th>@lang('admin.room')</th>
                                <th>@lang('admin.food')</th>
                                <th>@lang('admin.rules')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rates as $rate)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $rate->__('title') }}</td>
                                    <td>
                                        @php
                                            $cats = explode(', ', $rate->room_id);
                                            $rooms = \App\Models\Room::where('hotel_id', $hotel)->wherein('id', $cats)->get();
                                        @endphp
                                        @foreach($rooms as $room)
                                            {{ $room->__('title') }},
                                        @endforeach
                                    </td>
                                    <td>
                                        @isset($rate->food_id)
                                            {{ $rate->food_id }}
                                        @endisset
                                    </td>
                                    <td>
                                        @isset($rate->rule)
                                            {{ $rate->rule->__('title') }}
                                        @endisset
                                    </td>
                                    <td>
                                        <form action="{{ route('rates.destroy', $rate) }}" method="post">
                                            <ul>
                                                <li><a class="btn edit" href="{{ route('rates.edit', $rate)
                                            }}"><i class="fa-regular fa-pen-to-square"></i></a></li>
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Do you want to delete this?');"
                                                        class="btn delete"><i class="fa-regular fa-trash"></i></button>
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $rates->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.rates_not_found')</h2>
                    @endif
                    <div class="btn-wrap" style="margin-top: 20px">
                        <a class="btn add" href="{{ route('rates.create') }}"><i class="fa-solid
                                fa-plus"></i> @lang('admin.add')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
