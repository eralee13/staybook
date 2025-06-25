@extends('layouts.filter_mini')

@section('title', __('title'))

@section('content')

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>{{ $page->__('title') }}</h1>
                    {!! $page->__('description') !!}
                </div>
            </div>
        </div>
    </div>

@endsection
