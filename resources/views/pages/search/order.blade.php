@php use App\Models\Hotel; @endphp
@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h3><a href="search.html"><img src="{{ route('index') }}/img/icons/arrow-left.svg" alt=""></a>
                        Подтвердите и оплатите
                    </h3>
                </div>
            </div>
            @if($request->api_name == 'tourmind')
                @include('pages.tourmind.order', [
                    // 'hotel' => $hotel,
                    // 'request' => $request,
                    // 'tmroom_rates' => $tmroom_rates,
                    'tmimage' => $request->tmimage,
                    // 'meals' => $meals
                ])
            @else
             @include('pages.exely.order')
            @endif
        </div>
    </div>

    <style>
        #phone {
            padding-left: 50px;
        }
    </style>

@endsection