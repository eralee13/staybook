@extends('auth.layouts.master')

@section('title', __('admin.plans'))

@section('content')

    <div class="page admin">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @include('auth.layouts.subroom')
                    <h1>@lang('admin.plans')</h1>
                    <div class="row room_list_btn">
                        <div class="col-md-4">
                            <a href="{{ route('rates.index') }}" @routeactive('rate*')>@lang('admin.plans')</a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('cancellations.index') }}" @routeactive('cancel*')>@lang('admin.cancel_fines')</a>
                        </div>
                        <div class="col-md-4">
                            <a class="btn add" href="{{ route('rates.create') }}">@lang('admin.add')</a>
                        </div>
                    </div>
                    @if($rates->isNotEmpty())
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('admin.title')</th>
                                    <th>@lang('admin.room')</th>
                                    <th>@lang('admin.food')</th>
                                    <th>@lang('admin.action')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($rates as $rate)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $rate->__('title') ?? '' }}</td>
                                        @isset($rate->room)
                                            <td>{{ $rate->room->__('title') ?? '' }}</td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>{{ $rate->meal->code ?? '' }}</td>
                                        <td>
                                            <form action="{{ route('rates.destroy', $rate) }}" method="post">
                                                <ul>
                                                    <li><a href="{{ route('rates.edit', $rate)
                                            }}"><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></a></li>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="return confirm('Do you want to delete this?');"><img
                                                                src="{{ route('index') }}/img/icons/trash.svg" alt="">
                                                    </button>
                                                </ul>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $rates->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.rates_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
