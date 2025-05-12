@extends('auth.layouts.master')

@section('title', __('admin.plans_and_rules'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-2">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-10">
                    @include('auth.layouts.subroom')
                    <h1>@lang('admin.plans')</h1>
                    @if($rates->isNotEmpty())
                        <table class="table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.title')</th>
                                <th>@lang('admin.room')</th>
                                <th>@lang('admin.food')</th>
                                <th>@lang('admin.cancellations')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rates as $rate)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $rate->__('title') }}</td>
                                    <td>{{ $rate->room->title }}</td>
                                    <td>{{ $rate->meal->code }}</td>
                                    <td>{{ $rate->cancellationRule->title }}</td>
                                    <td>
                                        <form action="{{ route('rates.destroy', $rate) }}" method="post">
                                            <ul>
                                                <li><a href="{{ route('rates.edit', $rate)
                                            }}"><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></a></li>
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="return confirm('Do you want to delete this?');"
                                                        class="btn delete"><img src="{{ route('index') }}/img/icons/trash.svg" alt=""></button>
                                            </ul>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $rates->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">Тарифы не найдены</h2>
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
