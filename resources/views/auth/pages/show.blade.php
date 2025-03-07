@extends('auth.layouts.master')

@section('title', 'Страница ' . $page->title)

@section('content')

    <div class="page admin">
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    <h1>Страница {{ $page->title }}</h1>
                    {!! $page->description !!}
                </div>
            </div>
        </div>
    </div>

@endsection
