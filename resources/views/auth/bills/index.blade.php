@extends('auth.layouts.master')

@section('title', __('admin.bills'))

@section('content')

    <style>
        .admin table td{
            padding: 20px 10px
        }
        .admin table td a{
            color: var(--green);
        }
    </style>
    <div class="page admin bills">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    <h1>@lang('admin.agreements')</h1>
                    @isset($bill)
                        <div class="row" style="margin-top: 40px">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-11 col-10">
                                        <h4>Реквизиты</h4>
                                    </div>
                                    <div class="col-md-1 col-2" style="text-align: center">
                                        <a href="{{ route('bills.edit', $bill) }}"><img
                                                    src="{{ route('index') }}/img/icons/edit.svg" alt="" style="width: 24px"></a>
                                    </div>
                                </div>
                                <div class="table-wrap">
                                    <table>
                                        <tbody>
                                        <tr>
                                            <td style="border-top: none"><b>@lang('admin.signed_on')</b></td>
                                            <td style="border-top: none">{{ $bill->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Название</b></td>
                                            <td>{{ $bill->title }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Название Банка</b></td>
                                            <td>{{ $bill->bank_name ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>ИНН</b></td>
                                            <td>{{ $bill->bank_inn ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Р/с</b></td>
                                            <td>{{ $bill->bank_account ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>БИК</b></td>
                                            <td>{{ $bill->bank_bic ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Адрес</b></td>
                                            <td>{{ $bill->address ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>@lang('admin.files')</b></td>
                                            <td>
                                                <div class="file"><a target="_blank" href="{{ Storage::url($bill->agreement)
                                    }}">@lang('admin.agreement') StayBook</a></div>
                                                <div class="file"><a target="_blank" href="{{ Storage::url($bill->rules)
                                    }}">StayBook @lang('admin.rules_and_procedures')</a></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>@lang('admin.status')</b></td>
                                            <td>@lang('admin.active')</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="btn-wrap" style="margin-top: 20px">
                            <a href="{{ route('bills.create') }}" class="more">@lang('admin.conclude_contact')</a>
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>

@endsection
