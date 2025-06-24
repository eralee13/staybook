@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')

    <div class="page order">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    @if( $request->api_name == 'tourmind' )
                        @include('pages.tourmind.rezerve', ['request' => $request])
                    @else
                        @include('pages.exely.rezerve', ['request' => $request])
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .page #map {
            margin-top: 20px;
        }

        .page i {
            color: darkblue;
        }

        .page form {
            margin-top: 50px;
        }

        .page form button {
            width: auto;
            padding: 10px 30px;
            margin-left: 10px;
        }
    </style>

@endsection
