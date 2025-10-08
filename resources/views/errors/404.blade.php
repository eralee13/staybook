@extends('layouts.master')

@section('title', 'Ошибка 404')

@section('content')

    <style>
        .page {
            padding: 90px 0 200px;
        }

        .btn-wrap {
            margin-top: 40px;
        }
    </style>

    <div class="page page-not" style="padding: 250px 0">
        <div class="container">
            <div class="col-md-12">
                <div class="text-wrap">
                    <h1>@lang('main.error') 404</h1>
                    <div class="btn-wrap">
                        <a href="{{ route('index') }}">@lang('main.please_try_again')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
