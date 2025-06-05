@extends('layouts.head')

@section('title', 'Забронировать')

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">

                        @if($book->api_type == 'tourmind')
                            @include('pages.tourmind.cancel')
                        @else
                            @include('pages.exely.cancel')
                        @endif
                </div>
            </div>
        </div>
    </div>


@endsection
