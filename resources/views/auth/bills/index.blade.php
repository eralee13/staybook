@extends('auth.layouts.master')

@section('title', __('admin.bills'))

@section('content')

    <div class="page admin bills">
        <div class="container">
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
                                    <div class="col-md-11">
                                        <h4>Реквизиты</h4>
                                    </div>
                                    <div class="col-md-1">
                                        <a href="{{ route('bills.edit', $bill) }}"><img
                                                    src="{{ route('index') }}/img/icons/edit.svg" alt=""></a>
                                    </div>
                                </div>
                                <table>
                                    <tbody>
                                    <tr>
                                        <td>@lang('admin.signed_on')</td>
                                        <td>{{ $bill->created_at->format('d.m.Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Название</td>
                                        <td>{{ $bill->title }}</td>
                                    </tr>
                                    <tr>
                                        <td>Название Банка</td>
                                        <td>{{ $bill->bank_name ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>ИНН</td>
                                        <td>{{ $bill->bank_inn ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Р/с</td>
                                        <td>{{ $bill->bank_account ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>БИК</td>
                                        <td>{{ $bill->bank_bic ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Адрес</td>
                                        <td>{{ $bill->address ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>@lang('admin.files')</td>
                                        <td>
                                            <div class="file"><a target="_blank" href="{{ Storage::url($bill->agreement)
                                    }}">@lang('admin.agreement') StayBook</a></div>
                                            <div class="file"><a target="_blank" href="{{ Storage::url($bill->rules)
                                    }}">StayBook @lang('admin.rules_and_procedures')</a></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('admin.status')</td>
                                        <td>
                                            @if($bill->status==1)
                                                <div class="status"><i
                                                            class="fa-regular fa-check"></i> @lang('admin.active')
                                                </div>
                                            @else

                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
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
