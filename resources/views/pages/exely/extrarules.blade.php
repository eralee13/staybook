@extends('layouts.master')

@section('title', 'Extra Stay Rules')

@section('content')

    <div class="pagetitle">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 data-aos="fade-up" data-aos-duration="2000">Extra Stay Rules</h1>
                    <ul class="breadcrumbs">
                        <li><a href="{{route('index')}}">@lang('main.home')</a></li>
                        <li>></li>
                        <li>Extra Stay Rules</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="page property">
        <div class="container">
            <div class="row">
                <div class="col-md-2">
                    @include('pages.exely.sidebar')
                </div>
                <div class="col-md-10">
                    <div class="row">
                        @foreach($rules as $rule)
                            <div class="col-lg-6">
                                <div class="property-item" style="margin-bottom: 120px"">
                                    <ul>
                                        <li>PropertyID: {{ $rule->propertyId }}</li>
                                        <li>Checkin: {{ $rule->checkInTime }}</li>
                                    </ul>
                                    <h5>earlyCheckInPeriods</h5>
                                    <ul>
                                        @foreach($rule->earlyCheckInPeriods as $item)
                                            <li>Start time: {{ $item->startTime }}</li>
                                            <li>End time: {{ $item->endTime }}</li>
                                            <li>Charge Type: {{ $item->chargeType }}</li>
                                            <li>Percent options: %{{ $item->percentOptions->percentage  ?? '' }}</li>
                                            <li>Start time: {{ $item->startTime }}</li>
                                        @endforeach
                                        <li>Checkout: {{ $rule->checkOutTime }}</li>
                                    </ul>
                                    <h5>lateCheckOutPeriods</h5>
                                    <ul>
                                        @foreach($rule->lateCheckOutPeriods as $late)
                                            <li>Start time: {{ $late->startTime }}</li>
                                            <li>End time: {{ $late->endTime }}</li>
                                            <li>Charge Type: {{ $late->chargeType }}</li>
                                            <li>
                                                <ul>
                                                    @foreach($late->rates as $rate)
                                                        <li>{{ $rate->rate }} {{ $rate->currency }}</li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                        @endforeach
                                    </ul>

{{--                                    <li>Rates:--}}
{{--                                        @empty($item->rates)--}}
{{--                                            1`234--}}
{{--                                            <ul>--}}
{{--                                                @foreach($item->rates as $rate)--}}
{{--                                                    <li>{{ $rate->rate }} {{ $rate->currency }}</li>--}}
{{--                                                @endforeach--}}
{{--                                            </ul>--}}
{{--                                        @endisset--}}
{{--                                    </li>--}}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
