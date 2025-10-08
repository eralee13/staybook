@extends('layouts.master')

@section('title', 'Ошибка ' . $response->status())

@section('content')
    <style>
        .page{
            padding: 90px 0 200px;
        }
        .btn-wrap{
            margin-top: 40px;
        }
    </style>

    <div class="page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1>@lang('main.error') {{ $response->status() }}</h1>
                    <h4>@lang('main.invalid_request')</h4>
                    <h4>@lang('main.request_resulted'): {{ $response->status() }}</h4>
                    <div class="btn-wrap">
                        <a href="{{ route('index') }}" class="more">@lang('main.please_try_again')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
