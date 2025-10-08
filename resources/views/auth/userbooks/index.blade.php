@php use Carbon\Carbon; @endphp
@extends('auth.layouts.master')

@section('title', __('admin.bookings'))

@section('content')

    <div class="page admin bookings">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    @include('auth.layouts.sidebar')
                </div>
                <div class="col-md-9">
                    @if($books->isNotEmpty())
                        <h1>@lang('admin.my_bookings')</h1>
                        @foreach($books as $book)
                            <div class="table-wrap">
                                <table>
                                    <tbody>
                                    <tr>
                                        <td style="border-top: none">
                                            <div class="title">@lang('admin.order'):</div>
                                            <div class="value"># {{ $book->id }}</div>
                                        </td>
                                        @if($book->api_type == 'exely')
                                            <td style="border-top: none">
                                                <div class="title"># @lang('admin.order') Exelly</div>
                                                <div class="value">{{ $book->book_token }}</div>
                                            </td>
                                        @else
                                            <td style="border-top: none"></td>
                                        @endif
                                        <td style="border-top: none">
                                            <div class="title">@lang('admin.status'):</div>
                                            @if($book->status == 'Reserved')
                                                <div class="value" style="color: var(--green)">{{ $book->status }}</div>
                                            @else
                                                <div class="value" style="color: #CA6561">{{ $book->status }}</div>
                                            @endif
                                        </td>
                                        <td style="border-top: none">
                                            <div class="title">@lang('admin.guests'):</div>
                                            <div class="value">{{ $book->title }}</div>
                                            {{--                                        <div class="count">{{ $book->count }} @lang('admin.adult')</div>--}}
                                            {{--                                        @if($book->countc > 0)--}}
                                            {{--                                            <div class="count">{{ $book->countc }} @lang('admin.child')</div>--}}
                                            {{--                                        @endif--}}
                                        </td>
                                        <td style="border-top: none">
                                            <div class="title">@lang('admin.count'):</div>
                                            <div class="value">{{ $book->room_count }} @lang('admin.room') {{ $book->adult }} @lang('admin.adult') @if($book->child) {{ $book->child }} дет.@endif</div>
                                        </td>
                                        <td style="border-top: none">
                                            <div class="title">@lang('admin.date_creation'):</div>
                                            @php
                                                $date = \Carbon\Carbon::createFromDate($book->created_at)->format('d.m.Y H:i')
                                            @endphp
                                            <div class="value">{{ $date }}</div>
                                        </td>
                                    </tr>
                                    <tr style="border-top: none">
                                        <td>
                                            <div class="title">@lang('admin.be_paid')</div>
                                            <div class="value">{{ $book->sum }} {{ $book->currency ?? '$' }}</div>
                                        </td>
                                        <td>
                                            <div class="title">@lang('admin.checkin') / @lang('admin.checkout'):</div>
                                            <div class="value">{{ $book->showStartDate() }} - {{ $book->showEndDate() }}</div>
                                        </td>
                                        @php
                                            $hotel = \App\Models\Hotel::where('id', $book->hotel_id)->orWhere('exely_id', $book->hotel_id)->first();
                                        @endphp
                                        @isset($hotel)
                                            <td>
                                                <div class="title">@lang('admin.hotel')</div>
                                                <div class="value">{{ $hotel->__('title') }}</div>
                                            </td>
                                            <td>
                                                <div class="title">@lang('admin.city')</div>
                                                <div class="value">{{ $hotel->__('city') }}</div>
                                            </td>
                                        @else
                                            <td></td>
                                            <td></td>
                                        @endisset
                                        @php
                                            $cancel = \App\Models\CancellationRule::find($book->cancellation_id);
                                        @endphp

                                        @if($cancel)
                                            @php
                                                $timezone = $hotel->timezone ?? config('app.timezone');
                                                $createdAt = Carbon::parse($book->created_at)->timezone($timezone);
                                                $freeLimitDate = $createdAt->copy()->addDays($cancel->free_cancellation_days ?? 0);
                                                $now = Carbon::now($timezone);
                                                $canCancelFree = $now->lessThanOrEqualTo($freeLimitDate);
                                            @endphp
                                            <td>
                                                <div class="title">@lang('admin.rule')</div>
                                                @if($canCancelFree)
                                                    <div class="value">
                                                        @lang('admin.free_cancellation') {{ $freeLimitDate->translatedFormat('d M Y H:i') }} ({{ $timezone }})
                                                    </div>
                                                @else
                                                    <div class="value">
                                                        @lang('admin.cancellation_is_not_avaialble') @lang('admin.cancellation_amount'):
                                                        {{ $book->cancel_penalty }} {{ $book->currency ?? '$' }}
                                                    </div>
                                                @endif
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                        <td>
                                            @if($book->api_type == 'local')
                                                <form action="{{ route('userbooks.cancel_calculate', $book) }}"
                                                      method="post">
                                                    <ul>
                                                        <a href="{{ route('userbooks.show', $book)}}"><img
                                                                    src="{{ route('index') }}/img/icons/eye.svg" style="max-width: 24px"></a>
                                                        @csrf
                                                        @if($book->status == 'Reserved')
                                                            <button onclick="return confirm('Do you want to cancel this?');">
                                                                <img src="{{ route('index') }}/img/cancel.svg" alt=""></button>
                                                        @endif
                                                    </ul>
                                                </form>
                                            @else
                                                <form action="{{ route('userbooks.cancel_calculate_exely', $book) }}"
                                                      method="post">
                                                    <ul>
                                                        <a href="{{ route('userbooks.show', $book)}}"><img
                                                                    src="{{ route('index') }}/img/icons/eye.svg" style="max-width: 24px"></a>
                                                        @csrf
                                                        @if($book->status == 'Reserved')
                                                            <button onclick="return confirm('Do you want to cancel this?');">
                                                                <img src="{{ route('index') }}/img/cancel.svg" alt=""></button>
                                                        @endif
                                                    </ul>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @else
                        <h2 style="text-align: center">@lang('admin.bookings_not_found')</h2>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <style>
        table {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        table tbody {
            margin-bottom: 20px;
        }

        table tr {
            background-color: #fff;
        }

        table td, table th {
            padding: 20px;
            border-color: #f5f5f5;
        }

        .title {
            font-size: 12px;
            opacity: .6;
        }

        .value {
            font-size: 14px;
        }
    </style>

@endsection
