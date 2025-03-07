@extends('auth.layouts.master')

@section('title', __('admin.bills'))

@section('content')

    <div class="page admin bills">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebarbill')
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
                                        <div class="status"><i class="fa-regular fa-check"></i> @lang('admin.active')
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
                                            <li><a class="btn edit" href="{{ route('bills.edit', $bill)
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
