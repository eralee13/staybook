@extends('auth.layouts.master')

@section('title', __('admin.cancellations'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @include('auth.layouts.subroom')
                    <div class="row align-items-center aic">
                        <div class="col-md-7">
                            <h1>Политика отмены</h1>
                        </div>
                        <div class="col-md-5">
                            <div class="btn-wrap">
                                <a class="btn add" href="{{ route('cancellations.create') }}"><i class="fa-solid
                                fa-plus"></i> @lang('admin.add')</a>
                            </div>
                        </div>
                    </div>

                    @if($rules->isNotEmpty())
                        <table class="table">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>@lang('admin.title')</th>
                                <th>Тип штрафа</th>
                                <th>Тариф</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rules as $cancellation)
                                <tr>
                                    <td>{{ $cancellation->id }}</td>
                                    <td>{{ $cancellation->title }}</td>
                                    <td>
                                        @if($cancellation->cancel_policy === 'free_until_checkin')
                                            Бесплатная отмена вплоть до времени заезда
                                        @elseif($cancellation->cancel_policy === 'free_then_penalty')
                                            Бесплатная отмена, а затем отмена со штрафом вплоть до времени заезда
                                        @else
                                            Невозвратный тариф
                                        @endif
                                    </td>
                                    <td>{{ $cancellation->rate->title ?? '' }}</td>
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
                        {{ $rules->links('pagination::bootstrap-4') }}
                    @else
                        <h2 style="text-align: center">@lang('admin.rules_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
