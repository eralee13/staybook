@extends('layouts.filter_mini')

@section('title', 'Об отеле')

@section('content')

    <div class="page about">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12">
                    <h1>{{ $page->title }}</h1>
                    {!! $page->__('description') !!}
                </div>
            </div>
        </div>
    </div>

@endsection
