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
                    @if(!$bills->isEmpty())
                        <table>
                            <tr>
                                <th>#</th>
                                <th>@lang('admin.signed_on')</th>
                                <th>@lang('admin.status')</th>
                                <th>@lang('admin.company')</th>
                                <th>@lang('admin.files')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                            <tbody>
                            @foreach($bills as $bill)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $bill->created_at->format('d.m.Y') }}</td>
                                    <td>
                                        @if($bill->status==1)
                                            <div class="status"><i
                                                        class="fa-regular fa-check"></i> @lang('admin.active')
                                            </div>
                                        @else

                                        @endif
                                    </td>
                                    <td>{{ $bill->title }}</td>
                                    <td>
                                        <div class="file"><a target="_blank" href="{{ Storage::url($bill->agreement)
                                    }}">@lang('admin.agreement') StayBook</a></div>
                                        <div class="file"><a target="_blank" href="{{ Storage::url($bill->rules)
                                    }}">StayBook @lang('admin.rules_and_procedures')</a></div>
                                    </td>
                                    <td>
                                        <form action="{{ route('bills.destroy', $bill) }}" method="post">
                                            <ul>
                                                <li><a href="{{ route('bills.edit', $bill)
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

                        <div class="row" style="margin-top: 40px">
{{--                            <div class="col-md-6">--}}
{{--                                <h4>Данные о компании</h4>--}}
{{--                                <table>--}}
{{--                                    <tr>--}}
{{--                                        <td></td>--}}
{{--                                        <td></td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td></td>--}}
{{--                                        <td></td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td></td>--}}
{{--                                        <td></td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td></td>--}}
{{--                                        <td></td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td></td>--}}
{{--                                        <td></td>--}}
{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td></td>--}}
{{--                                        <td></td>--}}
{{--                                    </tr>--}}
{{--                                </table>--}}
{{--                            </div>--}}
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-11">
                                        <h4>Реквизиты</h4>
                                    </div>
                                    <div class="col-md-1">
                                        <a href="{{ route('bills.edit', $bill) }}"><img src="{{ route('index') }}/img/icons/edit.svg" alt=""></a>
                                    </div>
                                </div>
                                <table>
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
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="btn-wrap" style="margin-top: 20px">
                            <a href="{{ route('bills.create') }}" class="more">@lang('admin.conclude_contact')</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
