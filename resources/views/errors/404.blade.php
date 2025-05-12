@extends('layouts.head')

@section('title', 'Ошибка 404')

@section('content')

<div class="page page-not">
    <div class="container">
        <div class="col-md-12">
            <div class="text-wrap">
                <h1>{{ __('main.error_title') }}</h1>
                <h4>@lang('main.error_not')</h4>
                <div class="btn-wrap">
                    <a href="{{ route('index') }}">@lang('main.error_back')</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<style>
    .page{
        padding: 150px 0;
    }
    .page-not .text-wrap{
        text-align: center;
    }
    .page-not h4{
        margin: 20px 0 40px
    }
    .page-not a{
        color: #0163b4;
        text-decoration: none;
    }
</style>