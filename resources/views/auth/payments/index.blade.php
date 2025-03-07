@extends('auth.layouts.master')

@section('title', __('admin.payments'))

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <h1>@lang('admin.payments')</h1>
                    @if(!$payments->isEmpty())
                    <table>
                        <tr>
                            <th>@lang('admin.payments')</th>
                            <th>@lang('admin.action')</th>
                        </tr>
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->payments }}</td>
                                <td><a href="{{ route('payments.edit', $payment) }}" class="more"><i class="fa-regular
                                fa-pen-to-square"></i></a></td>
                            </tr>
                        @endforeach
                    </table>
                    @else
                        <div class="btn-wrap" style="margin-top: 20px">
                            <a href="{{ route('payments.create') }}" class="more">@lang('admin.add')</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
