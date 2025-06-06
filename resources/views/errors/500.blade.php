@extends('layouts.head')

@section('title', 'Ошибка 500')

@section('content')

    <div class="page about">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>@lang('main.error') {{ $response->status() }}</h1>
                    <h4>@lang('main.failed_api')</h4>
                    <h4>@lang('main.request_resulted'): {{ $response->status() }}</h4>
                    <div class="btn-wrap">
                        <a href="{{ route('index') }}">@lang('main.please_try_again')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
